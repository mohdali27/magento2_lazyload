<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\ResourceModel\Sales\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid collection
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orderCollection;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Magento\Framework\Module\Manager $moduleManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = '\Magento\Sales\Model\ResourceModel\Order'
    ) {
        $this->requestInterface = $requestInterface;
        $this->orderCollection = $orderCollection;
        $this->moduleManager = $moduleManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $warehouseId = $this->requestInterface->getParam('warehouse_id');
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        if($this->moduleManager->isEnabled('Magestore_Storepickup')) {
            $oderCollection = $this->orderCollection->addFieldToSelect(['warehouse_id', 'storepickup_id', 'storepickup_time', 'storepickup_status'])
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->getSelect()->assemble();
            if (!empty($warehouseId)) {
                $this->getSelect()->joinLeft(['sales_order' => new \Zend_Db_Expr("($oderCollection)")],
                    'main_table.entity_id = sales_order.entity_id',
                    ['warehouse_id' => 'sales_order.warehouse_id', 'storepickup_id' => 'sales_order.storepickup_id', 'storepickup_time' => 'sales_order.storepickup_time', 'storepickup_status' => 'sales_order.storepickup_status']);
                $this->addFieldToFilter('main_table.warehouse_id', $warehouseId);
            }
        }else{
            if (!empty($warehouseId)) {
                $this->addFieldToFilter('main_table.warehouse_id', $warehouseId);
            }
        }
        return $this;
    }
}
