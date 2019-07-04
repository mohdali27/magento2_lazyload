<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\RowCustomizer;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Amasty\Feed\Model\Export\Product as Export;

class Advanced implements RowCustomizerInterface
{
    const ATTRIBUTES = [
        'category_ids' => 'Category Ids',
    ];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var Export
     */
    private $export;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    public function __construct(
        Export $export,
        ProductRepositoryInterface $productRepository,
        StockItemRepositoryInterface $stockItemRepository
    ) {
        $this->export = $export;
        $this->productRepository = $productRepository;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->export->hasAttributes(Export::PREFIX_ADVANCED_ATTRIBUTE)) {
            $this->attributes = $this->export->getAttributesByType(Export::PREFIX_ADVANCED_ATTRIBUTE);
        }
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $dataRow['amasty_custom_data'][Export::PREFIX_ADVANCED_ATTRIBUTE] = [];

        foreach ($this->attributes as $attribute) {
            $result = '';

            switch ($attribute) {
                case 'category_ids':
                    $result = $this->getCategoryIds($productId);
                    break;
            }
            $dataRow['amasty_custom_data'][Export::PREFIX_ADVANCED_ATTRIBUTE][$attribute] = $result;
        }

        return $dataRow;
    }

    /**
     * @param int $productId
     *
     * @return string
     */
    private function getCategoryIds($productId)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId);
        $categoryIds = $product->getCategoryIds();

        return implode(",", $categoryIds);
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
