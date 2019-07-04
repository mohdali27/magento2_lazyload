<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Index
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Warehouse
 */
class ExportWarehouseStockXml extends AbstractWarehouse
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::warehouse_list';
    
    /**
     * Warehouse grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    /**
     * Export abandoned carts report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $fileFactory = $objectManager->get('Magento\Framework\App\Response\Http\FileFactory');
        $fileName = "stock_on_hand_". date('Ymd_His').".xml";
        $content = $this->_view->getLayout()->createBlock(
            'Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Stock\Grid'
        )->getCsvFile();
        return $fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}