<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Permission\Staff;


use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magestore\InventorySuccess\Model\Permission\PermissionManagement;
use Magento\User\Model\UserFactory;
use Magestore\InventorySuccess\Model\Warehouse;
use Magento\Ui\Component\Form;
use Magento\Framework\Phrase;
use Magento\Framework\App\RequestInterface;

/**
 * Class FormDataProvider
 * @package Magestore\InventorySuccess\Ui\DataProvider\Permission\Staff
 */
class FormDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    protected $collection;

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

    /**
     * @var RequestInterface
     */
    protected $requestInterface;

    protected $_warehouse;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $warehouseCollectionFactory,
        PermissionManagement $permissionManagement,
        UserFactory $userFactory,
        Warehouse $warehouse,
        Modifier\ModifierFormDataProvider $dataProvider,
        RequestInterface $requestInterface,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $warehouseCollectionFactory->create();
        $this->permissionManagement = $permissionManagement;
        $this->userFactory = $userFactory;
        $this->_warehouse = $warehouse;
        $this->dataProvider = $dataProvider;
        $this->requestInterface = $requestInterface;
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

        $userId = $this->requestInterface->getParam('user_id');
        if($userId){
            $this->loadedData[$userId] = $this->userFactory->create()->load($userId)->getData();
        }
        if($userId) {
            $users = $this->permissionManagement->getListPermissionsByObject($this->_warehouse, $userId);
            $usersData = $users->joinWarehouse()->toArray();
            $this->loadedData[$userId]['links']['associated'] = $usersData['items'];
        }

        return $this->loadedData;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta = $this->dataProvider->modifyMeta($meta);
        return $meta;
    }
}
