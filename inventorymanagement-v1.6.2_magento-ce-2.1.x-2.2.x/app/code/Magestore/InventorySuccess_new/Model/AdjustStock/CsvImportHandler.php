<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\AdjustStock;
use Magento\Framework\App\Filesystem\DirectoryList;
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
     * @var \Magestore\InventorySuccess\Model\AdjustStockFactory
     */
    protected $adjustStockFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface
     */
    protected $adjustStockManagement;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

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
     * Helper Data
     *
     * @var \Magestore\InventorySuccess\Helper\Data
     */
    protected $helper;

    /**
     * CsvImportHandler constructor.
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magestore\InventorySuccess\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory,
        \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magestore\InventorySuccess\Helper\Data $helper
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->adjustStockFactory = $adjustStockFactory;
        $this->adjustStockManagement = $adjustStockManagement;
        $this->request = $request;
        $this->filesystem = $filesystem;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->backendSession = $backendSession;
        $this->driverFile = $driverFile;
        $this->fileWriteFactory = $fileWriteFactory;
        $this->helper = $helper;
    }


    /**
     * @param $file
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile($file,$import_immediately = 0)
    {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $importProductRawData = $this->csvProcessor->getData($file['tmp_name']);
        $fileFields = $importProductRawData[0];
        $validFields = $this->_filterFileFields($fileFields);
        $invalidFields = array_diff_key($fileFields, $validFields);
        $importProductData = $this->_filterImportProductData($importProductRawData, $invalidFields, $validFields);
        $adjustStock = $this->adjustStockFactory->create();
        if($this->request->getParam('id')){
            $adjustStock = $adjustStock->load($this->request->getParam('id'));
        }
        $adjustData = array();
        $invalidData = array();
        $qtyKey = 'adjust_qty';
        if($this->helper->getAdjustStockChange()){
           $qtyKey = 'change_qty';
        }

        foreach ($importProductData as $rowIndex => $dataRow) {
            if ($rowIndex == 0) {
                continue;
            }
            $productSku = $dataRow[0];
            $productModel = $this->productCollectionFactory->create()
                ->addFieldToSelect('name')
                ->addFieldToFilter('sku', $productSku)
                ->setPageSize(1)->setCurPage(1)->getFirstItem();

            if ($productModel->getId() && isset($dataRow[1]) &&
                is_numeric($dataRow[1])) {
                $productNewQty = floatval($dataRow[1]);
                $adjustData['products'][$productModel->getId()] = array(
                    "product_sku" => $productSku,
                    $qtyKey => $productNewQty,
                    "product_name" => $productModel->getName()
                );
            } else {
                $invalidData[] = $dataRow;
            }
        }

        if ($adjustStock->getId()) {
            $adjustData['warehouse_id'] = $adjustStock->getData('warehouse_id');
            $adjustData['adjuststock_code'] = $adjustStock->getData('adjuststock_code');
            $adjustData['warehouse_code'] = $adjustStock->getData('warehouse_code');
            $adjustData['warehouse_name'] = $adjustStock->getData('warehouse_name');
            $adjustData['reason'] = $adjustStock->getData('reason');
            $adjustData['created_at'] = $adjustStock->getData('created_at');
            $adjustData['created_by'] = $adjustStock->getData('created_by');
        }

        if (count($invalidData)) {
            $this->createInvalidAdjustedFile($invalidData);
        }

        $this->adjustStockManagement->createAdjustment($adjustStock, $adjustData);
        if($import_immediately == 1) {
            $this->adjustStockManagement->complete($adjustStock);
        }

    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields(array $fileFields)
    {
        $filteredFields = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $fileFieldsNum = count($fileFields);

        // process title-related fields that are located right after required fields with store code as field name)
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
    protected function createInvalidAdjustedFile($invalidData)
    {
        $this->backendSession->setData('import_type', \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_ADJUST_STOCK);
        $this->backendSession->setData('error_import', true);
        $this->backendSession->setData('sku_invalid', count($invalidData));
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
        $filename = DirectoryList::VAR_DIR.'/import/'.'import_product_to_adjuststock_invalid.csv';

        $file = $this->fileWriteFactory->create(
            $filename,
            \Magento\Framework\Filesystem\DriverPool::FILE,
            'w'
        );
        $file->close();

        $data = array(
            array('SKU','QTY')
        );
        $data = array_merge($data, $invalidData);
        $this->csvProcessor->saveData($filename, $data);
    }

    /**
     * @return array
     */
    public function getRequiredCsvFields()
    {
        // indexes are specified for clarity, they are used during import
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
            if (count($dataRow) <= 1) {
                unset($productRawData[$rowIndex]);
                continue;
            }
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
