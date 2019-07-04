<?php
namespace Potato\Compressor\Helper;

use Potato\Compressor\Helper\File as FileHelper;

/**
 * Class Image
 */
class Image
{
    /** @var File  */
    protected $fileHelper;

    /**
     * Image constructor.
     * @param File $fileHelper
     */
    public function __construct(
        FileHelper $fileHelper
    ) {
        $this->fileHelper = $fileHelper;
    }
    
    /**
     * @param string $content
     * @param string $type
     *
     * @return string
     */
    public function getInlineImageByContent($content, $type)
    {
        return 'data:' . $type . ';base64,' . base64_encode($content);
    }

    /**
     * @param string $path
     *
     * @return string|null
     * @throws \Exception
     */
    public function getInlineImageByPath($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        if (!$this->isImagePath($path)) {
            return null;
        }
        $fileContent = file_get_contents($path);
        $mimeType = $this->fileHelper->getMimeType($path);
        return $this->getInlineImageByContent($fileContent, $mimeType);
    }

    /**
     * @param string $url
     *
     * @return string|null
     * @throws \Exception
     */
    public function getInlineImageByUrl($url)
    {
        $path = $this->fileHelper->getLocalPathFromUrl($url);
        return $this->getInlineImageByPath($path);
    }

    /**
     * @param string $path
     *
     * @return bool
     * @throws \Exception
     */
    public function isImagePath($path)
    {
        $mimeType = $this->fileHelper->getMimeType($path);
        return strpos($mimeType, 'image/') === 0;
    }

    /**
     * @param string $url
     *
     * @return bool
     * @throws \Exception
     */
    public function isImageUrl($url)
    {
        $path = $this->fileHelper->getLocalPathFromUrl($url);
        return $this->isImagePath($path);
    }
}
