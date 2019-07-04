<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Product\NoneInWarehouse;

use Magento\Catalog\Model\Product\Type as SimpleProductType;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\Product\NoneInWarehouse
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    const MAPPING_FIELD = [
        'entity_id' => 'e.entity_id',
        'name' => 'catalog_product_entity_varchar.value',
        'sku' => 'e.sku',
        'price' => 'catalog_product_entity_decimal.value',
        'status' => 'catalog_product_entity_int.value',
        'qty' => 'at_qty.qty',
        'sum_total_qty' => 'SUM(warehouse_product.total_qty)',
        'sum_qty_to_ship' => 'SUM(warehouse_product.qty_to_ship)',
        'available_qty' => '(SUM(warehouse_product.total_qty) - SUM(warehouse_product.qty_to_ship))'
    ];

    /**
     * Init select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $edition = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ProductMetadataInterface')
            ->getEdition();
        $rowId = strtolower($edition) == 'enterprise' ? 'row_id' : 'entity_id';
        $warehouseProductIds = $this->getWarehouseProductIds();
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute */
        $eavAttribute = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magento\Eav\Model\ResourceModel\Entity\Attribute');
        $productNameAttributeId = $eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'name');
        $productPriceAttributeId = $eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'price');
        $productStatusAttributeId = $eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'status');
        $productImagesAttributeId = $eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'image');

        $this->getSelect()->from([self::MAIN_TABLE_ALIAS => $this->getTable('catalog_product_entity')])
            //->where(self::MAIN_TABLE_ALIAS . '.type_id = ?', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->joinLeft(
                array('catalog_product_entity_varchar' => $this->getTable('catalog_product_entity_varchar')),
                self::MAIN_TABLE_ALIAS . ".entity_id = catalog_product_entity_varchar.$rowId &&
                catalog_product_entity_varchar.attribute_id = $productNameAttributeId",
                array('')
            )->columns(array('name' => self::MAPPING_FIELD['name']))
            ->joinLeft(
                array('catalog_product_entity_varchar_img' => $this->getTable('catalog_product_entity_varchar')),
                self::MAIN_TABLE_ALIAS . ".entity_id = catalog_product_entity_varchar_img.$rowId &&
                catalog_product_entity_varchar_img.attribute_id = $productImagesAttributeId &&
                catalog_product_entity_varchar_img.store_id = 0",
                array('')
            )->columns(array('image' => 'catalog_product_entity_varchar_img.value'))
            ->joinLeft(
                array('catalog_product_entity_decimal' => $this->getTable('catalog_product_entity_decimal')),
                self::MAIN_TABLE_ALIAS . ".entity_id = catalog_product_entity_decimal.$rowId &&
                catalog_product_entity_decimal.attribute_id = $productPriceAttributeId",
                array('')
            )->columns(array('price' => self::MAPPING_FIELD['price']))
            ->joinLeft(
                array('catalog_product_entity_int' => $this->getTable('catalog_product_entity_int')),
                self::MAIN_TABLE_ALIAS . ".entity_id = catalog_product_entity_int.$rowId &&
                catalog_product_entity_int.attribute_id = $productStatusAttributeId",
                array('')
            )->columns(array('status' => self::MAPPING_FIELD['status']));

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1 AND {{table}}.website_id=0',
                'left'
            );
        }
        $this->getSelect()->group(self::MAIN_TABLE_ALIAS . '.entity_id');
        $stocks = array_merge($warehouseProductIds['in_stock'], $warehouseProductIds['out_stock']);
        if (count($stocks)) {
            $this->getSelect()->where('e.entity_id NOT IN (?)', $stocks);
            if (count($warehouseProductIds['out_stock'])) {
                $this->getSelect()->orWhere('at_qty.qty > 0 AND e.entity_id IN (?)', $warehouseProductIds['out_stock']);
            }
        }
        return $this;
    }
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('e.entity_id');
        $idsSelect->columns('e.sku');
        $idsSelect->columns(array('qty' => self::MAPPING_FIELD['qty']));
        $idsSelect->columns(array('name' => self::MAPPING_FIELD['name']));
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Return ['in_stock' => [], 'out_stock' => []]
     *
     * @return array
     */
    public function getWarehouseProductIds()
    {
        $ids = ['in_stock' => [], 'out_stock' => []];
        $warehouseProductCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection');
        $warehouseProductCollection->getSelect()->group('product_id');
        $warehouseProductCollection->getSelect()->columns('sum(total_qty) as  sum_total_qty');

        foreach ($warehouseProductCollection as $warehouseProduct) {
            if ($warehouseProduct->getData('sum_total_qty') > 0) {
                $ids['in_stock'][] = $warehouseProduct->getProductId();
            } else {
                $ids['out_stock'][] = $warehouseProduct->getProductId();
            }
        }

        return $ids;
    }

    /**
     * Get count sql
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        if (!count($this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP))) {
            $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));
            return $countSelect;
        }
        $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $group = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT " . implode(", ", $group) . ")")));
        return $countSelect;
    }

    /**
     * @param string $columnName
     * @param array $filterValue
     * @return $this
     */
    public function addQtyToFilter($columnName, $filterValue)
    {
        if (isset($filterValue['from'])) {
            $this->getSelect()->having(self::MAPPING_FIELD[$columnName] . ' >= ?', $filterValue['from']);
        }
        if (isset($filterValue['to'])) {
            $this->getSelect()->having(self::MAPPING_FIELD[$columnName] . ' <= ?', $filterValue['to']);
        }
        return $this;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if(in_array($field, array_keys(self::MAPPING_FIELD)))
            $field = new \Zend_Db_Expr(self::MAPPING_FIELD[$field]);
        if (!is_array($field)) {
            $resultCondition = $this->_translateCondition($field, $condition);
        } else {
            $conditions = array();
            foreach ($field as $key => $currField) {
                $conditions[] = $this->_translateCondition(
                    $currField,
                    isset($condition[$key]) ? $condition[$key] : null
                );
            }

            $resultCondition = '(' . join(') ' . \Zend_Db_Select::SQL_OR . ' (', $conditions) . ')';
        }

        $this->_select->having($resultCondition);
        return $this;
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelect();
            $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
        }
        return intval($this->_totalRecords);
    }
}
