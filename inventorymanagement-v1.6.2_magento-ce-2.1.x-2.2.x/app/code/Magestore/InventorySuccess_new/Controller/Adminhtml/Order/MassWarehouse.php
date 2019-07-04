<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Order;

/**
 * Class Index
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Product\NoneInWarehouse
 */
class MassWarehouse extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::change_order_warehouse';

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\ChangeOrderWarehouseInterface
     */
    protected $changeOrderWarehouse;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory
     */
    protected $orderGridCollection;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory
     */
    protected $orderCollection;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Api\OrderProcess\ChangeOrderWarehouseInterface $changeOrderWarehouse,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderGridCollection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
    )
    {
        parent::__construct($context);
        $this->changeOrderWarehouse = $changeOrderWarehouse;
        $this->orderGridCollection = $orderGridCollection;
        $this->orderCollection = $orderCollection;
        $this->filter = $filter;
    }


    /**
     * Warehouse grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        if (!$warehouseId) {
            $this->messageManager->addErrorMessage(__('Please select a location!'));
            return $resultRedirect->setPath('sales/order/index');
        }
        $warehouse = $this->_warehouseFactory->create();
        $warehouse->getResource()->load($warehouse, $warehouseId);
        if (!$warehouse->getId()) {
            $this->messageManager->addErrorMessage(__('Selected location is not existed!'));
            return $resultRedirect->setPath('sales/order/index');
        }
        $collection = $this->orderGridCollection->create();
        $this->filter->getCollection($collection);
        $collection = $this->orderCollection->create()
            ->addFieldToFilter('entity_id', ['in' => $collection->getAllIds()]);
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection as $order) {
            if ($order->canShip())
                $this->changeOrderWarehouse->execute($order, $warehouse);
        }
        $this->messageManager->addSuccessMessage(__('Change location for selected order successfully!'));
        return $resultRedirect->setPath('sales/order/index');
    }
}