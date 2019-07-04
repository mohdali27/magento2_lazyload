<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Send;
use Magento\Framework\App\Filesystem\DirectoryList;
class DownloadSample extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{
    protected $csvProcessor;
    protected $fileFactory;
    protected $filesystem;
    protected $fileWriteFactory;
    protected $driverFile;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory,
        \Magento\Framework\Filesystem\Driver\File $driverFile
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->fileWriteFactory = $fileWriteFactory;
        $this->driverFile = $driverFile;
        parent::__construct($context);
    }

    const SAMPLE_QTY = 1;
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $name = md5(microtime());
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
        $filename = DirectoryList::VAR_DIR.'/import/'.$name.'.csv';

        $stream = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->openFile($filename, 'w+');
        $stream->lock();
        $data = array(
            array('SKU','QTY')
        );
        $data = array_merge($data, $this->generateSampleData(3));
        foreach ($data as $row) {
            $stream->writeCsv($row);
        }
        $stream->unlock();
        $stream->close();

        return $this->fileFactory->create(
            'import_product_to_send.csv',
            array(
                'type' => 'filename',
                'value' => $filename,
                'rm' => true  // can delete file after use
            ),
            DirectoryList::VAR_DIR
        );
    }
    

    public function generateSampleData($number) {
        $data = array();
        $transferStockId = $this->getRequest()->getParam('id');
        $transferStockModel = $this->_objectManager->create('Magestore\InventorySuccess\Model\TransferStock')->load($transferStockId);
        if ($transferStockModel->getId()) {
            $wareHouseId = $transferStockModel->getData('source_warehouse_id');
            if ($wareHouseId) {
                $productCollection = $this->_objectManager->get('Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement')
                    ->getListProduct($wareHouseId)
                    ->setPageSize($number)
                    ->setCurPage(1);
                foreach ($productCollection as $productModel) {
                    $data[]= array($productModel->getData('sku'), self::SAMPLE_QTY);
                }
            }
        }

        return $data;
    }

}


