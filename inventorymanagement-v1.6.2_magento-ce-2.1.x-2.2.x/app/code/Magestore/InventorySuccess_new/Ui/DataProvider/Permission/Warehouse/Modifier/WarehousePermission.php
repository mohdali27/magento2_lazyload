<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Permission\Warehouse\Modifier;


use Magento\Framework\App\RequestInterface;
use Magestore\InventorySuccess\Model\Permission\PermissionManagement;
use Magento\Ui\Component\Form;
use Magestore\InventorySuccess\Model\WarehouseFactory;

/**
 * Class WarehousePermission
 * @package Magestore\InventorySuccess\Ui\DataProvider\Permission\Warehouse\Modifier
 */
class WarehousePermission implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{

    /**
     * @var WarehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * @var RequestInterface
     */
    protected $_requestInterface;

    /**
     * @var PermissionManagement
     */
    protected $_permissionManagement;

    public function __construct(
        WarehouseFactory $warehouseFactory,
        RequestInterface $requestInterface,
        PermissionManagement $permissionManagement
    )
    {
        $this->_warehouseFactory = $warehouseFactory;
        $this->_requestInterface = $requestInterface;
        $this->_permissionManagement = $permissionManagement;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {

        $permissionStaff = $this->_permissionManagement->checkPermission('Magestore_InventorySuccess::warehouse_permission',
            $this->_warehouseFactory->create()->load($this->_requestInterface->getParam('id')));
        if (!$permissionStaff || !$this->_requestInterface->getParam('id')) {
            return $meta;
        }
        $meta = array_replace_recursive(
            $meta,
            [
                'warehouse_permission' => [
                    'children' => [
                        'warehouse_permission' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'autoRender' => true,
                                        'componentType' => 'insertForm',
                                        'component' => 'Magestore_InventorySuccess/js/form/components/insert-form',
                                        'ns' => 'os_warehouse_permission_form',
                                        'sortOrder' => '25',
                                        'params' => ['id' => $this->_requestInterface->getParam('id')]
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Location Permissions'),
                                'autoRender' => true,
                                'collapsible' => true,
                                'visible' => true,
                                'opened' => false,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 25
                            ],
                        ],
                    ],
                ],

            ]
        );
        return $meta;
    }
}