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

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Account BecomesellerPost Controller.
 */
class BecomesellerPost extends Action
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
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @param Context          $context
     * @param Session          $customerSession
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->_fileUploaderFactory = $fileUploaderFactory;
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
     * BecomesellerPost action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Redirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/account/becomeseller',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $fields = $this->getRequest()->getParams();

                $profileurlcount = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                )->getCollection()
                ->addFieldToFilter(
                    'shop_url',
                    $fields['profileurl']
                );
                if (!count($profileurlcount)) {
                    $sellerId = $this->_getSession()->getCustomerId();
                    $status = $this->_objectManager->get(
                        'Webkul\Marketplace\Helper\Data'
                    )->getIsPartnerApproval() ? 0 : 1;
                    $model = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Seller'
                    )
                    ->getCollection()
                    ->addFieldToFilter('shop_url', $fields['profileurl']);
                    if (!count($model)) {
                        if (isset($fields['is_seller']) && $fields['is_seller']) {
                            $autoId = 0;
                            $collection = $this->_objectManager->create(
                                'Webkul\Marketplace\Model\Seller'
                            )
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId);
                            foreach ($collection as $value) {
                                $autoId = $value->getId();
                            }
                            $value = $this->_objectManager->create(
                                'Webkul\Marketplace\Model\Seller'
                            )->load($autoId);
                            $value->setData('is_seller', $status);
                            $value->setData('shop_url', $fields['profileurl']);
                            $value->setData('seller_id', $sellerId);
                            $value->setCreatedAt($this->_date->gmtDate());
                            $value->setUpdatedAt($this->_date->gmtDate());
                            $value->setAdminNotification(1);
                            $value->save();
                            try {
                                if (!empty($errors)) {
                                    foreach ($errors as $message) {
                                        $this->messageManager->addError($message);
                                    }
                                } else {
                                    if ($status) {
                                        // clear cache
                                        $this->_objectManager->get(
                                            'Webkul\Marketplace\Helper\Data'
                                        )->clearCache();
                                        $this->messageManager->addSuccess(
                                            __('Congratulations! Your seller account is created.')
                                        );
                                    } else {
                                        $this->messageManager->addSuccess(
                                            __('Your request to become seller is successfully raised.')
                                        );
                                    }
                                }

                                return $this->resultRedirectFactory->create()->setPath(
                                    'marketplace/account/becomeseller',
                                    ['_secure' => $this->getRequest()->isSecure()]
                                );
                            } catch (\Exception $e) {
                                $this->messageManager->addException(
                                    $e,
                                    __('We can\'t save the customer.')
                                );
                            }

                            return $this->resultRedirectFactory->create()->setPath(
                                'marketplace/account/becomeseller',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                        } else {
                            $this->messageManager->addError(
                                __('Please confirm that you want to become seller.')
                            );

                            return $this->resultRedirectFactory->create()->setPath(
                                'marketplace/account/becomeseller',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                        }
                    } else {
                        $this->messageManager->addError(
                            __('Shop URL already exist please set another.')
                        );

                        return $this->resultRedirectFactory->create()->setPath(
                            'marketplace/account/becomeseller',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                } else {
                    $this->messageManager->addError(
                        __('Shop URL already exist please set another.')
                    );

                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/account/becomeseller',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
