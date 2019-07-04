<?php

namespace Potato\ImageOptimization\Model\Image;

use Potato\ImageOptimization\Model\Image\Gif as GifImage;
use Potato\ImageOptimization\Model\Image\Jpeg as JpegImage;
use Potato\ImageOptimization\Model\Image\Png as PngImage;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Fabric
 */
class Fabric
{
    /** @var ObjectManagerInterface  */
    protected $objectManager;

    /**
     * Fabric constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $imageType
     * @return null|GifImage|JpegImage|PngImage
     */
    public function getOptimizationWorkerByType($imageType)
    {
        $worker = null;
        switch ($imageType) {
            case GifImage::IMAGE_TYPE:
                $worker = $this->objectManager->create(GifImage::class);
                break;
            case PngImage::IMAGE_TYPE:
                $worker = $this->objectManager->create(PngImage::class);
                break;
            case JpegImage::IMAGE_TYPE:
                $worker = $this->objectManager->create(JpegImage::class);
                break;
        }
        return $worker;
    }
}