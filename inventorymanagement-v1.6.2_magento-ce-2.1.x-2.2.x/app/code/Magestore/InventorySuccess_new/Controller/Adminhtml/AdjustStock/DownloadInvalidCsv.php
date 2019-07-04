<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
/**
 * Class Import
 * @package Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock
 */
class DownloadInvalidCsv extends \Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock\AdjustStock
{
    public function execute()
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
        $filename = DirectoryList::VAR_DIR.'/import/'.'import_product_to_adjuststock_invalid.csv';           
        return $this->fileFactory->create(
            'import_product_to_adjuststock_invalid.csv',
            file_get_contents($filename),
            DirectoryList::VAR_DIR
        );
    }

    public function getCsvSampleLink() {
        $path = 'magestore/inventory/adjuststock/import_product_to_adjuststock_invalid.csv';
        $url =  $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
        return $url;
    }

    public function getBaseDirMedia()
    {
        return $this->filesystem->getDirectoryRead('media');
    }
}
