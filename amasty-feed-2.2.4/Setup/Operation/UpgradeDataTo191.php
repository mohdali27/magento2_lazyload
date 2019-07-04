<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

class UpgradeDataTo191
{
    const NOT_SUPPORTED = ['hourly', 'daily', 'weekly', 'monthly'];

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory
     */
    private $feedCollectionFactory;

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Feed
     */
    private $resourceModelFeed;

    /**
     * @var \Amasty\Feed\Model\Import
     */
    private $import;

    public function __construct(
        \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory $feedCollectionFactory,
        \Amasty\Feed\Model\ResourceModel\Feed $resourceModelFeed,
        \Amasty\Feed\Model\Import $import
    ) {
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->resourceModelFeed = $resourceModelFeed;
        $this->import = $import;
    }

    public function execute()
    {
        $this->import->update('google');

        /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $feedCollection */
        $feedCollection = $this->feedCollectionFactory->create();
        $feeds = $feedCollection->addFieldToFilter('execute_mode', ['in' => self::NOT_SUPPORTED])->getItems();

        /** @var \Amasty\Feed\Model\Feed $feed */
        foreach ($feeds as $feed) {
            switch ($feed->getExecuteMode()) {
                case 'hourly':
                case 'daily':
                    $feed->setCronDay(\Amasty\Feed\Model\CronProvider::EVERY_DAY);
                    $feed->setCronTime(0);
                    break;

                case 'weekly':
                case 'monthly':
                    $feed->setCronDay('1');
                    $feed->setCronTime(0);
                    break;
            }

            $feed->setExecuteMode('schedule');
            $this->resourceModelFeed->save($feed);
        }
    }
}
