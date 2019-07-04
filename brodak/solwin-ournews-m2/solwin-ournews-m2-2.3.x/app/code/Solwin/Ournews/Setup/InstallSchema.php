<?php
/**
 * Solwin Infotech
 * Solwin Ournews Extension
 *
 * @category   Solwin
 * @package    Solwin_Ournews
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
?>
<?php

namespace Solwin\Ournews\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('solwin_ournews_news')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('solwin_ournews_news')
            )
            ->addColumn(
                'news_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'News ID'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'News Title'
            )
            ->addColumn(
                'url_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'News URL Key'
            )
            ->addColumn(
                'url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'News URL'
            )
            ->addColumn(
                'image',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'News Image'
            )
            ->addColumn(
                'shortdesc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable => false'],
                'News Short Description'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'News Description'
            )
            ->addColumn(
                'start_publish_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable => false'],
                'News Start Publish Date'
            )
            ->addColumn(
                'end_publish_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable => false'],
                'News End Publish Date'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable => false'],
                'News Status'
            )
            ->addColumn(
                'meta_keyword',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'News Meta Keyword'
            )
            ->addColumn(
                'meta_description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable => false'],
                'News Meta Description'
            )

            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'News Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'News Updated At'
            )
            ->setComment('News Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('solwin_ournews_news'),
                $setup->getIdxName(
                    $installer->getTable('solwin_ournews_news'),
                    ['title','url_key','url','image','shortdesc','description',
                        'meta_keyword','meta_description'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['title','url_key','url','image','shortdesc','description',
                    'meta_keyword','meta_description'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

        /**
         * Create table 'solwin_ournews_store'
         */
        $table = $installer->getConnection()->newTable(
                        $installer->getTable('solwin_ournews_store')
                )->addColumn(
                        'news_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => false,
                            'primary' => true
                        ],
                        'News ID'
                )->addColumn(
                        'store_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Store ID'
                )->addIndex(
                        $installer->getIdxName(
                                'solwin_ournews_store', ['store_id']
                                ), ['store_id']
                )->addForeignKey(
                        $installer->getFkName('solwin_ournews_store',
                                'store_id', 'store', 'store_id'),
                        'store_id',
                        $installer->getTable('store'),
                        'store_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment(
                'Our News To Store Linkage Table'
                );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}