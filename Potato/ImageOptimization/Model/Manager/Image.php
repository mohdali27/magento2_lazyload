<?php

namespace Potato\ImageOptimization\Model\Manager;

use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Image\Fabric;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Potato\ImageOptimization\Logger\Logger;

/**
 * Class Image
 */
class Image
{
    const DEFAULT_BACKUP_FOLDER_NAME = 'po_image_optimization_original_images';
    const TEMP_FOLDER_NAME = 'po_image_optimization_temp_images';

    /** @var Config  */
    protected $config;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var ImageRepositoryInterface  */
    protected $imageRepository;

    /** @var Fabric  */
    protected $imageFabric;

    /** @var Logger  */
    protected $logger;

    /**
     * Image constructor.
     * @param ImageRepositoryInterface $imageRepository
     * @param Config $config
     * @param Filesystem $filesystem
     * @param Fabric $imageFabric
     * @param Logger $logger
     */
    public function __construct(
        ImageRepositoryInterface $imageRepository,
        Config $config,
        Filesystem $filesystem,
        Fabric $imageFabric,
        Logger $logger
    ) {
        $this->imageRepository = $imageRepository;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->imageFabric = $imageFabric;
        $this->logger = $logger;
    }

    /**
     * @param ImageInterface $image
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Exception
     */
    public function optimizeImage(ImageInterface $image)
    {
        $imagePath = $image->getPath();
        if (!is_readable($image->getPath())) {
            if (!file_exists($image->getPath())) {
                $this->imageRepository->delete($image);
            } else {
                $result = __("Can't read the file. Please check the file permissions.");
                $this->changeStatus(
                    $image, StatusSource::STATUS_ERROR, $result, filemtime($imagePath), ErrorSource::IS_NOT_READABLE
                );
            }
            return $this;
        }
        $excludeDirs = $this->config->getExcludeDirs();
        foreach ($excludeDirs as $excludeDir) {
            $localPath = substr_replace($imagePath, '', 0, strlen(BP));
            if (strpos($localPath, DIRECTORY_SEPARATOR . $excludeDir . DIRECTORY_SEPARATOR) !== false
                && $image->getStatus() != StatusSource::STATUS_OPTIMIZED
            ) {
                //remove item if local path has excluded dir
                $this->imageRepository->delete($image);
                return $this;
            }
        }
        $result = $this->backupImage($imagePath);
        if (false === $result) {
            $result = __("Can't create a backup of images. Please check the permissions of files and folders.");
            $this->changeStatus(
                $image, StatusSource::STATUS_ERROR, $result, filemtime($imagePath), ErrorSource::BACKUP_CREATION
            );
            return $this;
        }
        $imageType = $this->imageRepository->getImageType($image->getPath());
        $optimizationWorker = $this->imageFabric->getOptimizationWorkerByType($imageType);
        if (null === $optimizationWorker) {
            $result = __("Unsupported image type. Only images of PNG, JP(E)G and GIF types are supported.");
            $this->changeStatus(
                $image, StatusSource::STATUS_ERROR, $result, filemtime($imagePath), ErrorSource::UNSUPPORTED_IMAGE
            );
            return $this;
        }
        $originalFileSize = filesize($imagePath);
        if (!$this->createTempFile($imagePath)) {
            $result = __("Temp file can't be created. Please check the permissions of files and folders.");
            $this->changeStatus(
                $image, StatusSource::STATUS_ERROR, $result, filemtime($imagePath), ErrorSource::TEMP_CREATION
            );
            return $this;
        }
        try {
            $image = $optimizationWorker->optimize($image);
        } catch (\Exception $e) {
            $this->changeStatus($image, StatusSource::STATUS_ERROR, $e->getMessage(), filemtime($imagePath));
        }
        clearstatcache($imagePath);
        $optimizedFileSize = filesize($imagePath);
        if ((FALSE !== $originalFileSize && FALSE !== $optimizedFileSize && $optimizedFileSize > $originalFileSize)
            || $image->getStatus() == StatusSource::STATUS_ERROR
        ) {
            $rollbackResult = $this->rollbackTempFile($imagePath);
            if (!$rollbackResult) {
                $tempFile = $this->getTempFilePath($imagePath);
                $result = __("Can't restore the temp file.
                    Please check the permissions of file and folders.
                    Then restore file from folder %1 and run image optimization again.", $tempFile);
                $this->changeStatus(
                    $image, StatusSource::STATUS_ERROR, $result, filemtime($imagePath), ErrorSource::TEMP_CREATION
                );
                return $this;
            }
            if ($image->getStatus() !== StatusSource::STATUS_ERROR) {
                $image->setResult(__("%1 bytes -> %1 bytes", $originalFileSize));                
            }
        }
        $this->removeTempFile($imagePath);
        $image->setTime(filemtime($imagePath));
        $this->imageRepository->save($image);
        return $this;
    }

    /**
     * @param ImageInterface $image
     * @param string $status
     * @param string $result
     * @param null|string $time
     * @param null|string $errorType
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function changeStatus(ImageInterface $image, $status, $result, $time = null, $errorType = null)
    {
        $image
            ->setStatus($status)
            ->setErrorType($errorType)
            ->setResult($result)
            ->setTime($time)
        ;
        $this->imageRepository->save($image);
    }

    /**
     * @param ImageInterface $image
     * @return $this
     * @throws \Exception
     */
    public function restoreImage(ImageInterface $image)
    {
        $backupImg = $this->getBackupImagePath($image->getPath());
        $result = false;
        if ($backupImg && is_readable($backupImg)) {
            $content = file_get_contents($backupImg);
            $result = file_put_contents($image->getPath(), $content);
        }
        if (false === $result) {
            throw new \Exception(__("Can't restore the backup. Please check the permissions of file and folders."));
        }
        $result = __("The image has been restored");
        $this->changeStatus($image, StatusSource::STATUS_SKIPPED, $result, filemtime($image->getPath()));
        return $this;
    }

    /**
     * @param string $image
     * @return string
     */
    public function getBackupImagePath($image)
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
        $path .= self::DEFAULT_BACKUP_FOLDER_NAME . DIRECTORY_SEPARATOR
            . trim(str_replace(BP, '', $image), DIRECTORY_SEPARATOR);
        return $path;
    }

    /**
     * @param $image
     * @return bool
     * @throws \Exception
     */
    protected function backupImage($image)
    {
        if (!$this->config->isAllowImageBackup()) {
            //if backup is not enabled in system configuration continue optimization
            return true;
        }
        $path = str_replace(BP . DIRECTORY_SEPARATOR, '', $this->getBackupImagePath($image));
        $result = false;
        if (!isset($path)) {
            return $result;
        }

        $rootPath = BP;
        if (is_readable($rootPath . DIRECTORY_SEPARATOR . $path)) {
            //backup exist and readable
            return true;
        }
        $pathTargets = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($pathTargets as $key => $target) {
            $rootPath .= DIRECTORY_SEPARATOR . $target;
            if (file_exists($rootPath)) {
                continue;
            }
            if ($key === count($pathTargets) - 1) {
                $result = @copy($image, $rootPath);//skip E_WARNING
                if (FALSE === $result) {
                    throw new \Exception('Unable to copy file: ' . $image . ' to ' . $rootPath);
                }
                @chmod($rootPath, $this->config->getFilePermission());
                break;
            }
            mkdir($rootPath, $this->config->getFolderPermission(), true);
        }
        return $result;
    }
    
    /**
     * @param ImageInterface $image
     * @return bool
     */
    public function isOutdated(ImageInterface $image)
    {
        if ($image->getStatus() == StatusSource::STATUS_SERVICE) {
            //check service response waiting timeout
            $updated = $image->getTime();
            $now = time();
            if ($now - $updated > 86400) {
                $image->setStatus(StatusSource::STATUS_PENDING);
                return true;
            }
        }
        if ($image->getStatus() !== StatusSource::STATUS_OPTIMIZED) {
            return false;
        }
        return filemtime($image->getPath()) > $image->getTime();
    }

    /**
     * @param string $imagePath
     * @return bool
     */
    protected function createTempFile($imagePath)
    {
        $target = $this->getTempFilePath($imagePath);
        return copy($imagePath, $target);
    }

    /**
     * @param string $imagePath
     * @return bool
     */
    protected function rollbackTempFile($imagePath)
    {
        $target = $this->getTempFilePath($imagePath);
        return copy($target, $imagePath);
    }

    /**
     * @param string $imagePath
     * @return bool
     */
    protected function removeTempFile($imagePath)
    {
        $target = $this->getTempFilePath($imagePath);
        return unlink($target);
    }

    /**
     * @param string $imagePath
     * @return string
     */
    protected function getTempFilePath($imagePath)
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath()
            . self::TEMP_FOLDER_NAME . DIRECTORY_SEPARATOR;
        if (false === file_exists($path)) {
            mkdir($path, $this->config->getFolderPermission(), true);
        }
        return $path . md5($imagePath) . '.img_temp';
    }

    /**
     * @param ImageInterface[] $imageCollection
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function optimizeImageCollection($imageCollection)
    {
        foreach ($imageCollection as $image) {
            try {
                $this->optimizeImage($image);
            } catch (\Exception $e) {
                $image->setStatus(StatusSource::STATUS_ERROR);
                $this->imageRepository->save($image);
                $this->logger->error($e->getMessage());
            }
        }
        return $this;
    }
}