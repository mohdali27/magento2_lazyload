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
use Webkul\Marketplace\Api\Data\OrderPendingMailsInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace OrderPendingMails Model.
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\OrderPendingMails _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\OrderPendingMails getResource()
 */
class OrderPendingMails extends AbstractModel implements OrderPendingMailsInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Paid Order status.
     */
    const PAID_STATUS_PENDING = '0';
    const PAID_STATUS_COMPLETE = '1';
    const PAID_STATUS_HOLD = '2';
    const PAID_STATUS_REFUNDED = '3';
    const PAID_STATUS_CANCELED = '4';

    /**
     * Marketplace OrderPendingMails cache tag.
     */
    const CACHE_TAG = 'marketplace_order_pendingemails';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_order_pendingemails';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_order_pendingemails';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\OrderPendingMails');
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
            return $this->noRouteOrderPendingMails();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route OrderPendingMails.
     *
     * @return \Webkul\Marketplace\Model\OrderPendingMails
     */
    public function noRouteOrderPendingMails()
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
     * @return \Webkul\Marketplace\Api\Data\OrderPendingMailsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
