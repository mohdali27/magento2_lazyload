<?php

namespace Potato\ImageOptimization\Api;

use Potato\ImageOptimization\Api\Data\ImageInterface;

/**
 * @api
 */
interface UtilityInterface
{
    /**
     * @param ImageInterface $image
     * @return ImageInterface
     */
    public function optimize(ImageInterface &$image);
}
