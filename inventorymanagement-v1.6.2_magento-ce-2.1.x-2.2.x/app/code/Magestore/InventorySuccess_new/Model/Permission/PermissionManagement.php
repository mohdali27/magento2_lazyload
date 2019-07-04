<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Permission;

use Magestore\InventorySuccess\Api\Data\Permission\PermissionTypeInterface;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magestore\InventorySuccess\Model\ResourceModel\AbstractResource;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface;

/**
 * Class PermissionManagement
 * @package Magestore\InventorySuccess\Model\Permission
 */
class PermissionManagement  extends AbstractResource implements PermissionManagementInterface
{
    /**
     * @var \Magento\Framework\Authorization\PolicyInterface
     */
    protected $_policyInterface;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorizationInterface;

    /**
     * @var PermissionFactory
     */
    protected $_permissionFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    public function _construct(){
        
    }

    /**
     * PermissionManagement constructor.
     * @param \Magento\Framework\AuthorizationInterface $authorizationInterface
     * @param \Magento\Framework\Authorization\PolicyInterface $policyInterface
     * @param PermissionFactory $permissionFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param QueryProcessorInterface $queryProcessor
     * @param \Magento\Framework\Authorization\RoleLocatorInterface $roleLocator
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorizationInterface,
        \Magento\Framework\Authorization\PolicyInterface $policyInterface,
        \Magestore\InventorySuccess\Model\Permission\PermissionFactory $permissionFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcessor,
        \Magento\Framework\Authorization\RoleLocatorInterface $roleLocator,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ){
        parent::__construct( $queryProcessor,$context);
        $this->_authorizationInterface = $authorizationInterface;
        $this->_policyInterface = $policyInterface;
        $this->_permissionFactory = $permissionFactory;
        $this->_authSession = $authSession;
    }

    /**
     * @param $resourceId
     * @param PermissionTypeInterface|null $object
     * @param null $staffId
     * @return bool
     */
    public function checkPermission( $resourceId, PermissionTypeInterface $object = null, $staffId  = null){
        if(!$staffId){
            $staffId = $this->_authSession->getUser()->getId();
        }
        if(!$object){
            return $this->_authorizationInterface->isAllowed($resourceId);
        }
        if($this->_authorizationInterface->isAllowed('Magento_Backend::all')){
            return true;
        }
        if(!$this->_authorizationInterface->isAllowed($resourceId)){
            return false;
        }
        $permissionModel  = $this->loadPermissionByObject($object,$staffId);
        if($permissionModel->getRoleId() && $resourceId) {
            return $this->_policyInterface->isAllowed($permissionModel->getRoleId(), $resourceId);
        }else{
            return false;
        }
    }

    /**
     * @param PermissionTypeInterface $object
     * @param $staffId
     * @return \Magento\Framework\DataObject
     */
    public function loadPermissionByObject(PermissionTypeInterface $object ,$staffId){
        if(!$object->getId() || !$object->getPermissionType()|| !$staffId){
            return $this->_permissionFactory->create();
        }
        $collection = $this->getListPermissionsByObject($object,$staffId);
        if($collection->getSize()){
            return $collection->getFirstItem();
        }else{
            return $this->_permissionFactory->create();
        }
    }

    /**
     * @param SourceProviderInterface $collection
     * @param $resourceId
     * @param null $staffId
     * @return SourceProviderInterface
     */
    public function filterPermission(SourceProviderInterface $collection, $resourceId ,$staffId = null){

        if($resourceId == \Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission::ALL_WAREHOUSE_SEND_REQUEST)
        return $collection;

        if(!$this->checkPermission('Magento_Backend::all')){
            if(!$staffId){
                $staffId = $this->_authSession->getUser()->getId();
            }
            $collection->addFieldToFilter($collection->getIdFieldName(),['in'=>$this->getObjectAllIDsByAction($resourceId, $collection->getNewEmptyItem(), $staffId)]);
        }
        return $collection;
    }

    /**
     * @param $resourceId
     * @param PermissionTypeInterface $object
     * @param null $staffId
     * @return array
     */
    public function getObjectAllIDsByAction($resourceId, PermissionTypeInterface $object, $staffId = null){
        $objectIDs = $this->getObjectAllIDs($object,$staffId);
        $results = [];
        foreach ($objectIDs as $objectId){
            if($this->checkPermission($resourceId,$object->load($objectId),$staffId))
            {
                $results[] = $objectId;
            }
        }
        return $results;
    }

    /**
     * @param PermissionTypeInterface $object
     * @param null $staffId
     * @return mixed
     */
    public function getObjectAllIDs(PermissionTypeInterface $object, $staffId = null){
        if(!$staffId){
            $staffId = $this->_authSession->getUser()->getId();
        }
        return $this->getListPermissionsByObject($object,$staffId)->getAllObjectIDs();
    }

    /**
     * @param PermissionTypeInterface $object
     * @param null $staffId
     * @return mixed
     */
    public function getListPermissionsByObject(PermissionTypeInterface $object, $staffId = null){
        $collection = $this->_permissionFactory->create()->getCollection();
        if($object->getPermissionType()){
            $collection->addFieldToFilter('object_type',$object->getPermissionType());
        }
        if($object->getId()){
            $collection->addFieldToFilter('object_id',$object->getId());
        }
        if($staffId){
            $collection->addFieldToFilter('user_id',$staffId);
        }
        return $collection;
    }

    /**
     * @param PermissionTypeInterface $object
     * @param null $staffId
     * @return $this
     */
    public function removePermissionsByObject(PermissionTypeInterface $object, $staffId = null){
        /* start queries processing */
        $this->_queryProcessor->start();

        /* prepate to remove objectIds from Permission, then add queries to Processor */
        $this->_prepareRemovePermissionsByObject($object->getPermissionType(), $object->getId(), $staffId);

        /* process queries in Processor */
        $this->_queryProcessor->process();

        return $this;
    }

    /**
     * @param PermissionTypeInterface $object
     * @param null $staffId
     * @param $data
     * @return $this
     */
    public function setPermissionsByObject(PermissionTypeInterface $object, $staffId = null, $data){
        /* start queries processing */
        $this->_queryProcessor->start();

        /* prepate to remove objectIds from Permission, then add queries to Processor */
        $this->_prepareRemovePermissionsByObject($object->getPermissionType(), $object->getId(), $staffId);

        /* prepare to add objectIds to Permission, then add queries to Processor */
        $this->_prepareAddPermissionsByObject($object->getPermissionType(), $object->getId(), $staffId, $data);

        /* process queries in Processor */
        $this->_queryProcessor->process();

        return $this;
    }

    /**
     * @param $objectType
     * @param null $objectId
     * @param null $staffId
     * @return $this
     */
    protected function _prepareRemovePermissionsByObject($objectType, $objectId = null, $staffId = null)
    {
        $conditions = ['object_type = ?' => $objectType];
        if($objectId){
            $conditions['object_id = ?'] = $objectId;
        }
        if($staffId){
            $conditions['user_id = ?'] = $staffId;
        }
        /* add query to Processor */
        $this->_queryProcessor->addQuery(['type' => QueryProcessorInterface::QUERY_TYPE_DELETE,
            'condition' => $conditions,
            'table' => $this->_permissionFactory->create()->getResource()->getMainTable()
        ]);
        return $this;
    }

    /**
     * @param $objectType
     * @param null $objectId
     * @param null $staffId
     * @param $data
     * @return bool
     */
    protected function _prepareAddPermissionsByObject($objectType, $objectId = null, $staffId = null, $data)
    {
        /* add new objectIDs to Permission */
        if (!count($data)) {
            return false;
        }

        $insertData = [];
        foreach ($data as $item) {
            $permissionData = [];
            $permissionData['object_type'] = $objectType;
            if($staffId) {
                $permissionData['user_id'] = $staffId;
            }else {
                $permissionData['user_id'] = $item['user_id'];
            }
            if($objectId) {
                $permissionData['object_id'] = $objectId;
            }else {
                $permissionData['object_id'] = $item['object_id'];
            }

            if ($item['role_id']){
                $permissionData['role_id'] = $item['role_id'];
            } else {
                $request = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\RequestInterface');
                $roleUser = $request->getParam('roles');
                if (is_array($roleUser) && count($roleUser)){
                    $permissionData['role_id'] = $roleUser[0];
                } else {
                    $permissionData['role_id'] = '';
                }
            }

            $insertData[] = $permissionData;
        }
        /* add query to the processor */
        $this->_queryProcessor->addQuery(['type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $insertData,
            'table' => $this->_permissionFactory->create()->getResource()->getMainTable()
        ]);

        return true;
    }
}
