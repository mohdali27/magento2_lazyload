<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\Adminhtml\SupplyNeeds;

/**
 * class \Magestore\InventorySuccess\Controller\Adminhtml\SupplyNeeds\Index
 *
 * Delete location
 * Methods:
 *  execute
 *
 * @category    Magestore
 * @package     Magestore\InventorySuccess\Controller\Adminhtml\SupplyNeeds
 * @module      Inventorysuccess
 * @author      Magestore Developer
 */
class ShowSupplyNeeds extends \Magestore\InventorySuccess\Controller\Adminhtml\SupplyNeeds\AbstractSupplyNeeds
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data)) {
            $topFilter = base64_encode(serialize($data));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/', [
                'top_filter' => $topFilter,
                'id' => 1
            ]);
        }
    }
}