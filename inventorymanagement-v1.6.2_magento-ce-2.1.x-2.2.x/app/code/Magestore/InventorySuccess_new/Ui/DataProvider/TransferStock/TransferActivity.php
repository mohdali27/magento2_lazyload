<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock;

use Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivity\CollectionFactory;


class TransferActivity extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;


    protected $collection;

    /**
     * @var  \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory
     */
    protected $locatorFactory;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;



    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivityProduct\CollectionFactory */
    protected $_collectionFactory;


    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivityProduct\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->locatorFactory = $locatorFactory;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;

        $this->_collectionFactory = $collectionFactory;
        $this->collection = $this->getProductCollection();

    }


    /**
     * {@inheritdoc}
     */
    public function getProductCollection()
    {
        /** @var \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $locator = $this->locatorFactory->create();
        $activityId = $locator->getSessionByKey("current_activity_id");
        $collection = $this->_collectionFactory->create();
        $collection->getImageProduct();
        $collection->addFieldToFilter("activity_id", $activityId);
        return $collection;
    }
}