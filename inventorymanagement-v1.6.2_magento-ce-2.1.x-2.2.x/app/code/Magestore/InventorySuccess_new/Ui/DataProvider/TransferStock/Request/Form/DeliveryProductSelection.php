<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Request\Form;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;


class DeliveryProductSelection extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /** @var  \Magestore\InventorySuccess\Model\TransferStockFactory */
    protected $transferStockFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface request
     * @param \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock $adjustStockResource
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->transferStockFactory = $transferStockFactory;
        $this->collection = $collectionFactory->create();
        $this->getProductCollection();
    }


    /**
     * {@inheritdoc}
     */
    public function getProductCollection()
    {
        $transferstockId = $this->request->getParam('transferstock_id');
        $warehouseId = $this->getWarehouseId();
        $this->collection->getTransferStockProduct($transferstockId, $warehouseId);
        $this->collection->getSelect()->where('(main_table.qty - main_table.qty_delivered > ?)', 0);
        return $this->collection;
    }

    /**
     * Get current Adjustment
     *
     * @return Adjustment
     * @throws NoSuchEntityException
     */
    public function getWarehouseId()
    {
        $transferstockId = $this->request->getParam('transferstock_id');
        $transferStock = $this->transferStockFactory->create()->load($transferstockId);
        $warehouseId = $transferStock->getSourceWarehouseId();
        return $warehouseId;
    }


}