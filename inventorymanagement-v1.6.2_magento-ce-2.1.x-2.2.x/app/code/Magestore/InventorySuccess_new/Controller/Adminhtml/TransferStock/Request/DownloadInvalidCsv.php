<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Request;
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
        
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
        $filename = DirectoryList::VAR_DIR.'/'.'import_product_to_request_stock_invalid.csv';
        return $this->fileFactory->create(
            'import_product_to_request_stock_invalid.csv',
            file_get_contents($filename),
            DirectoryList::VAR_DIR
        );
    }


}


