<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel\Category;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

class Mapping extends AbstractDb
{
    /**
     * @var Mapping\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        \Amasty\Feed\Model\ResourceModel\Category\Mapping\CollectionFactory $collectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_feed_category_mapping', 'entity_id');
    }

    /**
     * @param \Amasty\Feed\Model\Category $feedMapper
     * @param array $data
     */
    public function saveCategoriesMapping($feedMapper, $data)
    {
        $connection = $this->getConnection();

        if (is_array($data)) {
            $this->collectionFactory->create()->addFieldToFilter(
                'feed_category_id',
                $feedMapper->getId()
            )->walk('delete');
            unset($data[0]); //Remove record with category id 0

            foreach ($data as $categoryId => $item) {
                $bind = [
                    'feed_category_id' => $feedMapper->getId(),
                    'category_id'      => $categoryId,
                    'variable'         => isset($item['name']) ? $item['name'] : null,
                    'skip'             => isset($item['skip']) ? $item['skip'] : false,
                ];

                $connection->insert($this->getMainTable(), $bind);
            }
        }
    }
}
