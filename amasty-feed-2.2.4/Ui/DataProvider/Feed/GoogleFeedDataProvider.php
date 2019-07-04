<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Ui\DataProvider\Feed;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory;

class GoogleFeedDataProvider extends AbstractDataProvider
{
    /**
     * Maximum file size allowed for file_uploader UI component
     */
    const MAX_FILE_SIZE = 2097152;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $feedCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $feedCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
