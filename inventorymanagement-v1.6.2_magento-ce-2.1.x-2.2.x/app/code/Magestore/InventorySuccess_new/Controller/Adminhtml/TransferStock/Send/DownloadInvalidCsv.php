<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Send;
use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadInvalidCsv extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
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

    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        if ($type == \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_SEND) {
            $path = 'import_product_to_transfer_send_invalid.csv';
        } else {
            $path = 'import_product_to_transfer_send_delivery_invalid.csv';
        }
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
        $filename = DirectoryList::VAR_DIR.'/'.$path;
        return $this->fileFactory->create(
            'import_product_to_send_stock_invalid.csv',
            file_get_contents($filename),
            DirectoryList::VAR_DIR
        );
    }

}


