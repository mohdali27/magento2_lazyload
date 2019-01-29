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
use Webkul\Marketplace\Api\Data\FeedbackInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Feedback Model
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Feedback _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Feedback getResource()
 */
class Feedback extends AbstractModel implements FeedbackInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Feedback's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**#@+
     * Feedback's Rating Options
     */
     const STAR1 = 20;
     const STAR2 = 40;
     const STAR3 = 60;
     const STAR4 = 80;
     const STAR5 = 100;
    /**#@-*/

    /**
     * Marketplace Feedback cache tag
     */
    const CACHE_TAG = 'marketplace_feedback';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_feedback';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_feedback';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Feedback');
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
            return $this->noRouteFeedback();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Feedback
     *
     * @return \Webkul\Marketplace\Model\Feedback
     */
    public function noRouteFeedback()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare feedback's statuses.
     * Available event marketplace_feedback_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Approved'),
            self::STATUS_DISABLED => __('Disapproved')
        ];
    }

    /**
     * Prepare feedback's rating options.
     * Available event marketplace_feedback_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAllRatingOptions()
    {
        return [
            self::STAR1 => __('1 Star'),
            self::STAR2 => __('2 Star'),
            self::STAR3 => __('3 Star'),
            self::STAR4 => __('4 Star'),
            self::STAR5 => __('5 Star'),
        ];
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
     * @return \Webkul\Marketplace\Api\Data\FeedbackInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
