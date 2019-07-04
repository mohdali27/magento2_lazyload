<?php

namespace Potato\ImageOptimization\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Potato\ImageOptimization\Model\Source\System\OptimizationMethod;
use Potato\ImageOptimization\Model\Config;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.3.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('potato_image_optimization_image'),
                'error_type',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Error type'
                ]
            );
        }
        $pathIndexName = $setup->getConnection()
            ->getIndexName($setup->getTable('potato_image_optimization_image'), 'path');
        $createPathIndexQuery = sprintf('CREATE INDEX %s ON %s(%s)',
            $pathIndexName, $setup->getTable('potato_image_optimization_image'), 'path(255)');

        if (version_compare($context->getVersion(), '1.3.3', '<')) {
            $setup->getConnection()->rawQuery($createPathIndexQuery);
        }
        if (version_compare($context->getVersion(), '1.4.0', '<')) {
            //fix 'path' column with varchar(255) type
            $columns = $setup->getConnection()->describeTable($setup->getTable('potato_image_optimization_image'));
            foreach ($columns as $column) {
                if ($column['COLUMN_NAME'] !== 'path' || $column['DATA_TYPE'] !== 'varchar') {
                    continue;
                }
                //remove old index
                $setup->getConnection()->dropIndex($setup->getTable('potato_image_optimization_image'),
                    $pathIndexName);

                $setup->getConnection()->changeColumn(
                    $setup->getTable('potato_image_optimization_image'),
                    'path',
                    'path',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => null,
                        'comment' => 'File absolute path'
                    ]
                );

                $setup->getConnection()->rawQuery($createPathIndexQuery);
            }

            //update "Can use service" for new settings
            $oldOptimizationMethodSettingPath = 'potato_image_optimization/general/optimization_method';
            $settingTableName = $setup->getTable('core_config_data');
            $oldOptimizationMethodValue = $setup->getConnection()->fetchOne(
                'SELECT value FROM ' . $settingTableName . ' WHERE path = "' . $oldOptimizationMethodSettingPath . '"'
            );
            if ($oldOptimizationMethodValue === OptimizationMethod::USE_SERVICE) {
                $setup->getConnection()->insertArray(
                    $settingTableName,
                    ['path', 'value'],
                    [
                        [Config::GIF_OPTIMIZATION_METHOD, OptimizationMethod::USE_SERVICE],
                        [Config::JPEG_OPTIMIZATION_METHOD, OptimizationMethod::USE_SERVICE],
                        [Config::PNG_OPTIMIZATION_METHOD, OptimizationMethod::USE_SERVICE],
                    ],
                    Mysql::REPLACE
                );
                //remove old setting value
                $setup->getConnection()->delete($settingTableName, ['path = ?' => $oldOptimizationMethodSettingPath]);
            }
        }
        $setup->endSetup();
    }
}