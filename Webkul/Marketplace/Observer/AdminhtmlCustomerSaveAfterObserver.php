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
use Webkul\Marketplace\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Webkul\Marketplace\Model\Product as ProductStatus;

/**
 * Webkul Marketplace AdminhtmlCustomerSaveAfterObserver Observer.
 */
class AdminhtmlCustomerSaveAfterObserver implements ObserverInterface
{
    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

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
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    protected $_mediaDirectory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $_sellerProduct;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @param Filesystem                                       $filesystem,
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime      $date,
     * @param \Magento\Framework\Message\ManagerInterface      $messageManager,
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager,
     * @param \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository,
     * @param CollectionFactory                                $collectionFactory,
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param ProductCollection                                $sellerProduct
     * @param \Magento\Framework\Json\DecoderInterface         $jsonDecoder
     */
    public function __construct(
        Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        ProductCollection $sellerProduct,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_productRepository = $productRepository;
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_sellerProduct = $sellerProduct;
        $this->_jsonDecoder = $jsonDecoder;
    }

    /**
     * admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->moveDirToMediaDir();
        $customer = $observer->getCustomer();
        $customerid = $customer->getId();
        $postData = $observer->getRequest()->getPostValue();
        if ($this->isSeller($customerid)) {
            list($data, $errors) = $this->validateprofiledata($observer);
            $productIds = isset($postData['sellerassignproid']) ?
            $postData['sellerassignproid'] : '';
            $sellerId = $customerid;
            if (isset($postData['is_seller_remove'])
                && $postData['is_seller_remove'] == true) {
                $this->removePartner($sellerId);
                $this->_messageManager->addSuccess(
                    __('You removed the customer from seller.')
                );

                return $this;
            }
            if ($productIds != '' || $productIds != 0) {
                $this->assignProduct($sellerId, $productIds);
            }
            if (!empty($postData['commission_enable'])) {
                $collectionselect = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleperpartner'
                )->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
                if ($collectionselect->getSize() == 1) {
                    foreach ($collectionselect as $verifyrow) {
                        $autoid = $verifyrow->getEntityId();
                    }

                    $collectionupdate = $this->_objectManager->get(
                        'Webkul\Marketplace\Model\Saleperpartner'
                    )->load($autoid);
                    if (!isset($postData['commission'])) {
                        $postData['commission'] = $collectionupdate->getCommissionRate();
                    }
                    $collectionupdate->setCommissionRate($postData['commission']);
                    $collectionupdate->save();
                } else {
                    if (!isset($postData['commission'])) {
                        $postData['commission'] = 0;
                    }
                    $collectioninsert = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Saleperpartner'
                    );
                    $collectioninsert->setSellerId($sellerId);
                    $collectioninsert->setCommissionRate($postData['commission']);
                    $collectioninsert->save();
                }
            }
            if (empty($errors)) {
                $target = $this->_mediaDirectory->getAbsolutePath('avatar/');
                // upload logo file
                $postData['banner_pic'] = $this->uploadSellerProfileImage(
                    $target,
                    'banner_pic'
                );

                // upload logo file
                $postData['logo_pic'] = $this->uploadSellerProfileImage(
                    $target,
                    'logo_pic'
                );

                $autoId = 0;
                $storeId = 0;
                if (!empty($postData['store_id'])) {
                    $storeId = $postData['store_id'];
                }
                $collection = $this->_collectionFactory->create()
                ->addFieldToFilter('seller_id', $sellerId)
                ->addFieldToFilter('store_id', $storeId);
                if (!count($collection)) {
                    $collection = $this->_collectionFactory->create()
                    ->addFieldToFilter('seller_id', $sellerId)
                    ->addFieldToFilter('store_id', 0);
                    foreach ($collection as $value) {
                        $postData['banner_pic'] = $postData['banner_pic'] ?
                        $postData['banner_pic'] : $value->getBannerPic();
                        $postData['logo_pic'] = $postData['logo_pic'] ?
                        $postData['logo_pic'] : $value->getLogoPic();
                    }
                    foreach ($collection as $value) {
                        $sellerDefaultData = $value->getData();
                        $postData['banner_pic'] = $postData['banner_pic'] ?
                        $postData['banner_pic'] : $value->getBannerPic();
                        $postData['logo_pic'] = $postData['logo_pic'] ?
                        $postData['logo_pic'] : $value->getLogoPic();
                    }
                    foreach ($sellerDefaultData as $key => $value) {
                        if (empty($postData[$key]) && $key != 'entity_id') {
                            $postData[$key] = $value;
                        }
                    }
                } else {
                    foreach ($collection as $value) {
                        $autoId = $value->getId();
                        $postData['banner_pic'] = $postData['banner_pic'] ?
                        $postData['banner_pic'] : $value->getBannerPic();
                        $postData['logo_pic'] = $postData['logo_pic'] ?
                        $postData['logo_pic'] : $value->getLogoPic();
                    }
                }
                $value = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                )->load($autoId);
                $value->addData($postData);
                $value->setIsSeller(1);
                $value->setUpdatedAt($this->_date->gmtDate());
                $value->save();
                if (isset($postData['seller_category_ids'])) {
                    $catIds = '';
                    foreach ($postData['seller_category_ids'] as $categoryId => $selected) {
                        if ($selected) {
                            $catIds = $catIds.$categoryId.',';
                        }
                    }
                    $catIds = rtrim($catIds, ',');
                    $value->setAllowedCategories($catIds);
                }
                if (isset($postData['company_description'])) {
                    $postData['company_description'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $postData['company_description']);
                    $value->setCompanyDescription(
                        $postData['company_description']
                    );
                }

                if (isset($postData['return_policy'])) {
                    $postData['return_policy'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $postData['return_policy']);
                    $value->setReturnPolicy($postData['return_policy']);
                }

                if (isset($postData['shipping_policy'])) {
                    $postData['shipping_policy'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $postData['shipping_policy']);
                    $value->setShippingPolicy($postData['shipping_policy']);
                }

                if (isset($postData['privacy_policy'])) {
                    $postData['privacy_policy'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $postData['privacy_policy']);
                    $value->setPrivacyPolicy($postData['privacy_policy']);
                }

                if (isset($postData['meta_description'])) {
                    $value->setMetaDescription($postData['meta_description']);
                }

                /**
                 * set taxvat number for seller
                 */
                if (isset($postData['taxvat'])) {
                    $customer = $this->_objectManager->create(
                        'Magento\Customer\Model\Customer'
                    )->load($sellerId);
                    $customer->setTaxvat($postData['taxvat']);
                    $customer->setId($sellerId)->save();
                }

                if (array_key_exists('country_pic', $postData)) {
                    $value->setCountryPic($postData['country_pic']);
                }
                $value->save();
            }
        } else {
            if (isset($postData['is_seller_add'])) {
                $isSellerAdd = $postData['is_seller_add'];
                $profileurl = $postData['profileurl'];
            } else {
                $isSellerAdd = false;
            }
            if ($isSellerAdd == true) {
                if ($profileurl != '') {
                    $profileurlcount = $this->_collectionFactory->create();
                    $profileurlcount->addFieldToFilter('shop_url', $profileurl);
                    $sellerProfileIds = [];
                    $sellerProfileUrl = '';
                    $collectionselect = $this->_collectionFactory->create();
                    $collectionselect->addFieldToFilter('seller_id', $customerid);
                    foreach ($collectionselect as $coll) {
                        array_push($sellerProfileIds, $coll->getEntityId());
                        $sellerProfileUrl = $coll->getShopUrl();
                    }
                    if ($profileurlcount->getSize() && ($profileurl != $sellerProfileUrl)) {
                        $this->_messageManager->addError(
                            __('This Shop URL already Exists.')
                        );
                    } else {
                        if (!empty($sellerProfileIds)) {
                            foreach ($sellerProfileIds as $sellerProfileId) {
                                $collection = $this->_objectManager->get(
                                    'Webkul\Marketplace\Model\Seller'
                                )->load($sellerProfileId);
                                $collection->setIsSeller(1);
                                $collection->setShopUrl($profileurl);
                                $collection->setSellerId($customerid);
                                $collection->setCreatedAt($this->_date->gmtDate());
                                $collection->setUpdatedAt($this->_date->gmtDate());
                                $collection->save();
                            }
                        } else {
                            $sellerProfileId = 0;
                            $collection = $this->_objectManager->get(
                                'Webkul\Marketplace\Model\Seller'
                            )->load($sellerProfileId);
                            $collection->setIsSeller(1);
                            $collection->setShopUrl($profileurl);
                            $collection->setStoreId(0);
                            $collection->setSellerId($customerid);
                            $collection->setCreatedAt($this->_date->gmtDate());
                            $collection->setUpdatedAt($this->_date->gmtDate());
                            $collection->save();
                        }

                        $helper = $this->_objectManager->get(
                            'Webkul\Marketplace\Helper\Data'
                        );
                        $adminStoreEmail = $helper->getAdminEmailId();
                        $adminEmail = $adminStoreEmail ? $adminStoreEmail :
                        $helper->getDefaultTransEmailId();
                        $adminUsername = 'Admin';

                        $seller = $this->_objectManager->get(
                            'Magento\Customer\Model\Customer'
                        )->load($customerid);

                        $emailTempVariables['myvar1'] = $seller->getName();
                        $emailTempVariables['myvar2'] = $this->_storeManager
                        ->getStore()->getUrl(
                            'customer/account/login'
                        );
                        $senderInfo = [
                            'name' => $adminUsername,
                            'email' => $adminEmail,
                        ];
                        $receiverInfo = [
                            'name' => $seller->getName(),
                            'email' => $seller->getEmail(),
                        ];
                        $this->_objectManager->create(
                            'Webkul\Marketplace\Helper\Email'
                        )->sendSellerApproveMail(
                            $emailTempVariables,
                            $senderInfo,
                            $receiverInfo
                        );
                        $this->_messageManager->addSuccess(
                            __('You created the customer as seller.')
                        );
                    }
                } else {
                    $this->_messageManager->addError(
                        __('Enter Shop Name of Customer.')
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Upload Seller Profile Image.
     *
     * @param string $target
     * @param string $fileName
     *
     * @return string
     */
    protected function uploadSellerProfileImage($target, $fileName)
    {
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(
                ['fileId' => $fileName]
            );
            $uploader->setAllowedExtensions(
                ['jpg', 'jpeg', 'gif', 'png']
            );
            $uploader->setAllowRenameFiles(true);
            $resul = $uploader->save($target);
            if ($resul['file']) {
                return $resul['file'];
            }
        } catch (\Exception $e) {
            return '';
        }
    }

    public function isSeller($customerid)
    {
        $sellerStatus = 0;
        $model = $this->_collectionFactory->create()
        ->addFieldToFilter('seller_id', $customerid)
        ->addFieldToFilter('store_id', 0);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }

        return $sellerStatus;
    }

    private function validateprofiledata($observer)
    {
        $errors = [];
        $data = [];
        $paramData = $observer->getRequest()->getParams();
        foreach ($paramData as $code => $value) {
            switch ($code) :
                case 'twitter_id':
                    if (trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)) {
                        $errors[] = __(
                            'Twitterid cannot contain space and special characters'
                        );
                    } else {
                        $data[$code] = $value;
                    }
                    break;
                case 'facebook_id':
                    if (trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)) {
                        $errors[] = __(
                            'Facebookid cannot contain space and special characters'
                        );
                    } else {
                        $data[$code] = $value;
                    }
            endswitch;
        }

        return [$data, $errors];
    }

    private function removePartner($sellerId)
    {
        $collectionselectdelete = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Seller'
        )->getCollection();
        $collectionselectdelete->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        foreach ($collectionselectdelete as $sellerColl) {
            $sellerColl->delete();
        }
        //Set Produt status disabled
        $sellerProduct = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );

        foreach ($sellerProduct as $productInfo) {
            $allStores = $this->_storeManager->getStores();
            foreach ($allStores as $_eachStoreId => $val) {
                try {
                    $product = $this->_productRepository->getById(
                        $productInfo->getMageproductId()
                    );
                    $product->setStatus(
                        ProductStatus::STATUS_DISABLED
                    );
                    $this->_productRepository->save($product);
                } catch (\Exception $e) {
                }
            }

            $productInfo->delete();
        }

        $helper = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Data'
        );
        $adminStoreEmail = $helper->getAdminEmailId();
        $adminEmail = $adminStoreEmail ?
        $adminStoreEmail : $helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';

        $seller = $this->_objectManager->get(
            'Magento\Customer\Model\Customer'
        )->load($sellerId);

        $emailTempVariables['myvar1'] = $seller->getName();
        $emailTempVariables['myvar2'] = $this->_storeManager->getStore()
        ->getUrl(
            'customer/account/login'
        );
        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $receiverInfo = [
            'name' => $seller->getName(),
            'email' => $seller->getEmail(),
        ];
        $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Email'
        )->sendSellerDisapproveMail(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo
        );
    }

    public function assignProduct($sellerId, $productIds)
    {
        $productids = array_flip($this->_jsonDecoder->decode($productIds));

        // get all seller's products
        $sellerProductIds = $this->_sellerProduct->getAllAssignProducts(
            '`seller_id`='.$sellerId
        );

        $additionalProductIds = array_diff(array_values($productids), array_values($sellerProductIds));
        $unassignProductIds = array_diff(array_values($sellerProductIds), array_values($productids));

        $helper = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Data'
        );
        $allowedProductTypeIds = explode(',', $helper->getAllowedProductType());

        // set product status to 1 to assign selected products from seller
        $productCollection = $this->_objectManager->create(
            'Magento\Catalog\Model\Product'
        )->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $additionalProductIds]
        )->addFieldToFilter(
            'type_id',
            ['in' => $allowedProductTypeIds]
        );
        $successMessage = '';
        foreach ($productCollection as $product) {
            $proid = $product->getId();
            $userid = '';
            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                $proid
            );
            $flag = 1;
            foreach ($collection as $coll) {
                $flag = 0;
                if ($sellerId != $coll['seller_id']) {
                    $this->_messageManager->addError(
                        __('The product with id %1 is already assigned to other seller.', $proid)
                    );
                } else {
                    $coll->setAdminassign(1)->save();
                }
            }
            if ($flag) {
                $collection1 = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Product'
                );
                $collection1->setMageproductId($proid);
                $collection1->setSellerId($sellerId);
                $collection1->setStatus($product->getStatus());
                $collection1->setAdminassign(1);
                $isApproved = 1;
                if ($product->getStatus() == 2 && $helper->getIsProductApproval()) {
                    $isApproved = 0;
                }
                $collection1->setIsApproved($isApproved);
                $collection1->setCreatedAt($this->_date->gmtDate());
                $collection1->setUpdatedAt($this->_date->gmtDate());
                $collection1->save();
                $successMessage = __(
                    'Products has been successfully assigned to seller.'
                );
            }
        }

        // remove unassign products from seller
        $this->unassignProduct($sellerId, $unassignProductIds);

        if ($successMessage) {
            $this->_messageManager->addSuccess($successMessage);
        }
    }

    public function unassignProduct($sellerId, $productIds)
    {
        $productids = $productIds;
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )->getCollection()
        ->addFieldToFilter(
            'mageproduct_id',
            ['in' => $productids]
        )
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        foreach ($collection as $coll) {
            $coll->delete();
        }
    }

    private function moveDirToMediaDir($value = '')
    {
        try {
            /** @var \Magento\Framework\ObjectManagerInterface $objManager */
            $objManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Framework\Module\Dir\Reader $reader */
            $reader = $objManager->get('Magento\Framework\Module\Dir\Reader');

            /** @var \Magento\Framework\Filesystem $filesystem */
            $filesystem = $objManager->get('Magento\Framework\Filesystem');

            $mediaAvatarFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('avatar');
            if (!file_exists($mediaAvatarFullPath)) {
                mkdir($mediaAvatarFullPath, 0777, true);
                $avatarBannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/avatar/banner-image.png';
                copy($avatarBannerImage, $mediaAvatarFullPath.'/banner-image.png');
                $avatarNoImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/avatar/noimage.png';
                copy($avatarNoImage, $mediaAvatarFullPath.'/noimage.png');
            }

            $mediaMarketplaceFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('marketplace');
            if (!file_exists($mediaMarketplaceFullPath)) {
                mkdir($mediaMarketplaceFullPath, 0777, true);
            }

            $mediaMarketplaceBannerFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('marketplace/banner');
            if (!file_exists($mediaMarketplaceBannerFullPath)) {
                mkdir($mediaMarketplaceBannerFullPath, 0777, true);
                $marketplaceBannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/marketplace/banner/sell-page-banner.png';
                copy(
                    $marketplaceBannerImage,
                    $mediaMarketplaceBannerFullPath.'/sell-page-banner.png'
                );
                // for landing page layout 2
                $marketplaceBannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/landingpage1/banner/sell-page-1-hero-banner.jpg';
                $this->_filesystemFile->cp(
                    $marketplaceBannerImage,
                    $mediaMarketplaceBannerFullPath.'/sell-page-1-hero-banner.jpg'
                );
                // for landing page layout 3
                $marketplaceBannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/landingpage2/banner/sell-page-2-hero-banner.jpg';
                $this->_filesystemFile->cp(
                    $marketplaceBannerImage,
                    $mediaMarketplaceBannerFullPath.'/sell-page-2-hero-banner.jpg'
                );
            }

            $mediaMarketplaceIconFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('marketplace/icon');
            if (!file_exists($mediaMarketplaceIconFullPath)) {
                mkdir($mediaMarketplaceIconFullPath, 0777, true);
                $icon1BannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/marketplace/icon/icon-add-products.png';
                copy(
                    $icon1BannerImage,
                    $mediaMarketplaceIconFullPath.'/icon-add-products.png'
                );

                $icon2BannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/marketplace/icon/icon-collect-revenues.png';
                copy(
                    $icon2BannerImage,
                    $mediaMarketplaceIconFullPath.'/icon-collect-revenues.png'
                );

                $icon3BannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/marketplace/icon/icon-register-yourself.png';
                copy(
                    $icon3BannerImage,
                    $mediaMarketplaceIconFullPath.'/icon-register-yourself.png'
                );

                $icon4BannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/marketplace/icon/icon-start-selling.png';
                copy(
                    $icon4BannerImage,
                    $mediaMarketplaceIconFullPath.'/icon-start-selling.png'
                );

                // for landing page layout 3
                $iconBannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-1.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-1.png'
                );
                $iconBannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-2.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-2.png'
                );
                $iconBannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-3.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-3.png'
                );
                $iconBannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-4.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-4.png'
                );
                $iconBannerImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-5.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-5.png'
                );
            }

            $mediaPlaceholderFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('placeholder');
            if (!file_exists($mediaPlaceholderFullPath)) {
                mkdir($mediaPlaceholderFullPath, 0777, true);
                $placeholderImage = $reader->getModuleDir(
                    '',
                    'Webkul_Marketplace'
                ).'/view/base/web/images/placeholder/image.jpg';
                copy(
                    $placeholderImage,
                    $mediaPlaceholderFullPath.'/image.jpg'
                );
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }
}
