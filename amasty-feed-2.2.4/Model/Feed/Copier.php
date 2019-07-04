<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Feed;

use Amasty\Feed\Model\Feed;

class Copier
{
    protected $feedFactory;

    public function __construct(
        \Amasty\Feed\Model\FeedFactory $feedFactory
    ) {
        $this->feedFactory = $feedFactory;
    }

    protected function _duplicate(Feed $feed)
    {
        $duplicate = $this->feedFactory->create();
        $duplicate->setData($feed->getData());
        $duplicate->setIsDuplicate(true);
        $duplicate->setOriginalId($feed->getId());

        $duplicate->setExecuteMode('manual');
        $duplicate->setGeneratedAt(null);
        $duplicate->setId(null);
        return $duplicate;
    }

    public function copy(Feed $feed)
    {
        $duplicate = $this->_duplicate($feed);

        $duplicate->setName($duplicate->getName() . '-duplicate');
        $duplicate->setFilename($duplicate->getFilename() . '-duplicate');

        $duplicate->save();

        return $duplicate;
    }

    /**
     * Create a new feed template based on this feed
     *
     * @param Feed $feed
     *
     * @return mixed
     */
    public function template(Feed $feed)
    {
        $duplicate = $this->_duplicate($feed);

        $duplicate->setIsTemplate(true);
        $duplicate->setStoreId(null);

        $duplicate->save();

        return $duplicate;
    }

    public function fromTemplate(Feed $template, $storeId)
    {
        $duplicate = $this->_duplicate($template);

        $duplicate->setIsTemplate(false);
        $duplicate->setStoreId($storeId);

        $duplicate->save();

        return $duplicate;
    }
}
