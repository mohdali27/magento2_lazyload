<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse;

use Magento\Backend\App\Action;
use Magestore\InventorySuccess\Model\Warehouse;
use Magento\Framework\App\Request\DataPersistorInterface;

abstract class AbstractWarehouse extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::warehouse';
    
    /**
     * @var \Magestore\InventorySuccess\Controller\Adminhtml\Context
     */
    protected $_context;
    
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magestore\InventorySuccess\Model\Permission\PermissionManagement
     */
    protected $_permissionManagement;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        DataPersistorInterface $dataPersistor
    ){
        parent::__construct($context);
        $this->_context = $context;
        $this->_permissionManagement = $permissionManagement;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->_context->getResultPageFactory()->create();
        $resultPage->setActiveMenu('Magestore_InventorySuccess::warehouse_list');
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        $resultPage->addBreadcrumb(__('Locations'), __('Locations'));
        return $resultPage;
    }
}