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

namespace Webkul\Marketplace\Block\Product;

/*
 * Webkul Marketplace Product Create Block
 */
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\GoogleOptimizer\Model\Code as ModelCode;
use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\DB\Helper as FrameworkDbHelper;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;

class Create extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var ModelCode
     */
    protected $_modelCode;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var FrameworkDbHelper
     */
    protected $frameworkDbHelper;

    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;

    /**
     * @var CacheInterface
     */
    private $cacheInterface;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Cms\Model\Wysiwyg\Config      $wysiwygConfig
     * @param Product                                $product
     * @param Category                               $category
     * @param ModelCode                              $modelCode
     * @param HelperData                             $helperData
     * @param ProductRepositoryInterface             $productRepository
     * @param CollectionFactory                      $categoryCollectionFactory
     * @param FrameworkDbHelper                      $frameworkDbHelper
     * @param CategoryHelper                         $categoryHelper
     * @param DataPersistorInterface                 $dataPersistor
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        Product $product,
        Category $category,
        ModelCode $modelCode,
        HelperData $helperData,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $categoryCollectionFactory,
        FrameworkDbHelper $frameworkDbHelper,
        CategoryHelper $categoryHelper,
        DataPersistorInterface $dataPersistor,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_product = $product;
        $this->_category = $category;
        $this->_modelCode = $modelCode;
        $this->_helperData = $helperData;
        $this->_productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->frameworkDbHelper = $frameworkDbHelper;
        $this->categoryHelper = $categoryHelper;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $data);
    }

    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData());
    }

    public function getProduct($id)
    {
        return $this->_product->load($id);
    }

    public function getCategory()
    {
        return $this->_category;
    }

    /**
     * Get Googleoptimizer Fields Values.
     *
     * @param ModelCode|null $experimentCodeModel
     *
     * @return array
     */
    public function getGoogleoptimizerFieldsValues()
    {
        $entityId = $this->getRequest()->getParam('id');
        $storeId = $this->_helperData->getCurrentStoreId();
        $experimentCodeModel = $this->_modelCode->loadByEntityIdAndType(
            $entityId,
            'product',
            $storeId
        );
        $result = [];
        $result['experiment_script'] =
        $experimentCodeModel ? $experimentCodeModel->getExperimentScript() : '';
        $result['code_id'] =
        $experimentCodeModel ? $experimentCodeModel->getCodeId() : '';

        return $result;
    }

    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }

    /**
     * Retrieve cache interface
     *
     * @return CacheInterface
     */
    private function getCacheModel()
    {
        if (!$this->cacheInterface) {
            $this->cacheInterface = ObjectManager::getInstance()
                ->get(cacheInterface::class);
        }
        return $this->cacheInterface;
    }

     /**
     * Retrieve categories tree
     *
     * @param string|null $filter
     * @return array
     */
    public function getCategoriesTree($filter = null)
    {
        $helper = $this->_helperData;
        if (!$helper->getAllowedCategoryIds()) {
            $categoryTree = $this->getCacheModel()->load('seller_category_tree_' . $filter);
            if ($categoryTree) {
                return json_encode(unserialize($categoryTree));
            }
        }
        $storeId = $this->_helperData->getCurrentStoreId();
        $categoryCollection = $this->categoryCollectionFactory->create();
        if ($filter !== null) {
            $categoryCollection->addAttributeToFilter(
                'name',
                ['like' => $this->frameworkDbHelper->addLikeEscape($filter, ['position' => 'any'])]
            );
        }

        if ($helper->getAllowedCategoryIds()) {
            $allowedCategoryIds = explode(',', trim($helper->getAllowedCategoryIds()));
            $categoryCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['in' => $allowedCategoryIds])
            ->setStoreId($storeId);
        } else {
            $categoryCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => Category::TREE_ROOT_ID])
            ->setStoreId($storeId);
        }
        $shownCategoriesIds = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            foreach (explode('/', $category['path']) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection = $this->categoryCollectionFactory->create();

        $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id']);

        $sellerCategory = [
            Category::TREE_ROOT_ID => [
                'value' => Category::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            $catId = $category->getId();
            $catParentId = $category->getParentId();
            foreach ([$catId, $catParentId] as $categoryId) {
                if (!isset($sellerCategory[$categoryId])) {
                    $sellerCategory[$categoryId] = ['value' => $categoryId];
                }
            }

            $sellerCategory[$catId]['is_active'] = $category->getIsActive();
            $sellerCategory[$catId]['label'] = $category->getName();
            $sellerCategory[$catParentId]['optgroup'][] = &$sellerCategory[$catId];
        }
        if (!$helper->getAllowedCategoryIds()) {
            $this->getCacheModel()->save(
                serialize($sellerCategory[Category::TREE_ROOT_ID]['optgroup']),
                'seller_category_tree_' . $filter,
                [
                    Category::CACHE_TAG,
                    \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
                ]
            );
        }
        return json_encode($sellerCategory[Category::TREE_ROOT_ID]['optgroup']);
    }

    public function getPersistentData()
    {
        $persistentData = (array)$this->dataPersistor->get('seller_catalog_product');
        if (empty($persistentData['set'])) {
            $persistentData['set'] = '';
        }
        if (empty($persistentData['type'])) {
            $persistentData['type'] = '';
        }
        if (empty($persistentData['product'])) {
            $persistentData['product'] = [];
        }
        if (empty($persistentData['product']['name'])) {
            $persistentData['product']['name'] = '';
        }
        if (empty($persistentData['product']['category_ids'])) {
            $persistentData['product']['category_ids'] = [];
        }
        if (empty($persistentData['product']['description'])) {
            $persistentData['product']['description'] = '';
        }
        if (empty($persistentData['product']['short_description'])) {
            $persistentData['product']['short_description'] = '';
        }
        if (empty($persistentData['product']['sku'])) {
            $persistentData['product']['sku'] = '';
        }
        if (empty($persistentData['product']['price'])) {
            $persistentData['product']['price'] = '';
        }
        if (empty($persistentData['product']['special_price'])) {
            $persistentData['product']['special_price'] = '';
        }
        if (empty($persistentData['product']['special_from_date'])) {
            $persistentData['product']['special_from_date'] = '';
        }
        if (empty($persistentData['product']['special_to_date'])) {
            $persistentData['product']['special_to_date'] = '';
        }
        if (empty($persistentData['product']['product_has_weight'])) {
            $persistentData['product']['product_has_weight'] = 1;
        }
        if (empty($persistentData['product']['weight'])) {
            $persistentData['product']['weight'] = '';
        }
        if (empty($persistentData['product']['mp_product_cart_limit'])) {
            $persistentData['product']['mp_product_cart_limit'] = '';
        }
        if (empty($persistentData['product']['visibility'])) {
            $persistentData['product']['visibility'] = '';
        }
        if (empty($persistentData['product']['tax_class_id'])) {
            $persistentData['product']['tax_class_id'] = '';
        }
        if (empty($persistentData['product']['meta_title'])) {
            $persistentData['product']['meta_title'] = '';
        }
        if (empty($persistentData['product']['meta_keyword'])) {
            $persistentData['product']['meta_keyword'] = '';
        }
        if (empty($persistentData['product']['meta_description'])) {
            $persistentData['product']['meta_description'] = '';
        }
        if (empty($persistentData['product']['quantity_and_stock_status'])) {
            $persistentData['product']['quantity_and_stock_status'] = [];
        }
        if (empty($persistentData['product']['quantity_and_stock_status']['is_in_stock'])) {
            $persistentData['product']['quantity_and_stock_status']['is_in_stock'] = 1;
        }
        if (empty($persistentData['product']['quantity_and_stock_status']['qty'])) {
            $persistentData['product']['quantity_and_stock_status']['qty'] = '';
        }
        if (empty($persistentData['product']['image'])) {
            $persistentData['product']['image'] = '';
        }
        if (empty($persistentData['product']['small_image'])) {
            $persistentData['product']['small_image'] = '';
        }
        if (empty($persistentData['product']['thumbnail'])) {
            $persistentData['product']['thumbnail'] = '';
        }
        $this->dataPersistor->clear('seller_catalog_product');
        return $persistentData;
    }
}
