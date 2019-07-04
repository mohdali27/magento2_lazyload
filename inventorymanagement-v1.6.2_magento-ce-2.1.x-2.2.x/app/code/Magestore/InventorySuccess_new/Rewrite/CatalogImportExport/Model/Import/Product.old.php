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
        $warehouseProducts = [];
        $warehouseAdjustStock = [];
        $adjustStockIds = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $stockData = [];
            $productIdsToReindex = [];
            // Format bunch to stock data rows
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                /** get warehouses and warehouseids to import */
                if ($rowNum == 0 && empty($warehouseIds)) {
                    /** @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection $warehouseCollection */
                    $warehouseCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
                        '\Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory'
                    )->create();
                    foreach ($rowData as $key => $value) {
                        $checkQtyKey = explode('qty_', $key);
                        if (isset($checkQtyKey[1]) && !in_array($checkQtyKey[1], $warehouseIds)) {
                            $warehouseIds[] = $checkQtyKey[1];
                        }
                        $checkLocationKey = explode('location_', $key);
                        if (isset($checkLocationKey[1]) && !in_array($checkLocationKey[1], $warehouseIds)) {
                            $warehouseIds[] = $checkLocationKey[1];
                        }
                    }
                    if ($warehouseIds) {
                        $warehouseCollection->addFieldToFilter('warehouse_id', ['in' => $warehouseIds]);
                        $warehouseIds = [];
                        foreach ($warehouseCollection as $warehouse) {
                            $warehouseIds[] = $warehouse->getId();
                            $warehouses[$warehouse->getId()] = $warehouse->getData();
                        }

                    }
                }
                /** end get warehouses and warehouseids to import */
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
                        if (isset($rowData['qty_'.$warehouseId])) {
                            $qty += $rowData['qty_'.$warehouseId];
                        }
                        /** @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $oldStock */
                        $oldStock = \Magento\Framework\App\ObjectManager::getInstance()->create(
                            '\Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface'
                        )->getStock($warehouseId, $row['product_id']);
                        $oldWarehouseProductQty = 0;
                        if ($oldStock->getId()) {
                            $oldWarehouseProductQty = $oldStock->getTotalQty();
                        }

                        if (!isset($adjustStockIds[$warehouseId])) {
                            /** @var \Magestore\InventorySuccess\Model\Adjuststock $adjustStock */
                            $adjustStock = \Magento\Framework\App\ObjectManager::getInstance()->create(
                                '\Magestore\InventorySuccess\Model\AdjuststockFactory'
                            )->create();
                            $adjustStockCode =  \Magento\Framework\App\ObjectManager::getInstance()->create(
                                'Magestore\InventorySuccess\Model\AdjustStock\AdjustStockManagement'
                            )->generateCode();
                            $adjustStock->setWarehouseId($warehouseId)
                                ->setWarehouseName($warehouses[$warehouseId]['warehouse_name'])
                                ->setWarehouseCode($warehouses[$warehouseId]['warehouse_code'])
                                ->setReason(__('Import products'))
                                ->setCreatedAt(date('Y-m-d'))
                                ->setCreatedBy(\Magento\Framework\App\ObjectManager::getInstance()->create(
                                        '\Magestore\InventorySuccess\Api\Helper\SystemInterface'
                                    )->getCurUser()->getUserName()
                                )
                                ->setStatus(\Magestore\InventorySuccess\Model\Adjuststock::STATUS_COMPLETED)
                                ->setAdjustStockCode($adjustStockCode);
                            try {
                                $adjustStock->save();
                                $adjustStockIds[$warehouseId] = $adjustStock->getId();
                            } catch (\Exception $e) {
                                \Magento\Framework\App\ObjectManager::getInstance()->create(
                                    '\Magestore\InventorySuccess\Api\Logger\LoggerInterface'
                                )->log($e->getMessage(), 'importProductByMagento');
                            }
                        }
                        $warehouseAdjustStock[] = [
                            'adjuststock_id' => $adjustStockIds[$warehouseId],
                            'product_id' => $row['product_id'],
                            'product_name' => $rowData['name'],
                            'product_sku' => $rowData['sku'],
                            'old_qty' => $oldWarehouseProductQty,
                            'adjust_qty' => isset($rowData['qty_'.$warehouseId]) ? $rowData['qty_'.$warehouseId] : 0
                        ];
                        $warehouseProducts[] = [
                            'product_id' => $row['product_id'],
                            'warehouse_id' => $warehouseId,
                            'shelf_location' => isset($rowData['location_'.$warehouseId]) ? $rowData['location_'.$warehouseId] : '',
                            'total_qty' => isset($rowData['qty_'.$warehouseId]) ? $rowData['qty_'.$warehouseId] : 0
                        ];
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

            /** update qty product to warehouse */
            if (!empty($warehouseProducts)) {
                $this->_connection->insertOnDuplicate($stockResource->getTable('os_warehouse_product'), array_values($warehouseProducts));
            }

            /** update product to adjust stock */
            if (!empty($warehouseAdjustStock)) {
                $this->_connection->insertOnDuplicate($stockResource->getTable('os_adjuststock_product'), array_values($warehouseAdjustStock));
            }

            if ($productIdsToReindex) {
                $indexer->reindexList($productIdsToReindex);
            }
        }
        return $this;
    }

}
