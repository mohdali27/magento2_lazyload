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
 * Webkul Marketplace Account RewriteUrlPost Controller.
 */
class RewriteUrlPost extends Action
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
     * @param Context          $context
     * @param Session          $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Filesystem $filesystem
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
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
     * Seller's Custom URL Post action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $fields = $this->getRequest()->getParams();
                $sellerId = $this->_getSession()->getCustomerId();
                $collection = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                )
                ->getCollection()
                ->addFieldToFilter('seller_id', $sellerId);
                foreach ($collection as $value) {
                    $profileurl = $value->getShopUrl();
                }

                $getCurrentStoreId = $this->_objectManager->create(
                    'Webkul\Marketplace\Helper\Data'
                )->getCurrentStoreId();

                if ($fields['profile_request_url']) {
                    $sourceUrl = 'marketplace/seller/profile/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = 0;
                    $profileRequestUrl = '';
                    $urlCollectionData = $this->_objectManager->create(
                        'Magento\UrlRewrite\Model\UrlRewrite'
                    )
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $profileRequestUrl = $value->getRequestPath();
                    }
                    if ($profileRequestUrl != $fields['profile_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['profile_request_url'])
                        ->save();
                    }
                }
                if ($fields['collection_request_url']) {
                    $sourceUrl = 'marketplace/seller/collection/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = 0;
                    $collectionRequestUrl = '';
                    $urlCollectionData = $this->_objectManager->create(
                        'Magento\UrlRewrite\Model\UrlRewrite'
                    )
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $collectionRequestUrl = $value->getRequestPath();
                    }
                    if ($collectionRequestUrl != $fields['collection_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['collection_request_url'])
                        ->save();
                    }
                }
                if ($fields['review_request_url']) {
                    $sourceUrl = 'marketplace/seller/feedback/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = 0;
                    $reviewRequestUrl = '';
                    $urlCollectionData = $this->_objectManager->create(
                        'Magento\UrlRewrite\Model\UrlRewrite'
                    )
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $reviewRequestUrl = $value->getRequestPath();
                    }
                    if ($reviewRequestUrl != $fields['review_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->_objectManager->create(
                            'Magento\UrlRewrite\Model\UrlRewrite'
                        )
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['review_request_url'])
                        ->save();
                    }
                }
                if ($fields['location_request_url']) {
                    $sourceUrl = 'marketplace/seller/location/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = 0;
                    $locationRequestUrl = '';
                    $urlCollectionData = $this->_objectManager->create(
                        'Magento\UrlRewrite\Model\UrlRewrite'
                    )
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $locationRequestUrl = $value->getRequestPath();
                    }
                    if ($locationRequestUrl != $fields['location_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['location_request_url'])
                        ->save();
                    }
                }
                if ($fields['policy_request_url']) {
                    $sourceUrl = 'marketplace/seller/policy/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = 0;
                    $policyRequestUrl = '';
                    $urlCollectionData = $this->_objectManager->create(
                        'Magento\UrlRewrite\Model\UrlRewrite'
                    )
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $policyRequestUrl = $value->getRequestPath();
                    }
                    if ($policyRequestUrl != $fields['policy_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['policy_request_url'])
                        ->save();
                    }
                }
                // clear cache
                $this->_objectManager->create(
                    'Webkul\Marketplace\Helper\Data'
                )->clearCache();
                $this->messageManager->addSuccess(__('The URL Rewrite has been saved.'));

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/editProfile',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
