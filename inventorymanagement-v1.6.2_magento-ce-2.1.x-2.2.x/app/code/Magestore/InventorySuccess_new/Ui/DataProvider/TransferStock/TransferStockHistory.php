<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock;
use Magestore\InventorySuccess\Model\ResourceModel\TransferStock\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magestore\InventorySuccess\Model\TransferStockFactory;


class TransferStockHistory extends AbstractDataProvider
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var use Magestore\InventorySuccess\Model\TransferStockFactory
     */
    protected $_transferStockFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $_warehouseSource;

    private $_registry;

    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\CollectionFactory  */
    protected $_collectionFactory;

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
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        \Magento\Framework\Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_collectionFactory = $collectionFactory;
        $this->_registry = $registry;
        $this->collection = $this->prepareCollection();
    }

    /**
     * @return mixed
     */
    public function prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        
        return $collection;
    }
}