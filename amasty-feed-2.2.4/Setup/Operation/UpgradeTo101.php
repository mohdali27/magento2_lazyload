<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

class UpgradeTo101
{
    /**
     * @var \Magento\Framework\Setup\SampleData\Executor
     */
    private $executor;

    /**
     * @var \Amasty\Feed\Setup\Updater
     */
    private $updater;

    public function __construct(
        \Magento\Framework\Setup\SampleData\Executor $executor,
        \Amasty\Feed\Setup\Updater $updater
    ) {
        $this->executor = $executor;
        $this->updater = $updater;
    }

    public function execute()
    {
        $this->updater->setTemplates(['bing']);
        $this->executor->exec($this->updater);
    }
}
