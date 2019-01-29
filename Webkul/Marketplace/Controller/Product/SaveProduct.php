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

namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\Marketplace\Helper\Data as MarketplaceHelperData;
use Webkul\Marketplace\Model\Product as SellerProduct;
use Magento\Framework\App\ObjectManager;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory as SampleFactory;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory as LinkFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;

/**
 * Webkul Marketplace SaveProduct controller.
 */
class SaveProduct
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var Builder
     */
    protected $_marketplaceProductBuilder;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $_catalogProductTypeManager;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_catalogProductTypeConfigurable;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\VariationHandler
     */
    protected $_variationHandler;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface  */
    protected $_productRepositoryInterface;

    /** @var \Magento\Catalog\Api\ProductLinkInterfaceFactory  */
    protected $_productLinkFactory;

    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks
     */
    protected $_productLinks;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $_jsHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @var MarketplaceHelperData
     */
    protected $_marketplaceHelperData;

    /**
     * @var SampleFactory
     */
    private $sampleFactory;

    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @var \Magento\Downloadable\Model\Sample\Builder
     */
    private $sampleBuilder;

    /**
     * @var \Magento\Downloadable\Model\Link\Builder
     */
    private $linkBuilder;

    /**
     * @var \Magento\Catalog\Model\Product\Link\Resolver
     */
    private $linkResolver;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    /**
     * @param \Magento\Framework\Event\Manager                                  $eventManager
     * @param \Magento\Framework\ObjectManagerInterface                         $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                       $date
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date                    $dateFilter,
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager              $catalogProductTypeManager
     * @param \Magento\ConfigurableProduct\Model\Product\VariationHandler       $variationHandler
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable      $productTypeConfigurable
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                   $productRepositoryInterface
     * @param \Magento\Catalog\Api\ProductLinkInterfaceFactory                  $productLinkFactory
     * @param \Magento\Catalog\Model\Product\Link\Resolver                      $linkResolver
     * @param \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $productLinks
     * @param Filesystem                                                        $filesystem
     * @param Builder                                                           $catalogProductBuilder
     * @param MarketplaceHelperData                                             $marketplaceHelperData
     * @param \Magento\Framework\Registry                                       $registry
     * @param \Magento\Catalog\Model\Product\Copier                             $productCopier
     * @param \Magento\Framework\Message\ManagerInterface                       $messageManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Catalog\Model\Product\TypeTransitionManager $catalogProductTypeManager,
        \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeConfigurable,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory,
        \Magento\Catalog\Model\Product\Link\Resolver $linkResolver,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $productLinks,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Framework\Filesystem $filesystem,
        Builder $marketplaceProductBuilder,
        MarketplaceHelperData $marketplaceHelperData,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_date = $date;
        $this->_dateFilter = $dateFilter;
        $this->_catalogProductTypeManager = $catalogProductTypeManager;
        $this->_variationHandler = $variationHandler;
        $this->_catalogProductTypeConfigurable = $productTypeConfigurable;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_productLinkFactory = $productLinkFactory;
        $this->_linkResolver = $linkResolver;
        $this->_productLinks = $productLinks;
        $this->_jsHelper = $jsHelper;
        $this->_marketplaceProductBuilder = $marketplaceProductBuilder;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_marketplaceHelperData = $marketplaceHelperData;
        $this->_registry = $registry;
        $this->productCopier = $productCopier;
        $this->_messageManager = $messageManager;
    }

    /**
     * saveProductData method for seller's product save action.
     *
     * @param $sellerId
     * @param $wholdata
     *
     * @return array
     */
    public function saveProductData($sellerId, $wholedata)
    {
        $returnArr = [];
        $returnArr['error'] = 0;
        $returnArr['product_id'] = '';
        $returnArr['message'] = '';
        $wholedata['new-variations-attribute-set-id'] = $wholedata['set'];
        $wholedata['product']['attribute_set_id'] = $wholedata['set'];

        $helper = $this->_marketplaceHelperData;

        $this->_registry->register('mp_flat_catalog_flag', 1);

        if (!empty($wholedata['id'])) {
            $mageProductId = $wholedata['id'];
            $editFlag = 1;
            $storeId = $helper->getCurrentStoreId();
            if (!$helper->getCustomerSharePerWebsite()) {
                $savedWebsiteIds = $this->_productRepositoryInterface
                ->getById(
                    $mageProductId
                )->getWebsiteIds();
                foreach ($wholedata['product']['website_ids'] as $websiteId) {
                    if (in_array($websiteId, $savedWebsiteIds)) {
                        $wholedata['product']['website_ids'] = $savedWebsiteIds;
                    } else {
                        array_push($savedWebsiteIds, $websiteId);
                    }
                }
                $wholedata['product']['website_ids'] = $savedWebsiteIds;
            }
            $this->_eventManager->dispatch(
                'mp_customattribute_deletetierpricedata',
                [$wholedata]
            );
            $wholedata = $this->adminStoreMediaImages($mageProductId, $wholedata);
        } else {
            $mageProductId = '';
            $editFlag = 0;
            $storeId = 0;
            $wholedata['product']['website_ids'][] = $helper->getWebsiteId();
            $wholedata['product']['url_key'] = '';
        }
        if ($mageProductId) {
            $status1 = $helper->getIsProductEditApproval() ?
            SellerProduct::STATUS_DISABLED : SellerProduct::STATUS_ENABLED;
            if ($helper->getIsProductApproval() && !$helper->getIsProductEditApproval()) {
                $sellerProductColls = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Product'
                )
                ->getCollection()
                ->addFieldToFilter(
                    'mageproduct_id',
                    $mageProductId
                )->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
                foreach ($sellerProductColls as $sellerProductColl) {
                    $status1 = !$sellerProductColl->getIsApproved() ?
                    SellerProduct::STATUS_DISABLED : SellerProduct::STATUS_ENABLED;
                }
            }
        } else {
            $status1 = $helper->getIsProductApproval() ?
            SellerProduct::STATUS_DISABLED : SellerProduct::STATUS_ENABLED;
        }

        $status = isset($wholedata['status'])?$wholedata['status']:$status1;

        $wholedata['store'] = $storeId;
        /*
        * Marketplace Product save before Observer
        */
        $this->_eventManager->dispatch(
            'mp_product_save_before',
            [$wholedata]
        );

        $catalogProductTypeId = $wholedata['type'];

        /*
        * Product Initialize method to set product data
        */
        $catalogProduct = $this->productInitialize(
            $this->_marketplaceProductBuilder->build($wholedata, $storeId),
            $wholedata
        );

        /*for downloadable products start*/

        $catalogProduct = $this->buildDownloadableProduct($catalogProduct, $wholedata);

        /*for downloadable products end*/

        /*for configurable products start*/

        $associatedProductIds = [];

        $resultData = $this->buildConfigurableProduct($catalogProduct, $wholedata);

        $catalogProduct = $resultData['catalogProduct'];
        $associatedProductIds = $resultData['associatedProductIds'];

        /*for configurable products end*/

        $this->_catalogProductTypeManager->processProduct($catalogProduct);

        $set = $catalogProduct->getAttributeSetId();

        $type = $catalogProduct->getTypeId();
        if (isset($set) && isset($type)) {
            $allowedsets = explode(',', $helper->getAllowedAttributesetIds());
            $allowedtypes = explode(',', $helper->getAllowedProductType());
            if (!in_array($type, $allowedtypes) || !in_array($set, $allowedsets)) {
                $returnArr['error'] = 1;
                $returnArr['message'] = __('Product Type Invalid Or Not Allowed');

                return $returnArr;
            }
        } else {
            $returnArr['error'] = 1;
            $returnArr['message'] = __('Product Type Invalid Or Not Allowed');

            return $returnArr;
        }
        if (isset($type) && $type == 'configurable') {
            $allowedtypes = explode(',', $helper->getAllowedProductType());
            if (!in_array('virtual', $allowedtypes) && empty($wholedata['product']['weight'])) {
                $returnArr['error'] = 1;
                $returnArr['message'] = __(
                    'You are not allowed to add virtual products, so please set product weight.'
                );

                return $returnArr;
            }
        }

        if ($catalogProduct->getSpecialPrice() == '') {
            $catalogProduct->setSpecialPrice(null);
            $catalogProduct->getResource()->saveAttribute($catalogProduct, 'special_price');
        }
        $catalogProduct->setUrlKey('');
        $originalSku = $catalogProduct->getSku();
        $catalogProduct->setStatus($status)->save();
        $mageProductId = $catalogProduct->getId();
        $this->handleImageRemoveError($wholedata, $mageProductId);
        $this->getCategoryLinkManagement()->assignProductToCategories(
            $catalogProduct->getSku(),
            $catalogProduct->getCategoryIds()
        );

        /*for configurable associated products save start*/

        $this->saveConfigurableAssociatedProducts($wholedata, $storeId);

        /*for configurable associated products save end*/

        $wholedata['id'] = $mageProductId;
        $this->_eventManager->dispatch(
            'mp_customoption_setdata',
            [$wholedata]
        );

        /* Update marketplace product*/
        $this->saveMaketplaceProductTable(
            $mageProductId,
            $sellerId,
            $status,
            $editFlag,
            $associatedProductIds
        );

        /*
        * Marketplace Custom Attribute Set Tier Price Observer
        */
        $this->_eventManager->dispatch(
            'mp_customattribute_settierpricedata',
            [$wholedata]
        );

        /*
        * Marketplace Product Save After Observer
        */
        $this->_eventManager->dispatch(
            'mp_product_save_after',
            [$wholedata]
        );

        /*
        * Marketplace Product Send Mail Function
        */
        $this->sendProductMail($wholedata, $sellerId, $editFlag);

        $returnArr['product_id'] = $mageProductId;

        /*
        * Create duplicate product
        */
        if (isset($wholedata['back']) && $wholedata['back'] === 'duplicate') {
            // get and save product for admin store id
            $storeId = 0;
            $duplicateCatalogProduct = $this->_marketplaceProductBuilder->build($wholedata, $storeId);

            $status1 = $helper->getIsProductApproval() ?
            SellerProduct::STATUS_DISABLED : SellerProduct::STATUS_ENABLED;
            $status = isset($wholedata['status'])?$wholedata['status']:$status1;

            $newProduct = $this->productCopier->copy($duplicateCatalogProduct);
            $this->_messageManager->addSuccessMessage(__('You duplicated the product.'));
            $newProductId = $newProduct->getEntityId();
            $wholedata['id'] = $newProductId;
            $editFlag = 0;

            $this->_eventManager->dispatch(
                'mp_customoption_setdata',
                [$wholedata]
            );

            $newAssociatedProductIds = [];

            /* Update marketplace product for duplicate product*/
            $this->saveMaketplaceProductTable(
                $newProductId,
                $sellerId,
                $status,
                $editFlag,
                $newAssociatedProductIds
            );

            /*
            * Marketplace Custom Attribute Set Tier Price Observer for duplicate product
            */
            $this->_eventManager->dispatch(
                'mp_customattribute_settierpricedata',
                [$wholedata]
            );

            /*
            * Marketplace Product Save After Observer for duplicate product
            */
            $this->_eventManager->dispatch(
                'mp_product_save_after',
                [$wholedata]
            );

            /*
            * Marketplace Product Send Mail Function for duplicate product
            */
            $this->sendProductMail($wholedata, $sellerId, $editFlag);
            $returnArr['product_id'] = $newProductId;
        }

        return $returnArr;
    }

    protected function adminStoreMediaImages($productId, $wholedata, $storeId = 0)
    {
        if (!empty($wholedata['product']['media_gallery'])) {
            $flag = 0;
            foreach ($wholedata['product']['media_gallery']['images'] as $key => $value) {
                if ($value['media_type'] == 'external-video') {
                    $flag = 1;
                }
            }
            if ($flag == 1) {
                $catalogProduct = $this->_productRepositoryInterface
                ->getById(
                    $productId,
                    false,
                    $storeId
                );
                /*for downloadable products start*/

                $catalogProduct = $this->buildDownloadableProduct($catalogProduct, $wholedata);

                /*for downloadable products end*/
                $catalogProduct->setMediaGallery($wholedata['product']['media_gallery'])->save();
                foreach ($wholedata['product']['media_gallery']['images'] as $key => $value) {
                    if ($value['value_id'] == '') {
                        unset($wholedata['product']['media_gallery']['images'][$key]);
                    }
                }
            }
        }
        return $wholedata;
    }

    /**
     * Get LinkFactory instance.
     *
     * @deprecated
     *
     * @return LinkFactory
     */
    private function getLinkFactory()
    {
        if (!$this->linkFactory) {
            $this->linkFactory = ObjectManager::getInstance()->get(LinkFactory::class);
        }

        return $this->linkFactory;
    }

    /**
     * Get Sample Factory.
     *
     * @deprecated
     *
     * @return SampleFactory
     */
    private function getSampleFactory()
    {
        if (!$this->sampleFactory) {
            $this->sampleFactory = ObjectManager::getInstance()->get(SampleFactory::class);
        }

        return $this->sampleFactory;
    }

    /**
     * Product initialize function before saving.
     *
     * @param \Magento\Catalog\Model\Product $catalogProduct
     * @param $requestData
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function productInitialize(\Magento\Catalog\Model\Product $catalogProduct, $requestData)
    {
        $helper = $this->_marketplaceHelperData;
        $requestProductData = $requestData['product'];
        unset($requestProductData['custom_attributes']);
        unset($requestProductData['extension_attributes']);

        /*
        * Manage seller product Stock data
        */
        $requestProductData = $this->manageSellerProductStock($requestProductData);

        $requestProductData = $this->normalizeProductData($requestProductData);

        if (!empty($requestProductData['is_downloadable'])) {
            $requestProductData['product_has_weight'] = 0;
        }

        $requestProductData = $this->manageProductCategoryWebsiteData($requestProductData);

        $wasLockedMedia = false;
        if ($catalogProduct->isLockedAttribute('media')) {
            $catalogProduct->unlockAttribute('media');
            $wasLockedMedia = true;
        }

        $requestProductData = $this->manageProductDateTimeFilter($catalogProduct, $requestProductData);

        if (isset($requestProductData['options'])) {
            $productOptions = $requestProductData['options'];
            unset($requestProductData['options']);
        } else {
            $productOptions = [];
        }

        $catalogProduct->addData($requestProductData);

        if ($wasLockedMedia) {
            $catalogProduct->lockAttribute('media');
        }

        if ($helper->getSingleStoreStatus()) {
            $catalogProduct->setWebsiteIds([$helper->getWebsiteId()]);
        }

        /*
         * Check for "Use Default Value" field value
         */
        $catalogProduct = $this->manageProductForDefaultAttribute($catalogProduct, $requestData);

        /*
         * Set Downloadable links if available
         */
        $catalogProduct = $this->manageProductDownloadableData($catalogProduct, $requestData);

        /*
         * Set Product options to product if exist
         */
        $catalogProduct = $this->manageProductOptionData($catalogProduct, $productOptions);

        /*
         * Set Product Custom options status to product
         */
        if (empty($requestData['affect_product_custom_options'])) {
            $requestData['affect_product_custom_options'] = '';
        }

        $catalogProduct->setCanSaveCustomOptions(
            (bool) $requestData['affect_product_custom_options']
            && !$catalogProduct->getOptionsReadonly()
        );

        return $catalogProduct;
    }

    /**
     * Set Downloadable Data in Product Model.
     *
     * @param \Magento\Catalog\Model\Product $catalogProduct
     * @param array $wholedata
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function buildDownloadableProduct($catalogProduct, $wholedata)
    {
        if (!empty($wholedata['downloadable']) && $downloadableParamData = $wholedata['downloadable']) {
            $downloadableParamData = $links = $this->getDownloadableParamData($downloadableParamData);

            $catalogProduct->setDownloadableData($downloadableParamData);

            $extension = $catalogProduct->getExtensionAttributes();

            if (isset($downloadableParamData['link']) && is_array($downloadableParamData['link'])) {
                $links = [];
                $links = $this->getDownloabaleLinkData($downloadableParamData, $links);
                $extension->setDownloadableProductLinks($links);
            }
            if (isset($downloadableParamData['sample']) && is_array($downloadableParamData['sample'])) {
                $samples = [];
                $samples = $this->getDownloabaleSampleData($downloadableParamData, $samples);
                $extension->setDownloadableProductSamples($samples);
            }
            $catalogProduct->setExtensionAttributes($extension);
            if ($catalogProduct->getLinksPurchasedSeparately()) {
                $catalogProduct->setTypeHasRequiredOptions(true)->setRequiredOptions(true);
            } else {
                $catalogProduct->setTypeHasRequiredOptions(false)->setRequiredOptions(false);
            }
        }
        return $catalogProduct;
    }

    /**
     * Set Downloadable Data in Product Model.
     *
     * @param \Magento\Catalog\Model\Product $catalogProduct
     * @param array $wholedata
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function buildConfigurableProduct($catalogProduct, $wholedata)
    {
        $associatedProductIds = [];
        if (!empty($wholedata['attributes'])) {
            $requestProductData = $wholedata['product'];
            $attributes = $wholedata['attributes'];
            $setId = $wholedata['set'];
            $catalogProduct->setAttributeSetId($setId);
            $this->_catalogProductTypeConfigurable->setUsedProductAttributeIds(
                $attributes,
                $catalogProduct
            );

            $extensionAttributes = $catalogProduct->getExtensionAttributes();

            $catalogProduct->setNewVariationsAttributeSetId($setId);
            $configurableOptions = [];

            $extensionAttributes->setConfigurableProductOptions($configurableOptions);

            if (!empty($wholedata['associated_product_ids'])) {
                $associatedProductIds = $wholedata['associated_product_ids'];
            }
            // Get variationsMatrix
            $variationsMatrix = [];
            if (!empty($wholedata['variations-matrix'])) {
                foreach ($wholedata['variations-matrix'] as $key => $value) {
                    if (empty($value['weight'])) {
                        if (!empty($wholedata['product']['weight'])) {
                            $productWeight = $wholedata['product']['weight'];
                            $wholedata['variations-matrix'][$key]['weight'] = $productWeight;
                        } else {
                            $wholedata['variations-matrix'][$key]['product_has_weight'] = 0;
                        }
                    }
                }
                $variationsMatrix = $wholedata['variations-matrix'];
            }

            if ($associatedProductIds || $variationsMatrix) {
                $this->_variationHandler->prepareAttributeSet($catalogProduct);
            }

            if (!empty($variationsMatrix)) {
                $generatedProductIds = $this->_variationHandler->generateSimpleProducts(
                    $catalogProduct,
                    $variationsMatrix
                );
                $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
            }
            $extensionAttributes->setConfigurableProductLinks(array_filter($associatedProductIds));
            if (!isset($wholedata['affect_configurable_product_attributes'])) {
                $wholedata['affect_configurable_product_attributes'] = '';
            }
            $catalogProduct->setCanSaveConfigurableAttributes(
                (bool) $wholedata['affect_configurable_product_attributes']
            );

            $catalogProduct->setExtensionAttributes($extensionAttributes);
        }
        return ["catalogProduct" => $catalogProduct, "associatedProductIds" => $associatedProductIds];
    }

    private function saveConfigurableAssociatedProducts($wholedata, $storeId)
    {
        $configurations = [];
        if (!empty($wholedata['configurations'])) {
            $configurations = $wholedata['configurations'];
        }

        if (!empty($configurations)) {
            $configurations = $this->_variationHandler
            ->duplicateImagesForVariations($configurations);
            foreach ($configurations as $associtedProductId => $associtedProductData) {
                $associtedProduct = $this->_productRepositoryInterface
                ->getById(
                    $associtedProductId,
                    false,
                    $storeId
                );
                $associtedProductData = $this->_variationHandler
                ->processMediaGallery(
                    $associtedProduct,
                    $associtedProductData
                );
                $associtedProduct->addData($associtedProductData);
                if ($associtedProduct->hasDataChanges()) {
                    $associtedProduct->save();
                }
            }
        }
    }

    /**
     * Set Product Records in marketplace_product table.
     *
     * @param int $mageProductId
     * @param int $sellerId
     * @param int $status
     * @param int $editFlag
     * @param array $associatedProductIds
     */
    private function saveMaketplaceProductTable(
        $mageProductId,
        $sellerId,
        $status,
        $editFlag,
        $associatedProductIds
    ) {
        $savedIsApproved = 0;
        $sellerProductId = 0;
        $helper = $this->_marketplaceHelperData;
        if ($mageProductId) {
            $sellerProductColls = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )
            ->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                $mageProductId
            )->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            foreach ($sellerProductColls as $sellerProductColl) {
                $sellerProductId = $sellerProductColl->getId();
                $savedIsApproved = $sellerProductColl->getIsApproved();
            }
            $collection1 = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )->load($sellerProductId);
            $collection1->setMageproductId($mageProductId);
            $collection1->setSellerId($sellerId);
            $collection1->setStatus($status);
            $isApproved = 1;
            if ($helper->getIsProductEditApproval()) {
                $collection1->setAdminPendingNotification(2);
            }
            if (!$editFlag) {
                $collection1->setCreatedAt($this->_date->gmtDate());
                if ($helper->getIsProductApproval()) {
                    $isApproved = 0;
                    $collection1->setAdminPendingNotification(1);
                }
            } else if (!$helper->getIsProductEditApproval()) {
                $isApproved = $savedIsApproved;
            } else {
                $isApproved = 0;
            }
            $collection1->setIsApproved($isApproved);
            $collection1->setUpdatedAt($this->_date->gmtDate());
            $collection1->save();
        }

        foreach ($associatedProductIds as $associatedProductId) {
            if ($associatedProductId) {
                $sellerAssociatedProductId = 0;
                $sellerProductColls = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Product'
                )
                ->getCollection()
                ->addFieldToFilter(
                    'mageproduct_id',
                    $associatedProductId
                )
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
                foreach ($sellerProductColls as $sellerProductColl) {
                    $sellerAssociatedProductId = $sellerProductColl->getId();
                }
                $collection1 = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Product'
                )
                ->load($sellerAssociatedProductId);
                $collection1->setMageproductId($associatedProductId);
                if (!$editFlag) {
                    /* If new product is added*/
                    $collection1->setStatus(SellerProduct::STATUS_ENABLED);
                    $collection1->setCreatedAt($this->_date->gmtDate());
                }
                if ($editFlag) {
                    $collection1->setAdminPendingNotification(2);
                }
                $collection1->setUpdatedAt($this->_date->gmtDate());
                $collection1->setSellerId($sellerId);
                $collection1->setIsApproved(1);
                $collection1->save();
            }
        }
    }

    /**
     * @param array $requestProductData
     *
     * @return array
     */
    private function manageSellerProductStock($requestProductData)
    {
        if ($requestProductData) {
            if (isset($requestProductData['quantity_and_stock_status']['qty'])) {
                if ($requestProductData['quantity_and_stock_status']['qty'] < 0) {
                    $requestProductData['quantity_and_stock_status']['qty'] = abs(
                        $requestProductData['quantity_and_stock_status']['qty']
                    );
                    $requestProductData['stock_data']['qty'] = $requestProductData['quantity_and_stock_status']['qty'];
                }
            }
            $stockData = isset($requestProductData['stock_data']) ?
            $requestProductData['stock_data'] : [];
            if (isset($stockData['qty']) && (double) $stockData['qty'] > 99999999.9999) {
                $stockData['qty'] = 99999999.9999;
            }
            if (isset($stockData['min_qty']) && (int) $stockData['min_qty'] < 0) {
                $stockData['min_qty'] = 0;
            }
            if (!isset($stockData['use_config_manage_stock'])) {
                $stockData['use_config_manage_stock'] = 0;
            }
            if ($stockData['use_config_manage_stock'] == 1 && !isset($stockData['manage_stock'])) {
                $stockData['manage_stock'] = $this->stockConfiguration
                ->getManageStock();
            }
            if (!isset($stockData['is_decimal_divided']) || $stockData['is_qty_decimal'] == 0) {
                $stockData['is_decimal_divided'] = 0;
            }
            $requestProductData['stock_data'] = $stockData;
        }
        return $requestProductData;
    }

    /**
     * @param array $requestProductData
     *
     * @return array
     */
    private function manageProductCategoryWebsiteData($requestProductData)
    {
        foreach (['category_ids', 'website_ids'] as $field) {
            if (!isset($requestProductData[$field])) {
                $requestProductData[$field] = [
                    0=>$this->_marketplaceHelperData->getRootCategoryIdByStoreId()
                ];
            }
        }
        foreach ($requestProductData['website_ids'] as $websiteId => $checkboxValue) {
            if (!$checkboxValue) {
                unset($requestProductData['website_ids'][$websiteId]);
            }
        }
        return $requestProductData;
    }

    /**
     * @param Magento/Catalog/Model/Product $catalogProduct
     * @param array $requestProductData
     *
     * @return array
     */
    private function manageProductDateTimeFilter($catalogProduct, $requestProductData)
    {
        $dateFieldFilters = [];
        $attributes = $catalogProduct->getAttributes();
        foreach ($attributes as $attrKey => $attribute) {
            if ($attribute->getBackend()->getType() == 'datetime') {
                if (array_key_exists($attrKey, $requestProductData) && $requestProductData[$attrKey]!='') {
                    $dateFieldFilters[$attrKey] = $this->_dateFilter;
                }
            }
        }
        $inputFilter = new \Zend_Filter_Input(
            $dateFieldFilters,
            [],
            $requestProductData
        );
        $requestProductData = $inputFilter->getUnescaped();
        return $requestProductData;
    }

    /**
     * @param Magento/Catalog/Model/Product $catalogProduct
     * @param array $requestData
     *
     * @return Magento/Catalog/Model/Product
     */
    private function manageProductForDefaultAttribute($catalogProduct, $requestData)
    {
        if (!empty($requestData['use_default'])) {
            foreach ($requestData['use_default'] as $attributeCode => $useDefaultState) {
                if ($useDefaultState) {
                    $catalogProduct->setData($attributeCode, null);
                    if ($catalogProduct->hasData('use_config_'.$attributeCode)) {
                        $catalogProduct->setData('use_config_'.$attributeCode, false);
                    }
                }
            }
        }
        return $catalogProduct;
    }

    /**
     * @param Magento/Catalog/Model/Product $catalogProduct
     * @param array $requestData
     *
     * @return Magento/Catalog/Model/Product
     */
    private function manageProductDownloadableData($catalogProduct, $requestData)
    {
        if (!empty($requestData['links'])) {
            $links = $this->_linkResolver->getLinks();

            $catalogProduct->setProductLinks([]);

            $catalogProduct = $this->_productLinks->initializeLinks($catalogProduct, $links);
            $productLinks = $catalogProduct->getProductLinks();

            $linkTypes = [
                'related' => $catalogProduct->getRelatedReadonly(),
                'upsell' => $catalogProduct->getUpsellReadonly(),
                'crosssell' => $catalogProduct->getCrosssellReadonly()
            ];
            foreach ($linkTypes as $linkType => $readonly) {
                if (isset($links[$linkType]) && !$readonly) {
                    foreach ((array) $links[$linkType] as $linkData) {
                        if (empty($linkData['id'])) {
                            continue;
                        }

                        $linkProduct = $this->_productRepositoryInterface->getById($linkData['id']);
                        $link = $this->_productLinkFactory->create();
                        $link->setSku($catalogProduct->getSku())
                            ->setLinkedProductSku($linkProduct->getSku())
                            ->setLinkType($linkType)
                            ->setPosition(isset($linkData['position']) ? (int)$linkData['position'] : 0);
                        $productLinks[] = $link;
                    }
                }
            }
            $catalogProduct->setProductLinks($productLinks);
        }
        return $catalogProduct;
    }

    /**
     * @param Magento/Catalog/Model/Product $catalogProduct
     * @param $productOptions
     *
     * @return Magento/Catalog/Model/Product
     */
    public function manageProductOptionData($catalogProduct, $productOptions)
    {
        if ($productOptions && !$catalogProduct->getOptionsReadonly()) {
            // mark custom options that should to fall back to default value
            $options = $this->mergeProductOptions(
                $productOptions,
                []
            );
            $customOptions = [];
            foreach ($options as $customOptionData) {
                if (empty($customOptionData['is_delete'])) {
                    if (empty($customOptionData['option_id'])) {
                        $customOptionData['option_id'] = null;
                    }
                    if (isset($customOptionData['values'])) {
                        $customOptionData['values'] = array_filter(
                            $customOptionData['values'],
                            function ($valueData) {
                                return empty($valueData['is_delete']);
                            }
                        );
                    }
                    $customOption = $this->_objectManager->get(
                        'Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory'
                    )->create(['data' => $customOptionData]);
                    $customOption->setProductSku($catalogProduct->getSku());
                    $customOptions[] = $customOption;
                }
            }
            $catalogProduct->setOptions($customOptions);
        }
        return $catalogProduct;
    }

    /**
     * Internal normalization
     *
     * @param array $requestProductData
     *
     * @return array
     */
    private function normalizeProductData(array $requestProductData)
    {
        foreach ($requestProductData as $key => $value) {
            if (is_scalar($value)) {
                if ($value === 'true') {
                    $requestProductData[$key] = '1';
                } elseif ($value === 'false') {
                    $requestProductData[$key] = '0';
                }
            } elseif (is_array($value)) {
                $requestProductData[$key] = $this->normalizeProductData($value);
            }
        }

        return $requestProductData;
    }

    /**
     * Merge product and default options for product.
     *
     * @param array $productOptions   product options
     * @param array $overwriteOptions default value options
     *
     * @return array
     */
    public function mergeProductOptions($productOptions, $overwriteOptions)
    {
        if (!is_array($productOptions)) {
            return [];
        }

        if (!is_array($overwriteOptions)) {
            return $productOptions;
        }

        foreach ($productOptions as $index => $option) {
            $optionId = $option['option_id'];

            if (!isset($overwriteOptions[$optionId])) {
                continue;
            }

            foreach ($overwriteOptions[$optionId] as $fieldName => $overwrite) {
                if ($overwrite && isset($option[$fieldName]) && isset($option['default_'.$fieldName])) {
                    $productOptions[$index][$fieldName] = $option['default_'.$fieldName];
                }
            }
        }

        return $productOptions;
    }

    private function getDownloadableParamData($downloadableParamData)
    {
        if (isset($downloadableParamData['link']) && is_array($downloadableParamData['link'])) {
            foreach ($downloadableParamData['link'] as $key => $linkData) {
                if ($linkData['link_id'] == 0) {
                    $linkData['link_id'] = null;
                }
                $linkData['file'] = $this->_objectManager->get(
                    'Magento\Framework\Json\Helper\Data'
                )->jsonDecode($linkData['file']);
                $linkData['sample']['file'] = $this->_objectManager->get(
                    'Magento\Framework\Json\Helper\Data'
                )->jsonDecode($linkData['sample']['file']);
                $downloadableParamData['link'][$key]['link_id'] = $linkData['link_id'];
                $downloadableParamData['link'][$key]['file'] = $linkData['file'];
                $downloadableParamData['link'][$key]['sample']['file'] = $linkData['sample']['file'];
            }
        }
        if (isset($downloadableParamData['sample']) && is_array($downloadableParamData['sample'])) {
            foreach ($downloadableParamData['sample'] as $key => $sampleData) {
                if ($sampleData['sample_id'] == 0) {
                    $sampleData['sample_id'] = null;
                }
                $sampleData['file'] = $this->_objectManager->get(
                    'Magento\Framework\Json\Helper\Data'
                )->jsonDecode($sampleData['file']);
                $downloadableParamData['sample'][$key]['sample_id'] = $sampleData['sample_id'];
                $downloadableParamData['sample'][$key]['file'] = $sampleData['file'];
            }
        }
        return $downloadableParamData;
    }

    /**
     * Get Product Link Data from Post Downloadable Data.
     *
     * @param array $downloadableParamData
     * @param array $links
     *
     * @return array
     */
    private function getDownloabaleLinkData($downloadableParamData, $links)
    {
        foreach ($downloadableParamData['link'] as $linkData) {
            if (!$linkData || (isset($linkData['is_delete']) && $linkData['is_delete'])) {
                continue;
            } else {
                if (!$this->linkBuilder) {
                    $this->linkBuilder = ObjectManager::getInstance()->get(
                        \Magento\Downloadable\Model\Link\Builder::class
                    );
                }
                $links[] = $this->linkBuilder->setData(
                    $linkData
                )->build(
                    $this->getLinkFactory()->create()
                );
            }
        }
        return $links;
    }

    /**
     * Get Product Sample Data from Post Downloadable Data.
     *
     * @param array $downloadableParamData
     * @param array $sampleData
     *
     * @return array
     */
    private function getDownloabaleSampleData($downloadableParamData, $samples)
    {
        foreach ($downloadableParamData['sample'] as $sampleData) {
            if (!$sampleData || (isset($sampleData['is_delete']) && (bool) $sampleData['is_delete'])) {
                continue;
            } else {
                if (!$this->sampleBuilder) {
                    $this->sampleBuilder = ObjectManager::getInstance()->get(
                        \Magento\Downloadable\Model\Sample\Builder::class
                    );
                }
                $samples[] = $this->sampleBuilder->setData(
                    $sampleData
                )->build(
                    $this->getSampleFactory()->create()
                );
            }
        }
        return $samples;
    }

    /**
     * @param array  $data
     * @param string $sellerId
     * @param bool   $editFlag
     */
    private function sendProductMail($data, $sellerId, $editFlag = null)
    {
        $helper = $this->_marketplaceHelperData;

        $customer = $this->_objectManager->get(
            'Magento\Customer\Model\Customer'
        )->load($sellerId);

        $sellerName = $customer->getFirstname().' '.$customer->getLastname();
        $sellerEmail = $customer->getEmail();

        if (isset($data['product']) && !empty($data['product']['category_ids'])) {
            $categoriesy = $this->_objectManager->get(
                'Magento\Catalog\Model\Category'
            )->load(
                $data['product']['category_ids'][0]
            );
            $categoryname = $categoriesy->getName();
        } else {
            $categoryname = '';
        }

        $emailTempVariables = [];
        $adminStoremail = $helper->getAdminEmailId();
        $adminEmail = $adminStoremail ?
        $adminStoremail : $helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';

        $emailTempVariables['myvar1'] = $data['product']['name'];
        $emailTempVariables['myvar2'] = $categoryname;
        $emailTempVariables['myvar3'] = $adminUsername;
        if ($editFlag == null) {
            $emailTempVariables['myvar4'] = __(
                'I would like to inform you that recently I have added a new product in the store.'
            );
        } else {
            $emailTempVariables['myvar4'] = __(
                'I would like to inform you that recently I have updated a  product in the store.'
            );
        }
        $senderInfo = [
            'name' => $sellerName,
            'email' => $sellerEmail,
        ];
        $receiverInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        if (($editFlag == null && $helper->getIsProductApproval() == 1)
            || ($editFlag && $helper->getIsProductEditApproval() == 1)) {
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Email'
            )->sendNewProductMail(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $editFlag
            );
        }
    }

    /**
     * Notify seller when image was not deleted in specific case.
     *
     * @param array $wholedata
     * @param int $mageProductId
     * @return void
     */
    private function handleImageRemoveError($wholedata, $mageProductId)
    {
        if (isset($wholedata['product']['media_gallery']['images'])) {
            $removedImagesCount = 0;
            foreach ($wholedata['product']['media_gallery']['images'] as $image) {
                if (!empty($image['removed'])) {
                    $removedImagesCount++;
                }
            }
            if ($removedImagesCount) {
                $expectedImagesCount = count($wholedata['product']['media_gallery']['images']) - $removedImagesCount;
                $catalogProduct = $this->_productRepositoryInterface->getById($mageProductId);
                if ($expectedImagesCount != count($catalogProduct->getMediaGallery('images'))) {
                    $this->_messageManager->addNotice(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                }
            }
        }
    }

    /**
     * @return CategoryLinkManagementInterface
     */
    private function getCategoryLinkManagement()
    {
        if (null === $this->categoryLinkManagement) {
            $this->categoryLinkManagement = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(CategoryLinkManagementInterface::class);
        }
        return $this->categoryLinkManagement;
    }
}
