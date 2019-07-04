<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Class Import
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
class DownloadSample extends \Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking\Stocktaking
{
    const SAMPLE_QTY = 1;
    
    /**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;    

    
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper,
        \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface $stocktakingManagement,
        \Magestore\InventorySuccess\Model\StocktakingFactory $stocktakingFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking $stocktakingResource,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ){
        $this->assetRepo = $assetRepo;        
        parent::__construct($context, 
                $moduleManager, 
                $systemHelper, 
                $stocktakingManagement, 
                $stocktakingFactory, 
                $stocktakingResource, 
                $stockChange,
                $adminSession,
                $fileFactory,
                $filesystem,
                $csvProcessor,
                $timezone
        );
    }
    
    
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $name = 'stocktaking_' . md5(microtime());
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
        $filename = DirectoryList::VAR_DIR.'/import/'.$name.'.csv';
        $stream = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->openFile($filename, 'w+');        
        $stream->lock();
        
        $importLabel = 'SKU';
        if($isBarcode = $this->getRequest()->getParam('is_barcode')){
            $importLabel = "BARCODE";
        }
        if($this->isQty()){
            $data = array(
                array($importLabel,'QTY')
            );
        }else{
            $data = array(
                array($importLabel)
            );
        }

        $data = array_merge($data, $this->generateSampleData(3, $isBarcode));  
        
        foreach ($data as $row) {
            $stream->writeCsv($row);
        }
        $stream->unlock();
        $stream->close();        

        return $this->fileFactory->create(
            'import_product_to_stocktaking.csv',
            array(
                'type' => 'filename',
                'value' => $filename,
                'rm' => true  // can delete file after use
            ),
            DirectoryList::VAR_DIR
        );
    }

    /**
     * get sample csv url
     *
     * @return string
     */
    public function getCsvSampleLink()
    {
        return $this->assetRepo->getUrlWithParams('sample/import_product_to_stocktaking.csv', 
                ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * get base dir media
     *
     * @return string
     */
    public function getBaseDirMedia()
    {
        return $this->filesystem->getDirectoryRead('media');
    }

    /**
     * generate sample data
     *
     * @param int
     * @return array
     */
    public function generateSampleData($number, $isBarcode = false)
    {
        $data = array();
        $stocktakingId = $this->getRequest()->getParam('id');
        $stocktakingModel = $this->_objectManager->create('Magestore\InventorySuccess\Model\Stocktaking')->load($stocktakingId);
        if($stocktakingModel->getId()) {
            $wareHouseId = $stocktakingModel->getData('warehouse_id');
            $productCollection = $this->_objectManager->get('Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement')
                ->getListProduct($wareHouseId)
                ->setPageSize($number)
                ->setCurPage(1);
            $importKey = 'sku';
            if($isBarcode){
                $productCollection->addBarcodeToSelect();
                $importKey = 'barcode';
            }
            if($productCollection->getSize()){
                if ($this->isQty()) {
                    foreach ($productCollection as $productModel) {
                        $data[] = array($productModel->getData($importKey), self::SAMPLE_QTY);
                    }
                } else {
                    foreach ($productCollection as $productModel) {
                        $data[] = array($productModel->getData($importKey));
                    }
                }
            }
        }
        return $data;
    }

    /**
     * is use qty to generate
     *
     * @return string
     */
    public function isQty()
    {
        return $this->getRequest()->getParam('is_qty');
    }
}
