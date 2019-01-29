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
use Webkul\Marketplace\Api\Data\OrdersInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Webkul\Marketplace\Model\Saleslist;

/**
 * Marketplace Orders Model.
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Orders _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Orders getResource()
 */
class Orders extends AbstractModel implements OrdersInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Marketplace Orders cache tag.
     */
    const CACHE_TAG = 'marketplace_orders';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_orders';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_orders';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Orders');
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
            return $this->noRouteOrders();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Orders.
     *
     * @return \Webkul\Marketplace\Model\Orders
     */
    public function noRouteOrders()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare product's statuses.
     * Available event marketplace_product_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            Saleslist::PAID_STATUS_PENDING => __('Pending'),
            Saleslist::PAID_STATUS_COMPLETE => __('Paid'),
            Saleslist::PAID_STATUS_HOLD => __('Hold'),
            Saleslist::PAID_STATUS_REFUNDED => __('Refunded'),
            Saleslist::PAID_STATUS_CANCELED => __('Voided')
        ];
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
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
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
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
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
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
