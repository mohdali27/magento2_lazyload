<?php
namespace Potato\ImageOptimization\Model;

use Magento\Framework\UrlInterface;
use Potato\ImageOptimization\Model\App\ImageOptimization as AppImageOptimization;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\ResourceModel\Image\CollectionFactory as ImageCollectionFactory;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Magento\Framework\App\State;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class App
 */
class App
{
    const SERVICE_IMAGES_DATA_NAME = 'potato_service_images';
    const SERVICE_IMAGES_TRANSFER_LIMIT = 500;

    /** @var ImageCollectionFactory  */
    protected $imageCollectionFactory;

    /** @var UrlInterface  */
    protected $urlBuilder;

    /** @var AppImageOptimization  */
    protected $appImageOptimization;

    /** @var Logger  */
    protected $logger;

    /** @var  State */
    protected $appEmulation;

    /**
     * App constructor.
     * @param ImageCollectionFactory $imageCollectionFactory
     * @param UrlInterface $urlBuilder
     * @param AppImageOptimization $appImageOptimization
     * @param Logger $logger
     * @param State $emulation
     */
    public function __construct(
        ImageCollectionFactory $imageCollectionFactory,
        UrlInterface $urlBuilder,
        AppImageOptimization $appImageOptimization,
        Logger $logger,
        State $emulation
    ) {
        $this->imageCollectionFactory = $imageCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->appImageOptimization = $appImageOptimization;
        $this->logger = $logger;
        $this->appEmulation = $emulation;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function prepareAndSendServiceImages()
    {
        /** @var \Potato\ImageOptimization\Model\ResourceModel\Image\Collection $imageCollection */
        $imageCollection = $this->imageCollectionFactory->create();
        $imageCollection->addFilterByStatus(StatusSource::STATUS_PENDING_SERVICE);
        $imageCollection->setPageSize(self::SERVICE_IMAGES_TRANSFER_LIMIT);
        try {
            $imagesForServiceCount = $this->sendServiceImages($imageCollection);
        } catch (\Exception $e) {
            $imagesForServiceCount = 0;
            $this->logger->critical($e->getMessage());
        }
        return $imagesForServiceCount;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function createServiceCallbackUrl()
    {
        $url = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_WEB, '_secure' => true, '_scope' => 'default']);
        return rtrim($url, '/') . '/po_image/app/save';
    }

    /**
     * @param AbstractDb $imageCollection
     * @return int
     * @throws \Exception
     */
    public function sendServiceImages(AbstractDb $imageCollection)
    {
        $images = $imageCollection->toOptionHash();
        $imagesForService = [];
        foreach ($images as $imagePath) {
            $imagePath = $this->createImageUrlFromPath($imagePath);
            $imagesForService[] = ['url' => $imagePath];
        }
        $imagesForServiceCount = count($imagesForService);
        if (!$imagesForServiceCount) {
            return $imagesForServiceCount;
        }
        $url = $this->createServiceCallbackUrl();
        $this->appImageOptimization->process($url, $imagesForService);

        foreach ($imageCollection->getItems() as $item) {
            $item
                ->setStatus(StatusSource::STATUS_SERVICE)
                ->setResult(__('The image has been transferred to the service. Waiting for complete optimization'))
                ->save();
        }
        return $imagesForServiceCount;
    }

    /**
     * @param string $imagePath
     * @return string
     * @throws \Exception
     */
    private function createImageUrlFromPath($imagePath)
    {
        $mediaPath = BP . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;
        $staticPath = BP . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR;
        $urlType = UrlInterface::URL_TYPE_WEB;
        $basePath = BP;
        if (strpos($imagePath, $mediaPath) === 0) {
            $urlType = UrlInterface::URL_TYPE_MEDIA;
            $basePath = $mediaPath;
        } else if (strpos($imagePath, $staticPath) === 0) {
            $urlType = UrlInterface::URL_TYPE_STATIC;
            $basePath = $staticPath;
        }
        $url = $this->urlBuilder->getBaseUrl(['_type' => $urlType, '_secure' => true, '_scope' => 'default']);
        if (strpos($imagePath, $basePath) === 0) {
            $imagePath = substr_replace($imagePath, '', 0, strlen($basePath));
        } else {
            throw new \Exception('Unable to convert path to url');
        }
        return rtrim($url, '/') . '/' . $imagePath;
    }
}