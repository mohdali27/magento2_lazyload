<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Send;
use Magento\Framework\App\Filesystem\DirectoryList;
class DownloadSummary extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{
    protected $csvProcessor;
    protected $fileFactory;
    protected $filesystem;


    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    const SAMPLE_QTY = 1;
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        
        $id = $this->_request->getParam("id");
        $this->getBaseDirMedia()->create('magestore/inventory/transferstock');
        $filename = $this->getBaseDirMedia()->getAbsolutePath('magestore/inventory/transferstock/stock_summary.csv');
        $data = array(
            array('Name','SKU','Sent QTY', 'QTY RECEIVED' , 'QTY RETUNED')
        );
        $data = array_merge($data, $this->generateSampleData());

        $this->csvProcessor->saveData($filename, $data);
        return $this->fileFactory->create(
            'stock_summary'. $id. '.csv',
            file_get_contents($filename),
            DirectoryList::VAR_DIR
        );
    }
    
    /**
     * get base dir media
     *
     * @return string
     */
    public function getBaseDirMedia()
    {
        return $this->filesystem->getDirectoryWrite('media');
    }

    public function generateSampleData() {
        $id = $this->_request->getParam("id");

        $data = array();
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\Collection $productCollection */
        $productCollection = $this->_objectManager->create('Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\Collection')
            ->addFieldToFilter("transferstock_id", $id)

            ->setCurPage(1);
        foreach ($productCollection as $product) {
            $data[]= array($product->getData('product_name'), $product->getData('product_sku'), $product->getData('qty'), $product->getData('qty_received'),$product->getData('qty_returned'));
        }
        return $data;
    }

}


