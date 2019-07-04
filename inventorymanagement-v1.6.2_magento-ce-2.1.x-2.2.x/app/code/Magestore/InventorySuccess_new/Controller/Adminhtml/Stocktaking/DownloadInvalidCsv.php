<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
/**
 * Class Import
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
class DownloadInvalidCsv extends \Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking\Stocktaking
{
    public function execute()
    {
        $filename = $this->getBaseDirMedia()
                ->getAbsolutePath('magestore/inventory/stocktaking/import_product_to_stocktake_invalid.csv');
        $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->create('magestore/inventory/stocktaking');    
        
        return $this->fileFactory->create(
            'import_product_to_stocktake_invalid.csv',
            file_get_contents($filename),
            DirectoryList::VAR_DIR
        );
    }

    public function getCsvSampleLink() {
        $path = 'magestore/inventory/stocktaking/import_product_to_stocktake_invalid.csv';
        $url =  $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
        return $url;
    }

    public function getBaseDirMedia()
    {
        return $this->filesystem->getDirectoryRead('media');
    }
}
