<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\StockChange;

use Magento\Framework\Event\Observer as EventObserver;

class StockMovementObserver
{

    /**
     * @var \Magestore\InventorySuccess\Api\StockActivity\StockMovementActionInterface
     */
    protected $stockMovementAction;

    /**
     * @var array
     */
    protected $stockMovementConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface
     */
    protected $queryProcessor;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
    
    /**
     * @var \Magestore\InventorySuccess\Api\StockMovement\StockTransferServiceInterface
     */
    protected $stockTransferService;    

    /**
     * AdjustStockAfter constructor.
     *
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockMovementActionInterface $stockMovementActionInterface
     */
    public function __construct(
        \Magestore\InventorySuccess\Api\StockActivity\StockMovementActionInterface $stockMovementActionInterface,
        \Magestore\InventorySuccess\Model\StockActivity\StockMovementProvider $stockMovementProvider,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger,
        \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcessor,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magestore\InventorySuccess\Api\StockMovement\StockTransferServiceInterface $stockTransferService
    )
    {
        $this->stockMovementAction = $stockMovementActionInterface;
        $this->stockMovementConfig = $stockMovementProvider->getActionConfig();
        $this->objectManager = $objectManagerInterface;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
        $this->queryProcessor = $queryProcessor;
        $this->date = $date;
        $this->dateTime = $dateTime;
        $this->stockTransferService = $stockTransferService;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $data = $observer->getData();
        $insertData = $this->processData($data);
        $this->stockMovementAction->addStockMovementAction($insertData);
        $this->updateStockItemUpdatedTime($data);
        $this->stockTransferService->addAllStockMovement();
        return $this;
    }

    /**
     * process adjustment data to add stock movement
     *
     * @param array $data
     * @return array
     */
    protected function processData($data)
    {
        $insertData = [];
        $actionNumber = $this->getActionNumber($data['action_type'], $data['action_id']);
        foreach ($data['products'] as $productId => $productData) {
            if ($productData['adjust_qty'] == $productData['old_qty']) {
                continue;
            }
            $insertData[] = [
                'product_id' => $productId,
                'product_sku' => $productData['product_sku'],
                'qty' => $productData['adjust_qty'] - $productData['old_qty'],
                'action_code' => $data['action_type'],
                'action_id' => $data['action_id'],
                'action_number' => $actionNumber,
                'warehouse_id' => $data['warehouse_id']
            ];
        }
        return $insertData;
    }

    /**
     *
     * @param string $actionType
     * @return \Magestore\InventorySuccess\Api\StockMovement\StockMovementActivityInterface
     */
    protected function getStockMovementActionProvider($actionType)
    {
        $config = $this->stockMovementConfig[$actionType];
        if (!isset($config['class'])) {
            $this->logger->log('There is no action provider of ' . $actionType);
            throw new \Exception(__('There was an error while saving stock movement.'));
        }
        /** @var \Magestore\InventorySuccess\Api\StockMovement\StockMovementActivityInterface */
        $actionProvider = $this->objectManager->create($config['class']);
        return $actionProvider;
    }

    /**
     *
     * @param type $data
     */
    protected function getActionNumber($actionType, $actionId)
    {
        return $this->getStockMovementActionProvider($actionType)
            ->getStockMovementActionReference($actionId);
    }

    /**
     *
     * @param array $productIds
     * @return array
     */
    protected function _loadProductData($productIds)
    {
        $productData = [];
        $products = $this->productCollectionFactory->create()
            ->addAttributeToSelect(['sku'])
            ->addFieldToFilter('entity_id', ['in' => $productIds]);
        if ($products->getSize()) {
            foreach ($products as $product) {
                $productData[$product->getId()] = $product->getData();
            }
        }
        return $productData;
    }

    /**
     * Get Product Ids update updated time
     * 
     * @param $data
     * @return array
     */
    public function getUpdateStockItemData($data)
    {
        $productIds = [];
        foreach ($data['products'] as $productId => $productData) {
            if ($productData['adjust_qty'] == $productData['old_qty']) {
                continue;
            }
            $productIds[] = $productId;
        }
        return $productIds;
    }

    /**
     * Update Updated Time in table cataloginventory_stock_item
     * 
     * @param $data
     * @return $this
     */
    public function updateStockItemUpdatedTime($data)
    {
        $productIds = $this->getUpdateStockItemData($data);
        if (empty($productIds))
            return $this;
        $this->queryProcessor->start();
        $where = ['product_id IN (?)' => $productIds, 'website_id = ?' => $data['warehouse_id']];
        $this->queryProcessor->addQuery([
            'type' => \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => ['updated_time' => $this->dateTime->formatDate($this->date->gmtTimestamp())],
            'condition' => $where,
            'table' => $this->productCollectionFactory->create()->getResource()->getTable('cataloginventory_stock_item')
        ]);
        $this->queryProcessor->process();
    }

}
