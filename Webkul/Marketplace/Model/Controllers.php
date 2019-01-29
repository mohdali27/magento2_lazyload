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
use Webkul\Marketplace\Api\Data\ControllersInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Controllers Model.
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Controllers _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Controllers getResource()
 */
class Controllers extends AbstractModel implements ControllersInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Marketplace Controllers cache tag.
     */
    const CACHE_TAG = 'marketplace_controller_list';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_controller_list';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_controller_list';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Controllers');
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
            return $this->noRouteControllers();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Controllers.
     *
     * @return \Webkul\Marketplace\Model\Controllers
     */
    public function noRouteControllers()
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
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Module Name
     *
     * @return int|null
     */
    public function getModuleName()
    {
        return parent::getData(self::MODULE_NAME);
    }

    /**
     * Set Module Name
     *
     * @param int $modulename
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setModuleName($modulename)
    {
        return $this->setData(self::MODULE_NAME, $modulename);
    }

    /**
     * Get controller path
     *
     * @return int|null
     */
    public function getControllerPath()
    {
        return parent::getData(self::CONTROLLER_PATH);
    }

    /**
     * Set controller path
     *
     * @param int $controllerPath
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setControllerPath($controllerPath)
    {
        return $this->setData(self::CONTROLLER_PATH, $controllerPath);
    }

    /**
     * Get Label
     *
     * @return int|null
     */
    public function getLabel()
    {
        return parent::getData(self::LABEL);
    }

    /**
     * Set Label
     *
     * @param int $label
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }
    
    /**
     * Get Is Child value
     *
     * @return int|null
     */
    public function getIsChild()
    {
        return parent::getData(self::IS_CHILD);
    }

    /**
     * Set Is Child value
     *
     * @param int $isChild
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setIsChild($isChild)
    {
        return $this->setData(self::IS_CHILD, $isChild);
    }

    /**
     * Get Parent Id
     *
     * @return int|null
     */
    public function getParentId()
    {
        return parent::getData(self::PARENT_ID);
    }

    /**
     * Set Parent Id
     *
     * @param int $parentId
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
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
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
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
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
