<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Minifier;

use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Helper\File as FileHelper;
use Potato\Compressor\Helper\Data as DataHelper;
use Magento\Framework\App\RequestInterface;


abstract class AbstractMinifier
{

    protected $fileHelper;
    
    protected $dataHelper;

    /** @var  RequestInterface */
    protected $request;
    
    public function __construct(
        FileHelper $fileHelper,
        DataHelper $dataHelper,
        RequestInterface $request
    ) {
        $this->fileHelper = $fileHelper;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
    }
    
    /**
     * @param Tag $tag
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function minify($tag)
    {
        $urlToFile = $this->getPathFromTag($tag);
        if (!$this->fileHelper->isInternalUrl($urlToFile)) {
            return $urlToFile;
        }
        $file = realpath(
            $this->getLocalPath($urlToFile)
        );
        if (FALSE === $file) {
            return null;
        }
        $targetFilename = $this->getTargetFilename($file);
        $targetFile = $this->dataHelper->getRootCachePath() . DIRECTORY_SEPARATOR . $targetFilename;
        if (file_exists($targetFile)) {
            $timeOfCurrentFile = filemtime($file);
            $timeOfNewFile = filemtime($targetFile);
            if (FALSE !== $timeOfCurrentFile && FALSE !== $timeOfNewFile
                && $timeOfCurrentFile < $timeOfNewFile
            ) {
                return $this->dataHelper->getRootCacheUrl($this->request->isSecure()) . '/' . $targetFilename;
            }
        }
        $content = file_get_contents($file);
        $content = $this->beforeMinifyFile($file, $content);
        $resultContent = $this->minifyContent($content);
        if (strlen(trim($resultContent)) === 0) {
            return null;
        }
        $this->fileHelper->putContentInFile($resultContent, $targetFile);
        return $this->dataHelper->getRootCacheUrl($this->request->isSecure()) . '/' . $targetFilename;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    abstract public function minifyContent($content);

    /**
     * @param string $file
     *
     * @return string
     */
    abstract protected function getTargetFilename($file);

    /**
     * @param Tag $tag
     *
     * @return string
     */
    abstract protected function getPathFromTag($tag);

    /**
     * @param string $url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getLocalPath($url)
    {
        return $this->fileHelper->getLocalPathFromUrl($url);
    }

    /**
     * @param string $file
     * @param string $content
     *
     * @return string
     */
    protected function beforeMinifyFile($file, $content)
    {
        return $content;
    }
}
