<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Grid;

use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Grid
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @inheritdoc
     */
    protected $document = Document::class;

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'os_stocktaking',
        $resourceModel = 'Magestore\InventorySuccess\Model\ResourceModel\Stocktaking'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    public function getData()
    {
        $data = parent::getData();

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $om->get('Magento\Framework\App\RequestInterface');
        $options = $om->get('Magestore\InventorySuccess\Model\Stocktaking\Options\Status')
            ->toOptionHash();
        $timeZone = $om->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $requestInterface = $om->get('Magento\Framework\App\RequestInterface');
        if( ($requestInterface->getActionName() == 'gridToCsv') || ($requestInterface->getActionName() == 'gridToXml')){
            foreach ($data as &$item) {
                $item['status'] = $options[$item['status']];
                $item['created_at'] = $timeZone->date($item['created_at'])->format('m-d-Y');
            }
        }

        return $data;
    }

    /**
     * prepare collection
     *
     * @return array
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()])
            ->columns(
                [
                    'status', 'created_by', 'created_at', 'stocktaking_code', 'warehouse_name',
                    'warehouse' => new \Zend_Db_Expr('CONCAT(warehouse_name, " (",warehouse_code,")")')
                ]);

        return $this;
    }
    /**
     * rewrite add field to filters from collection
     *
     * @return array
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'warehouse') {
            $field = new \Zend_Db_Expr('CONCAT(warehouse_name, " (",warehouse_code,")")');
        }
        return parent::addFieldToFilter($field, $condition);
    }
}