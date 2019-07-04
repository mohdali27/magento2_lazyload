<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

class CronProvider
{
    const MINUTES_IN_DAY = 1440;
    const MINUTES_IN_HOUR = 60;
    const MINUTES_IN_STEP = 30;
    const EVERY_DAY = '7';

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    private $list;

    public function __construct(\Magento\Framework\Locale\ListsInterface $list)
    {
        $this->list = $list;
    }

    /**
     * @return array
     */
    public function getCronTime()
    {
        $stTime = strtotime(date('Y-m-d'));
        $times = [];

        for ($time = 0; $time < self::MINUTES_IN_DAY; $time += self::MINUTES_IN_STEP) {
            $times[$time] = ['label' => date('g:i A', $stTime + ($time * self::MINUTES_IN_HOUR)), 'value' => $time];
        }

        return $times;
    }

    /**
     * @return array
     */
    public function getOptionWeekdays()
    {
        $optionWeekdays = $this->list->getOptionWeekdays();

        return $optionWeekdays;
    }
}
