<?php

namespace Potato\ImageOptimization\Model\Image;

use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Api\OptimizationInterface;

/**
 * Class Jpeg
 */
class Jpeg implements OptimizationInterface
{
    const IMAGE_TYPE = 'image/jpeg';
    
    /** @var Jpeg\Jpegoptim  */
    protected $jpegManager;
    
    /**
     * Jpeg constructor.
     * @param Jpeg\Jpegoptim $jpegManager
     */
    public function __construct(
        Jpeg\Jpegoptim $jpegManager
    ) {
        $this->jpegManager = $jpegManager;
    }
    
    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Exception
     */
    public function optimize(ImageInterface &$image)
    {
        return $this->jpegManager->optimize($image);
    }
}
