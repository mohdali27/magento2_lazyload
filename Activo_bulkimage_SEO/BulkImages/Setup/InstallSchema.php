<?php
/**
 * Activo Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Activo Commercial License
 * that is available through the world-wide-web at this URL:
 * http://extensions.activo.com/license_professional
 *
 * @copyright   Copyright (c) 2017 Activo Extensions (http://extensions.activo.com)
 * @license     Commercial
 */
namespace Activo\BulkImages\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\App\Filesystem\DirectoryList;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('activo_bulkimages_import'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'successfull',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true, 'default' => '0']
            )
            ->addColumn(
                'num_images',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => false, 'default' => '0']
            )
            ->addColumn(
                'num_images_unmatched',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => false, 'default' => '0']
            )
            ->addColumn(
                'num_skus',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => false, 'default' => '0']
            )
            ->addColumn(
                'num_skus_unmatched',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => false, 'default' => '0']
            )
            ->addColumn(
                'num_matches',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => false, 'default' => '0']
            )
            ->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00']
            );
        $installer->getConnection()->createTable($table);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA);
        $importFolder = $mediaDirectory->getAbsolutePath('import');

        $io = new \Magento\Framework\Filesystem\Io\File;
        $io->setAllowCreateFolders(true);
        $io->checkAndCreateFolder($importFolder);

        $installer->endSetup();
    }
}
