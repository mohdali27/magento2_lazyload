<?php
/**
 * Solwin Infotech
 * Solwin Ournews Extension
 *
 * @category   Solwin
 * @package    Solwin_Ournews
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
?>
<?php

namespace Solwin\Ournews\Model\ResourceModel;

class News extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date time handler
     * 
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * Date model
     * 
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * constructor
     * 
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        $this->_dateTime = $dateTime;
        $this->_date     = $date;
        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('solwin_ournews_news', 'news_id');
    }

    /**
     * Retrieves News Title from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getNewsTitleById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'title')
            ->where('news_id = :news_id');
        $binds = ['news_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }
    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|
     * \Solwin\Ournews\Model\News $object
     * @return $this
     */
    protected function _beforeSave(
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $object->setUpdatedAt($this->_date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->_date->date());
        }
        foreach (['start_publish_date', 'end_publish_date'] as $field) {
            $value = !$object->getData($field) ? null :
                    $object->getData($field);
            $object->setData($field, $this->_dateTime->formatDate($value));
        }
        return parent::_beforeSave($object);
    }
    
    /**
     * Process post data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $condition = ['news_id = ?' => (int)$object->getId()];

        $this->getConnection()
                ->delete($this->getTable('solwin_ournews_store'), $condition);
       
        return parent::_beforeDelete($object);
    }
    /**
     * Assign post to store views, categories, related posts, etc.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $oldIds = $this->lookupStoreIds($object->getId());
        $newIds = (array)$object->getStores();
        
        if (empty($newIds)) {
            $newIds = (array)$object->getStoreId();
        }
        $this->_updateLinks($object, $newIds, $oldIds, 
                'solwin_ournews_store', 'store_id');

        return parent::_afterSave($object);
    }
    
    /**
     * Update post connections
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @param  Array $newRelatedIds
     * @param  Array $oldRelatedIds
     * @param  String $tableName
     * @param  String  $field
     * @return void
     */
    protected function _updateLinks(
        \Magento\Framework\Model\AbstractModel $object,
        Array $newRelatedIds,
        Array $oldRelatedIds,
        $tableName,
        $field
    ) {
        $table = $this->getTable($tableName);

        $insert = array_diff($newRelatedIds, $oldRelatedIds);
        $delete = array_diff($oldRelatedIds, $newRelatedIds);

        if ($delete) {
            $where = ['news_id = ?' => (int)$object->getId(), 
                $field.' IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['news_id' => (int)$object->getId(), 
                    $field => (int)$storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }
    }

    /**
     * Load an object using 'url_key' field if there's no field specified
     * and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(
        \Magento\Framework\Model\AbstractModel $object,
        $value,
        $field = null
    ) {
        if (!is_numeric($value) && $field === null) {
            $field = 'url_key';
        }

        return parent::load($object, $value, $field);
    }
    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(
        \Magento\Framework\Model\AbstractModel $object
    ) {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }
    
    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $pageId
     * @return array
     */
    public function lookupStoreIds($newsId)
    {
        return $this->_lookupIds($newsId, 'solwin_ournews_store', 'store_id');
    }
    /**
     * Get ids to which specified item is assigned
     * @param  int $postId
     * @param  string $tableName
     * @param  string $field
     * @return array
     */
    
    protected function _lookupIds($newsId, $tableName, $field)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()->from(
            $this->getTable($tableName),
            $field
        )->where(
            'news_id = ?',
            (int)$newsId
        );

        return $adapter->fetchCol($select);
    }
    
    public function checkUrlExits(
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $stores = $object->getStores();
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('solwin_ournews_news'),
            'news_id'
            )
        ->where(
            'url_key = ?',
            $object->getUrlKey()
            )
        ->where(
            'news_id != ?',
            $object->getId()
            );

        $newsIds = $connection->fetchCol($select);
        if (count($newsIds)>0 && is_array($stores)) {
            if (in_array('0', $stores)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('URL key for specified store already exists.')
                    );  
            }
            $stores[] = '0';
            $select = $connection->select()->from(
                $this->getTable('solwin_ournews_store'),
                'news_id'
                )
            ->where(
                'news_id IN (?)',
                $newsIds
                )
            ->where(
                'store_id IN (?)',
                $stores
                );
            $result = $connection->fetchCol($select);
            if (count($result)>0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('URL key for specified store already exists.')
                    );  
            }
        }
        return $this;
    }
}