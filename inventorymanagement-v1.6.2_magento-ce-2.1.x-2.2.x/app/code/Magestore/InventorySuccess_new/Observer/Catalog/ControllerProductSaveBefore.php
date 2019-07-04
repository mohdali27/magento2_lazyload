<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Catalog;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class ControllerProductSaveBefore implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_request = $request;
    }
    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        /* process add/ edit product */

        $array = $product->getData('quantity_and_stock_status');
        if(!is_array($array)){
            return;
        }
        if(array_key_exists('use_config_qty',$array)){
            if(!$product->getData('quantity_and_stock_status')['use_config_qty']){
                $productData = $product->getData();
                $postData = $this->_request->getParam('product');
                $currentQty = $postData['global_stock']['global_available_qty'];
                $productData['quantity_and_stock_status']['qty'] = $currentQty;
                $product->setData($productData);
            }
        }
    }
}