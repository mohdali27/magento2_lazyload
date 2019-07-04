<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Api\ScheduleRepositoryInterface;
use Amasty\Feed\Model\CronProvider;
use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\Import;
use Amasty\Feed\Model\ResourceModel\Feed as ResourceFeed;
use Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory;
use Amasty\Feed\Model\ResourceModel\ValidProducts\CollectionFactory as ValidProductsCollectionFactory;
use Amasty\Feed\Model\ScheduleFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeDataTo220
{
    /**
     * @var CollectionFactory
     */
    private $feedCollectionFactory;

    /**
     * @var ScheduleRepositoryInterface
     */
    private $scheduleRepository;

    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var ValidProductsCollectionFactory
     */
    private $validProductsFactory;

    /**
     * @var Import
     */
    private $import;

    public function __construct(
        CollectionFactory $feedCollectionFactory,
        ScheduleRepositoryInterface $scheduleRepository,
        ScheduleFactory $scheduleFactory,
        ValidProductsCollectionFactory $validProductsFactory,
        Import $import
    ) {
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->scheduleRepository = $scheduleRepository;
        $this->scheduleFactory = $scheduleFactory;
        $this->validProductsFactory = $validProductsFactory;
        $this->import = $import;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $this->import->update('google');

        /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $feedCollection */
        $feedCollection = $this->feedCollectionFactory->create();
        $feedCollection->addFieldToFilter('is_template', 0);

        /** @var Feed $feed */
        foreach ($feedCollection->getItems() as $feed) {
            $this->transferScheduleData($feed);
            $this->fillDataToNewColumns($feed);
            $this->addOptionModificatorToExisted($feed);
        }

        $feedCollection->save();

        $this->removeScheduleDataFromFeedTable($setup);
    }

    /**
     * Transfer schedule data from Feed table
     *
     * @param Feed $feed
     */
    private function transferScheduleData($feed)
    {
        if ($feed->getCronDay() != CronProvider::EVERY_DAY) {
            $this->createScheduleData($feed, $feed->getCronDay());
        } else {
            for ($i = 0; $i < CronProvider::EVERY_DAY; $i++) {
                $this->createScheduleData($feed, $i);
            }
        }
    }

    /**
     * @param \Amasty\Feed\Model\Feed $feed
     * @param int $cronDay
     */
    private function createScheduleData($feed, $cronDay)
    {
        /** @var \Amasty\Feed\Model\Schedule $schedule */
        $schedule = $this->scheduleFactory->create();
        $schedule->setCronDay($cronDay)
            ->setCronTime($feed->getCronTime())
            ->setFeedId($feed->getId());

        $this->scheduleRepository->save($schedule);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function removeScheduleDataFromFeedTable(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        $connection->dropColumn(
            $setup->getTable(ResourceFeed::TABLE_NAME),
            'cron_time'
        );

        $connection->dropColumn(
            $setup->getTable(ResourceFeed::TABLE_NAME),
            'cron_day'
        );
    }

    /**
     * @param Feed $feed
     */
    private function addOptionModificatorToExisted($feed)
    {
        if ($feed->isXml()) {
            $content = $feed->getXmlContent();
            $content = str_replace('modify=', 'optional="no" modify=', $content);
            $feed->setXmlContent($content);
        }
    }

    /**
     * @param Feed $feed
     */
    private function fillDataToNewColumns($feed)
    {
        $validProductsCollection = $this->validProductsFactory->create();
        $validProductsCollection->addFieldToFilter(ValidProductsInterface::FEED_ID, $feed->getId());
        $feed->setProductsAmount($validProductsCollection->getSize());
        $feed->setStatus($feed->getGeneratedAt() ? Feed::READY : Feed::NOT_GENERATED);
        $feed->setGenerationType(
            $feed->getExecuteMode() === 'manual' ? Feed::MANUAL_GENERATED : Feed::CRON_GENERATED
        );
    }
}
