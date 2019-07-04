<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Action\Product\NoneInWarehouse;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;
use Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface;

/**
 * Class Actions
 * @package Magestore\InventorySuccess\Ui\Component\Listing\Columns\Product\NoneInWarehouse
 */
class Warehouses extends Action
{
    protected $warehouseFactory;

    /**
     * @var PermissionManagementInterface
     */
    protected $permissionManagementInterface;
    
    /**
     * Warehouses constructor.
     * @param ContextInterface $context
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param array $components
     * @param array $data
     * @param null $actions
     */
    public function __construct(
        ContextInterface $context,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        PermissionManagementInterface $permissionManagementInterface,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        parent::__construct($context, $components, $data);
        $this->warehouseFactory = $warehouseFactory;
        $this->permissionManagementInterface = $permissionManagementInterface;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        if(empty($this->actions)){
            $actions = [];
            $collection = $this->warehouseFactory->create()->getCollection();
            $collection = $this->permissionManagementInterface
                ->filterPermission($collection,'Magestore_InventorySuccess::product_none_in_warehouse');
            $warehouses = $collection->getItems();
            foreach ($warehouses as $warehouse) {
                $action = [];
                $action['type'] = $warehouse['warehouse_id'];
                $action['label'] = $warehouse['warehouse_name'] . ' (' . $warehouse['warehouse_code'] . ')';
                $action['url'] = $this->context->getUrl(
                    'inventorysuccess/product_noneInWarehouse/massWarehouse',
                    ['id' => $warehouse['warehouse_id']]);
                $actions[] = $action;
            }
            $this->actions = $actions;
        }
        if (!empty($this->actions)) {
            $this->setData('config', array_replace_recursive(['actions' => $this->actions], $this->getConfiguration()));
        }
        parent::prepare();
    }
}
