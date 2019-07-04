<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\External;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;
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
        $this->getBaseDirMedia()->create('magestore/inventory/transferstock');
        $filename = $this->getBaseDirMedia()->getAbsolutePath('magestore/inventory/transferstock/transfer_products.csv');
        $id = $this->_request->getParam("id");

        $data = array(
            array('Name','SKU','QTY')
        );

        $data = array_merge($data, $this->generateData());

        $this->csvProcessor->saveData($filename, $data);
        return $this->fileFactory->create(
            'transfer_products'. $id. '.csv',
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

    public function generateData() {
        $id = $this->_request->getParam("id");

        $data = array();
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\Collection $productCollection */
        $productCollection = $this->_objectManager->create('Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\Collection')
            ->addFieldToFilter("transferstock_id", $id)

            ->setCurPage(1);
        foreach ($productCollection as $product) {
            $data[]= array($product->getData('product_name'), $product->getData('product_sku'), $product->getData('qty'));
        }
        return $data;
    }

}


