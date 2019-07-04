<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\Component\Form\Field\LowStockNotification\Notification;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Field;

/**
 * Class Websites Fieldset
 */
class NotifierEmails extends Field
{
    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        /** @var  \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $locator = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\Locator\LocatorFactory'
        )->create();
        $lowStockNotification = $locator->getSesionByKey('current_lowstock_notification');
        if (!$lowStockNotification->getNotifierEmails())
            $this->_data['config']['visible'] = false;
    }
}
