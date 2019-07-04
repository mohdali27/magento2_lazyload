<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup;

use Amasty\Feed\Model\Feed;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var Operation\UpgradeTo160
     */
    private $upgradeTo160;

    /**
     * @var Operation\UpgradeTo170
     */
    private $upgradeTo170;

    /**
     * @var Operation\UpgradeTo190
     */
    private $upgradeTo190;

    /**
     * @var Operation\UpgradeTo191
     */
    private $upgradeTo191;

    /**
     * @var Operation\UpgradeTo200
     */
    private $upgradeTo200;

    /**
     * @var Operation\UpgradeTo210
     */
    private $upgradeTo210;

    /**
     * @var Operation\UpgradeTo220
     */
    private $upgradeTo220;

    public function __construct(
        \Magento\Framework\App\State $state,
        Operation\UpgradeTo160\Proxy $upgradeTo160,
        Operation\UpgradeTo170\Proxy $upgradeTo170,
        Operation\UpgradeTo190\Proxy $upgradeTo190,
        Operation\UpgradeTo191\Proxy $upgradeTo191,
        Operation\UpgradeTo200\Proxy $upgradeTo200,
        Operation\UpgradeTo210\Proxy $upgradeTo210,
        Operation\UpgradeTo220\Proxy $upgradeTo220
    ) {
        $this->appState = $state;
        $this->upgradeTo160 = $upgradeTo160;
        $this->upgradeTo170 = $upgradeTo170;
        $this->upgradeTo190 = $upgradeTo190;
        $this->upgradeTo191 = $upgradeTo191;
        $this->upgradeTo200 = $upgradeTo200;
        $this->upgradeTo210 = $upgradeTo210;
        $this->upgradeTo220 = $upgradeTo220;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addCompressColumns($setup);
        }

        if (version_compare($context->getVersion(), '1.2.1', '<')) {
            $this->addSkipColumn($setup);
        }

        if (version_compare($context->getVersion(), '1.6.0', '<')) {
            $this->upgradeTo160->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.7.0', '<')) {
            $this->appState->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_FRONTEND,
                [$this->upgradeTo170, 'execute'],
                [$setup]
            );
        }

        if (version_compare($context->getVersion(), '1.9.0', '<')) {
            $this->upgradeTo190->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.9.1', '<')) {
            $this->upgradeTo191->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->upgradeTo200->execute();
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->upgradeTo210->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->upgradeTo220->execute($setup);
        }

        $setup->endSetup();
    }

    protected function addCompressColumns(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_feed_entity');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'compress',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => false,
                'default'  => Feed::COMPRESS_NONE,
                'comment'  => 'Compress'
            ]
        );
    }

    protected function addSkipColumn(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_feed_category_mapping');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'skip',
            [
                'type'     => Table::TYPE_BOOLEAN,
                'length'   => null,
                'nullable' => false,
                'default'  => false,
                'comment'  => 'Skip this category in feed'
            ]
        );
    }
}
