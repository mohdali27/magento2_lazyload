<?php

namespace Magecomp\Emailquotepro\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install( SchemaSetupInterface $setup, ModuleContextInterface $context )
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('emailproductquote'))
            ->addColumn(
                'emailproductquote_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Entity ID'
            )
            ->addColumn(
                'quote_id',
                Table::TYPE_INTEGER,
                null,
                [],
                'Quote Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                [],
                'Product Id'
            )
            ->addColumn(
                'product_sku',
                Table::TYPE_TEXT,
                100,
                [],
                'Product Sku'
            )
            ->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                100,
                [],
                'Customer Email'
            )
            ->addColumn(
                'customer_name',
                Table::TYPE_TEXT,
                15,
                [],
                'Customer Name'
            )
            ->addColumn(
                'grand_total',
                Table::TYPE_DECIMAL,
                '2M',
                [],
                'grand_total'
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                [],
                'Status'
            )
            ->addColumn(
                'created_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'telephone',
                Table::TYPE_TEXT,
                15,
                [],
                'Telephone'
            )
            ->addColumn(
                'comment',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Comment'
            );
        $installer->getConnection()->createTable($table);
    }
}