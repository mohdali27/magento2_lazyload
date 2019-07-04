<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Permission\Warehouse;


use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory;
use Magestore\InventorySuccess\Model\WarehouseFactory;
use Magestore\InventorySuccess\Model\Permission\PermissionManagement;
use Magento\User\Model\UserFactory;
use Magento\Ui\Component\Form;
use Magento\Framework\Phrase;
use Magento\Framework\App\RequestInterface;

/**
 * Class DataProvider
 * @package Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Permission
 */
class FormDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    protected $collection;

    /**
     * @var WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var Modifier
     */
    protected $dataProvider;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $warehouseCollectionFactory,
        PermissionManagement $permissionManagement,
        RequestInterface $requestInterface,
        WarehouseFactory $warehouseFactory,
        Modifier\ModifierFormDataProvider $dataProvider,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $warehouseCollectionFactory->create();
        $this->permissionManagement = $permissionManagement;
        $this->requestInterface = $requestInterface;
        $this->warehouseFactory = $warehouseFactory;
        $this->dataProvider = $dataProvider;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $warehouse_id = $this->requestInterface->getParam('id');
        if($warehouse_id){
            $this->loadedData[$warehouse_id] = $this->warehouseFactory->create()->load($warehouse_id)->getData();
        }

        if($warehouse_id) {
            $users = $this->permissionManagement->getListPermissionsByObject($this->warehouseFactory->create()->load($warehouse_id));
            $usersData = $users->joinStaff()->toArray();
            $this->loadedData[$warehouse_id]['links']['associated'] = $usersData['items'];
        }

        return $this->loadedData;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $warehouse_id = $this->requestInterface->getParam('id');
        if($warehouse_id) {
            $meta = $this->dataProvider->modifyMeta($meta);
        }
        return $meta;
    }
}
