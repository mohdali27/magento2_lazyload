<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel\Feed\Grid;

use Amasty\Feed\Model\Feed;

class ExecuteModeList implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            'manual' => __(Feed::MANUAL_GENERATED),
            'schedule' => __(Feed::CRON_GENERATED),
        ];
    }
}
