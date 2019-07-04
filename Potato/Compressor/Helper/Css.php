<?php
namespace Potato\Compressor\Helper;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Potato\Compressor\Helper\File as FileHelper;
use Potato\Compressor\Helper\Image as ImageHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Url\CssResolver;
use Potato\Compressor\Model\Config;

class Css
{
    /** @var null|string */
    protected $callbackFileDir = null;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var File  */
    protected $fileHelper;
    
    /** @var Image  */
    protected $imageHelper;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var Config */
    protected $config;

    /**
     * @param Filesystem $filesystem
     * @param File $fileHelper
     * @param Image $imageHelper
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        Filesystem $filesystem,
        FileHelper $fileHelper,
        ImageHelper $imageHelper,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->filesystem = $filesystem;
        $this->fileHelper = $fileHelper;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function inlineImagesByContent($content)
    {
        $cssUrl = '/url\\(\\s*(?![\'"]{0,1}data:)([^\\)\\s]+)\\s*\\)?/';
        $content = preg_replace_callback($cssUrl, [$this, 'cssInlineImageCallback'], $content);
        return $content;
    }

    /**
     * @param string $file
     * @param string $content
     *
     * @return string
     */
    public function unifyUrlInCssContent($file, $content)
    {
        $baseDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        if (strpos($file, $baseDir) === 0) {
            $file = substr_replace($file, '', 0, strlen($baseDir));
            $file = ltrim($file, '/' . DIRECTORY_SEPARATOR);
        }
        $this->callbackFileDir = str_replace(DIRECTORY_SEPARATOR, '/', dirname($file));

        $cssImport = '/@import\\s+([\'"])(.*?)[\'"]/';
        $content = preg_replace_callback($cssImport, array($this, 'cssMergerImportCallback'), $content);

        $cssUrl = CssResolver::REGEX_CSS_RELATIVE_URLS;
        $content = preg_replace_callback($cssUrl, array($this, 'cssMergerUrlCallback'), $content);

        return $content;
    }

    /**
     * Callback function replaces relative links for url() matches in css file
     *
     * @param array $match
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function cssInlineImageCallback($match)
    {
        $quote = ($match[1][0] == "'" || $match[1][0] == '"') ? $match[1][0] : '';
        $uri = ($quote == '') ? $match[1] : substr($match[1], 1, strlen($match[1]) - 2);
        $defaultResult = "url({$quote}{$uri}{$quote})";
        if (!preg_match('/^https?:/i', $uri) || !$this->fileHelper->isInternalUrl($uri)) {
            return $defaultResult;
        }
        $path = $this->fileHelper->getLocalPathFromUrl($uri);
        if (!file_exists($path)) {
            return $defaultResult;
        }
        $mimeType = $this->fileHelper->getMimeType($path);
        if (strpos($mimeType, 'image/') === 0 && strpos($mimeType, 'font/') === 0
            && $mimeType !== 'application/octet-stream') {
            return $defaultResult;
        }
        $fileContent = file_get_contents($path);
        $inlineContent = 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
        $limit = $this->config->getImageMergeCSSMaxFileSizeInBytes();
        if (is_int($limit) && $limit < strlen($inlineContent)) {
            return $defaultResult;
        }
        return "url({$quote}{$inlineContent}{$quote})";
    }

    /**
     * Callback function replaces relative links for @import matches in css file
     *
     * @param array $match
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function cssMergerImportCallback($match)
    {
        $quote = $match[1];
        $uri = $this->prepareUrl($match[2]);

        return "@import {$quote}{$uri}{$quote}";
    }

    /**
     * Callback function replaces relative links for url() matches in css file
     *
     * @param array $match
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function cssMergerUrlCallback($match)
    {
        $quote = ($match[1][0] == "'" || $match[1][0] == '"') ? $match[1][0] : '';
        $uri = ($quote == '') ? $match[1] : substr($match[1], 1, strlen($match[1]) - 2);
        $uri = $this->prepareUrl($uri);

        return "url({$quote}{$uri}{$quote})";
    }

    /**
     * Prepare url for css replacement
     *
     * @param string $uri
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function prepareUrl($uri)
    {
        // check absolute or relative url
        if (!preg_match('/^https?:/i', $uri) && !preg_match('/^\//i', $uri)) {
            $fileDir = '';
            $pathParts = explode('/', $uri);
            $fileDirParts = explode('/', $this->callbackFileDir);
            $store = $this->storeManager->getStore();
            if (count($fileDirParts) > 1 && 'media' == $fileDirParts[1]) {
                $baseUrl = $this->storeManager->getStore()->getBaseUrl(
                    UrlInterface::URL_TYPE_MEDIA, $store->isCurrentlySecure()
                );
                $fileDirParts = array_slice($fileDirParts, 2);
            }
            else if (count($fileDirParts) > 1 && 'static' == $fileDirParts[1]) {
                $baseUrl = $this->storeManager->getStore()->getBaseUrl(
                    UrlInterface::URL_TYPE_STATIC, $store->isCurrentlySecure()
                );
                $fileDirParts = array_slice($fileDirParts, 2);
            } else {
                $baseUrl = $this->storeManager->getStore()->getBaseUrl(
                    UrlInterface::URL_TYPE_WEB, $store->isCurrentlySecure()
                );
            }
            foreach ($pathParts as $key=>$part) {
                if ($part == '.' || $part == '..') {
                    unset($pathParts[$key]);
                }
                if ($part == '..' && count($fileDirParts)) {
                    $fileDirParts = array_slice($fileDirParts, 0, count($fileDirParts) - 1);
                }
            }

            if (count($fileDirParts)) {
                $fileDir = implode('/', $fileDirParts).'/';
            }
            $uri = $baseUrl . $fileDir . implode('/', $pathParts);
        }
        return $uri;
    }
}
