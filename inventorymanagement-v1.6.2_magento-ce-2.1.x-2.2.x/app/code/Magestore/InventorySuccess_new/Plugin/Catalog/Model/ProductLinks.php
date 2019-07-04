<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\Catalog\Model;

class ProductLinks
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * ProductLinks constructor.
     *
     * @param Configuration $configuration
     * @param Stock $stockHelper
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Configuration $configuration,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
    )
    {
        $this->configuration = $configuration;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param Link $subject
     * @param Collection $collection
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductCollection(
        \Magento\Catalog\Model\Product\Link $subject,
        \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $collection
    )
    {
        if ($this->configuration->isShowOutOfStock() != 1) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
            $collection->getSelect()->where('at_inventory_in_stock.website_id = ?', $scopeId);
        }
        return $collection;
    }
}