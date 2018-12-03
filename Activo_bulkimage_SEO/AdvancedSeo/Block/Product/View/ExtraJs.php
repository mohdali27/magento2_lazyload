<?php 
/**
 * Activo Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Activo Commercial License
 * that is available through the world-wide-web at this URL:
 * http://extensions.activo.com/license_professional
 *
 * @copyright   Copyright (c) 2017 Activo Extensions (http://extensions.activo.com)
 * @license     Commercial
 */

namespace Activo\AdvancedSeo\Block\Product\View;

class ExtraJs extends \Magento\Framework\View\Element\Template
{

    /**
     * Mage Registry
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    
    protected $_isScopePrivate;
    
    /**
     * Catalog Image
     * @var \Magento\Catalog\Helper\ImageFactory 
     */
    protected $imageHelperFactory;
    
    /**
     * Activo_AdvancedSeo Helper
     * @var \Activo\AdvancedSeo\Helper\Data 
     */
    protected $seoHelper;
    
    /**
     * Catalog Product
     */
    protected $_product;
    
    /**
     * CatalogInventory StockItem
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository 
     */
    protected $_stockItemRepository;
    
    
    protected $_storeManager;
    
    /**
     * Review 
     * @var \Magento\Review\Model\Review
     */
    protected $_reviewSummary;
    
    /**
     * Review Collection
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory 
     */
    protected $_reviewsColFactory;
    protected $_brandAttribute;

    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Activo\AdvancedSeo\Helper\Data $seoHelper
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param \Magento\Review\Model\Review $reviewSummary
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Activo\AdvancedSeo\Helper\Data $seoHelper, \Magento\Catalog\Helper\ImageFactory $imageHelperFactory, \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository, \Magento\Review\Model\Review $reviewSummary, \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory, array $data = []
    )
    {
        $this->_registry = $registry;
        $this->_isScopePrivate = true;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->seoHelper = $seoHelper;
        $this->_product = $this->_registry->registry('current_product');
        $this->_stockItemRepository = $stockItemRepository;
        $this->_storeManager = $context->getStoreManager();
        $this->_reviewSummary = $reviewSummary;
        $this->_reviewsColFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * 
     * @return type
     */
    public function getCurrentProduct()
    {
        if ($this->_product) {
            return $this->_product;
        }
        return $this->_registry->registry('current_product');
    }

    /**
     * Return Product thumbnail image
     * @return string
     */
    public function getProductImage()
    {
        $imageUrl = $this->imageHelperFactory->create()->init($this->_product, 'product_thumbnail_image')->getUrl();
        return $imageUrl;
    }

    /**
     * Check product is available or not.
     * @return string
     */
    public function getAvailability()
    {

        if (!$this->_product->getId()) {
            return "http://schema.org/OutOfStock";
        }
        if ($this->_product->isSaleable()) {
            return "http://schema.org/InStock";
        }

        return "http://schema.org/OutOfStock";
    }

    /**
     * Retrive current currency code
     * @return string
     */
    public function getCurrencyCode()
    {

        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Get Rating summary of the product
     * @return array
     */
    public function getRatingSummary()
    {

        $storeId = $this->_storeManager->getStore()->getId();
        $this->_reviewSummary->getEntitySummary($this->_product, $storeId);
        $ratingSummaryObj = $this->_product->getRatingSummary();
        $ratingSummary = $ratingSummaryObj->getRatingSummary();

        if (!isset($ratingSummary)) {
            return;
        }

        //created required array values to send over JSON data
        $ratingSummaryArray['ratingValue'] = ($ratingSummary * 5) / 100;
        $ratingSummaryArray['reviewCount'] = $ratingSummaryObj->getReviewsCount();
        return $ratingSummaryArray;
    }

    /**
     * Retrive product attribute code given in system configuration
     * @return string
     */
    private function _getBrandAttribute()
    {
        if (empty($this->_brandAttribute)) {
            $this->_brandAttribute = $this->seoHelper->getBrandAttribute();
        }

        return $this->seoHelper->getBrandAttribute();
    }

    /**
     * Retrive product attribute value 
     * @return string
     */
    public function getBrand()
    {
        $brandCode = $this->_getBrandAttribute();

        if (empty($brandCode)) {
            return;
        }
        return $this->_product->getAttributeText($brandCode);
    }

    /**
     * Retrive Review of the product
     * Limit set to 2 review sort by date
     * @return array
     */
    public function getReviews()
    {
        $this->_reviewsCollection = $this->_reviewsColFactory->create()->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product', $this->_product->getId()
            )->setDateOrder()->setPageSize(2); // Limit set to 2 review

        $this->_reviewsCollection->load()->addRateVotes();

        $reviews = $this->_reviewsCollection->getItems();

        if (!count($reviews)) {
            return;
        }
        $reviewData = [];
        foreach ($reviews as $review) {

            foreach ($review->getRatingVotes() as $_vote) {
                $reviewVote = [];
                $reviewVote['author'] = $review->getNickname();
                $reviewVote['datePublished'] = $review->getCreatedAt();
                $reviewVote['description'] = $review->getDetail();
                $reviewVote['name'] = $review->getTitle();
                $reviewVote['bestRating'] = 100;
                $reviewVote['ratingValue'] = $_vote->getPercent();
                $reviewVote['worstRating'] = 1;
                $reviewData[] = $reviewVote;
            }
        }
        return $reviewData;
    }

    /**
     * Retrive product price
     * Check product type and return Minimum price if product is configurable, grouped or bundled
     * @return double
     */
    public function getPrice()
    {

        $price = 0;
        $min = 10000;
        $productType = $this->_product->getTypeId();

        switch ($productType) {
            case 'configurable':
                
                $simpleProduct = $this->_product->getTypeInstance()->getUsedProducts($this->_product);
                foreach ($simpleProduct as $child) {
                    if ($child->getPrice() < $min) {
                        $min = $child->getPrice();
                    }
                }
                $price = $min;

                break;

            case 'bundle':

                $selectionCollection = $this->_product->getTypeInstance(true)->getSelectionsCollection(
                    $this->_product->getTypeInstance(true)->getOptionsIds($this->_product), $this->_product
                );
                foreach ($selectionCollection as $option) {
                    if ($option->getPrice() < $min) {
                        $min = $option->getPrice();
                    }
                }
                $price = $min;

                break;

            case 'grouped':
                
                $_associated_products = $this->_product->getTypeInstance(true)->getAssociatedProducts($this->_product);
                foreach ($_associated_products as $_associated_product) {
                    if ($_associated_product->getPrice() < $min) {
                        $min = $_associated_product->getPrice();
                    }
                }
                $price = $min;
                
                break;

            default:
                
                $price = $this->_product->getPrice();
                
                break;
        }
        return $price;
    }
}
