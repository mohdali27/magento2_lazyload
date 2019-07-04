<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Permission\Warehouse;

use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory;
use Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface;

/**
 * Class ListDataProvider
 * @package Magestore\InventorySuccess\Ui\DataProvider\Permission\Warehouse
 */
class ListDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Warehouse collection
     *
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    protected $collection;

    /**
     * @var PermissionManagementInterface
     */
    protected $permissionManagementInterface;


    /**
     * ListDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PermissionManagementInterface $permissionManagementInterface
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PermissionManagementInterface $permissionManagementInterface,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->permissionManagementInterface = $permissionManagementInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();
        $collection->getSelect()
            ->columns(
                array('warehouse' => new \Zend_Db_Expr('CONCAT(warehouse_name, " (",warehouse_code,")")'))
            );
        $collection = $this->permissionManagementInterface->filterPermission($collection,'Magestore_InventorySuccess::warehouse_permission');
        return $collection;
    }
}
