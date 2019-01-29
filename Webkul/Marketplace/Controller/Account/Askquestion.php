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

namespace Webkul\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

/**
 * Webkul Marketplace Askquestion Controller.
 */
class Askquestion extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Ask Query to seller action.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

        $sellerName = $this->_customerSession->getCustomer()->getName();
        $sellerEmail = $this->_customerSession->getCustomer()->getEmail();

        $adminStoremail = $helper->getAdminEmailId();
        $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';

        $emailTemplateVariables = [];
        $senderInfo = [];
        $receiverInfo = [];
        $emailTemplateVariables['myvar1'] = $adminUsername;
        $emailTemplateVariables['myvar2'] = $sellerName;
        $emailTemplateVariables['subject'] = $data['subject'];
        $emailTemplateVariables['myvar3'] = $data['ask'];
        $senderInfo = [
            'name' => $sellerName,
            'email' => $sellerEmail,
        ];
        $receiverInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $this->_objectManager
        ->create('Webkul\Marketplace\Helper\Email')
        ->askQueryAdminEmail($emailTemplateVariables, $senderInfo, $receiverInfo);
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')
            ->jsonEncode('true')
        );
    }
}
