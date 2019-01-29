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

namespace Webkul\Marketplace\Block\Order;

/*
 * Webkul Marketplace Order Salesdetail Block
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;

class Salesdetail extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /** @var \Webkul\Marketplace\Model\Saleslist */
    public $salesLists;

    /**
     * @param Context                                   $context
     * @param array                                     $data
     * @param Customer                                  $customer
     * @param Order                                     $order
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Order $order,
        Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Orders'));
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */
    public function getCollection()
    {
        if (!($customerId = $this->getCustomerId())) {
            return false;
        }
        if (!$this->salesLists) {
            $ids = [];
            $orderids = [];

            $collectionOrders = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )->getCollection()
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $customerId]
            )
            ->addFieldToFilter(
                'mageproduct_id',
                ['eq' => $this->getRequest()->getParam('id')]
            )
            ->addFieldToFilter(
                'magequantity',
                ['neq' => 0]
            )
            ->addFieldToSelect('order_id')
            ->distinct(true);
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['in' => $collectionOrders->getData()]
            );
            $collection->setOrder(
                'entity_id',
                'desc'
            );
            $this->salesLists = $collection;
        }

        return $this->salesLists;
    }

    public function getOrderById($orderId = '')
    {
        return $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
    }

    public function getProduct()
    {
        $productId = (int) $this->getRequest()->getParam('id');

        return $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.salesdetail.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }
}
