<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Notification;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportNotificationCsv extends \Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\AbstractLowStockNotification
{
    /**
     * Export abandoned carts report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = "Lowstock_listing_". date('Ymd_His').".csv";
        $content = $this->_view->getLayout()->createBlock(
            'Magestore\InventorySuccess\Block\Adminhtml\LowStockNotification\Notification\Edit\Productgrid'
        )->getCsvFile();
        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
