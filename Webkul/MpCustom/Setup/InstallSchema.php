<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpCustom
 * @author    Webkul
 * @copyright Copyright (c) 2010-2019 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpCustom\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
      $installer = $setup;
      $installer->startSetup();
      /**
       * update table 'marketplace_userdata'
       */
       $installer->getConnection()->addColumn(
           $installer->getTable('marketplace_userdata'),
           'is_vat',
           [
               'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
               'unsigned' => true,
               'nullable' => false,
               'default' => '0',
               'comment' => 'Is registered for VAT'
           ]
       );

      $installer->endSetup();
    }
}
