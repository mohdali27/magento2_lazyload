<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Permission\Warehouse;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Assign
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Permission\Warehouse
 */
class Assign extends \Magestore\InventorySuccess\Controller\Adminhtml\Permission\AbstractPermission
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $objectId = $this->getRequest()->getParam('warehouse_id');
        $links = $this->getRequest()->getParam('links');
        $response = [];
        try {
            $this->permissionManagementInterface->setPermissionsByObject($this->warehouseFactory->create()->load($objectId), null, $links['associated']);
            $this->messageManager->addSuccess(__('You saved the staff permission.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $layout = $this->layoutFactory->create();
        $layout->initMessages();
        $response['error'] = true;
        $response['messages'] = [$layout->getMessagesBlock()->getGroupedHtml()];
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($response);
    }
}
