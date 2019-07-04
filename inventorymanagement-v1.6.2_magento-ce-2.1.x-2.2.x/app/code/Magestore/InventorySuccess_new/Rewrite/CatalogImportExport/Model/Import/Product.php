<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magestore\InventorySuccess\Rewrite\CatalogImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import as ImportExport;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;

/**
 * Import entity product model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends \Magento\CatalogImportExport\Model\Import\Product
{

    /**
     * Stock item saving.
     *
     * @return $this
     */
    public function _saveStockItem()
    {
        $indexer = $this->indexerRegistry->get('catalog_product_category');
        /** @var $stockResource \Magento\CatalogInventory\Model\ResourceModel\Stock\Item */
        $stockResource = $this->_stockResItemFac->create();
        $entityTable = $stockResource->getMainTable();

        $warehouseIds = [];
        $warehouses = [];
        $warehouseAdjustStock = [];
        $warehouseAdjustIds = [];
        $warehouseProductLocations = [];
        $warehouseLocationIds = [];
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection $warehouseCollection */
        $warehouseCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory'
        )->create();
        foreach ($warehouseCollection as $warehouse) {
            $warehouseIds[] = $warehouse->getId();
            $warehouses[$warehouse->getId()] = $warehouse->getData();
        }

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $stockData = [];
            $productIdsToReindex = [];
            // Format bunch to stock data rows
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $row = [];
                $row['product_id'] = $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['entity_id'];
                $productIdsToReindex[] = $row['product_id'];

                $row['website_id'] = $this->stockConfiguration->getDefaultScopeId();
                $row['stock_id'] = $this->stockRegistry->getStock($row['website_id'])->getStockId();

                $stockItemDo = $this->stockRegistry->getStockItem($row['product_id'], $row['website_id']);
                $existStockData = $stockItemDo->getData();

                $row = array_merge(
                    $this->defaultStockData,
                    array_intersect_key($existStockData, $this->defaultStockData),
                    array_intersect_key($rowData, $this->defaultStockData),
                    $row
                );

                if ($this->stockConfiguration->isQty(
                    $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['type_id']
                )) {
                    $stockItemDo->setData($row);
                    $row['is_in_stock'] = $this->stockStateProvider->verifyStock($stockItemDo);
                    if ($this->stockStateProvider->verifyNotification($stockItemDo)) {
                        $row['low_stock_date'] = $this->dateTime->gmDate(
                            'Y-m-d H:i:s',
                            (new \DateTime())->getTimestamp()
                        );
                    }
                    $row['stock_status_changed_auto'] =
                        (int) !$this->stockStateProvider->verifyStock($stockItemDo);
                } else {
                    $row['qty'] = 0;
                }
                /** get qty product when have warehouse */
                if (!empty($warehouseIds)) {
                    $qty = 0;
                    foreach ($warehouseIds as $warehouseId) {
                        /** @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistryInterface */
                        $warehouseStockRegistryInterface = \Magento\Framework\App\ObjectManager::getInstance()->create(
                            '\Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface'
                        );
                        /** @var \Magestore\InventorySuccess\Model\Warehouse\Product $warehouseStock */
                        $warehouseStock = $warehouseStockRegistryInterface->getStock($warehouseId, $row['product_id']);
                        $oldWarehouseProductQty = 0;
                        /** check qty product in warehouse */
                        if (isset($rowData['qty_'.$warehouseId])) {
                            $qty += $rowData['qty_'.$warehouseId];
                            if ($warehouseStock->getId()) {
                                $qty -= $warehouseStock->getQtyToShip();
                                $oldWarehouseProductQty = $warehouseStock->getTotalQty();
                            }
                            $newWarehouseProductQty = $rowData['qty_'.$warehouseId];
                            if ($oldWarehouseProductQty != $newWarehouseProductQty) {
                                if (isset($rowData['name']) && isset($rowData['sku'])){
                                    $warehouseAdjustStock[$warehouseId]['products'][$row['product_id']] = [
                                        'adjust_qty' => $newWarehouseProductQty,
                                        'product_name' => $rowData['name'],
                                        'product_sku' => $rowData['sku'],
                                        'old_qty' => 0
                                    ];
                                }
                                if (!in_array($warehouseId, $warehouseAdjustIds)) {
                                    $warehouseAdjustIds[] = $warehouseId;
                                }
                            }
                        } else {
                            if ($warehouseStock->getId()) {
                                $qty += $warehouseStock->getTotalQty() - $warehouseStock->getQtyToShip();
                            }
                        }
                        /** check shelf location product in warehouse */
                        if (isset($rowData['location_'.$warehouseId])) {
                            $warehouseProductLocations[$warehouseId][$row['product_id']] = $rowData['location_'.$warehouseId];
                            if (!in_array($warehouseId, $warehouseLocationIds)) {
                                $warehouseLocationIds[] = $warehouseId;
                            }
                        }
                    }
                    $row['qty'] = $qty;
                }
                /** end get qty product when have warehouse */

                if (!isset($stockData[$rowData[self::COL_SKU]])) {
                    $stockData[$rowData[self::COL_SKU]] = $row;
                }
            }
            if (!empty($stockData)) {
                $this->_connection->insertOnDuplicate($entityTable, array_values($stockData));
            }

            /** create adjust stock */
            if (!empty($warehouseAdjustStock)) {
                foreach ($warehouseAdjustIds as $warehouseId) {
                    $productToAdjusts = $warehouseAdjustStock[$warehouseId];
                    $adjustData['products'] = $productToAdjusts['products'];

                    $adjustData[AdjustStockInterface::WAREHOUSE_ID] = $warehouseId;
                    $adjustData[AdjustStockInterface::WAREHOUSE_CODE] = isset($warehouses[$warehouseId]['warehouse_code']) ?
                        $warehouses[$warehouseId]['warehouse_code'] :
                        null;
                    $adjustData[AdjustStockInterface::WAREHOUSE_NAME] = isset($warehouses[$warehouseId]['warehouse_name']) ?
                        $warehouses[$warehouseId]['warehouse_name'] :
                        null;
                    $adjustData[AdjustStockInterface::REASON] = __('Import Products');
                    if (!empty($productToAdjusts)) {
                        /** @var \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory */
                        $adjustStockFactory = \Magento\Framework\App\ObjectManager::getInstance()->create(
                            '\Magestore\InventorySuccess\Model\AdjustStockFactory'
                        );
                        $adjustStock = $adjustStockFactory->create();

                        /** @var \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement */
                        $adjustStockManagement = \Magento\Framework\App\ObjectManager::getInstance()->create(
                            '\Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface'
                        );
                        /* create stock adjustment, require products */
                        $adjustStockManagement->createAdjustment($adjustStock, $adjustData);

                        /* created adjuststock or not */
                        if($adjustStock->getId()) {
                            /* complete stock adjustment */
                            $adjustStockManagement->complete($adjustStock, false);
                        }
                    }
                }
            }
            /** end create adjust stock  */

            /** update location for product in warehouse */
            if (!empty($warehouseProductLocations)) {
                foreach ($warehouseLocationIds as $warehouseId) {
                    if (!empty($warehouseProductLocations[$warehouseId])) {
                        $locations = $warehouseProductLocations[$warehouseId];
                        /** @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry */
                        $warehouseStockRegistry = \Magento\Framework\App\ObjectManager::getInstance()->create(
                            '\Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface'
                        );
                        $warehouseStockRegistry->updateLocation($warehouseId, $locations);
                    }
                }
            }
            if ($productIdsToReindex) {
                $indexer->reindexList($productIdsToReindex);
            }
        }
        return $this;
    }

}
