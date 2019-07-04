<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\Stocktaking;

use Magestore\InventorySuccess\Api\Data\StocktakingInterface;
use Magestore\InventorySuccess\Model\Stocktaking;

/**
 * Tax Rate CSV Import Handler
 */
class CsvImportHandler
{

    /**
     * CSV Processor
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magestore\InventorySuccess\Model\StocktakingFactory
     */
    protected $stocktakingFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface
     */
    protected $stocktakingManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement
     */
    protected $warehouseManagement;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Filesystem\File\WriteFactory
     */
    protected $fileWriteFactory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    /**
     * CsvImportHandler constructor.
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magestore\InventorySuccess\Model\StocktakingFactory $stocktakingFactory
     * @param \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface $stocktakingManagement
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement $warehouseManagement
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magestore\InventorySuccess\Model\StocktakingFactory $stocktakingFactory,
        \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface $stocktakingManagement,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement $warehouseManagement,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory,
        \Magento\Framework\Filesystem\Driver\File $driverFile
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->stocktakingFactory = $stocktakingFactory;
        $this->stocktakingManagement = $stocktakingManagement;
        $this->request = $request;
        $this->warehouseManagement = $warehouseManagement;
        $this->filesystem = $filesystem;
        $this->backendSession = $backendSession;
        $this->driverFile = $driverFile;
        $this->fileWriteFactory = $fileWriteFactory;
    }

    /**
     * @param $file
     * @param string $status
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile($file, $status = '0')
    {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Missing file upload attempt.'));
        }
        $importProductRawData = $this->csvProcessor->getData($file['tmp_name']);
        $fileFields = $importProductRawData[0];
        $validFields = $this->_filterFileFields($fileFields, $status);
        $invalidFields = array_diff_key($fileFields, $validFields);
        $importProductData = $this->_filterImportProductData($importProductRawData, $invalidFields, $validFields);
        $stocktaking = $this->stocktakingFactory->create();
        if($this->request->getParam('id')){
            $stocktaking = $stocktaking->load($this->request->getParam('id'));
        }
        $stocktakingData = array();
        if ($stocktaking->getId()) {
            $stocktakingData = $this->getDataFromFile($importProductData, $status, $stocktaking->getData('warehouse_id'));
            $stocktakingData['warehouse_id'] = $stocktaking->getData('warehouse_id');
            $stocktakingData['stocktaking_code'] = $stocktaking->getData('stocktaking_code');
            $stocktakingData['warehouse_code'] = $stocktaking->getData('warehouse_code');
            $stocktakingData['warehouse_name'] = $stocktaking->getData('warehouse_name');
            $stocktakingData['reason'] = $stocktaking->getData('reason');
            $stocktakingData['created_at'] = $stocktaking->getData('created_at');
            $stocktakingData['created_by'] = $stocktaking->getData('created_by');
            $stocktakingData['participants'] = $stocktaking->getData('participants');
            $stocktakingData['stocktake_at'] = $stocktaking->getData('stocktake_at');
            $stocktakingData['status'] = $status;
        }
        $this->stocktakingManagement->createStocktaking($stocktaking, $stocktakingData);
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function getDataFromFile($importProductData, $status, $wareHouseId)
    {
        $stocktakingData = array();
        $invalidData = array();
        $isBarcode = false;
        if($status == Stocktaking::STATUS_PENDING) {
            foreach ($importProductData as $rowIndex => $dataRow) {
                if ($rowIndex == 0) {
                    if(strtoupper($dataRow[0]) == 'BARCODE'){
                        $isBarcode = true;
                    }
                    continue;
                }
                $productCode = $dataRow[0];
                $productCollection = $this->warehouseManagement
                    ->getListProduct($wareHouseId);
                if($isBarcode){
                    $productCollection->addBarcodeToSelect();
                    $productCollection->addBarcodeToFilter($productCode);
                }
                if(!$isBarcode){
                    $productCollection->addFieldToFilter('sku', $productCode);
                }
                if ($productCollection->getSize()) {
                    $productModel = $productCollection->setPageSize(1)->setCurPage(1)->getFirstItem();
                    $productData = [];
                    $productData['product_sku'] = $productModel->getSku();
                    $productData['product_name'] = $productModel->getName();
                    $productData['stocktaking_qty'] = 0;
                    $stocktakingData['products'][$productModel->getId()] = $productData;
                }else {
                    $invalidData[] = array($dataRow[0]);
                }
            }
        }
        if($status == Stocktaking::STATUS_PROCESSING){
            foreach ($importProductData as $rowIndex => $dataRow) {
                if ($rowIndex == 0) {
                    if(strtoupper($dataRow[0]) == 'BARCODE'){
                        $isBarcode = true;
                    }
                    continue;
                }
                $productCode = $dataRow[0];
                $productCollection = $this->warehouseManagement
                    ->getListProduct($wareHouseId);
                if($isBarcode){
                    $productCollection->addBarcodeToSelect();
                    $productCollection->addBarcodeToFilter($productCode);
                }
                if(!$isBarcode){
                    $productCollection->addFieldToFilter('sku', $productCode);
                }
                if ($productCollection->getSize()) {
                    $productModel = $productCollection->setPageSize(1)->setCurPage(1)->getFirstItem();
                    $coutedProduct = floatval($dataRow[1]);
                    $productData = [];
                    $productData['product_sku'] = $productModel->getSku();
                    $productData['product_name'] = $productModel->getName();
                    //if($coutedProduct)
                        $productData['stocktaking_qty'] = $coutedProduct;
                    $stocktakingData['products'][$productModel->getId()] = $productData;
                }else {
                    $invalidData[] = array($dataRow[0]);
                }
            }
        }
        if (count($invalidData, $isBarcode)) {
            $this->createInvalidStocktakingFile($invalidData, $isBarcode);
        }
        return $stocktakingData;
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields(array $fileFields, $status)
    {
        $filteredFields = $this->getRequiredCsvFields($status);
        $requiredFieldsNum = count($this->getRequiredCsvFields($status));
        $fileFieldsNum = count($fileFields);
        for ($index = $requiredFieldsNum; $index < $fileFieldsNum; $index++) {
            $titleFieldName = $fileFields[$index];
            $filteredFields[$index] = $titleFieldName;
        }
        return $filteredFields;
    }

    /**
     * create adjusted invalid file
     *
     * @param array
     * @return
     */
    protected function createInvalidStocktakingFile($invalidStocktakingData, $isBarcode)
    {
        $this->backendSession->setData('import_type', \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_STOCKTAKING);
        $this->backendSession->setData('error_import', true);
        $this->backendSession->setData('sku_invalid', count($invalidStocktakingData));
        $isHasDirectory = $this->driverFile->isExists($this->getBaseDirMedia()->getAbsolutePath('magestore/inventory/stocktaking'));
        if (!$isHasDirectory) {
            $this->driverFile->createDirectory($this->getBaseDirMedia()->getAbsolutePath('magestore/inventory/stocktaking'));
        }
        $filename = $this->getBaseDirMedia()->getAbsolutePath('magestore/inventory/stocktaking/import_product_to_stocktake_invalid.csv');
        $file = $this->fileWriteFactory->create(
            $filename,
            \Magento\Framework\Filesystem\DriverPool::FILE,
            'w'
        );
        $file->close();
        $data = array(
            array('SKU')
        );
        if($isBarcode){
            $data = array(
                array('BARCODE')
            );
        }
        $data = array_merge($data, $invalidStocktakingData);
        $this->csvProcessor->saveData($filename, $data);
    }

    /**
     * @return array
     */
    public function getRequiredCsvFields($status)
    {
        if($status == Stocktaking::STATUS_PENDING)
            return [
                0 => __('SKU')
            ];
        if($status == Stocktaking::STATUS_PROCESSING)
        return [
                0 => __('SKU'),
                1 => __('QTY')
            ];
    }

    /**
     * @param array $productRawData
     * @param array $invalidFields
     * @param array $validFields
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _filterImportProductData(array $productRawData, array $invalidFields, array $validFields)
    {
        $validFieldsNum = count($validFields);
        foreach ($productRawData as $rowIndex => $dataRow) {
            // skip empty rows
//            if (count($dataRow) <= 1) {
//                unset($productRawData[$rowIndex]);
//                continue;
//            }

            // unset invalid fields from data row
            foreach ($dataRow as $fieldIndex => $fieldValue) {
                if (isset($invalidFields[$fieldIndex])) {
                    unset($productRawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
            if (count($productRawData[$rowIndex]) != $validFieldsNum) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format.'));
            }
        }
        return $productRawData;
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    public function getBaseDirMedia()
    {
        return $this->filesystem->getDirectoryRead('media');
    }

}
