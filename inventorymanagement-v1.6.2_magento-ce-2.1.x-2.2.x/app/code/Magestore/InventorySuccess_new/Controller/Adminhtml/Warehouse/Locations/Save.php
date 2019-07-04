<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\Locations;

use Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\AbstractWarehouse;
use Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface;

/**
 * Class Save
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\Locations
 */
class Save extends AbstractWarehouse
{
    /**
     * @var MappingManagementInterface
     */
    protected $_mappingManagement;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        MappingManagementInterface $mappingManagement
    )
    {
        parent::__construct($context, $permissionManagement, $dataPersistor);
        $this->_mappingManagement = $mappingManagement;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParam('links',[]);
        if (!(isset($params['mapping']) && is_array($params['mapping']))) {
            $params['mapping'] = [];
        }
        $this->_mappingManagement->createListMapping($params['mapping'], true);
        $this->messageManager->addSuccessMessage(__('The mapping location - locations have been saved.'));
        return $resultRedirect->setPath('*/*/mapping');
    }
}
