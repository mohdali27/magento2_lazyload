<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\StockMovement\StockTransfer\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magestore\InventorySuccess\Api\Data\StockMovement\StockTransferInterface;
use Magestore\InventorySuccess\Model\AdjustStock\StockMovementActivity\Adjustment;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse as Warehouse;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

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
        $mainTable = 'os_stock_transfer',
        $resourceModel = 'Magestore\InventorySuccess\Model\ResourceModel\StockMovement\StockTransfer'
    ) {
        $this->requestInterface = $requestInterface;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        $this->addFieldToFilter(StockTransferInterface::ACTION_CODE, ['neq' => Adjustment::STOCK_MOVEMENT_ACTION_CODE]);
        if($this->requestInterface->getParam('is_export') == 'true'){
            $this->getSelect()->joinLeft(
                ['warehouse' => $this->getTable(Warehouse::TABLE_INVENTORY_WARHOURSE)],
                'main_table.warehouse_id = warehouse.warehouse_id',
                ['warehouse_id' => new \Zend_Db_Expr('CONCAT(warehouse.warehouse_name, " (", warehouse.warehouse_code, ")")')]
            );
        }
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'reference_number') {
            $field = 'action_number';
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == 'reference_number') {
            $field = 'action_number';
        }
        return parent::setOrder($field, $direction);
    }
    
}
