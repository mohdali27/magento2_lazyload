<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Buttons;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface;

/**
 * Class Generic
 */
class Generic implements ButtonProviderInterface
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
     * @var WarehouseInterface
     */
    protected $warehouseInterface;

    /**
     * @var \Magestore\InventorySuccess\Model\Permission\PermissionManagement
     */
    protected $_permissionManagement;

    /**
     * Generic constructor
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry,
        WarehouseInterface $warehouseInterface,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->warehouseInterface = $warehouseInterface;
        $this->_permissionManagement = $permissionManagement;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrl($route, $params);
    }

    /**
     * Get product
     *
     * @return ProductInterface
     */
    public function getWarehouse()
    {
        return $this->registry->registry('current_warehouse');
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }
}
