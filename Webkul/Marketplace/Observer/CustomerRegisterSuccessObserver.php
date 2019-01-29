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

namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

/**
 * Webkul Marketplace CustomerRegisterSuccessObserver Observer.
 */
class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager,
     * @param CollectionFactory                           $collectionFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        CollectionFactory $collectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_collectionFactory = $collectionFactory;
        $this->_date = $date;
    }

    /**
     * customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer['account_controller'];
        try {
            $paramData = $data->getRequest()->getParams();
            if (!empty($paramData['is_seller']) && !empty($paramData['profileurl']) && $paramData['is_seller'] == 1) {
                $customer = $observer->getCustomer();

                $profileurlcount = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                )->getCollection();
                $profileurlcount->addFieldToFilter(
                    'shop_url',
                    $paramData['profileurl']
                );
                if (!$profileurlcount->getSize()) {
                    $status = $this->_objectManager->get(
                        'Webkul\Marketplace\Helper\Data'
                    )->getIsPartnerApproval() ? 0 : 1;
                    $customerid = $customer->getId();
                    $model = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Seller'
                    );
                    $model->setData('is_seller', $status);
                    $model->setData('shop_url', $paramData['profileurl']);
		    $model->setData('profileurlarea', $paramData['profileurlarea']);
                    $model->setData('seller_id', $customerid);
                    $model->setData('store_id', 0);
                    $model->setCreatedAt($this->_date->gmtDate());
                    $model->setUpdatedAt($this->_date->gmtDate());
                    if ($status == 0) {
                        $model->setAdminNotification(1);
                    }
                    $model->save();
                    $loginUrl = $this->_objectManager->create(
                        'Magento\Framework\UrlInterface'
                    )->getUrl("marketplace/account/dashboard");
                    $this->_objectManager->create(
                        'Magento\Customer\Model\Session'
                    )->setBeforeAuthUrl($loginUrl);
                    $this->_objectManager->create(
                        'Magento\Customer\Model\Session'
                    )->setAfterAuthUrl($loginUrl);

                    $helper = $this->_objectManager->get(
                        'Webkul\Marketplace\Helper\Data'
                    );
                    if ($helper->getAutomaticUrlRewrite()) {
                        $this->createSellerPublicUrls($paramData['profileurl']);
                    }
                    $adminStoremail = $helper->getAdminEmailId();
                    $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
                    $adminUsername = 'Admin';
                    $senderInfo = [
                        'name' => $customer->getFirstName().' '.$customer->getLastName(),
                        'email' => $customer->getEmail(),
                    ];
                    $receiverInfo = [
                        'name' => $adminUsername,
                        'email' => $adminEmail,
                    ];
                    $emailTemplateVariables['myvar1'] = $customer->getFirstName().' '.
                    $customer->getLastName();
                    $emailTemplateVariables['myvar2'] = $this->_objectManager->get(
                        'Magento\Backend\Model\Url'
                    )->getUrl(
                        'customer/index/edit',
                        ['id' => $customer->getId()]
                    );
                    $emailTemplateVariables['myvar3'] = 'Admin';

                    $this->_objectManager->create(
                        'Webkul\Marketplace\Helper\Email'
                    )->sendNewSellerRequest(
                        $emailTemplateVariables,
                        $senderInfo,
                        $receiverInfo
                    );
                } else {
                    $this->_messageManager->addError(
                        __('This Shop URL already Exists.')
                    );
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    private function createSellerPublicUrls($profileurl = '')
    {
        if ($profileurl) {
            $getCurrentStoreId = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Data'
            )->getCurrentStoreId();

            /*
            * Set Seller Profile Url
            */
            $sourceProfileUrl = 'marketplace/seller/profile/shop/'.$profileurl;
            $requestProfileUrl = $profileurl;
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $profileRequestUrl = '';
            $urlCollectionData = $this->_objectManager->create(
                'Magento\UrlRewrite\Model\UrlRewrite'
            )
            ->getCollection()
            ->addFieldToFilter('target_path', $sourceProfileUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $profileRequestUrl = $value->getRequestPath();
            }
            if ($profileRequestUrl != $requestProfileUrl) {
                $idPath = rand(1, 100000);
                $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceProfileUrl)
                ->setRequestPath($requestProfileUrl)
                ->save();
            }

            /*
            * Set Seller Collection Url
            */
            $sourceCollectionUrl = 'marketplace/seller/collection/shop/'.$profileurl;
            $requestCollectionUrl = $profileurl.'/collection';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $collectionRequestUrl = '';
            $urlCollectionData = $this->_objectManager->create(
                'Magento\UrlRewrite\Model\UrlRewrite'
            )
            ->getCollection()
            ->addFieldToFilter('target_path', $sourceCollectionUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $collectionRequestUrl = $value->getRequestPath();
            }
            if ($collectionRequestUrl != $requestCollectionUrl) {
                $idPath = rand(1, 100000);
                $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceCollectionUrl)
                ->setRequestPath($requestCollectionUrl)
                ->save();
            }

            /*
            * Set Seller Feedback Url
            */
            $sourceFeedbackUrl = 'marketplace/seller/feedback/shop/'.$profileurl;
            $requestFeedbackUrl = $profileurl.'/feedback';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $feedbackRequestUrl = '';
            $urlFeedbackData = $this->_objectManager->create(
                'Magento\UrlRewrite\Model\UrlRewrite'
            )
            ->getCollection()
            ->addFieldToFilter('target_path', $sourceFeedbackUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlFeedbackData as $value) {
                $urlId = $value->getId();
                $feedbackRequestUrl = $value->getRequestPath();
            }
            if ($feedbackRequestUrl != $requestFeedbackUrl) {
                $idPath = rand(1, 100000);
                $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceFeedbackUrl)
                ->setRequestPath($requestFeedbackUrl)
                ->save();
            }

            /*
            * Set Seller Location Url
            */
            $sourceLocationUrl = 'marketplace/seller/location/shop/'.$profileurl;
            $requestLocationUrl = $profileurl.'/location';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $locationRequestUrl = '';
            $urlLocationData = $this->_objectManager->create(
                'Magento\UrlRewrite\Model\UrlRewrite'
            )
            ->getCollection()
            ->addFieldToFilter('target_path', $sourceLocationUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlLocationData as $value) {
                $urlId = $value->getId();
                $locationRequestUrl = $value->getRequestPath();
            }
            if ($locationRequestUrl != $requestLocationUrl) {
                $idPath = rand(1, 100000);
                $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceLocationUrl)
                ->setRequestPath($requestLocationUrl)
                ->save();
            }

            /**
             * Set Seller Policy Url
             */
            $sourcePolicyUrl = 'marketplace/seller/policy/shop/'.$profileurl;
            $requestPolicyUrl = $profileurl.'/policy';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $policyRequestUrl = '';
            $urlPolicyData = $this->_objectManager->create(
                'Magento\UrlRewrite\Model\UrlRewrite'
            )
            ->getCollection()
            ->addFieldToFilter('target_path', $sourcePolicyUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlPolicyData as $value) {
                $urlId = $value->getId();
                $policyRequestUrl = $value->getRequestPath();
            }
            if ($policyRequestUrl != $requestPolicyUrl) {
                $idPath = rand(1, 100000);
                $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourcePolicyUrl)
                ->setRequestPath($requestPolicyUrl)
                ->save();
            }
        }
    }
}
