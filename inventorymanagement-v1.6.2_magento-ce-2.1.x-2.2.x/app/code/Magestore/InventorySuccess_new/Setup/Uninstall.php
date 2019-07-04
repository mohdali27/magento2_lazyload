<?php
/**
 * Created by PhpStorm.
 * User: duongdiep
 * Date: 10/03/2017
 * Time: 14:12
 */

namespace Magestore\InventorySuccess\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    const MAIN_TABLE = 'cataloginventory_stock_item';
    const MAIN_KEY   = 'website_id';
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $setup->connection->delete(
            $setup->getTable(self::MAIN_TABLE),
            $setup->connection->quoteInto(self::MAIN_KEY.'!= (?)',0)
        );
        $setup->endSetup();
    }
}