<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse;

use Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement as WarehouseManagement;

/**
 * Class Save
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Warehouse
 */
class Save extends AbstractWarehouse
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::warehouse_create';
    
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magestore\InventorySuccess\Controller\Adminhtml\Context
     */
    protected $_context;
    
    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement 
     */
    protected $warehouseManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseStoreViewMap\WarehouseStoreViewMapManagement
     */
    protected $warehouseStoreViewMapManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Permission\PermissionFactory
     */
    protected $permissionFactory;
    /**
     * @var \Magento\Directory\Model\Region
     */
    protected $region;

    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterfaceFactory
     */
    protected $countryInformationAcquirerInterfaceFactory;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        WarehouseManagement $warehouseManagement,
        \Magestore\InventorySuccess\Model\WarehouseStoreViewMap\WarehouseStoreViewMapManagement $warehouseStoreViewMapManagement,
        \Magestore\InventorySuccess\Model\Permission\PermissionFactory $permissionFactory,
        \Magento\Directory\Model\Region $region,
        \Magento\Directory\Api\CountryInformationAcquirerInterfaceFactory $countryInformationAcquirerInterfaceFactory
    ){
        parent::__construct($context, $permissionManagement, $dataPersistor);
        $this->_context = $context;
        $this->dataPersistor = $dataPersistor;
        $this->warehouseManagement = $warehouseManagement;
        $this->warehouseStoreViewMapManagement = $warehouseStoreViewMapManagement;
        $this->permissionFactory = $permissionFactory;
        $this->region = $region;
        $this->countryInformationAcquirerInterfaceFactory = $countryInformationAcquirerInterfaceFactory;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $id = isset($params['warehouse_id']) && $params['warehouse_id']>0?$params['warehouse_id']:null;
        $warehouse = $this->_context->getWarehouseFactory()->create();
        if (isset($params['region_id']) && $params['region_id']){
            $params['region'] = $this->region->load($params['region_id'])->getName();
        }
        if (isset($params['country_id']) && $params['country_id']){
            try {
                $info = $this->countryInformationAcquirerInterfaceFactory->create()->getCountryInfo($params['country_id']);
                $name = $info->getFullNameLocale();
            } catch (\Exception $e) {
                $name = '';
            }
            $params['country'] = $name;
        }
        foreach ($params as $key => $value){
            $warehouse->setData($key, $value);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if($warehouse->checkWarehouseCode($id)>0){
            $message = __('The location code (%1) is existed.', $warehouse->getWarehouseCode());
            $this->messageManager->addErrorMessage($message);
            $warehouse = $this->_context->getWarehouseFactory()->create()->load($id);
            $params['warehouse_code'] = $warehouse->getWarehouseCode();
            $this->dataPersistor->set('inventorysuccess_warehouse', $params);
            if($id)
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            return $resultRedirect->setPath('*/*/new');
        }
        
        try{
            $warehouse->setId($id)->save();

            if (!$id)
                $this->setWarehousePermission($warehouse);

            if(isset($params['store_ids']))
                $this->warehouseStoreViewMapManagement->linkWarehouseToStores($warehouse, $params['store_ids']);
            
            $this->dataPersistor->clear('inventorysuccess_warehouse');
            $eventManager = $this->_context->getEventManager();
            $eventManager->dispatch('controller_after_save_warehouse', ['warehouse' => $warehouse, 'request_params' => $params]);
            $this->messageManager->addSuccessMessage(__('The location has been saved.'));
        }catch (\Exception $ex){
            $this->messageManager->addErrorMessage($ex->getMessage());
            if($id)
                return $resultRedirect->setPath('*/*/edit', ['id' => $warehouse->getWarehouseId()]);
            return $resultRedirect->setPath('*/*/new');
        }        
        if(isset($params['back']) && $params['back']=='edit'){
            return $resultRedirect->setPath('*/*/edit', ['id' => $warehouse->getWarehouseId()]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param \Magestore\InventorySuccess\Model\Warehouse $warehouse
     * @throws \Exception
     */
    public function setWarehousePermission($warehouse)
    {
        /** @var $user \Magento\User\Model\User */
        $user = $this->_auth->getUser();
        $roleId = $user->getRole()->getId();
        if ($roleId) {
            /** @var \Magestore\InventorySuccess\Model\Permission\Permission $permission */
            $permission = $this->permissionFactory->create();
            $permission->setUserId($user->getId());
            $permission->setObjectType($warehouse->getPermissionType());
            $permission->setObjectId($warehouse->getWarehouseId());
            $permission->setRoleId($roleId);
            $permission->getResource()->save($permission);
        }
    }
    
}