<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\AdjustStock\Grid;

use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\AdjustStock\Grid
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
        $mainTable = 'os_adjuststock',
        $resourceModel = 'Magestore\InventorySuccess\Model\ResourceModel\AdjustStock'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    public function getData()
    {
        $data = parent::getData();

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $om->get('Magento\Framework\App\RequestInterface');
        $options = $om->get('Magestore\InventorySuccess\Model\AdjustStock\Options\Status')
            ->toOptionHash();
        if($request->getParam('is_export')) {
            foreach ($data as &$item) {
                $item['status'] = $options[$item['status']];
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
                    'status', 'created_by', 'created_at', 'adjuststock_code', 'warehouse_name',
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