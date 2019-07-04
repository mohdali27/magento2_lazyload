<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\AdjustStock;

interface AdjustStockRepositoryInterface
{
    /**
     * Get adjust stock history by code
     *
     * @param string $adjustStockCode
     * @return \Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($adjustStockCode);

    /**
     * Create new adjust stock
     *
     * @param \Magestore\InventorySuccess\Api\Data\AdjustStock\CreateAdjustStockRequestInterface $adjustStockRequest
     * @return \Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface
     */
    public function createAdjustStock($adjustStockRequest);
}

