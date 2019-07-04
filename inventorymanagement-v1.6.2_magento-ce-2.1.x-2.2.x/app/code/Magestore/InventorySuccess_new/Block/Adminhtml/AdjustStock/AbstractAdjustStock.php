<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\AdjustStock;


use Magestore\InventorySuccess\Model\ResourceModel\AdjustStock;
use Magento\Framework\View\Element\UiComponent\Context;

/**
 * Class AbstractAdjustStock
 * @package Magestore\InventorySuccess\Block\Adminhtml\AdjustStock
 */
class AbstractAdjustStock extends \Magento\Backend\Block\Template
{
    /**
     * Url Builder
     *
     * @var Context
     */
    protected $context;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * PermissionManagementInterface
     *
     * @var \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface
     */
    protected $permissionManagementInterface;


    /**
     * AbstractAdjustStock constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagementInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagementInterface,
        array $data = []
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->permissionManagementInterface = $permissionManagementInterface;
        parent::__construct($context, $data);
    }

    /**
     * Get adjust stock
     *
     * @return AdjustStockInterface
     */
    public function getAdjustStock()
    {
        return $this->registry->registry('current_adjuststock');
    }

    /**
     * Get adjust stock status
     *
     * @return string
     */
    public function getAdjustStockStatus()
    {
        $adjustStock = $this->getAdjustStock();
        if($adjustStock){
            return $adjustStock->getStatus();
        }
            return '0';
    }
    
    /**
     * Get current Warehouse which creating adjust
     * 
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getWarehouse()
    {
        return $this->registry->registry('current_warehouse');
    }
}
