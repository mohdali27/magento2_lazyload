<?php

namespace Magecomp\Emailquotepro\Block;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Emailquote extends Template
{
    protected $customersession;
    protected $context;

    public function __construct(
        CustomerSession $customersession,
        Context $context,
        array $data = [] )
    {
        parent::__construct($context, $data);
        $this->customersession = $customersession;
    }

    public function getHomeUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getCartUrl()
    {
        return $this->getUrl('*/*/*');
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('emailquotepro/index/sendmail');
    }

    public function getCustomerIdData()
    {
        if ($this->customersession->isLoggedIn()) {
            $customer = $this->customersession->getCustomer();
            return $customer->getEntityId();
        } else {
            return 0;
        }
    }

    public function getCustomerNameData()
    {
        if ($this->customersession->isLoggedIn()) {
            $customer = $this->customersession->getCustomer();
            return $customer->getFirstname() . ' ' . $customer->getLastname();
        } else {
            return '';
        }
    }

    public function getCustomerEmailData()
    {
        if ($this->customersession->isLoggedIn()) {
            $customer = $this->customersession->getCustomer();

            return $customer->getEmail();
        } else {
            return '';
        }
    }

    public function getCustomerTelephoneData()
    {
        if ($this->customersession->isLoggedIn()) {
            $customer = $this->customersession->getCustomer();
            $address = $customer->getPrimaryBillingAddress();
            if ($address) {
                return $address->getTelephone();
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
}