<?php
namespace Webkul\Customattribute\Model;

use Webkul\Customattribute\Api\Data\ManageAttributeInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Customattribute Manageattribute Model
 *
 */
class Manageattribute extends \Magento\Framework\Model\AbstractModel implements ManageAttributeInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Seller's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Marketplace Seller cache tag
     */
    const CACHE_TAG = 'attribute_list';

    /**
     * @var string
     */
    protected $_cacheTag = 'attribute_list';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'attribute_list';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Customattribute\Model\ResourceModel\Manageattribute');
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteSeller();
        }
        return parent::load($id, $field);
    }
    /**
     * Prepare post's statuses.
     * Available event to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Active'), self::STATUS_DISABLED => __('Deactive')];
    }

    /**
     * Load No-Route Seller
     *
     * @return \Webkul\Marketplace\Model\Seller
     */
    public function noRouteSeller()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }


    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
