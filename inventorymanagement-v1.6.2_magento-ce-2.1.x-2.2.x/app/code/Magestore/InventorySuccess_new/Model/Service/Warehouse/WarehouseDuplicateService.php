<?php

namespace Magestore\InventorySuccess\Model\Service\Warehouse;

use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;
use \Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class WarehouseDuplicateService extends \Magestore\InventorySuccess\Model\ResourceModel\AbstractResource implements \Magestore\InventorySuccess\Api\Warehouse\WarehouseDuplicateServiceInterface {

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Helper\Data
     */
    protected $helper;

    /**
     * WarehouseDuplicateService constructor.
     * @param QueryProcessorInterface $queryProcessor
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Helper\Data $helper
     * @param null $connectionName
     */
    function __construct(
        \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcessor,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Helper\Data $helper,
        $connectionName = null
    ){
        parent::__construct($queryProcessor, $context, $connectionName);
        $this->_objectManager = $objectManager;
        $this->warehouseFactory = $warehouseFactory;
        $this->helper = $helper;
    }

    public function _construct() {

    }

    /**
     * @inheritdoc
     */
    public function duplicateWarehouse($id)
    {
        $currentWarehouse = $this->warehouseFactory->create()->load($id);
        if(!$currentWarehouse->getId()) {
            return false;
        }
        $newWarehouse = $this->createNewWarehouse($currentWarehouse);
        if(!$newWarehouse || !$newWarehouse->getId()) {
            return false;
        }

        $this->_queryProcessor->start();

        // duplicate stock data
        $this->duplicateWarehouseStockData($id, $newWarehouse->getId());

        $this->_queryProcessor->process();

        return $newWarehouse;
    }

    protected function createNewWarehouse($currentWarehouse){
        $data = $currentWarehouse->getData();
        unset($data['warehouse_id']);
        $data['is_primary'] = 0;
        $dateTime = new \DateTime();
        $dateTime = $dateTime->format('Y-m-d H:i:s');
        $data['warehouse_name'] .= str_replace(['-',' ',':'],'',$dateTime);
        $data['warehouse_code'] .= str_replace(['-',' ',':'],'',$dateTime);
        $newWarehouse = $this->warehouseFactory->create();
        try {
            $newWarehouse->setData($data)->save();
            return $newWarehouse;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function duplicateWarehouseStockData($warehouseId, $newWarehouseId)
    {
        $products = [];
        $dataStock = [];
        $batch = 0;
        $isDuplicateProductStock = $this->helper->getDuplicateStockData();
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getTable(WarehouseProductResource::MAIN_TABLE), '*')
            ->where('website_id = ?', $warehouseId);
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            if (!isset($products[$batch])) {
                $products[$batch] = [];
            }
            unset($row[WarehouseProductResource::PRIMARY_KEY]);
            $row[WarehouseProductInterface::WAREHOUSE_ID] = $newWarehouseId;
            $row[WarehouseProductInterface::WEBSITE_ID] = $newWarehouseId;
            $row[WarehouseProductInterface::STOCK_ID] = $newWarehouseId;
            if ($isDuplicateProductStock) {
                $dataStock[$row[WarehouseProductInterface::PRODUCT_ID]] = [
                    WarehouseProductInterface::TOTAL_QTY => $row[WarehouseProductInterface::TOTAL_QTY]
                ];
                $row[WarehouseProductInterface::TOTAL_QTY] = $row[WarehouseProductInterface::TOTAL_QTY] ?: 0;
            } else {
                $row[WarehouseProductInterface::TOTAL_QTY] = 0;
            }
            $row[WarehouseProductInterface::AVAILABLE_QTY] = $row[WarehouseProductInterface::TOTAL_QTY];
            $products[$batch][] = $row;
            if (count($products[$batch]) > 900) {
                $batch++;
            }
        }

        /* add query to Processor */
        foreach ($products as $batch => $items) {
            $this->_queryProcessor->addQuery([
                'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
                'values' => $items,
                'table' => $this->getTable(WarehouseProductResource::MAIN_TABLE)
            ]);
        }

        if ($isDuplicateProductStock) {
            $this->reCalculateGlobalStock($dataStock);
        }
    }

    protected function reCalculateGlobalStock($stockData) {
        $connection = $this->getConnection();
        $conditions = [];

        if(!count($stockData)){
            return $this;
        }

        $globalWarehouseId = \Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface::DEFAULT_SCOPE_ID;

        foreach ($stockData as $productId => $item) {
            $case = $connection->quoteInto('?', $productId);
            $totalQtyResult = $connection->quoteInto('total_qty+?', $item[WarehouseProductInterface::TOTAL_QTY]);
            $availableQtyResult = $connection->quoteInto('qty+?', $item[WarehouseProductInterface::TOTAL_QTY]);
            $conditions[WarehouseProductInterface::TOTAL_QTY][$case] = $totalQtyResult;
            $conditions[WarehouseProductInterface::AVAILABLE_QTY][$case] = $availableQtyResult;
        }
        $values = [
            WarehouseProductInterface::TOTAL_QTY => $connection->getCaseSql('product_id', $conditions[WarehouseProductInterface::TOTAL_QTY], WarehouseProductInterface::TOTAL_QTY),
            WarehouseProductInterface::AVAILABLE_QTY => $connection->getCaseSql('product_id', $conditions[WarehouseProductInterface::AVAILABLE_QTY], WarehouseProductInterface::AVAILABLE_QTY),
        ];
        $where = [
            'product_id IN (?)' => array_keys($stockData),
            WarehouseProductInterface::WAREHOUSE_ID . '=?' => $globalWarehouseId
        ];

        /* add query to the processor */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $values,
            'condition' => $where,
            'table' => $this->getTable(WarehouseProductResource::MAIN_TABLE)
        ]);

        return $this;
    }
}