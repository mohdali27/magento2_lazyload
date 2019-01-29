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
namespace Webkul\Marketplace\Plugin\Helper;

class Category
{
    /**
     * Store categories cache
     *
     * @var array
     */
    protected $_storeCategories = [];

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Lib data collection factory
     *
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Magento\Catalog\Model\CategoryFactory             $categoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Data\CollectionFactory          $dataCollectionFactory
     * @param CategoryRepositoryInterface                        $categoryRepository
     * @param \Magento\Framework\App\Request\Http                $request
     * @param \Magento\Customer\Api\CustomerRepositoryInterface  $customerRepositoryInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\CollectionFactory $dataCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->request = $request;
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * @param \Magento\Catalog\Helper\Category $subject
     * @param callable $proceed
     * @param $sorted
     * @param $asCollection
     * @param $toLoad
     * @return array
     */
    public function aroundGetStoreCategories(
        \Magento\Catalog\Helper\Category $subject,
        callable $proceed,
        $sorted = false,
        $asCollection = false,
        $toLoad = true
    ) {
        $parent = $this->_storeManager->getStore()->getRootCategoryId();
        if (!empty($this->request->getParam('id')) && $this->request->getFullActionName() == 'customer_index_edit') {
            $storeId = $this->_customerRepositoryInterface->getById(
                $this->request->getParam('id')
            )->getStoreId();
            $parent = $this->_storeManager->getStore($storeId)->getRootCategoryId();
            if (!$parent) {
                $parent = $this->_storeManager->getStore()->getRootCategoryId();
            }
            $cacheKey = sprintf(
                '%d-%d-%d-%d',
                $parent,
                $sorted,
                $asCollection,
                $toLoad
            );
            if (isset($this->_storeCategories[$cacheKey])) {
                return $this->_storeCategories[$cacheKey];
            }

            $categoryData = $this->_categoryFactory->create();
            if (!$categoryData->checkId($parent)) {
                if ($asCollection) {
                    return $this->_dataCollectionFactory->create();
                }
                return [];
            }
            $navigationMaxDepth = (int)$this->scopeConfig->getValue(
                'catalog/navigation/max_depth',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $recursionLevel = max(0, $navigationMaxDepth);
            $storeCategories = $categoryData->getCategories(
                $parent,
                $recursionLevel,
                $sorted,
                $asCollection,
                $toLoad
            );

            $this->_storeCategories[$cacheKey] = $storeCategories;
            return $storeCategories;
        }
        return $proceed();
    }
}
