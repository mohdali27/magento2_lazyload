<?php

namespace Potato\ImageOptimization\Controller\App;

use Magento\Framework\App\Action\Context;
use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\App\ImageOptimization;
use Magento\Framework\Controller\ResultFactory;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Logger\Logger;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Save
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /** @var ImageRepositoryInterface  */
    protected $imageRepository;

    /** @var ImageOptimization  */
    protected $appImageOptimization;

    /** @var Logger  */
    protected $logger;

    /** @var Filesystem  */
    protected $filesystem;

    /**
     * Save constructor.
     * @param Context $context
     * @param ImageRepositoryInterface $imageRepository
     * @param ImageOptimization $appImageOptimization
     * @param Logger $logger
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        ImageRepositoryInterface $imageRepository,
        ImageOptimization $appImageOptimization,
        Logger $logger,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->imageRepository = $imageRepository;
        $this->appImageOptimization = $appImageOptimization;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|$this
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        try {
            $optimizationResult = $this->getRequest()->getParam('optimization_result');
            $images = $this->appImageOptimization->getOptimizedImages($optimizationResult);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $resultForward->forward('noroute');
            return $resultForward;
        }

        /** @var  $image \Potato\ImageOptimization\Model\App\Image\Result;*/
        foreach ($images as $image) {
            $imagePath = $this->createImagePathFromUrl($image->getOriginalUrl());
            if ($image->getAlternativeUrl()) {
                $imagePath = $this->createImagePathFromUrl($image->getAlternativeUrl());
            }

            try {
                $imageEntity = $this->imageRepository->getByPath($imagePath);
                if (!@file_exists($imagePath)) {
                    $this->imageRepository->delete($imageEntity);
                    continue;
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage());
                continue;
            }

            if ($imageEntity->getStatus() == StatusSource::STATUS_OPTIMIZED) {
                continue;
            }
            if (!$image->isOptimized()) {
                $imageEntity
                    ->setErrorType(ErrorSource::APPLICATION)
                    ->setStatus(StatusSource::STATUS_ERROR)
                    ->setPath($imagePath)
                    ->setResult($image->getResult())
                    ->setTime(filemtime($imagePath))
                ;
                try {
                    $this->imageRepository->save($imageEntity);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
                continue;
            }

            $optimizedImage = $this->_getContentFromUrl($image->getOptimizedUrl());
            if (false === $optimizedImage || strlen($optimizedImage) == 0) {
                $imageEntity
                    ->setStatus(StatusSource::STATUS_ERROR)
                    ->setErrorType(ErrorSource::IS_NOT_READABLE)
                    ->setResult(
                        __("The optimized image can't be retrieved from the service. Path to file: %1
                            Possible solution: Submit a support ticket 
                            <a href='https://potatocommerce.com/contacts/'>here</a>",
                            $image->getOptimizedUrl())
                    );
                try {
                    $this->imageRepository->save($imageEntity);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
                continue;
            }

            $result = file_put_contents($imagePath, $optimizedImage);
            if (false === $result) {
                $imageEntity
                    ->setStatus(StatusSource::STATUS_ERROR)
                    ->setErrorType(ErrorSource::CANT_UPDATE)
                    ->setResult(__("Can't update the file. Please check the file permissions."));
                try {
                    $this->imageRepository->save($imageEntity);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
                continue;
            }
            $imageEntity->setStatus(StatusSource::STATUS_OPTIMIZED);
            if (!$image->isOptimized()) {
                $imageEntity
                    ->setErrorType(ErrorSource::APPLICATION)
                    ->setStatus(StatusSource::STATUS_ERROR);
            }
            $imageEntity
                ->setPath($imagePath)
                ->setResult($image->getResult())
                ->setTime(filemtime($imagePath))
            ;
            try {
                $this->imageRepository->save($imageEntity);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode([
                'result' => true
            ])
        );
        return;
    }

    /**
     * @param $url
     * @return mixed|string
     */
    protected function _getContentFromUrl($url)
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl_handle);
        $httpcode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        if ($httpcode == 404) {
            curl_close($curl_handle);
            return '';
        }
        curl_close($curl_handle);
        return $result;
    }

    /**
     * @param string $imageUrl
     * @return string
     */
    private function createImagePathFromUrl($imageUrl)
    {
        $secure = false;
        if (preg_match('/^https:\/\//', $imageUrl)) {
            $secure = true;
        }
        $staticContentBaseUrl = trim($this->_url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_STATIC, '_secure' => $secure]), '/') . '/';
        if (strpos($imageUrl, $staticContentBaseUrl) !== False) {
            return str_replace(
                $staticContentBaseUrl,
                $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->getAbsolutePath(),
                $imageUrl
            );
        }
        $mediaContentBaseUrl = trim($this->_url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA, '_secure' => $secure]), '/') . '/';
        if (strpos($imageUrl, $mediaContentBaseUrl) !== False) {
            return str_replace(
                $mediaContentBaseUrl,
                $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(),
                $imageUrl
            );
        }
        $baseUrl = trim($this->_url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_WEB, '_secure' => $secure]), '/') . '/';
        return str_replace(
            $baseUrl,
            $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath(),
            $imageUrl
        );
    }
}