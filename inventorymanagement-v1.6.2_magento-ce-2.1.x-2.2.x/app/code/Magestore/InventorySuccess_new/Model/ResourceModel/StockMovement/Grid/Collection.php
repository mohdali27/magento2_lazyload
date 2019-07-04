<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\StockMovement\Grid;

use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

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
        $mainTable = 'os_stock_movement',
        $resourceModel = 'Magestore\InventorySuccess\Model\ResourceModel\StockMovement'
    ) {
        $this->requestInterface = $requestInterface;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    public function getData()
    {
        $data = parent::getData();
        if( ($this->requestInterface->getActionName() == 'gridToCsv') || ($this->requestInterface->getActionName() == 'gridToXml')){
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $options = $om->get('Magestore\InventorySuccess\Model\Warehouse\Options')->toHashOption();
            foreach ($data as &$item) {
                $item['warehouse_id'] = $options[$item['warehouse_id']];
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $warehouseId = $this->requestInterface->getParam('warehouse_id', false);
        $productId = $this->requestInterface->getParam('product_id', false);
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        if($warehouseId){
            $this->addFieldToFilter('warehouse_id', $warehouseId);
        }
        if($productId){
            $this->addFieldToFilter('product_id', $productId);
        }
        return $this;
    }


    public function addFieldToFilter($field, $condition = null)
    {   
        if ($field == 'reference_number') {
            $field = 'action_number';
        } 
        if ($field == 'product') {
            $field = 'product_sku';
        }
        if ($field == 'source_warehouse_name') {
            $field = 'source_warehouse';
        }
        if ($field == 'des_warehouse_name') {
            $field = 'des_warehouse';
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
        if ($field == 'product') {
            $field = 'product_sku';
        }
        if ($field == 'source_warehouse_name') {
            $field = 'source_warehouse';
        }
        if ($field == 'des_warehouse_name') {
            $field = 'des_warehouse';
        }
        return parent::setOrder($field, $direction);
    }
}
