<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     *
     * @var \Magestore\InventorySuccess\Api\InstallManagementInterface
     */
    protected $_installManagement;

    /**
     *
     * @param \Magestore\InventorySuccess\Api\InstallManagementInterface $installManagement
     */
    public function __construct(
    \Magestore\InventorySuccess\Api\InstallManagementInterface $installManagement
    )
    {
        $this->_installManagement = $installManagement;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /* create default warehouse */
        $this->_installManagement->createDefaultWarehouse();

        /* transfer products to default warehouse */
        $this->_installManagement->transferProductsToDefaultWarehouse();

        /* calculate qty-to-ship of all products, save to database, update to default warehouse */
        $this->_installManagement->calculateQtyToShip();

        /** create default notification rule */
        $this->_installManagement->createDefaultNotificationRule();

        $setup->endSetup();
    }
}
