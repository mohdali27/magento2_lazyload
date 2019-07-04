<?php

namespace Magecomp\Emailquotepro\Controller\Adminhtml\Emailproductquote;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

class Edit extends Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $quoteFactory;

    public function __construct( Action\Context $context,
                                 PageFactory $resultPageFactory,
                                 Registry $registry,
                                 QuoteFactory $quoteFactory,
                                 StoreManagerInterface $storeManager
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->quoteFactory = $quoteFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {

        $quoteid = $this->getRequest()->getParam('quote_id');
        $customerEmail = $this->getRequest()->getParam('customer_email');
        $customerName = $this->getRequest()->getParam('customer_name');
        $customerData = ObjectManager::getInstance()->create('\Magento\Customer\Model\Customer')->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())->loadByEmail($customerEmail);
        $customerId = 0;
        if (!($customerData->getId())) {
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $store = $this->_storeManager->getStore();
            $customer = ObjectManager::getInstance()->create('\Magento\Customer\Model\Customer');
            //$newPassword = $customer->generatePassword();
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($customerName)
                ->setLastname('User')
                ->setEmail($customerEmail)
                ->setPassword('123456789');
            try {
                $customer->save();
                $storeId = $customer->getSendemailStoreId();
                $customer->sendNewAccountEmail('registered', '', $storeId);
                $quote = $this->quoteFactory->create()->load($quoteid);
                $quote->setCustomerId((int)$customerId);
                $quote->setCustomerEmail($customerEmail);
                $quote->setCustomerFirstname($customerName);
                $quote->save();
                $customerId = (int)$customer->getId();

            } catch (\Exception $e) {
            }
        } else {
            $customerId = (int)$customerData->getId();
        }
        $customerData = ObjectManager::getInstance()->create('\Magento\Customer\Model\Customer')->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())->loadByEmail($customerEmail);

        $sessionQuote = ObjectManager::getInstance()->get('\Magento\Backend\Model\Session\Quote');
        $sessionQuote->setCustomerId($customerId);
        $sessionQuote->setQuoteId($quoteid);
        $sessionQuote->setStoreId(1);

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order_create');
    }

    protected function _isAllowed()
    {
        return true;
    }
}
