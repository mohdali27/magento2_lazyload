<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\Bundle\Model\ResourceModel\Selection;


class Collection
{
    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfiguration;
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;
    /**
     * Collection constructor.
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    )
    {
        $this->stockConfiguration = $stockConfiguration;
        $this->productMetadata = $productMetadata;
    }

    /**
     *
     * @param \Magento\CatalogInventory\Model\Configuration $stockConfiguration
     * @param int $scopeId
     * @return int
     */
    public function afterAddQuantityFilter(\Magento\Bundle\Model\ResourceModel\Selection\Collection $subject, $collection)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $collection->getSelect()->where('stock.website_id = ?', $scopeId);
        $version = $this->productMetadata->getVersion();
        if(version_compare($version, '2.2.4', '>=')){
            $collection->getSelect()->where('stock_item.website_id = ?', $scopeId);
        }

        return $collection;
    }
}