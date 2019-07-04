<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Rule;

class Applyrule extends \Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\AbstractLowStockNotification
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

        /** @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\Collection $ruleCollection */
        $ruleCollection = $this->_objectManager->create(
            'Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\CollectionFactory'
        )->create();
        $ruleCollection->addFieldToFilter('apply', \Magestore\InventorySuccess\Model\LowStockNotification\Rule::NOT_APPLY)
            ->addFieldToFilter('status', \Magestore\InventorySuccess\Model\LowStockNotification\Rule::STATUS_ACTIVE);
        if ($ruleCollection->getSize()) {
            try {
                $count = 0;
                foreach ($ruleCollection as $rule) {
                    if ($rule->getStatus() == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::STATUS_ACTIVE) {
                        $this->_ruleProductResourceFactory->create()->applyRule($rule);
                        $count++;
                    }
                }
                if ($count > 0) {
                    $this->messageManager->addSuccess(__('Have %1 rule(s) applied', $count));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->_redirect('inventorysuccess/*/');
                return;
            }
        }
        $this->_redirect('inventorysuccess/*/');
    }
}
