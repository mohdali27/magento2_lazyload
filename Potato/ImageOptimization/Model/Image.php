<?php
namespace Potato\ImageOptimization\Model;

use Magento\Framework;
use Potato\ImageOptimization\Api\Data;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;

/**
 * Class Image
 * @package Potato\ImageOptimization\Model
 */
class Image extends \Magento\Framework\Model\AbstractModel
{
    /** @var Data\ImageInterfaceFactory  */
    private $imageDataFactory;

    /** @var Framework\Api\DataObjectHelper  */
    private $dataObjectHelper;

    /**
     * Image constructor.
     * @param Framework\Model\Context $context
     * @param Framework\Registry $registry
     * @param ResourceModel\Image $resource
     * @param ResourceModel\Image\Collection $resourceCollection
     * @param Data\ImageInterfaceFactory $imageDataFactory
     * @param Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array $data
     */
    public function __construct(
        Framework\Model\Context $context,
        Framework\Registry $registry,
        ResourceModel\Image $resource,
        ResourceModel\Image\Collection $resourceCollection,
        Data\ImageInterfaceFactory $imageDataFactory,
        Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->imageDataFactory = $imageDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Image::class);
    }
    
    /**
     * Retrieve Image model with data
     *
     * @return \Potato\ImageOptimization\Api\Data\ImageInterface
     */
    public function getDataModel()
    {
        $data = $this->getData();
        $dataObject = $this->imageDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            Data\ImageInterface::class
        );
        return $dataObject;
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getStatus() != StatusSource::STATUS_ERROR) {
            $this->setErrorType(null);
        }
        parent::beforeSave();
        return $this;
    }
}
