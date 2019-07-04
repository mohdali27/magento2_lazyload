<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Action\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;
use Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface;

/**
 * Class ChangeWarehouse
 * @package Magestore\InventorySuccess\Ui\Component\Listing\Action\Order
 */
class ChangeWarehouse extends Action
{
    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface
     */
    protected $orderProcessService;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $warehouseCollection;
    
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
        \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollection,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        parent::__construct($context, $components, $data);
        $this->orderProcessService = $orderProcessService;
        $this->warehouseCollection = $warehouseCollection;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        if(empty($this->actions)){
            $actions = [];
            $warehouses = $this->warehouseCollection->create();
            /** @var \Magestore\InventorySuccess\Model\Warehouse $warehouse */
            foreach ($warehouses as $warehouse) {
                $action = [];
                $action['type'] = $warehouse->getWarehouseId();
                $action['label'] = $warehouse->getWarehouseName() . ' (' . $warehouse->getWarehouseCode() . ')';
                $action['url'] = $this->context->getUrl(
                    'inventorysuccess/order/massWarehouse',
                    ['warehouse_id' => $warehouse->getWarehouseId()]);
                $action['confirm'] = [
                    'title' => __('Change Location'),
                    'message' => __('Are you sure you want to change location for selected order(s)?'),
                ];
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
