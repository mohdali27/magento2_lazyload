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
namespace Webkul\Marketplace\Api\Data;

/**
 * Marketplace Controllers interface.
 * @api
 */
interface ControllersInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID         = 'entity_id';
    /**#@-*/

    const MODULE_NAME       = 'module_name';

    const CONTROLLER_PATH   = 'controller_path';
    
    const LABEL             = 'label';
    
    const IS_CHILD          = 'is_child';
    
    const PARENT_ID         = 'parent_id';
    
    const CREATED_AT        = 'created_at';
    
    const UPDATED_AT        = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setId($id);

    /**
     * Get Module Name
     *
     * @return int|null
     */
    public function getModuleName();

    /**
     * Set Module Name
     *
     * @param int $modulename
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setModuleName($modulename);

    /**
     * Get controller path
     *
     * @return int|null
     */
    public function getControllerPath();

    /**
     * Set controller path
     *
     * @param int $controllerPath
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setControllerPath($controllerPath);

    /**
     * Get Label
     *
     * @return int|null
     */
    public function getLabel();

    /**
     * Set Label
     *
     * @param int $label
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setLabel($label);

    /**
     * Get Is Child value
     *
     * @return int|null
     */
    public function getIsChild();

    /**
     * Set Is Child value
     *
     * @param int $isChild
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setIsChild($isChild);

    /**
     * Get Parent Id
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Set Parent Id
     *
     * @param int $parentId
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setParentId($parentId);

    /**
     * Get Created Time
     *
     * @return int|null
     */
    public function getCreatedAt();

    /**
     * Set Created Time
     *
     * @param int $modulename
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated Time
     *
     * @return int|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated Time
     *
     * @param int $updatedAt
     * @return \Webkul\Marketplace\Api\Data\ControllersInterface
     */
    public function setUpdatedAt($updatedAt);
}
