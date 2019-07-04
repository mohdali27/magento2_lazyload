<?php

namespace Meetanshi\Notifications\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Meetanshi\Notifications\Helper\Data;

class CustomerRegister implements ObserverInterface
{
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        if ($config = $this->helper->getRegistrationConfig()) {
            try {
                $customer = $observer->getEvent()->getCustomer();
                $config['customername'] = $customer->getFirstName() . ' ' . $customer->getLastName();
                $config['customermail'] = $customer->getEmail();
                $config['time'] = $this->helper->getCurrentTime();
                $this->helper->sendCustomMailSendMethod($config);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }
}
