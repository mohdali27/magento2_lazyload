<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock;

use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\Collection;
use Magestore\InventorySuccess\Model\WarehouseFactory;

/**
 * Class AbstractGridProduct
 * @package Magestore\InventorySuccess\Block\Adminhtml\ManageStock
 */
class AbstractGridProduct extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var WarehouseFactory
     */
    protected $warehouseFactory;
    
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\CollectionFactory
     */
    protected $_stockCollectionFactory;

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
     * @var string
     */
    protected $_hiddenInputField = 'selected_products';

    /**
     * @var \Magestore\InventorySuccess\Model\Permission\PermissionManagement
     */
    protected $_permissionManagement;

    /**
     * @var \Magestore\InventorySuccess\Helper\Data
     */
    protected $inventoryHelper;

    /**
     * AbstractGridProduct constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magestore\InventorySuccess\Helper\Data $inventoryHelper
     * @param WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\CollectionFactory $stockCollectionFactory
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
        \Magestore\InventorySuccess\Helper\Data $inventoryHelper,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\CollectionFactory $stockCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        array $data = []
    )
    {
        $this->inventoryHelper = $inventoryHelper;
        $this->warehouseFactory = $warehouseFactory;
        $this->_stockCollectionFactory = $stockCollectionFactory;
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
        $this->setId("warehouse_list_products");
        $this->setDefaultSort("warehouse_product_id");
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
        $collection = $this->_stockCollectionFactory->create();
        $collection = $this->modifyCollection($collection);
        $sort = $this->getRequest()->getParam('sort');
        $dir = $this->getRequest()->getParam('dir');
        if (array_key_exists($sort, Collection::MAPPING_FIELD)) { 
            $collection->getSelect()->order(Collection::MAPPING_FIELD[$sort]. ' ' . $dir);
        }
        return $collection;
    }

    /**
     * function to modify collection 
     * 
     * @param $collection
     * @return $collection
     */
    public function modifyCollection($collection){
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
        $this->addColumn(
            "in_warehouse",
            [
                "type" => "checkbox",
                "name" => "in_warehouse",
                "values" => $this->_getSelectedProducts(),
                "filter" => false,
                "index" => "product_id",
                "header_css_class" => "col-select col-massaction",
                "column_css_class" => "col-select col-massaction",
                "is_system"   => true,
            ]
        );
        $this->addColumn("sku",
            [
                "header" => __("SKU"),
                "index" => "sku",
                "sortable" => true,
            ]
        );
        $this->addColumn("name",
            [
                "header" => __("Name"),
                "index" => "name",
                "sortable" => true,
            ]
        );
        if($this->inventoryHelper->getShowThumbnailProduct()) {
            $this->addColumn("image",
                [
                    "header" => __("Thumbnail"),
                    "index" => "image",
                    "sortable" => false,
                    'renderer'  => 'Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Renderer\Image'
                ]
            );
        }
        $editable = false;
        if($this->_permissionManagement->checkPermission('Magestore_InventorySuccess::create_adjuststock') &&
            $this->_permissionManagement->checkPermission('Magestore_InventorySuccess::confirm_adjuststock'))
            $editable = true;
        $this->addColumn("sum_total_qty",
            [
                "header" => __("Qty in Location(s)"),
                'renderer' => 'Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Grid\Column\Renderer\Text',
                "index" => "sum_total_qty",
                'type' => 'number',
                "editable" => $editable,
                "sortable" => true,
                'filter_condition_callback' => array($this, '_filterTotalQtyCallback')
            ]
        );
        $this->addColumn("sum_qty_to_ship",
            [
                "header" => __("Qty to Ship"),
                "index" => "sum_qty_to_ship",
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
                'renderer' => 'Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Grid\Column\Renderer\Text',
                "index" => "shelf_location",
                "editable" => true,
                "sortable" => true,
                'filter_condition_callback' => array($this, '_filterLocationCallback')
            ]
        );
        $this->modifyColumns();
        $this->_eventManager->dispatch('prepare_warehouse_stock_columns', ['object' => $this]);
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
        return $this->getUrl("*/warehouse_product/grid", ["_current" => true]);
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/warehouse_product/save', ["_current" => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        return $this->getRequest()->getParam('selected_products', []);
    }

    /**
     * @return string
     */
    public function getSelectedProduct()
    {
        return $this->jsonEncoder->encode($this->_getSelectedProducts());
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