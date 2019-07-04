<?php


namespace Meetanshi\Notifications\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Meetanshi\Notifications\Helper\Data;
use Magento\Framework\Registry;

class Review implements ObserverInterface
{
    private $helper;
    private $registry;

    public function __construct(
        Data $helper,
        Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    public function execute(Observer $observer)
    {
        if ($config = $this->helper->getReviewConfig()) {
            try {
                $product = $this->registry->registry("current_product");
                $review = $observer->getDataByKey('object');
                $config['name'] = $product->getName();
                $config['url'] = $product->getProductUrl();
                $config['nickname'] = $review['nickname'];
                $config['title'] = $review['title'];
                $config['detail'] = $review['detail'];
                $config['time'] = $this->helper->getCurrentTime();
                $this->helper->sendCustomMailSendMethod($config);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            return $this;
        }
    }
}
