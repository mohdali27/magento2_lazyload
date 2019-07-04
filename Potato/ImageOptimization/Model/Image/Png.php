<?php

namespace Potato\ImageOptimization\Model\Image;

use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Model\App;
use Potato\ImageOptimization\Api\OptimizationInterface;

/**
 * Class Png
 */
class Png implements OptimizationInterface
{
    const IMAGE_TYPE = 'image/png';
    
    /** @var Png\Pngquant  */
    protected $pngManager;

    /**
     * Png constructor.
     * @param Png\Optipng $pngManager
     */
    public function __construct(
        Png\Optipng $pngManager
    ) {
        $this->pngManager = $pngManager;
    }
    
    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Exception
     */
    public function optimize(ImageInterface &$image)
    {
        return $this->pngManager->optimize($image);
    }
}
