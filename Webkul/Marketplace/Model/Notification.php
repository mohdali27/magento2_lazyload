<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\Marketplace\Api\Data\NotificationInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Notification Model.
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Notification _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Notification getResource()
 */
class Notification extends AbstractModel implements NotificationInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';
    
    /**#@+
     * Notification's Types
     */
    const TYPE_PRODUCT = 1;
    const TYPE_SELLER = 2;
    const TYPE_ORDER = 3;
    const TYPE_TRANSACTION = 4;
    const TYPE_REVIEW = 5;
    /**#@-*/

    /**
     * Marketplace Notification cache tag.
     */
    const CACHE_TAG = 'marketplace_notification_list';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_notification_list';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_notification_list';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Notification');
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteNotification();
        }

        return parent::load($id, $field);
    }

    /**
     * Prepare Notification's Types.
     *
     * @return array
     */
    public function getAllTypes()
    {
        return [
            self::TYPE_PRODUCT => __('Product'),
            self::TYPE_ORDER => __('Sale'),
            self::TYPE_TRANSACTION => __('Transaction'),
            self::TYPE_REVIEW => __('Review')
        ];
    }

    /**
     * Load No-Route Notification.
     *
     * @return \Webkul\Marketplace\Model\Notification
     */
    public function noRouteNotification()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\Marketplace\Api\Data\NotificationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Notification ID.
     *
     * @return int
     */
    public function getNotificationId()
    {
        return parent::getData(self::NOTIFICATION_ID);
    }

    /**
     * Set Notification ID.
     *
     * @param int $notificationId
     *
     * @return \Webkul\Marketplace\Api\Data\NotificationInterface
     */
    public function setNotificationId($notificationId)
    {
        return $this->setData(self::NOTIFICATION_ID, $notificationId);
    }

    /**
     * Get Type.
     *
     * @return int
     */
    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    /**
     * Set Type.
     *
     * @param int $type
     *
     * @return \Webkul\Marketplace\Api\Data\NotificationInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get Created Time
     *
     * @return int|null
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set Created Time
     *
     * @param int $modulename
     * @return \Webkul\Marketplace\Api\Data\NotificationInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Updated Time
     *
     * @return int|null
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set Updated Time
     *
     * @param int $updatedAt
     * @return \Webkul\Marketplace\Api\Data\NotificationInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
