<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\LowStockNotification\Rule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DuplicateButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getButtonData()
    {
        if ($this->permissionManagementInterface->checkPermission('Magestore_InventorySuccess::view_notification_rule')) {
            $data = [];
            $ruleId = $this->getRuleId();
            if ($ruleId) {
                if ($this->canRender('duplicate')) {
                    $data = [
                        'label' => __('Duplicate Rule'),
                        'class' => 'duplicate',
                        'on_click' => 'deleteConfirm(\'' . __(
                                'Are you sure you want to dupplicate this rule?'
                            ) . '\', \'' . $this->urlBuilder->getUrl('*/*/dupplicate', ['id' => $ruleId]) . '\')',
                        'sort_order' => 20,
                    ];
                }
                return $data;
            }
        }
    }
}
