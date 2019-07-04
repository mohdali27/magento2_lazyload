<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\Adminhtml\SupplyNeeds;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportSupplyNeedsCsv extends \Magestore\InventorySuccess\Controller\Adminhtml\SupplyNeeds\AbstractSupplyNeeds
{
    /**
     * Export abandoned carts report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = "supplyneeds_". date('Ymd_His').".csv";
        $content = $this->_view->getLayout()->createBlock(
            'Magestore\InventorySuccess\Block\Adminhtml\SupplyNeeds\Edit\Productgrid'
        )->getCsvFile();
        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
