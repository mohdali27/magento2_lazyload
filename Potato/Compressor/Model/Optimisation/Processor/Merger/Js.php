<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Merger;

use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Helper\File as FileHelper;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Model\Config;
use Potato\Compressor\Helper\Log as LogHelper;
use Magento\Framework\App\RequestInterface;
use Potato\Compressor\Helper\HtmlParser;

/**
 * Class Css
 */
class Js extends AbstractMerger
{
    /** @var DataHelper  */
    protected $dataHelper;

    /** @var Config  */
    protected $config;

    /** @var RequestInterface  */
    protected $request;

    /**
     * Css constructor.
     * @param DataHelper $dataHelper,
     * @param FileHelper $fileHelper
     * @param LogHelper $logHelper
     * @param Config $config
     * @param RequestInterface $request
     */
    public function __construct(
        DataHelper $dataHelper,
        FileHelper $fileHelper,
        LogHelper $logHelper,
        Config $config,
        RequestInterface $request
    ) {
        parent::__construct($fileHelper, $logHelper);
        $this->dataHelper = $dataHelper;
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @param string[] $files
     *
     * @return null|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function mergeFileList($files)
    {
        $isSecure = $this->request->isSecure();
        $targetFilename = md5($this->getMergeFilename($files)) . '.js';
        $targetDir = $this->dataHelper->getRootCachePath() . DIRECTORY_SEPARATOR . 'js';
        $resultUrl = $this->dataHelper->getRootCacheUrl($isSecure)  . '/js/'
            . $targetFilename
        ;
        if (file_exists($targetDir . DIRECTORY_SEPARATOR . $targetFilename)) {
            return $resultUrl;
        }
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, $this->config->getFolderPermission());
        }
        if (!is_writeable($targetDir)) {
            return null;
        }
        $mergeFilesResult = $this->mergeFiles(
            $files,
            $targetDir . DIRECTORY_SEPARATOR . $targetFilename,
            false,
            array($this, 'beforeMergeJs'),
            'js'
        );
        if ($mergeFilesResult) {
            @chmod($targetDir . DIRECTORY_SEPARATOR . $targetFilename, $this->config->getFilePermission());
            return $resultUrl;
        }
        return null;
    }

    /**
     * @param string $file
     * @param string $contents
     *
     * @return string
     */
    public function beforeMergeJs($file, $contents)
    {
        return $contents. ';';
    }

    /**
     * @param Tag $tag
     *
     * @return string
     * @throws \Exception
     */
    protected function getPathFromTag($tag)
    {
        $attributes = $tag->getAttributes();
        if (array_key_exists('src', $attributes)) {
            return $attributes['src'];
        }
        $dir = $this->dataHelper->getRootCachePath()
            . DIRECTORY_SEPARATOR . 'js'
            . DIRECTORY_SEPARATOR . 'inline'
        ;
        return $this->fileHelper->createFileByContent(
            HtmlParser::getContentFromTag($tag->getContent()),
            $dir,
            'js'
        );
    }

    /**
     * @param array $fileList
     *
     * @return string
     */
    protected function getMergeFilename($fileList)
    {
        $result = [];
        foreach ($fileList as $filename) {
            if (strpos($filename, $this->dataHelper->getRootCachePath()) === 0 ||
                //too many issues when requirejs-config.js was updated after each web request
                strpos($filename, 'requirejs-config.js') !== False
            ) {
                $result[] = $filename;
            } else {
                $timestamp = filemtime(realpath($filename));
                $result[] = $filename . '+' . $timestamp;
            }
        }
        return implode(',', $result);
    }
}
