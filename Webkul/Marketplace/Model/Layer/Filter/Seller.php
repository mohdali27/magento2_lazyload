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
namespace Webkul\Marketplace\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;

class Seller extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    /**
     * Active Category Id
     *
     * @var int
     */
    protected $_categoryId;

    /**
     * Applied Category
     *
     * @var \Magento\Catalog\Model\Category
     */
    protected $_appliedCategory;

    /**
     * Core data
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        CategoryFactory $categoryDataProviderFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
        $this->_storeManager = $storeManager;
        $this->_escaper = $escaper;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->_resource = $resource;
        $this->_mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->_request = $request;
        $this->_mpHelper = $mpHelper;
        $this->_requestVar = $this->_mpHelper->getRequestVar();
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
     */
    public function getResetValue()
    {
        return $this->dataProvider->getResetValue();
    }

    /**
     * Apply category filter to layer
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        $collection = $this->getLayer()->getProductCollection();

        $productTable = $this->_resource->getTableName('marketplace_product');

        if ($filter == $this->_mpHelper::MARKETPLACE_ADMIN_URL) {
            $collection->getSelect()->where("e.entity_id not in (select mageproduct_id from $productTable)");
        } else {
            $sellerProductCollection = $this->_mpProductCollectionFactory->create();
            $sellerTable = $this->_resource->getTableName('marketplace_userdata');
            $fields = ['shop_url'];
            $sellerProductCollection->getSelect()->join($sellerTable.' as seller', 'seller.seller_id = main_table.seller_id', $fields);
            $sellerProductCollection->getSelect()->where("seller.shop_url = '".$filter."'");
            $sellerProductCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('main_table.mageproduct_id');
            $sellerProductCollection->getSelect()->group("main_table.mageproduct_id");
            $query = $sellerProductCollection->getSelect()->__toString();
            $collection->getSelect()->where("e.entity_id in ($query)");
        }

        $this->getLayer()->getState()->addFilter($this->_createItem($filter, $filter));
        return $this;
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        return __('Seller');
    }

    /**
     * Get data array for building attribute filter items
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    protected function _getItemsData()
    {
        if ($this->_mpHelper->isSellerFilterActive()) {
            return $this->itemDataBuilder->build();
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->getLayer()->getProductCollection();
        $col = clone $collection;
        $col->getSelect()
            ->reset(\Zend_Db_Select::LIMIT_COUNT)
            ->reset(\Zend_Db_Select::LIMIT_OFFSET)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->reset(\Zend_Db_Select::ORDER)
            ->columns('e.entity_id');

        $query = $col->getSelect()->__toString();
        $sellerProductCollection = $this->_mpProductCollectionFactory->create();
        $productTable = $this->_resource->getTableName('catalog_product_entity');
        $fields = ['entity_id', 'count(main_product.entity_id) as count'];
        $sellerProductCollection->getSelect()->joinRight($productTable.' as main_product', 'main_table.mageproduct_id = main_product.entity_id', $fields);
        $sellerProductCollection->getSelect()->where("main_product.entity_id in ($query)");
        $sellerProductCollection->getSelect()->group("main_table.seller_id");
        $sellerTable = $this->_resource->getTableName('marketplace_userdata');
        $field = 'IFNULL(NULLIF((select shop_url from '.$sellerTable.' as seller where (seller.seller_id=main_table.seller_id) group by seller.seller_id), ""), "")';
        $sellerProductCollection->getSelect()->columns(['shop_url' => new \Zend_Db_Expr($field)]);
        $field = 'IFNULL(NULLIF((select shop_title from '.$sellerTable.' as seller where (seller.seller_id=main_table.seller_id and store_id = 0) group by seller.seller_id), ""), "")';
        $sellerProductCollection->getSelect()->columns(['default_shop_title' => new \Zend_Db_Expr($field)]);
        $field = 'IFNULL(NULLIF((select shop_title from '.$sellerTable.' as seller where (seller.seller_id=main_table.seller_id and store_id = '.$storeId.') group by seller.seller_id), ""), "")';
        $sellerProductCollection->getSelect()->columns(['shop_title' => new \Zend_Db_Expr($field)]);

        foreach ($sellerProductCollection as $item) {
            $sellerId = (int) $item->getSellerId();
            $shopUrl = $item->getShopUrl();
            $defaultShopTitle = $item->getDefaultShopTitle();
            $shopTitle = $item->getShopTitle();
            $count = $item->getCount();
            if ($sellerId == 0) {
                $title = $this->_mpHelper->getAdminFilterDisplayName();
                $shopUrl = $this->_mpHelper::MARKETPLACE_ADMIN_URL;
            } else {
                if ($shopTitle != "") {
                    $title = $shopTitle;
                } elseif ($defaultShopTitle != "") {
                    $title = $defaultShopTitle;
                } else {
                    $title = $shopUrl;
                }
            }

            $this->itemDataBuilder->addItemData($title, $shopUrl, $count);
        }

        return $this->itemDataBuilder->build();
    }
}
