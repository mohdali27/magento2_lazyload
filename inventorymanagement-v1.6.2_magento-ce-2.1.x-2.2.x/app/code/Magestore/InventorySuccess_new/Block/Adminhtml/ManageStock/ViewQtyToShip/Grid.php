<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock\ViewQtyToShip;


/**
 * Class Grid
 * @package Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Product
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $warehouseFactory;

    protected $collection;

    protected $request;

    protected $_coreRegistry;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\InventorySuccess\Model\Warehouse $warehouseProductFactory,
        \Magestore\InventorySuccess\Model\Service\Sales\PendingOrderItemService $collection,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $coreRegistry, array $data = []
    ) {
        $this->warehouseFactory = $warehouseProductFactory;
        $this->collection = $collection;
        $this->request = $request;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct() {
        parent::_construct();
        $this->setId('qtytoshipGrid');
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $product_id = $this->request->getParam('id',0);
        $collection = $this->collection->getCollection($product_id);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns() {
        $warehouseOptions = $this->getOptions();
        $this->addColumn('increment_id', array(
            'header' => __('Order ID'),
            'index' => 'increment_id',
            'float' => 'left'
        ));
        $this->addColumn('item_id', array(
            'header' => __('Item ID'),
            'index' => 'item_id',
            'float' => 'left'
        ));
        $this->addColumn('pending_qty', array(
            'header' => __('Qty need to ship'),
            'index' => 'pending_qty',
            'type' => 'number',
            'float' => 'left',
            'filter_condition_callback' => array($this, '_filterQtyCallback')
        ));
        $this->addColumn('warehouse_id', array(
            'header' => __('Location'),
            'index' => 'warehouse_id',
            'float' => 'left',
            'type' => 'options',
            'options' => $warehouseOptions,
            'filter_condition_callback' => array($this, '_filterWarehouseCallback')
        ));
        //        $this->addExportType('*/*/exportCsv', __('CSV'));
        //        $this->addExportType('*/*/exportXml', __('XML'));
        //        $this->addExportType('*/*/exportExcel', __('Excel'));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/viewQtyToShipGrid', array('_current'=>true));
    }

    /**
     * @param $collection
     * @param $column
     */
    protected function _filterQtyCallback($collection, $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        return  $this->collection->_filterQtyCallback($collection,$column->getId(),$value);
    }

    /**
     * @param $collection
     * @param $column
     */
    protected function _filterWarehouseCallback($collection, $column){
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        return  $this->collection->_filterWarehouseCallback($collection,$value);
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', array(
                'order_id'=>$row->getOrderId())
        );
    }
    protected function getOptions(){
        $warehouses = $this->warehouseFactory->getCollection();
        $array = array();
        $array[0]=' ';
        foreach($warehouses as $warehouse){
            $array[$warehouse->getId()] = $warehouse->getWarehouseName();
        }
        return $array;
    }

}