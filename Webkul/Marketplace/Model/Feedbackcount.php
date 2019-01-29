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
use Webkul\Marketplace\Api\Data\FeedbackcountInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Feedbackcount Model.
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Feedbackcount _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Feedbackcount getResource()
 */
class Feedbackcount extends AbstractModel implements FeedbackcountInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@-*/

    /**
     * Marketplace Feedbackcount cache tag.
     */
    const CACHE_TAG = 'marketplace_feedbackcount';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_feedbackcount';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_feedbackcount';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Feedbackcount');
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
            return $this->noRouteFeedbackcount();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Feedbackcount.
     *
     * @return \Webkul\Marketplace\Model\Feedbackcount
     */
    public function noRouteFeedbackcount()
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
     * @return \Webkul\Marketplace\Api\Data\FeedbackcountInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
