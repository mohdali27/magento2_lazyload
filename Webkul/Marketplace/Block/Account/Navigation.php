<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Block\Account;

/**
 * Marketplace Navigation link
 *
 */
class Navigation extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * @var int
     */
    private $orderId;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->objectManager = $objectManager;
        $this->date = $date;
        $this->customerSession = $customerSession;
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }
    /**
     * Get all marketplce product collection seller wise.
     * @return \Webkul\Marketplace\Model\Product
     */
    public function getProductCollection()
    {
        $customerId = $this->customerSession->getCustomerId();
        $storeCollection = $this->objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $customerId
        )->addFieldToFilter(
            'seller_pending_notification',
            1
        );
        return $storeCollection;
    }

    /**
     * Get all marketplce product collection seller wise.
     * @return \Webkul\Marketplace\Model\Product
     */
    public function getMarketplaceOrderCollection()
    {
        $customerId = $this->customerSession->getCustomerId();
        $orderCollection = $this->objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $customerId
        )->addFieldToFilter(
            'seller_pending_notification',
            1
        );
        
        $salesOrder = $this->objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
        )->getTable('sales_order');

        $orderCollection->getSelect()->join(
            $salesOrder.' as so',
            'main_table.order_id = so.entity_id'
        )->where(
            'so.order_approval_status = 1'
        );
        return $orderCollection;
    }

    /**
     * Get all transaction for seller.
     * @return \Webkul\Marketplace\Model\Product
     */
    public function getTransactionCollection()
    {
        $customerId = $this->customerSession->getCustomerId();
        $transactionCollection = $this->objectManager->create(
            'Webkul\Marketplace\Model\Sellertransaction'
        )
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $customerId
        )->addFieldToFilter(
            'seller_pending_notification',
            1
        )->setOrder('created_at', 'DESC');
        return $transactionCollection;
    }

    /**
     * Load product by id
     * @param  int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function loadProduct($productId)
    {
        $product = $this->objectManager->create(
            'Magento\Catalog\Model\Product'
        )->load($productId);
        return $product;
    }

    /**
     * Load order by id
     * @param  int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function loadOrder($orderId)
    {
        if ($this->orderId == $orderId) {
            return $this->order;
        }
        $order = $this->objectManager->create(
            'Magento\Sales\Model\Order'
        )->load($orderId);
        $this->orderId = $orderId;
        $this->order = $order;
        return $this->order;
    }

    /**
     * Count total notifications.
     * @return int
     */
    public function getProductNotificationCount()
    {
        return $this->getProductCollection()->getSize();
    }

    /**
     * Generate notification title according to product status.
     * @param  int $productId
     * @param  int $productStatus
     * @return string
     */
    public function getProductNotificationTitle($productId, $productStatus)
    {
        $product = $this->loadProduct($productId);
        if ($productStatus == 1) {
            return __('Product approved');
        } else {
            return __('Product disapproved');
        }
    }

    /**
     * Generate notification body according to product status.
     * @param  int $productId
     * @param  int $productStatus
     * @return string
     */
    public function getProductNotificationDesc($productId, $productStatus)
    {
        $product = $this->loadProduct($productId);
        if ($productStatus == 1) {
            return __(
                sprintf(
                    'Product %s has been approved by admin. 
                    Please go to your My Product List section to check product(s) status',
                    '<span class="wk-focus">'.$product->getName().'</span>'
                )
            );
        } else {
            return __(
                sprintf(
                    'Product %s has been disapproved by admin. 
                    Please go to your My Product List section to check product(s) status',
                    '<span class="wk-focus">'.$product->getName().'</span>'
                )
            );
        }
    }

    public function getProductNotifyDateTime($date)
    {
        return $this->date->gmtDate('l jS \of F Y h:i:s A', strtotime($date));
    }

    /**
     * Count total order notifications.
     * @return int
     */
    public function getOrderNotificationCount()
    {
        return $this->getMarketplaceOrderCollection()->getSize();
    }

    /**
     * Generate notification title according to order status.
     * @param  int $productId
     * @param  int $productStatus
     * @return string
     */
    public function getOrderNotificationTitle($orderId)
    {
        $order = $this->loadOrder($orderId);
        return __('Order placed notification');
    }

    /**
     * Generate notification body according to order.
     * @param  int $productId
     * @param  int $productStatus
     * @return string
     */
    public function getOrderNotificationDesc($orderId)
    {
        $customerId = $this->customerSession->getCustomerId();
        $order = $this->loadOrder($orderId);
        $saleslistIds = [];
        $collection1 = $this->objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )->getCollection()
        ->addFieldToFilter('order_id', $orderId)
        ->addFieldToFilter('seller_id', $customerId)
        ->addFieldToFilter('parent_item_id', ['null' => 'true'])
        ->addFieldToFilter('magerealorder_id', ['neq' => 0])
        ->addFieldToSelect('entity_id');

        $saleslistIds = $collection1->getData();

        $fetchsale = $this->objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $saleslistIds]
        );
        $fetchsale->getSellerOrderCollection();
        $productNames = [];
        foreach ($fetchsale as $value) {
            $productNames[] = $value->getMageproName();
        }
        $productNames = implode(',', $productNames);
        return __(
            sprintf(
                'Product(s) %s has been sold from your store with order id %s',
                '<span class="wk-focus">'.$productNames.'</span>',
                '<span class="wk-focus">#'.$order->getIncrementId().'</span>'
            )
        );
    }

    public function getOrderCreatedDate($orderId)
    {
        $createdAt = $this->loadOrder($orderId)->getCreatedAt();
        return $this->date->gmtDate('l jS \of F Y h:i:s A', strtotime($createdAt));
    }

    /**
     * return notification count
     * @return int
     */
    public function getTransactionNotificationCount()
    {
        return $this->getTransactionCollection()->getSize();
    }

    /**
     * generate notification title
     * @param  int $transactionId
     * @return string
     */
    public function getTransactionNotifyTitle($transactionId)
    {
        $transactionBlock = $this->getLayout()->createBlock(
            'Webkul\Marketplace\Block\Transaction\View'
        );
        $details = $transactionBlock->sellertransactionOrderDetails($transactionId);
        $orderId = $details->getFirstItem()->getMagerealorderId();
        $title = __(sprintf('Payment has been successfully done for "#%s" Order', $orderId));
        return $title;
    }

    /**
     * generate notification description
     * @param  int $transactionId
     * @return string
     */
    public function getTransactionNotifyDesc($id)
    {
        $transactionBlock = $this->getLayout()->createBlock(
            'Webkul\Marketplace\Block\Transaction\View'
        );
        $sellerTransation = $this->sellertransactionDetails($id);
        $details = $transactionBlock->sellertransactionOrderDetails($id);
        $orderId = $details->getFirstItem()->getMagerealorderId();
        $desc = __(sprintf(
            'You have recieved payment for %s order. Mode of payment is %s.',
            '<span class="wk-focus">#'.$orderId.'</span>',
            '<span class="wk-focus">'.$sellerTransation->getMethod().'</span>'
        ));
        return $desc;
    }

    public function sellertransactionDetails($id)
    {
        return $this->objectManager->create(
            'Webkul\Marketplace\Model\Sellertransaction'
        )->load($id);
    }

    public function getTransactionDate($date)
    {
        return $this->date->gmtDate('l jS \of F Y h:i:s A', strtotime($date));
    }
}
