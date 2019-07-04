<?php

namespace Magecomp\Emailquotepro\Controller\Index;

use Magecomp\Emailquotepro\Model\EmailproductquoteFactory;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Setcart extends Action
{
    protected $_EmailproductquoteFactory;
    protected $customerRepository;
    protected $quoteFactory;
    protected $session;
    protected $checkoutSession;
    protected $customerModel;

    public function __construct(
        Context $context,
        EmailproductquoteFactory $EmailproductquoteFactory,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        QuoteFactory $quoteFactory,
        CheckoutSession $checkoutSession,
        CustomerModel $customerModel,
        Cart $modelCart
    )
    {
        $this->_EmailproductquoteFactory = $EmailproductquoteFactory;
        $this->session = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->customerRepository = $customerRepository;
        $this->_storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->customerModel = $customerModel;
        $this->_modelCart = $modelCart;
        parent::__construct($context);
    }

    public function execute()
    {
        $redirectPath = "checkout/cart/";
        try {
            $id = $this->getRequest()->getParam('quoteid');
            if ($id != '') {
                $id = substr($id, 3, -3);
                $modelEmailProduct = $this->_EmailproductquoteFactory->create()->load($id, 'quote_id');
                if ($modelEmailProduct->getEmailproductquoteId()) {
                    $modelEmailProduct->setStatus(1)->save();
                }
                $quoteA = $this->quoteFactory->create()->load($id);

                $customerData = $this->customerModel->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())->loadByEmail($quoteA->getCustomerEmail());
                $customerId = 0;

                if (!($customerData->getId())) {
                    $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                    $store = $this->_storeManager->getStore();

                    $customer = ObjectManager::getInstance()->create('\Magento\Customer\Model\Customer');
                    $customer->setWebsiteId($websiteId)
                        ->setStore($store)
                        ->setFirstname($quoteA->getCustomerFirstName() ? $quoteA->getCustomerFirstName() : 'New')
                        ->setLastname('User')
                        ->setEmail($quoteA->getCustomerEmail())
                        ->setPassword('pwd123');

                    try {
                        $customer->save();
                        $storeId = $customer->getSendemailStoreId();
                        $customer->sendNewAccountEmail('registered', '', $storeId);
                        $customerId = (int)$customer->getId();
                        $quote = $this->quoteFactory->create()->load($id);
                        $quote->setCustomerId((int)$customerId);
                        $quote->setCustomerEmail($quoteA->getCustomerEmail());
                        $quote->setCustomerFirstname($quoteA->getCustomerFirstName() ? $quoteA->getCustomerFirstName() : 'New');
                        $quote->save();
                    } catch (\Exception $e) {
                    }
                }
                $storeScope = ScopeInterface::SCOPE_STORE;
                $customerSession = $this->session;
                if (!$customerSession->isLoggedIn()) {
                    $customerData = $this->customerModel->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())->loadByEmail($quoteA->getCustomerEmail());
                    $this->session->setCustomerAsLoggedIn($customerData);
                    $this->session->regenerateId();
                }

                // Clear Shopping Cart start
                $cart = $this->_modelCart;
                $quoteItems = $this->checkoutSession->getQuote()->getItemsCollection();

                foreach ($quoteItems as $item) {
                    $cart->removeItem($item->getId());
                }
                // Clear Shopping Cart end

                $CurrentQuotes = $this->checkoutSession->getQuote();
                $CurrentQuotes->merge($quoteA);
                $CurrentQuotes->collectTotals()->save();
            }
            $redirectPath = "checkout/cart/";
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $redirectPath = "checkout/cart/";
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($redirectPath);
        return $resultRedirect;
    }
}