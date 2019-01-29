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

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Webkul Marketplace Seller Newfeedback controller.
 */
class Newfeedback extends Action implements AccountInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param Context                                     $context
     * @param Session                                     $customerSession
     * @param FormKeyValidator                            $formKeyValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Save New Seller feeback entry in table.
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
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $wholedata = $this->getRequest()->getParams();

        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/feedback',
                        ['shop' => $wholedata['shop_url']]
                    );
                }
                $sellerId = $wholedata['seller_id'];
                $buyerId = $this->_getSession()->getCustomerId();
                $buyerEmail = $this->_getSession()->getEmail();
                $wholedata['buyer_id'] = $buyerId;
                $wholedata['buyer_email'] = $buyerEmail;
                $wholedata['created_at'] = $this->_date->gmtDate();
                $wholedata['admin_notification'] = 1;
                $feedbackcount = 0;
                $collectionfeed = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Feedbackcount'
                )
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                )->addFieldToFilter(
                    'buyer_id',
                    [$buyerId]
                );
                foreach ($collectionfeed as $value) {
                    $feedbackcount = $value->getFeedbackCount();
                    $value->setFeedbackCount($feedbackcount + 1);
                    $value->save();
                }
                $collection = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Feedback'
                );
                $collection->setData($wholedata);
                $collection->save();

                $this->messageManager->addSuccess(
                    __('Your Review was successfully saved')
                );

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/feedback',
                    ['shop' => $wholedata['shop_url']]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/feedback',
                    ['shop' => $wholedata['shop_url']]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/feedback',
                ['shop' => $wholedata['shop_url']]
            );
        }
    }
}
