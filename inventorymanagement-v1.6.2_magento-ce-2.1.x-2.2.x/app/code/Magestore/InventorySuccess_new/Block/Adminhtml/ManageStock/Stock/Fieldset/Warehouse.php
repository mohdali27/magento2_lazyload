<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset;

/**
 * Class Warehouse
 * @package Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset
 */
class Warehouse extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\Permission\PermissionManagement
     */
    protected $_permissionManagement;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        array $data = []
    ){
        $this->_warehouseFactory = $warehouseFactory;
        $this->_permissionManagement = $permissionManagement;
        parent::__construct($context, $data);
    }
    
    /**
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::managestock/stock/fieldset/warehouse.phtml';

    public function getOptionWarehouses(){
        $collection = $this->_warehouseFactory->create()->getCollection();
        $collection->getSelect()->columns(
            array('warehouse' => new \Zend_Db_Expr('CONCAT(warehouse_name, " (",warehouse_code,")")'))
        );
        $collection = $this->_permissionManagement->filterPermission($collection, 'Magestore_InventorySuccess::warehouse_stock_view');
        return $collection->getItems();
    }
}