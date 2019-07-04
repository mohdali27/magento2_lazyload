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
class ChangeWarehouse extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::change_order_warehouse';

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\ChangeOrderWarehouseInterface
     */
    protected $changeOrderWarehouse;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * ChangeWarehouse constructor.
     * @param \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magestore\InventorySuccess\Api\OrderProcess\ChangeOrderWarehouseInterface $changeOrderWarehouse
     */
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magestore\InventorySuccess\Api\OrderProcess\ChangeOrderWarehouseInterface $changeOrderWarehouse
    )
    {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->changeOrderWarehouse = $changeOrderWarehouse;
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
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$warehouseId) {
            $this->messageManager->addErrorMessage(__('Please select a location!'));
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        }
        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('Please select an order to change location!'));
            return $resultRedirect->setPath('sales/order/index');
        }
        $warehouse = $this->_warehouseFactory->create();
        $warehouse->getResource()->load($warehouse, $warehouseId);
        if (!$warehouse->getId()) {
            $this->messageManager->addErrorMessage(__('Selected location is not existed!'));
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        }
        try {
            $order = $this->orderRepository->get($orderId);
        }catch (\Exception $e){
            $this->messageManager->addErrorMessage(__('Selected order is not existed!'));
            return $resultRedirect->setPath('sales/order/index');
        }
        if ($order->canShip()) {
            $this->changeOrderWarehouse->execute($order, $warehouse);
            $this->messageManager->addSuccessMessage(__('Change location for this order successfully!'));
        } else{
            $this->messageManager->addErrorMessage(__('Cannot change location for this order!'));
        }
        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}