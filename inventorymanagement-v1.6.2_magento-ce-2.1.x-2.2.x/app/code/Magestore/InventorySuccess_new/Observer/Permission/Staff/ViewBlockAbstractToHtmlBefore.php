<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Permission\Staff;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Helper\RenderUiComponent;
use Magestore\InventorySuccess\Model\Permission\PermissionManagement;



class ViewBlockAbstractToHtmlBefore implements ObserverInterface
{
    /**
     * @var RenderUiComponent
     */
    protected $renderUiComponent;

    /**
     * @var PermissionManagement
     */
    protected $permissionManagement;

    /**
     * ViewBlockAbstractToHtmlBefore constructor.
     * @param RenderUiComponent $renderUiComponent
     * @param PermissionManagement $permissionManagement
     */
    public function __construct(
        RenderUiComponent $renderUiComponent,
        PermissionManagement $permissionManagement

    ){
        $this->renderUiComponent = $renderUiComponent;
        $this->permissionManagement = $permissionManagement;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if($block instanceof \Magento\User\Block\User\Edit\Tabs
                && $this->permissionManagement->checkPermission('Magestore_InventorySuccess::warehouse_permission')){
            $block->addTab(
                'warehouse_section',
                [
                    'label' => __('Location'),
                    'title' => __('Location'),
                    'after' => 'roles_section',
                    'content' => $this->renderUiComponent->renderUiComponent('os_user_permission_form')
                ]
            );
        }
        return $this;
    }
}
