<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\Order;

class View extends \Magento\Sales\Controller\Adminhtml\Order\View
{
    /**
     * After execute order view
     *
     * @param \Magento\Sales\Controller\Adminhtml\Order\View $action
     * @param $result`
     */
    public function afterExecute(\Magento\Sales\Controller\Adminhtml\Order\View $action, $result)
    {
        /** @var \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $service */
        $service = $this->_objectManager->create('Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface');
        if ($service->canChangeOrderWarehouse())
            return $result;
        $order = $this->_coreRegistry->registry('current_order');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($order) {
            $warehouseIds = $service->getViewWarehouseList()->getAllIds();
            if (!in_array($order->getWarehouseId(), $warehouseIds)) {
                $this->messageManager->addErrorMessage(__('You don not have permission to view order'));
                $resultRedirect->setPath('sales/order/index');
                return $resultRedirect;
            }
        }
        return $result;
    }

}
