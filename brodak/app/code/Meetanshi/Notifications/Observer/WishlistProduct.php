<?php


namespace Meetanshi\Notifications\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Meetanshi\Notifications\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;

class WishlistProduct implements ObserverInterface
{
    private $helper;
    private $customerSession;
    private $productFactory;

    public function __construct(
        Data $helper,
        ProductFactory $productFactory,
        Session $customerSession
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->productFactory = $productFactory;
    }

    public function execute(Observer $observer)
    {
        if ($config = $this->helper->getWishlistConfig()) {
            try {
                $product = $this->productFactory->create()->load($observer->getEvent()->getProduct()->getEntityId());

                $config['sku'] = $product->getName();
                $config['url'] = $product->getProductUrl();
                $config['customermail'] = $this->customerSession->getCustomer()->getEmail();
                $config['customrename'] = $this->customerSession->getCustomer()->getName();
                $config['time'] = $this->helper->getCurrentTime();

                $this->helper->sendCustomMailSendMethod($config);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            return $this;
        }
    }
}
