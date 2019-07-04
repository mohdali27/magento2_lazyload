<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Request;
use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadInvalidTransferCsv extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{
    protected $csvProcessor;
    protected $fileFactory;
    protected $filesystem;

    const TYPE_TRANSFER_STOCK_TO_TRANSFER_DELIVERY = 3;
    const TYPE_TRANSFER_STOCK_TO_TRANSFER_RECEIVING = 4;

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

    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        if ($type == self::TYPE_TRANSFER_STOCK_TO_TRANSFER_DELIVERY) {
            $path = 'import_product_to_transfer_delivery_invalid.csv';
        } else {
            $path = 'import_product_to_transfer_receiving_invalid.csv';
        }

        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
        $filename = DirectoryList::VAR_DIR.'/'.$path;
        return $this->fileFactory->create(
            'import_product_to_transfer_invalid.csv',
            file_get_contents($filename),
            DirectoryList::VAR_DIR
        );
    }


}


