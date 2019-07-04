<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset;

use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

/**
 * Class AbstractGridProduct
 * @package Magestore\InventorySuccess\Block\Adminhtml\ManageStock
 */
class Infor extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magestore\InventorySuccess\Model\Permission\PermissionManagement
     */
    protected $_permissionManagement;

    /**
     * Infor constructor.
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $productCollectionFactory,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        array $data = []
    )
    {
        $this->warehouseFactory = $warehouseFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->_status = $status;
        $this->jsonEncoder = $jsonEncoder;
        $this->messageManager = $messageManager;
        $this->_permissionManagement = $permissionManagement;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId("warehouse_products_infor");
        $this->setDefaultSort(WarehouseProductInterface::WAREHOUSE_PRODUCT_ID);
        $this->setUseAjax(true);
    }

    /**
     * Set hidden input field name for selected products
     *
     * @param $name
     */
    protected function _setHiddenInputField($name){
        $this->_hiddenInputField = $name;
    }

    /**
     * get hidden input field name for selected products
     *
     * @return string
     */
    public function getHiddenInputField(){
        return $this->_hiddenInputField;
    }

    /**
     * Prepare collection for grid product
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->getDataColllection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Get Collection for grid product
     *
     * @return Collection
     */
    public function getDataColllection(){
        $collection = $this->productCollectionFactory->create();
        $warehouses = $this->warehouseFactory->create()->getCollection();
        $warehouses = $this->_permissionManagement
            ->filterPermission($warehouses, 'Magestore_InventorySuccess::warehouse_stock_view');
        $warehouseIds = $warehouses->getAllIds();
        $collection->getSelect()->where("main_table.". WarehouseProductInterface::WAREHOUSE_ID ." IN ('" . implode("','", $warehouseIds) . "')");
        $id = $this->getRequest()->getParam('id', null);
        $collection->retrieveWarehouseStocks($id);
        return $collection;
    }
    /**
     * prepare columns for grid product
     *
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn("warehouse",
            [
                "header" => __("Location"),
                "index" => "warehouse",
                "sortable" => true,
            ]
        );
        $this->addColumn("total_qty",
            [
                "header" => __("Qty in Location"),
                "index" => "total_qty",
                'type' => 'number',
                "sortable" => true,
                'filter_condition_callback' => array($this, '_filterTotalQtyCallback')
            ]
        );
        $this->addColumn("qty_to_ship",
            [
                "header" => __("Qty to Ship"),
                "index" => "qty_to_ship",
                'type' => 'number',
                "sortable" => true,
                'filter_condition_callback' => array($this, '_filterTotalQtyCallback')
            ]
        );
        $this->addColumn("available_qty",
            [
                "header" => __("Available Qty"),
                "index" => "available_qty",
                'type' => 'number',
                "sortable" => true,
                'filter_condition_callback' => array($this, '_filterTotalQtyCallback')
            ]
        );
        $this->addColumn(
            "shelf_location",
            [
                "header" => __("Shelf Location"),
                "index" => "shelf_location",
                "sortable" => true,
                'filter_condition_callback' => array($this, '_filterLocationCallback')
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * function to add, remove or modify product grid columns
     *
     * @return $this
     */
    public function modifyColumns(){
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl("*/manageStock_product/infor", ["_current" => true]);
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/manageStock_product/save', ["_current" => true]);
    }

    /**
     * Apply `qty` filters to product grid.
     *
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\Collection $collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return $this
     */
    protected function _filterTotalQtyCallback($collection, $column) {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        $collection->addQtyToFilter($column->getId(), $value);
    }

    /**
     * Apply `shelf_location` filters to product grid.
     *
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\Collection $collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return $this
     */
    protected function _filterLocationCallback($collection, $column) {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        $collection->addSheldLocationToFilter($column->getId(), $value);
    }
}