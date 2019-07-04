<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\Stocktaking;

use Magestore\InventorySuccess\Model\ResourceModel\Stocktaking;
use Magento\Framework\View\Element\UiComponent\Context;

/**
 * Class AbstractStocktaking
 * @package Magestore\InventorySuccess\Block\Adminhtml\Stocktaking
 */
class AbstractStocktaking extends \Magento\Backend\Block\Template
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
     * Get stocktaking
     *
     * @return StocktakingInterface
     */
    public function getStocktaking()
    {
        return $this->registry->registry('current_stocktaking');
    }

    /**
     * Get stocktaking status
     *
     * @return string
     */
    public function getStocktakingStatus()
    {
        $stocktaking = $this->getStocktaking();
        if($stocktaking){
            return $stocktaking->getStatus();
        }
            return null;
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
