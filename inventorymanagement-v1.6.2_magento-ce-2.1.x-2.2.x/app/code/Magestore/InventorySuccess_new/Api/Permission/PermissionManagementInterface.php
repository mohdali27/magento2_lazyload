<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Permission;

use Magestore\InventorySuccess\Api\Data\Permission\PermissionTypeInterface;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;

interface PermissionManagementInterface
{
    /**
     * @param $resourceId
     * @param PermissionTypeInterface|null $object
     * @param null $staffId
     * @return mixed
     */
    public function checkPermission( $resourceId, PermissionTypeInterface $object = null, $staffId  = null);

    /**
     * @param $object
     * @param $staffId
     * @return mixed
     */
    public function loadPermissionByObject(PermissionTypeInterface $object ,$staffId);

    /**
     * @param SourceProviderInterface $collection
     * @param $resourceId
     * @param null $staffId
     * @return mixed
     */
    public function filterPermission(SourceProviderInterface $collection, $resourceId ,$staffId = null);

    /**
     * @param $resourceId
     * @param PermissionTypeInterface $object
     * @param null $staffId
     * @return mixed
     */
    public function getObjectAllIDsByAction($resourceId, PermissionTypeInterface $object, $staffId = null);

    /**
     * @param PermissionTypeInterface $object
     * @param null $staffId
     * @return mixed
     */
    public function getObjectAllIDs(PermissionTypeInterface $object, $staffId = null);

    /**
     * @param $object
     * @param null $staffId
     * @return mixed
     */
    public function getListPermissionsByObject(PermissionTypeInterface $object, $staffId = null);

    /**
     * @param $object
     * @param null $staffId
     * @return mixed
     */
    public function removePermissionsByObject(PermissionTypeInterface $object, $staffId = null);

    /**
     * @param PermissionTypeInterface $object
     * @param null $staffId
     * @param $data
     * @return mixed
     */
    public function setPermissionsByObject(PermissionTypeInterface $object, $staffId = null, $data);
}