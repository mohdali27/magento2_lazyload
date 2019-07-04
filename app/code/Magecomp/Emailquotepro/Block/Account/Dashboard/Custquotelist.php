<?php

namespace Magecomp\Emailquotepro\Block\Account\Dashboard;

use Magecomp\Emailquotepro\Model\EmailproductquoteFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Custquotelist extends Template
{
    protected $modelsavecart;
    protected $customerSession;
    protected $emailproductquoteFactory;

    public function __construct( Context $context,
                                 Session $customerSession,
                                 EmailproductquoteFactory $emailproductquoteFactory,
                                 array $data = [] )
    {
        $this->customerSession = $customerSession;
        $this->emailproductquoteFactory = $emailproductquoteFactory;
        parent::__construct($context, $data);
    }

    public function getLogginCustomerQuoteList()
    {
        $email = $this->customerSession->getCustomer()->getEmail();
        return $this->emailproductquoteFactory->create()->getCollection()
            ->addFieldToFilter('customer_email', $email)
            ->setOrder('emailproductquote_id', 'DESC');
    }


}