<?php

/**
 * Webkul Software.
 *
 * @category Webkul
 *
 * @author Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module.
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('marketplace_custom_attribute'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
              ->addColumn(
                  'attribute_id',
                  Table::TYPE_INTEGER,
                  null,
                  ['nullable' => false, 'default' => 0],
                  'Attribute ID'
              )
              ->addColumn(
                  'status',
                  Table::TYPE_INTEGER,
                  null,
                  ['nullable' => false, 'default' => 0],
                  'Status'
              )
            ->setComment('Custom Attribute Tabel');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
