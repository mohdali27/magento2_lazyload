<?php


namespace Meetanshi\Notifications\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Meetanshi\Notifications\Helper\Data;

class NewsletterSubscription implements ObserverInterface
{
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        try {
            $subscriber = $observer->getEvent()->getSubscriber();
            \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($subscriber->isSubscribed());
            if ($subscriber->isSubscribed()) {
                if ($config = $this->helper->getNewsletterConfig()) {
                    $config['customermail'] = $observer->getEvent()->getSubscriber()->getSubscriberEmail();
                    $config['time'] = $this->helper->getCurrentTime();
                    $this->helper->sendCustomMailSendMethod($config);
                }
            } else {
                if ($config = $this->helper->getUnsubscriptionConfig()) {
                    $config['customermail'] = $observer->getEvent()->getSubscriber()->getSubscriberEmail();
                    $config['time'] = $this->helper->getCurrentTime();
                    $this->helper->sendCustomMailSendMethod($config);
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $this;
    }
}
