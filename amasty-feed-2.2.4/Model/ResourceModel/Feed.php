<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Feed extends AbstractDb
{
    const TABLE_NAME = 'amasty_feed_entity';
    const ID = 'entity_id';

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID);
    }

    /**
     *  Load an object by feed id
     *
     * @return $this
     */
    public function loadByFeedId(\Amasty\Feed\Model\Feed $feed, $feedId)
    {
        if ($feedId) {
            $this->load($feed, $feedId);
        }

        return $this;
    }
}
