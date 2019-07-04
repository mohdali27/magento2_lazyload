<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\LowStockNotification\Rule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class RunNowButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if ($this->permissionManagementInterface->checkPermission('Magestore_InventorySuccess::view_notification_rule')) {
            $data = [];
            $ruleId = $this->getRuleId();
            if ($ruleId && $this->canRender('run_now')) {
                $data = [
                    'label' => __('Run Now'),
                    'class' => 'delete',
                    'on_click' => 'deleteConfirm(\'' . __(
                            'Do you want to run this rule now?'
                        ) . '\', \'' . $this->urlBuilder->getUrl('*/*/run', ['id' => $ruleId]) . '\')',
                    'sort_order' => 40,
                ];
            }
            return $data;
        }
    }
}
