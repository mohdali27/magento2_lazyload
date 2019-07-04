<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Rule;

class Run extends \Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\AbstractLowStockNotification
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
                if (!$model->getApply()) {
                    $this->_ruleProductResourceFactory->create()->applyRule($model);
                }
                /** @var \Magestore\InventorySuccess\Model\LowStockNotification\RuleManagement $ruleManagement */
                $ruleManagement = \Magento\Framework\App\ObjectManager::getInstance()->create(
                    '\Magestore\InventorySuccess\Model\LowStockNotification\RuleManagement'
                );
                $ruleManagement->startNotification($model->toArray());

                $this->messageManager->addSuccess(__('This rule has been processed. Please go to Low Stock Notifications to see the list of low stock items.'));
                $this->_redirect('inventorysuccess/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t run this rule right now. Please review the log and try again.')
                );
                $this->_redirect('inventorysuccess/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a rule to tun.'));
        $this->_redirect('inventorysuccess/*/edit', ['id' => $this->getRequest()->getParam('id')]);
    }
}
