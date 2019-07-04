<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\TransferStock\Import;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Tax Rate CSV Import Handler
 */
class CsvRequestImportHandler
{
    /**
     * CSV Processor
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    protected $filesystem;

    protected $backendSession;

    protected $fileWriteFactory;

    protected $driverFile;
    /**
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory,
        \Magento\Framework\Filesystem\Driver\File $driverFile
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->request = $request;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->filesystem = $filesystem;
        $this->backendSession = $backendSession;
        $this->fileWriteFactory = $fileWriteFactory;
        $this->driverFile = $driverFile;
    }


    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $importProductRawData = $this->csvProcessor->getData($file['tmp_name']);
        $fileFields = $importProductRawData[0];
        $validFields = $this->_filterFileFields($fileFields);
        $invalidFields = array_diff_key($fileFields, $validFields);
        $importProductData = $this->_filterImportProductData($importProductRawData, $invalidFields, $validFields);
        $dataToImport = array();
        $invalidData = array(
            array('SKU','QTY')
        );

        if (!count($importProductData)) {
            $invalidData = $importProductRawData;
        }
        foreach ($importProductData as $rowIndex => $dataRow) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            $productSku = $dataRow[0];
            $productQty = $dataRow[1];
            $productModel = $this->productCollectionFactory->create()
                ->addFieldToSelect('name')
                ->addFieldToFilter('sku', $productSku)
                ->setPageSize(1)->setCurPage(1)->getFirstItem();
            if ($productModel->getId() && isset($dataRow[1]) && is_numeric($dataRow[1]) && $dataRow[1]>0) {
                $productData = array(
                    'product_id' => $productModel->getId(),
                    'product_name' => $productModel->getName(),
                    'product_sku' => $productSku,
                    "qty" => $productQty,
                    "transferstock_id" => $this->request->getParam('id')
                );
                $dataToImport[$productModel->getId()] = $productData;
            } else {
                $invalidData[] = $dataRow;
            }
        }
        if (count($invalidData) > 1) {

            $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
            $filename = DirectoryList::VAR_DIR.'/'.'import_product_to_request_stock_invalid.csv';

            $file = $this->fileWriteFactory->create(
                $filename,
                \Magento\Framework\Filesystem\DriverPool::FILE,
                'w'
            );
            $file->close();
            $this->backendSession->setData('import_type', \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_REQUEST);
            $this->backendSession->setData('error_import', true);
            $this->backendSession->setData('sku_invalid', count($invalidData) -1 );
            

            $this->csvProcessor->saveData($filename, $invalidData);
        }
        return $dataToImport;
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

    public function getRequiredCsvFields()
    {
        // indexes are specified for clarity, they are used during import
        return [
            0 => __('SKU'),
            1 => __('QTY')
        ];
    }

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

    public function getBaseDirMedia()
    {
        return $this->filesystem->getDirectoryRead('media');
    }

}
