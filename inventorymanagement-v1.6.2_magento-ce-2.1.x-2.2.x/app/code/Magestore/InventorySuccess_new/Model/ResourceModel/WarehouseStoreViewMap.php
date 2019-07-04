<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel;

use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;

/**
 * Class WarehouseStoreViewMap
 * @package Magestore\InventorySuccess\Model\ResourceModel
 */
class WarehouseStoreViewMap extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAIN_TABLE = 'os_warehouse_store_view';

    /**
     *
     * @var \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface
     */
    protected $queryProcessor;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcessor,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->queryProcessor = $queryProcessor;
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }

    /**
     * @param int $warehouseId
     * @param array $storeIds
     * @return $this
     */
    public function linkWarehouseToStores($warehouseId, $storeIds)
    {
        $insertData = [];
        foreach ($storeIds as $storeId) {
            $insertData[] = [
                'warehouse_id' => $warehouseId,
                'store_id' => $storeId
            ];
        }

        $this->queryProcessor->start();
        $this->queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $insertData,
            'table' => $this->getMainTable()
        ]);
        $this->queryProcessor->process();
    }
}