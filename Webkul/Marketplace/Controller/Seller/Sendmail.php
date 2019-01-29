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

namespace Webkul\Marketplace\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\Product;

/**
 * Webkul Marketplace Sendmail controller.
 */
class Sendmail extends Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @param Context  $context
     * @param Session  $customerSession
     * @param Customer $customer
     * @param Product  $product
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Customer $customer,
        Product $product
    ) {
        $this->_customer = $customer;
        $this->_product = $product;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Sendmail to Seller action.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $helper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        if (!$helper->getSellerProfileDisplayFlag()) {
            $this->getRequest()->initForward();
            $this->getRequest()->setActionName('noroute');
            $this->getRequest()->setDispatched(false);

            return false;
        }
        $data = $this->getRequest()->getParams();
        if ($data['seller-id']) {
            $this->_eventManager->dispatch(
                'mp_send_querymail',
                [$data]
            );
            if ($this->_customerSession->isLoggedIn()) {
                $buyerName = $this->_customerSession->getCustomer()->getName();
                $buyerEmail = $this->_customerSession->getCustomer()->getEmail();
            } else {
                $buyerEmail = $data['email'];
                $buyerName = $data['name'];
                if (strlen($buyerName) < 2) {
                    $buyerName = 'Guest';
                }
            }
            $emailTemplateVariables = [];
            $senderInfo = [];
            $receiverInfo = [];
            $seller = $this->_customer->load($data['seller-id']);
            $emailTemplateVariables['myvar1'] = $seller->getName();
            $sellerEmail = $seller->getEmail();
            if (!isset($data['product-id'])) {
                $data['product-id'] = 0;
            } else {
                $emailTemplateVariables['myvar3'] = $this->_product->load(
                    $data['product-id']
                )->getName();
            }
            $emailTemplateVariables['myvar4'] = $data['ask'];
            $emailTemplateVariables['myvar6'] = $data['subject'];
            $emailTemplateVariables['myvar5'] = $buyerEmail;
            $senderInfo = [
                'name' => $buyerName,
                'email' => $buyerEmail,
            ];
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $sellerEmail,
            ];
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Email'
            )->sendQuerypartnerEmail(
                $data,
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo
            );
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(
                'Magento\Framework\Json\Helper\Data'
            )->jsonEncode('true')
        );
    }
}
