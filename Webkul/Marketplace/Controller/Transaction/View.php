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

namespace Webkul\Marketplace\Controller\Transaction;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Model\Sellertransaction;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;
use Webkul\Marketplace\Model\Notification;

/**
 * Webkul Marketplace Transaction View Controller.
 */
class View extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Sellertransaction
     */
    protected $sellertransaction;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var NotificationHelper
     */
    protected $notificationHelper;

    /**
     * @param Context                           $context
     * @param PageFactory                       $resultPageFactory
     * @param \Magento\Customer\Model\Session   $customerSession
     * @param Sellertransaction                 $sellertransaction
     * @param HelperData                        $helper
     * @param NotificationHelper                $notificationHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        Sellertransaction $sellertransaction,
        HelperData $helper,
        NotificationHelper $notificationHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->sellertransaction = $sellertransaction;
        $this->helper = $helper;
        $this->notificationHelper = $notificationHelper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get(
            'Magento\Customer\Model\Url'
        )->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Default customer account page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = 0;
        $paramData = $this->getRequest()->getParams();
        if (!empty($paramData['id'])) {
            $id = $paramData['id'];
        }
        $transaction = $this->sellertransaction->load($id);
        if ($transaction->getId()) {
            $helper = $this->helper;
            $isPartner = $helper->isSeller();
            if ($isPartner == 1) {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->_resultPageFactory->create();
                if ($helper->getIsSeparatePanel()) {
                    $resultPage->addHandle('marketplace_layout2_transaction_view');
                }
                $resultPage->getConfig()->getTitle()->set(
                    __('Marketplace Seller Transaction View')
                );
                $type = Notification::TYPE_TRANSACTION;
                $this->notificationHelper->updateNotification(
                    $transaction,
                    $type
                );
                return $resultPage;
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/transaction/history',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
