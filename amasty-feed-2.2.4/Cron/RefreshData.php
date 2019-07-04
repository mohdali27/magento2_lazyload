<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Cron;

use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Model\Config\Source\Events;
use Amasty\Feed\Model\CronProvider;
use Amasty\Feed\Model\EmailManagement;
use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\ResourceModel\ValidProducts\Collection as ValidProductsCollection;
use Magento\Framework\App\ResourceConnection;

class RefreshData
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory
     */
    private $feedCollectionFactory;

    /**
     * @var \Amasty\Feed\Model\Config
     */
    private $config;

    /**
     * @var \Amasty\Feed\Model\ResourceModel\ValidProducts\CollectionFactory
     */
    private $validProductsFactory;

    /**
     * @var EmailManagement
     */
    private $emailManagement;

    /**
     * @var \Amasty\Feed\Model\ScheduleManagement
     */
    private $scheduleManagement;

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Schedule\CollectionFactory
     */
    private $scheduleCollectionFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory $feedCollectionFactory,
        \Amasty\Feed\Model\Config $config,
        EmailManagement $emailManagement,
        \Amasty\Feed\Model\ResourceModel\ValidProducts\CollectionFactory $validProductsFactory,
        \Amasty\Feed\Model\ScheduleManagement $scheduleManagement,
        \Amasty\Feed\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->dateTime = $dateTime;
        $this->_localeDate = $localeDate;
        $this->logger = $logger;
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->config = $config;
        $this->validProductsFactory = $validProductsFactory;
        $this->emailManagement = $emailManagement;
        $this->scheduleManagement = $scheduleManagement;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
    }

    public function execute()
    {
        $itemsPerPage = (int)$this->config->getItemsPerPage();
        /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $collection */
        $collection = $this->feedCollectionFactory->create();
        $events = $this->config->getSelectedEvents();
        $events = explode(",", $events);

        /** @var Feed $feed */
        foreach ($collection as $feed) {
            try {
                if ($this->_onSchedule($feed)) {
                    $page = 1;
                    $lastPage = false;

                    /** @var ValidProductsCollection $vProductsCollection */
                    $vProductsCollection = $this->validProductsFactory->create()
                        ->setPageSize($itemsPerPage)->setCurPage($page);
                    $vProductsCollection->addFieldToFilter(ValidProductsInterface::FEED_ID, $feed->getId());

                    $feed->setGenerationType(Feed::CRON_GENERATED);
                    $feed->setProductsAmount(0);

                    while ($page <= $vProductsCollection->getLastPageNumber()) {
                        if ($page == $vProductsCollection->getLastPageNumber()) {
                            $lastPage = true;
                        }

                        $collectionData = $vProductsCollection->getData();
                        $productIds = [];

                        foreach ($collectionData as $datum) {
                            $productIds[] = $datum[ValidProductsInterface::VALID_PRODUCT_ID];
                        }

                        $feed->export($page - 1, $productIds, $lastPage);

                        $vProductsCollection->setCurPage(++$page)->resetData();
                    }

                    if ($events && in_array(Events::SUCCESS, $events)) {
                        $emailTemplate = $this->config->getSuccessEmailTemplate();
                        $this->emailManagement->sendEmail($feed, $emailTemplate);
                    }
                }
            } catch (\Exception $e) {
                if ($events && in_array(Events::UNSUCCESS, $events)) {
                    $emailTemplate = $this->config->getUnsuccessEmailTemplate();
                    $this->emailManagement->sendEmail($feed, $emailTemplate, $e->getMessage());
                }

                $feed->setStatus(Feed::FAILED);
                $collection->save();

                $this->logger->critical($e);
            }
        }
    }

    /**
     * @param Feed $feed
     *
     * @return bool
     */
    protected function _validateTime($feed)
    {
        $validate = false;

        $mageTime = $this->_localeDate->scopeTimeStamp();
        $now = (date("H", $mageTime) * 60) + date("i", $mageTime);

        /** @var \Amasty\Feed\Model\ResourceModel\Schedule\Collection $scheduleCollection */
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection->addValidateTimeFilter($feed->getId(), $now, date('w'));

        if ($scheduleCollection->getItems()) {
            $validate = true;
        }

        return $validate;
    }

    /**
     * @param Feed $feed
     *
     * @return bool
     */
    protected function _onSchedule($feed)
    {
        $currentDateTime = $this->dateTime->gmtDate();
        $lastValidDate = date('Y:m:d H:i:s', strtotime($currentDateTime . '-' . CronProvider::MINUTES_IN_STEP . ' minutes'));
        if ($feed->getExecuteMode() != 'manual'
            && $lastValidDate >= $feed->getGeneratedAt()
            && $this->_validateTime($feed)
        ) {
            return true;
        }

        return false;
    }
}
