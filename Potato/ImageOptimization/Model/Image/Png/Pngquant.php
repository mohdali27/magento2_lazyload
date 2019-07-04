<?php

namespace Potato\ImageOptimization\Model\Image\Png;

use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Image\AbstractUtility;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;

/**
 * Class Pngquant
 */
class Pngquant extends AbstractUtility
{
    const LIB_PATH = 'pngquant';
    const DEFAULT_OPTIONS = '--quality=80-100';

    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Exception
     */
    public function optimize(ImageInterface &$image)
    {
        if ($this->config->canPngUseService()) {
            return $this->sendToService($image);
        }
        $beforeFilesize = filesize($image->getPath());
        $command = $this->getLibPath(self::LIB_PATH)
            . ' ' . self::DEFAULT_OPTIONS
            . ' -f -o ' . escapeshellarg($image->getPath())
            . ' ' . escapeshellarg($image->getPath())
            . ' 2>&1'
        ;
        exec(
            $command,
            $result, $status
        );
        clearstatcache($image->getPath());
        $afterFilesize = filesize($image->getPath());
        if ($status == 98 || $status == 99) {//skip statuses
            $image
                ->setStatus(StatusSource::STATUS_OPTIMIZED)
                ->setResult(__("%1 bytes -> %2 bytes optimized", $beforeFilesize, $afterFilesize));
            return $image;
        }
        if ($status != 0 && ($status != 98 && $status != 99)) {
            $image->setErrorType(ErrorSource::APPLICATION);
            $resultAsString = join("\n", $result);
            throw new \Exception('Status ' . $status . ' : ' . $resultAsString . '. Current script owner: ' . get_current_user() . '. Command:' . $command);
        }

        $image
            ->setStatus(StatusSource::STATUS_OPTIMIZED)
            ->setResult(__("%1 bytes -> %2 bytes optimized", $beforeFilesize, $afterFilesize));
        return $image;
    }
}
