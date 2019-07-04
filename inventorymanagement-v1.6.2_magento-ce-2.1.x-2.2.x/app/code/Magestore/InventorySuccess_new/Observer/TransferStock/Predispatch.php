<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\TransferStock;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class Predispatch implements ObserverInterface
{

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlInterface,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_url     = $urlInterface;
        $this->_request = $request;
    }

    /**
     * @param EventObserver $observer
     * @return EventObserver
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request    = $observer->getEvent()->getControllerAction()->getRequest();
        $currentKey = $request->getParam(\Magento\Backend\Model\Url::SECRET_KEY_PARAM_NAME);
        $newKey     = $this->_url->getSecretKey($request->getRouteName(), $request->getControllerName(),
            $request->getActionName());
        if ($currentKey != $newKey) {
            $request->setParam(\Magento\Backend\Model\Url::SECRET_KEY_PARAM_NAME, $newKey);
            $newUrl = $this->_url->getUrl($request->getFullActionName('/'), $request->getParams());
            $observer->getEvent()->getControllerAction()->getResponse()->setRedirect($newUrl);
        }
        return $observer;
    }
}