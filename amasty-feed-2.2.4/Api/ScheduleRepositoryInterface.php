<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Api;

use Amasty\Feed\Api\Data\ScheduleInterface;

interface ScheduleRepositoryInterface
{
    /**
     * @param ScheduleInterface $scheduleModel
     * @return ScheduleInterface
     */
    public function save(ScheduleInterface $scheduleModel);

    /**
     * @param int $id
     * @return ScheduleInterface
     */
    public function get($id);

    /**
     * @param ScheduleInterface $scheduleModel
     * @return bool
     */
    public function delete(ScheduleInterface $scheduleModel);

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById($id);

    /**
     * @param int $feedId
     * @return bool
     */
    public function deleteByFeedId($feedId);
}
