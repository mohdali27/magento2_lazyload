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

namespace Webkul\Marketplace\Block;

use Magento\Catalog\Model\Category;

class Sellercategory extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @param Category                                         $category
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        Category $category,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_category = $category;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    public function getCategoryById($id)
    {
        return $this->_category->load($id);
    }

    /**
     * @return array
     */
    public function getProfileDetail($value = '')
    {
        $shopUrl = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        )->getCollectionUrl();
        if (!$shopUrl) {
            $shopUrl = $this->getRequest()->getParam('shop');
        }
        if ($shopUrl) {
            $data = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Data'
            )->getSellerCollectionObjByShop($shopUrl);
            foreach ($data as $seller) {
                return $seller;
            }
        }
    }

    public function getCategoryList()
    {
        $sellerId = $this->getProfileDetail()->getSellerId();
        $querydata = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )
        ->getCollection()
        ->addFieldToFilter('seller_id', $sellerId)
        ->addFieldToFilter('status', ['neq' => 2])
        ->addFieldToSelect('mageproduct_id')
        ->setOrder('mageproduct_id');

        $collection = $this->_objectManager->create(
            'Magento\Catalog\Model\Product'
        )->getCollection()
        ->addAttributeToSelect('entity_id')
        ->addAttributeToFilter('entity_id', ['in' => $querydata->getData()])
        ->addAttributeToFilter('visibility', ['in' => [4]]);
        $collection->addStoreFilter();

        $marketplaceProduct = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
        )->getTable('marketplace_product');

        $collection->getSelect()->join(
            ['mpp' => $marketplaceProduct],
            'mpp.mageproduct_id = e.entity_id',
            ['mageproduct_id' => 'e.entity_id']
        );

        $eavAttribute = $this->_objectManager->get(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute'
        );
        $proAttId = $eavAttribute->getIdByCode('catalog_category', 'name');
        $isActiveAttrId = $eavAttribute->getIdByCode('catalog_category', 'is_active');

        $storeId = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        )->getCurrentStoreId();
        $paramData = $this->getRequest()->getParams();
        if (!isset($paramData['cat'])) {
            $paramData['cat'] = '';
        }
        if (!$paramData['cat']) {
            $parentid = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Data'
            )->getRootCategoryIdByStoreId($storeId);
        } else {
            $parentid = $paramData['cat'];
        }
        $catalogCategoryProduct = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('catalog_category_product');
        $catalogCategoryEntity = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('catalog_category_entity');
        $catalogCategoryEntityVarchar = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('catalog_category_entity_varchar');
        $catalogCategoryEntityInt = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('catalog_category_entity_int');

        $collection->getSelect()->join(
            ['ccp' => $catalogCategoryProduct],
            'ccp.product_id = mpp.mageproduct_id',
            ['category_id' => 'category_id']
        )->join(
            ['cce' => $catalogCategoryEntity],
            'cce.entity_id = ccp.category_id',
            ['parent_id' => 'parent_id']
        )
        ->where("cce.parent_id = '".$parentid."'")
        ->columns('COUNT(cce.entity_id) AS countCategory')
        ->group('category_id')
        ->join(
            ['ce1' => $catalogCategoryEntityVarchar],
            'ce1.entity_id = ccp.category_id',
            ['catname' => 'value']
        )->where('ce1.attribute_id = '.$proAttId.' AND ce1.store_id = 0')
        ->order('catname');
        $collection->getSelect()->join(
            ['ce2' => $catalogCategoryEntityInt],
            'ce2.entity_id = ccp.category_id'
        )->where('ce2.value = 1 AND ce2.attribute_id = '.$isActiveAttrId.' AND ce2.store_id = 0');

        return $collection->getData();
    }
}
