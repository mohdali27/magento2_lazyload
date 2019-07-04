<?php

namespace Meetanshi\Notifications\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Meetanshi\Notifications\Helper\Data;

class NewOrder implements ObserverInterface
{
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        if ($config = $this->helper->getNewOrderConfig()) {
            try {
                $order = $observer->getEvent()->getOrder();
                $config['incrementid'] = $order->getIncrementId();
                $config['email'] = $order->getCustomerEmail();
                $config['name'] = $order->getCustomerFirstname().' '.$order->getCustomerLastname();
                $this->helper->sendCustomMailSendMethod($config);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }
}
