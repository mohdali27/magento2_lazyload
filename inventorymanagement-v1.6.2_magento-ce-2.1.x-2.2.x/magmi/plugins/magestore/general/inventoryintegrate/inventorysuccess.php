<?php
/**
 * Created by PhpStorm.
 * User: duongdiep
 * Date: 9/7/2560
 * Time: 12:43 น.
 */
require_once("magmi_csvreader.php");
require_once("fshelper.php");

class Magmi_ReStockMagestoreInventory extends Magmi_GeneralImportPlugin
{

    protected $skus = [];
    protected $warehouses = [];

    /**
     * @return Plugin information
     */
    public function getPluginInfo()
    {
        return array("name" => "Update stock in Magestore InventorySuccess", "author" => " Edward ", "version" => "1.0",
            "url" => "https://www.youtube.com/watch?v=7v6A5HOjKSw");
    }

    /**
     * @return bool
     */
    public function afterImport()
    {
        $csvObject = $this->prepareCSV();
        $csvObjectClone = $this->prepareCSV();

        $this->prepareProductSku($csvObjectClone);
        if (empty($this->skus))
            return false;

        $this->getWarehouseIds();
        if (empty($this->warehouses))
            return false;

        $this->processCsvRows($csvObject);

        $this->import();

        $this->log(" - Import was processed!", "info");

        return true;
    }

    /**
     * @return Magmi_CSVReader
     * @throws Magmi_CSVException
     */
    private function prepareCSV()
    {
        $defaultParams = $this->getParams();
        $csvParams = $this->getCsvImportedParams();
        $params = array_merge($defaultParams, $csvParams);
        $csvreader = new Magmi_CSVReader();
        $csvreader->initialize($params, 'CSV');
        $csvreader->checkCSV();
        $csvreader->openCSV();
        $csvreader->getColumnNames();
        return $csvreader;
    }

    /**
     * @return array
     */
    protected function getCsvImportedParams()
    {
        $eng = $this->_callers[0];
        $ds = $eng->getPluginInstanceByClassName("datasources", "Magmi_CSVDataSource");
        if ($ds != null) {
            return $ds->getParams();
        }
        return array();
    }


    /**
     * @return array
     */
    public function isRunnable()
    {
        return array(FSHelper::getExecMode() != null, "");
    }

    /**
     * Get all product sku and id from import file
     *
     * @param Magmi_CSVReader $csvObject
     */
    protected function prepareProductSku($csvObject)
    {
        $skus = [];
        while (($item = $csvObject->getNextRecord()) !== false) {
            if (isset($item['sku'])) {
                $skus[] = $item['sku'];
            }
        }
        if (!empty($skus)) {
            $productTableName = $this->tablename('catalog_product_entity');
            $skus = implode("','", $skus);
            $query = "SELECT entity_id, sku from $productTableName WHERE sku IN ('$skus')";
            $results = $this->selectAll($query);
            foreach ($results as $result) {
                $this->skus[$result['sku']] = $result['entity_id'];
            }
        }
    }

    /**
     * Prepare warehouse ids to update
     */
    protected function getWarehouseIds()
    {
        $warehouseTableName = $this->tablename('os_warehouse');
        $query = "SELECT warehouse_id from $warehouseTableName";
        $results = $this->selectAll($query);
        foreach ($results as $result) {
            $this->warehouses[$result['warehouse_id']] = [];
        }
    }

    /**
     * Prepare data to update
     *
     * @param Magmi_CSVReader $csvObject
     */
    protected function processCsvRows($csvObject)
    {
        $warehouses = [];
        while (($item = $csvObject->getNextRecord()) !== false) {
            $keys = array_keys($item);
            foreach ($keys as $key) {
                if (strpos($key, 'warehouse_') !== false) {
                    $warehouses[str_replace('warehouse_', '', $key)] = [];
                }
            }

            if (empty($warehouses))
                return false;

            if (!isset($item['sku']))
                continue;

            foreach ($this->warehouses as $warehouseId => $data) {
                if (!isset($item['warehouse_' . $warehouseId]) || empty($item['warehouse_' . $warehouseId]))
                    continue;

                if (!isset($this->skus[$item['sku']]))
                    continue;

                $this->warehouses[$warehouseId][$this->skus[$item['sku']]] = $item['warehouse_' . $warehouseId];
            }
        }
    }

    protected function import()
    {
        $stockTableName = $this->tablename('cataloginventory_stock_item');
        foreach ($this->warehouses as $websiteId => $data) {
            if (empty($data))
                continue;
            $productIds = implode(",", array_keys($data));
            $sqlSelect = "SELECT item_id, product_id, qty, total_qty, website_id FROM $stockTableName " .
                "WHERE product_id IN ($productIds) AND website_id = $websiteId";
            $results = $this->selectAll($sqlSelect);
            $beforeUpdate = [];
            foreach ($results as $stockData) {
                $beforeUpdate[$stockData['product_id']] = $stockData;
            }

            $sqlTotalQty = "";
            foreach ($data as $productId => $qty) {
                if (isset($beforeUpdate[$productId]))
                    $sqlTotalQty .= "WHEN product_id = $productId THEN $qty ";
            }
            if (empty($sqlTotalQty))
                continue;

            $sqlUpdateTotalQty = "UPDATE $stockTableName SET total_qty = CASE $sqlTotalQty ELSE total_qty END WHERE website_id = $websiteId";
            $this->update($sqlUpdateTotalQty);

            $sqlSelect = "SELECT item_id, product_id, qty, total_qty, website_id FROM $stockTableName " .
                "WHERE product_id IN ($productIds) AND website_id = $websiteId";

            $results = $this->selectAll($sqlSelect);
            $afterUpdate = [];
            foreach ($results as $stockData) {
                $afterUpdate[$stockData['product_id']] = $stockData;
            }

            $sqlQty = $sqlGlobalTotalQty = "";
            foreach ($afterUpdate as $productId => $stockData) {
                if (isset($beforeUpdate[$productId])) {
                    $updateQty = $stockData['total_qty'] - $beforeUpdate[$productId]['total_qty'];
                    $sqlQty .= "WHEN product_id = $productId THEN (qty + $updateQty) ";
                    $sqlGlobalTotalQty .= "WHEN product_id = $productId THEN (total_qty + $updateQty) ";
                }
            }
            if (empty($sqlQty))
                continue;

            $sqlUpdateQty = "UPDATE $stockTableName SET qty = CASE $sqlQty ELSE qty END WHERE website_id = $websiteId";
            $this->update($sqlUpdateQty);
            $sqlUpdateGlobalQty = "UPDATE $stockTableName SET qty = CASE $sqlQty ELSE qty END WHERE website_id = 0";
            $this->update($sqlUpdateGlobalQty);
            $sqlUpdateGlobalTotalQty = "UPDATE $stockTableName SET total_qty = CASE $sqlGlobalTotalQty ELSE total_qty  END WHERE website_id = 0";
            $this->update($sqlUpdateGlobalTotalQty);
        }
    }
}