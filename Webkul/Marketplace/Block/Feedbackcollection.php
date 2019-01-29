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

namespace Webkul\Marketplace\Block;

/*
 * Webkul Marketplace Seller Feedbackcollection Block
 */
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory;

class Feedbackcollection extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerSession;

    /**
     * @var CollectionFactory
     */
    protected $_feedbackCollectionFactory;

    /** @var \Webkul\Marketplace\Model\Feedback */
    protected $_feedbackList;

    /**
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     * @param Customer               $customer
     * @param CustomerSession        $customerSession
     * @param CollectionFactory      $feedbackCollectionFactory
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Customer $customer,
        CustomerSession $customerSession,
        CollectionFactory $feedbackCollectionFactory,
        array $data = []
    ) {
        $this->_feedbackCollectionFactory = $feedbackCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->_customer = $customer;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getCustomerIsLogin()
    {
        return $this->_customerSession->isLoggedIn();
    }

    public function getCustomerSessionName()
    {
        return $this->_customerSession->getCustomer()->getName();
    }

    public function setCustomerSessionAfterAuthUrl()
    {
        $this->_customerSession->setAfterAuthUrl($this->getCurrentUrl());
    }

    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getCollection()
    {
        if (!$this->_feedbackList) {
            $collection = [];
            $partner = $this->getProfileDetail();
            if (count($partner)) {
                $collection = $this->_feedbackCollectionFactory->create()
                ->addFieldToFilter(
                    'status',
                    ['neq' => 0]
                )
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $partner->getSellerId()]
                )
                ->setOrder('entity_id', 'DESC');
            }
            $this->_feedbackList = $collection;
        }

        return $this->_feedbackList;
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
                'marketplace.feedback.pager'
            )
            ->setCollection(
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

    /**
     * @return array
     */
    public function getProfileDetail($value = '')
    {
        $shopUrl = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        )->getFeedbackUrl();
        if (!$shopUrl) {
            $shopUrl = $this->getRequest()->getParam('shop');
        }
        if ($shopUrl) {
            $data = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Data'
            )->getSellerCollectionObjByShop($shopUrl);
            foreach ($data as $seller) {
                return $seller;
            }
        }
    }

    public function getFeed()
    {
        $partner = $this->getProfileDetail();
        if (count($partner)) {
            return $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Data'
            )->getFeedTotal($partner->getSellerId());
        } else {
            return [];
        }
    }

    public function getFeedcountCollection()
    {
        $collection = [];
        $partner = $this->getProfileDetail();
        if (count($partner)) {
            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Feedbackcount'
            )->getCollection()
            ->addFieldToFilter('buyer_id', $this->_customerSession->getCustomerId())
            ->addFieldToFilter('seller_id', $partner->getSellerId());
        }

        return $collection;
    }

    public function getFeedCollection()
    {
        $collection = [];
        $partner = $this->getProfileDetail();
        if (count($partner)) {
            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Feedback'
            )->getCollection()
            ->addFieldToFilter('status', ['neq' => 0])
            ->addFieldToFilter('seller_id', $partner->getSellerId())
            ->setOrder('entity_id', 'DESC')
            ->setPageSize(2)
            ->setCurPage(1);
        }

        return $collection;
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
}
