<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Api\LowStockNotification;

/**
 * Interface RuleRepositoryInterface
 * @api
 */
interface RuleRepositoryInterface
{
    /**
     * @param \Magestore\InventorySuccess\Api\Data\LowStockNotification\RuleInterface $rule
     * @return \Magestore\InventorySuccess\Api\Data\LowStockNotification\RuleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magestore\InventorySuccess\Api\Data\LowStockNotification\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return \Magestore\InventorySuccess\Api\Data\LowStockNotification\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($ruleId);

    /**
     * @param \Magestore\InventorySuccess\Api\Data\LowStockNotification\RuleInterface $rule
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Magestore\InventorySuccess\Api\Data\LowStockNotification\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($ruleId);
}
