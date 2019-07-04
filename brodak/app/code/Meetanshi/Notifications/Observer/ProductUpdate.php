<?php

namespace Meetanshi\Notifications\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Meetanshi\Notifications\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockStateInterface;

class ProductUpdate implements ObserverInterface
{
    private $helper;
    private $productFactory;
    private $stockItem;

    public function __construct(
        Data $helper,
        ProductFactory $productFactory,
        StockStateInterface $stockItem
    ) {
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->stockItem = $stockItem;
    }

    public function execute(Observer $observer)
    {
        if ($config = $this->helper->getStockConfig()) {
            try {
                $product = $observer->getProduct();
                $stockStatus = $this->stockItem->getStockQty($product->getEntityId(), $product->getStore()->getWebsiteId());
                if ($stockStatus <= $config['limit']) {
                    $config['name'] = $product->getName();
                    $config['stock'] = $stockStatus;
                    $this->helper->sendCustomMailSendMethod($config);
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }
}
