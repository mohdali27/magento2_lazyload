<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Request\Form;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;


class ShortfallProductSelection extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * ShortfallProductSelection constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->collection = $collectionFactory->create();
        $this->getProductCollection();

    }

    /**
     * {@inheritdoc}
     */
    public function getProductCollection()
    {
        $transferstockId = $this->request->getParam('transferstock_id');
        $this->collection->getTransferStockProduct($transferstockId);
        if ($transferstockId) {
            $this->collection->addFieldToFilter('transferstock_id', $transferstockId);
            $this->collection->getSelect()->where('(qty_delivered - qty_received - qty_returned) > ?',0);
        }
    }
}