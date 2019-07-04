<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Permission;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\LayoutFactory;
use Magestore\InventorySuccess\Model\WarehouseFactory;
use Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface;

/**
 * Class AbstractPermission
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Permission
 */
abstract class AbstractPermission extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var PermissionManagementInterface
     */
    protected $permissionManagementInterface;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * AbstractPermission constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param WarehouseFactory $permissionFactory
     * @param LayoutFactory $layoutFactory
     * @param PermissionManagementInterface $permissionManagementInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WarehouseFactory $warehouseFactory,
        LayoutFactory $layoutFactory,
        PermissionManagementInterface $permissionManagementInterface
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->warehouseFactory = $warehouseFactory;
        $this->layoutFactory = $layoutFactory;
        $this->permissionManagementInterface = $permissionManagementInterface;
    }
}
