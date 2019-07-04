<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;

/**
 * Class NewAction
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
class NewAction extends \Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking\Stocktaking
{
    /**
     * Create new customer action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->_resultForwardFactory->create();
        $resultForward->forward('edit');
        return $resultForward;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_InventorySuccess::create_stocktaking');
    }
}
