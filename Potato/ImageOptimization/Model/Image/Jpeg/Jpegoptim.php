<?php

namespace Potato\ImageOptimization\Model\Image\Jpeg;

use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Potato\ImageOptimization\Model\Image\AbstractUtility;

/**
 * Class Jpegoptim
 */
class Jpegoptim extends AbstractUtility
{
    const LIB_PATH = 'jpegoptim';
    const DEFAULT_OPTIONS = '-f -o --strip-all --strip-icc --strip-iptc';

    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Exception
     */
    public function optimize(ImageInterface &$image)
    {
        if ($this->config->canJpgUseService()) {
            return $this->sendToService($image);
        }
        $beforeFilesize = filesize($image->getPath());
        $command = $this->getLibPath(self::LIB_PATH) . ' '
            . self::DEFAULT_OPTIONS . ' '
            . $this->config->getCompressionLevel() . ' "' . $image->getPath() . '" 2>&1'
        ;
        exec(
            $command,
            $result,
            $error
        );
        $stringResult = implode(' ', $result);
        if (empty($result) || $error != 0) {
            $image->setErrorType(ErrorSource::APPLICATION);
            throw new \Exception(__('Application for JP(E)G files optimization returns the error. Error code: %1 %2. Current script owner: %3. Command: %4',
                $error, $stringResult, get_current_user(), $command));
        }
        clearstatcache($image->getPath());
        $afterFilesize = filesize($image->getPath());
        $image
            ->setStatus(StatusSource::STATUS_OPTIMIZED)
            ->setResult(__("%1 bytes -> %2 bytes optimized", $beforeFilesize, $afterFilesize));
        return $image;
    }
}
