<?php

namespace Meetanshi\Notifications\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Meetanshi\Notifications\Helper\Data;

class OrderStatus implements ObserverInterface
{
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        if ($config = $this->helper->getOrderStatusConfig()) {
            try {
                $order = $observer->getEvent()->getOrder();
                if ($order instanceof \Magento\Framework\Model\AbstractModel) {
                    $orderStatus = explode(',', $config['to']);
                    if (in_array($order->getState(), $orderStatus)) {
                        $key = array_search($order->getState(), $orderStatus);
                        $config['incrementid'] = $order->getIncrementId();
                        $config['status'] = ucwords($order->getState());
                        $config['old'] = ucwords($orderStatus[$key - 1]);
                        $config['time'] = $this->helper->getCurrentTime();
                        $this->helper->sendCustomMailSendMethod($config);
                    }
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }
}
