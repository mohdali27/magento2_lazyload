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
use Webkul\Marketplace\Api\Data\SellertransactionInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Sellertransaction Model.
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Sellertransaction _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Sellertransaction getResource()
 */
class Sellertransaction extends AbstractModel implements SellertransactionInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Marketplace Sellertransaction cache tag.
     */
    const CACHE_TAG = 'marketplace_sellertransaction';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_sellertransaction';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_sellertransaction';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Sellertransaction');
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
            return $this->noRouteSellertransaction();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Sellertransaction.
     *
     * @return \Webkul\Marketplace\Model\Sellertransaction
     */
    public function noRouteSellertransaction()
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
     * @return \Webkul\Marketplace\Api\Data\SellertransactionInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
