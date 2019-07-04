<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Rule;

class Delete extends \Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\AbstractLowStockNotification
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::view_notification_rule';
    /**
     * Promo quote save action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Rule $model */
                $model = $this->_objectManager->create('Magestore\InventorySuccess\Model\LowStockNotification\RuleFactory')->create();

                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the rule.'));
                $this->_redirect('inventorysuccess/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete this rule right now. Please review the log and try again.')
                );
                $this->_redirect('inventorysuccess/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a rule to delete.'));
        $this->_redirect('inventorysuccess/*/');
    }
}
