<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\LowStockNotification;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class Redirect implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $id = $request->getParam('id');
        if (($controller == 'lowstocknotification_notification')
            && $action == 'notify') {
            /** @var \Magento\Backend\Model\Url $backendUrl */
            $backendUrl = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magento\Backend\Model\Url'
            );
            $redirectUrl = $backendUrl->getUrl('inventorysuccess/lowstocknotification_notification/edit', ['id' => $id]);
            $observer->getControllerAction()->getResponse()->setRedirect($redirectUrl);
            return $this;
        }
    }
}