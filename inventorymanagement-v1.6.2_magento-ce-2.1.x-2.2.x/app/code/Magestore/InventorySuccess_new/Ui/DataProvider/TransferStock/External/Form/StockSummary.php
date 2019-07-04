<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\External\Form;

use Magestore\InventorySuccess\Model\ResourceModel\TransferStock;
use Magestore\InventorySuccess\Model\TransferStock as ModelTransferStock;
use Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magestore\InventorySuccess\Model\TransferStock\TransferStockProductFactory;


class StockSummary extends AbstractDataProvider
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var use Magestore\InventorySuccess\Model\TransferStock\TransferStockProductFactory
     */
    protected $_transferStockProductFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /** @var  \Magestore\InventorySuccess\Model\TransferStockFactory $_transferStockFactory */
    protected $_transferStockFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $_warehouseSource;

    /**
     * Generate constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $collectionFactory,
        \Magestore\InventorySuccess\Model\TransferStock\TransferStockProductFactory $transferStockProductFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->_request = $request;
        $this->collection = $collectionFactory->create();
        $this->prepareCollection();
    }

    public function prepareCollection()
    {
        $transferstock_id = $this->_request->getParam('transferstock_id');
        $this->collection->getTransferStockProduct($transferstock_id);
    }
}