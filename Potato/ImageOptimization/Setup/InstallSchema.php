<?php

namespace Potato\ImageOptimization\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable($installer->getTable('potato_image_optimization_image'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'identity' => true]
            )
            ->addColumn(
                'path',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'time',
                Table::TYPE_INTEGER,
                null,
                []
            )
            ->addColumn(
                'result',
                Table::TYPE_TEXT,
                null,
                []
            );
        
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}