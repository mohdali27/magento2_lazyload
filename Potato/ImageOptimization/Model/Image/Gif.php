<?php

namespace Potato\ImageOptimization\Model\Image;

use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Potato\ImageOptimization\Api\OptimizationInterface;

/**
 * Class Gif
 */
class Gif implements OptimizationInterface
{
    const IMAGE_TYPE = 'image/gif';
    
    /** @var Png\Optipng  */
    protected $pngManager;

    /** @var Gif\Gifsicle  */
    protected $gifManager;
    
    /**
     * Gif constructor.
     * @param Png\Optipng $pngManager
     * @param Gif\Gifsicle $gifManager
     */
    public function __construct(
        Png\Optipng $pngManager,
        Gif\Gifsicle $gifManager
    ) {
        $this->pngManager = $pngManager;
        $this->gifManager = $gifManager;
    }

    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Exception
     */
    public function optimize(ImageInterface &$image)
    {
        if (!$this->isAnimatedGif($image)) {
            $pngFileName = dirname($image->getPath())
                . DIRECTORY_SEPARATOR . basename($image->getPath(), ".gif") . '.png';
            if (file_exists($pngFileName)) {
                //after optimization img may be renamed to .png -> need do backup if same file already exists
                rename($pngFileName, $pngFileName . '_tmp');
            }
            $image = $this->pngManager->optimize($image);
            if (file_exists($pngFileName)) {
                rename($pngFileName, $image->getPath());
            }
            if (file_exists($pngFileName . '_tmp')) {
                //restore previously renamed image
                rename($pngFileName . '_tmp', $pngFileName);
            }
            return $image;
        }
        return $this->gifManager->optimize($image);
    }

    /**
     * @param ImageInterface $image
     * @return bool
     */
    protected function isAnimatedGif(ImageInterface &$image)
    {
        $imagePath = $image->getPath();
        if (!is_readable($imagePath)) {
            $image->setErrorType(ErrorSource::APPLICATION);
            throw new \Exception(__('The file is not readable'));
        }
        $content = file_get_contents($imagePath);
        $strLoc = 0;
        $count = 0;

        // There is no point in continuing after we find a 2nd frame
        while ($count < 2) {
            $where1 = strpos($content, "\x00\x21\xF9\x04", $strLoc);
            if ($where1 === false) {
                break;
            }
            $str_loc = $where1 + 1;
            $where2  = strpos($content, "\x00\x2C", $str_loc);
            if ($where2 === false) {
                break;
            } else {
                if ($where1 + 8 == $where2) {
                    $count++;
                }
                $strLoc = $where2 + 1;
            }
        }
        // gif is animated when it has two or more frames
        return ($count >= 2);
    }
}
