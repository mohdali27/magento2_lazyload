<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\Product\Delete;

class Delete extends \Magestore\InventorySuccess\Controller\Adminhtml\ManageStock\Product\Grid
{
    protected $_stockGrid = 'Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\DeleteProduct\Grid';
    protected $_stockGridName = 'warehouse.delete.product.grid';

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface
     */
    protected $_stockRegistryInterface;

    /**
     * Delete constructor.
     *
     * @param \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $stockRegistryInterface
     */
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $stockRegistryInterface
    ){
        parent::__construct($context, $permissionManagement, $dataPersistor, $resultRawFactory, $layoutFactory);
        $this->_stockRegistryInterface = $stockRegistryInterface;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $warehouseId = $this->getRequest()->getParam('id');
        $selectedProducts = json_decode($this->getRequest()->getParam('selected_product'),true);
        if(isset($selectedProducts['on']))
            unset($selectedProducts['on']);
        $result = $this->_stockRegistryInterface->removeProducts($warehouseId, array_keys($selectedProducts));
        if(count($result['success'])>0)
            $this->_context->getMessageManager()->addSuccessMessage(
                __('%1 product(s) has been deleted from location', count($result['success']))
            );
        if(count($result['error'])>0)
            $this->_context->getMessageManager()->addErrorMessage(
                __('%1 product(s) has been deleted fail', count($result['error']))
            );
        return parent::execute();
    }
}