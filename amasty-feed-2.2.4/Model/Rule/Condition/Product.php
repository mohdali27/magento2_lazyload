<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Rule\Condition;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /** @var \Magento\Framework\Registry */
    private $registry;

    /** @var  array|null */
    private $productCategoryLink;
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $productType;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        array $data
    ) {
        parent::__construct(
            $context, $backendData, $config, $productFactory,
            $productRepository, $productResource, $attrSetCollection, $localeFormat, $data
        );

        $this->productType = $productType;
        $this->registry = $registry;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * @param AbstractModel $object
     *
     * @return array
     */
    public function getAvailableInCategories(AbstractModel $object)
    {
        $connection = $object->getResource()->getConnection();

        if ($registryIds = $this->registry->registry('fee_matching_product_ids')) {
            if ($this->productCategoryLink === null) {
                if (!$registryIds) {
                    $this->productCategoryLink = [];
                } else {
                    $select = $object->getResource()->getConnection()->select()->distinct()->from(
                        $object->getResource()->getTable('catalog_category_product'),
                        ['product_id', 'GROUP_CONCAT(category_id)']
                    )->where('product_id IN (?)', $registryIds)->group('product_id');
                    $this->productCategoryLink = $connection->fetchPairs($select);
                }
            }
            if (isset($this->productCategoryLink[(int)$object->getEntityId()])) {
                return explode(',', $this->productCategoryLink[(int)$object->getEntityId()]);
            }
            return [];
        }

        //old part

        // is_parent=1 ensures that we'll get only category IDs those are direct parents of the product, instead of
        // fetching all parent IDs, including those are higher on the tree
        $select = $object->getResource()->getConnection()->select()->distinct()->from(
            $object->getResource()->getTable('catalog_category_product'),
            ['category_id']
        )->where(
            'product_id = ?',
            (int)$object->getEntityId()
        );

        return $connection->fetchCol($select);
    }

    public function validate(AbstractModel $model)
    {
        $attrCode = $this->getAttribute();

        switch ($attrCode) {
            case 'quantity_and_stock_status':
                try {
                    $stockItem = $this->stockItemRepository->get($model->getEntityId());

                    return $this->validateAttribute($stockItem->getIsInStock());
                } catch (NoSuchEntityException $e) {
                    break;
                }
            case 'category_ids':
                return $this->validateAttribute($this->getAvailableInCategories($model));
        }

        $oldAttrValue = $model->hasData($attrCode) ? $model->getData($attrCode) : null;
        $this->_setAttributeValue($model);
        $result = $this->validateAttribute($model->getData($this->getAttribute()));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();

        $options = $this->getAttributeOption();

        $options['type_id'] = __('Type');

        // Override weird default attribute names
        $titles = [
            'status' => __('Status'),
            'quantity_and_stock_status' => __('Stock Status')
        ];

        foreach ($titles as $code => $title) {
            if (isset($options[$code])) {
                $options[$code] = $title;
            }
        }

        asort($options);

        $this->setAttributeOption($options);

        return $this;
    }

    public function getValueSelectOptions()
    {
        if ($this->getAttribute() == 'type_id') {
            $this->setData('value_select_options', $this->productType->getOptions());

            $this->getAttributeObject()->setFrontendInput('select');
        }

        return parent::getValueSelectOptions();
    }
}
