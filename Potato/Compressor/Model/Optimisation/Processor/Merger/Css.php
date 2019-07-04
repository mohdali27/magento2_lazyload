<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Merger;

use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Helper\File as FileHelper;
use Potato\Compressor\Helper\Css as CssHelper;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Model\Config;
use Potato\Compressor\Helper\Log as LogHelper;
use Magento\Framework\App\RequestInterface;
use Potato\Compressor\Helper\HtmlParser;

/**
 * Class Css
 */
class Css extends AbstractMerger
{
    /** @var DataHelper  */
    protected $dataHelper;

    /** @var CssHelper  */
    protected $cssHelper;

    /** @var Config  */
    protected $config;

    /** @var RequestInterface  */
    protected $request;

    /**
     * Css constructor.
     * @param FileHelper $fileHelper
     * @param DataHelper $dataHelper
     * @param LogHelper $logHelper
     * @param CssHelper $cssHelper
     * @param Config $config
     * @param RequestInterface $request
     */
    public function __construct(
        FileHelper $fileHelper,
        DataHelper $dataHelper,
        LogHelper $logHelper,
        CssHelper $cssHelper,
        Config $config,
        RequestInterface $request
    ) {
        parent::__construct($fileHelper, $logHelper);
        $this->dataHelper = $dataHelper;
        $this->cssHelper = $cssHelper;
        $this->config = $config;
        $this->request = $request;
    }
    
    /**
     * @param string[] $files
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function mergeFileList($files)
    {
        $isSecure = $this->request->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->dataHelper->getRootCachePath() . DIRECTORY_SEPARATOR . $mergerDir;
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, $this->config->getFolderPermission());
        }
        if (!is_writeable($targetDir)) {
            return null;
        }

        // base hostname & port
        $baseMediaUrl = $this->dataHelper->getRootCacheUrl($isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }
        // merge into target file
        $targetFilename = md5($this->getMergeFilename($files) . "|{$hostname}|{$port}") . '.css';
        $mergeFilesResult = $this->mergeFiles(
            $files,
            $targetDir . DIRECTORY_SEPARATOR . $targetFilename,
            false,
            array($this, 'beforeMergeCss'),
            'css'
        );
        if ($mergeFilesResult) {
            @chmod($targetDir . DIRECTORY_SEPARATOR . $targetFilename, $this->config->getFilePermission());
            return $baseMediaUrl . '/' . $mergerDir . '/' . $targetFilename;
        }
        return null;
    }

    /**
     * Before merge css callback function
     *
     * @param string $file
     * @param string $content
     * @return string
     */
    public function beforeMergeCss($file, $content)
    {
        return $this->cssHelper->unifyUrlInCssContent($file, $content);
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
        if (array_key_exists('href', $attributes)) {
            return $attributes['href'];
        }
        $dir = $this->dataHelper->getRootCachePath()
            . DIRECTORY_SEPARATOR . 'css'
            . DIRECTORY_SEPARATOR . 'inline'
        ;
        return $this->fileHelper->createFileByContent(
            HtmlParser::getContentFromTag($tag->getContent()),
            $dir,
            'css'
        );
    }
}
