<?php


/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Warehouse;

interface WarehouseStockRepositoryInterface
{
    /**
     * Get product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\ProductSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get stock of 1 SKU in warehouse
     *
     * @param int $warehouseId
     * @param string $productSku
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWarehouseStockBySku($warehouseId, $productSku);

    /**
     * Update stock of 1 SKU in warehouse
     *
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\UpdateWarehouseStockRequestInterface[] $warehouseStocks
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface[]
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function updateWarehouseStockBySku($warehouseStocks);
}