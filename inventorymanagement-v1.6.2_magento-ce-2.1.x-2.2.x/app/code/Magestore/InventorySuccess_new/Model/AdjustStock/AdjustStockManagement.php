<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\AdjustStock;

use Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface;
use Magestore\InventorySuccess\Model\StockActivity\ProductSelectionManagement;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;
use Magestore\InventorySuccess\Model\AdjustStock\StockMovementActivity\Adjustment;

/**
 * Class AdjustStockManagement
 * @package Magestore\InventorySuccess\Model\AdjustStock
 */
class AdjustStockManagement extends ProductSelectionManagement implements AdjustStockManagementInterface
{
    /**
     * @var array|null
     */
    protected $data;

    /**
     * @var \Magestore\InventorySuccess\Helper\Data
     */
    protected $helper;

    /**
     * AdjustStockManagement constructor.
     * @param \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagementFactory $resourceProductSelectionManagementFactory
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegsitry
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Api\IncrementIdManagementInterface $incrementIdManagement
     * @param \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper
     * @param \Magestore\InventorySuccess\Helper\Data $helper
     * @param array|null $data
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagementFactory $resourceProductSelectionManagementFactory,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegsitry,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\IncrementIdManagementInterface $incrementIdManagement,
        \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper,
        \Magestore\InventorySuccess\Helper\Data $helper,
        array $data = null
    )
    {
        parent::__construct(
                $resourceProductSelectionManagementFactory,
                $stockChange,
                $warehouseStockRegsitry,
                $warehouseFactory,
                $incrementIdManagement,
                $systemHelper
        );
        $this->data = $data;
        $this->helper = $helper;
    }

    /**
     * Create new Stock Adjustment
     *
     * @param AdjustStockInterface $adjustStock
     * @param array $data
     * @param bool $requiredProduct
     * @param bool $requireChange
     * @return AdjustStockInterface
     */
    public function createAdjustment(AdjustStockInterface $adjustStock, $data, $requiredProduct = false, $requireChange = false)
    {
        $this->data = $data;
        $createdAt = isset($data[AdjustStockInterface::CREATED_AT]) ?
                $data[AdjustStockInterface::CREATED_AT] :
                $this->_systemHelper->getCurTime();
        $createdBy = isset($data[AdjustStockInterface::CREATED_BY]) ?
                $data[AdjustStockInterface::CREATED_BY] :
                ($this->_systemHelper->getCurUser() ? $this->_systemHelper->getCurUser()->getUserName() : null);
        $adjustStockCode = isset($data[AdjustStockInterface::ADJUSTSTOCK_CODE]) ?
                $data[AdjustStockInterface::ADJUSTSTOCK_CODE] :
                $this->generateCode();

        /* load warehouse data if $data[AdjustStockInterface::WAREHOUSE_NAME] is null */
        if (!isset($data[AdjustStockInterface::WAREHOUSE_NAME])) {
            $warehouse = $this->_warehouseFactory->create()->load($data[AdjustStockInterface::WAREHOUSE_ID]);
            $data[AdjustStockInterface::WAREHOUSE_NAME] = $warehouse->getWarehouseName();
            $data[AdjustStockInterface::WAREHOUSE_CODE] = $warehouse->getWarehouseCode();
        }

        /* prepare data for stock adjustment */
        $adjustStock->setReason($data[AdjustStockInterface::REASON])
                ->setStatus(AdjustStockInterface::STATUS_PENDING)
                ->setWarehouseId($data[AdjustStockInterface::WAREHOUSE_ID])
                ->setWarehouseName($data[AdjustStockInterface::WAREHOUSE_NAME])
                ->setWarehouseCode($data[AdjustStockInterface::WAREHOUSE_CODE])
                ->setCreatedAt($createdAt)
                ->setCreatedBy($createdBy)
                ->setAdjuststockCode($adjustStockCode)
        ;

        if (isset($data['products']) && count($data['products'])) {
            /* load old_qty of products in warehouse */
            $whProducts = $this->_warehouseStockRegsitry->getStocks($data[AdjustStockInterface::WAREHOUSE_ID], array_keys($data['products']));
            if ($whProducts->getSize()) {
                if($this->helper->getAdjustStockChange()){
                    foreach ($whProducts as $whProduct) {
                        $data['products'][$whProduct->getProductId()]['old_qty'] = $whProduct->getTotalQty();
                        if(!isset($data['products'][$whProduct->getProductId()]['adjust_qty'])
                            || $data['products'][$whProduct->getProductId()]['adjust_qty'] == '') {
                            $data['products'][$whProduct->getProductId()]['adjust_qty'] = $whProduct->getTotalQty() +
                                $data['products'][$whProduct->getProductId()]['change_qty'];
                        }
                        if(!isset($data['products'][$whProduct->getProductId()]['change_qty'])
                            || $data['products'][$whProduct->getProductId()]['change_qty'] == '') {
                            $data['products'][$whProduct->getProductId()]['change_qty'] =
                                $data['products'][$whProduct->getProductId()]['adjust_qty'] - $whProduct->getTotalQty();
                        }
                    }
                }else{
                    foreach ($whProducts as $whProduct) {
                        $data['products'][$whProduct->getProductId()]['old_qty'] = $whProduct->getTotalQty();
                        $data['products'][$whProduct->getProductId()]['change_qty'] =
                            $data['products'][$whProduct->getProductId()]['adjust_qty'] - $whProduct->getTotalQty();

                    }
                }
            }
            // Fix for product that it is not in warehouse
            foreach ($data['products'] as &$adjustData) {
                if (!isset($adjustData['change_qty'])) {
                    $adjustData['change_qty'] = $adjustData['adjust_qty'];
                }
            }
            /* require change qty while creating stock adjustment */
            /* if there is no qty changed, do not create adjuststock */
            if($requireChange) {
                foreach($data['products'] as $productId => $adjustData) {
                    if(isset($adjustData['change_qty']) && $adjustData['change_qty'] == 0) {
                        unset($data['products'][$productId]);
                    }
                }
            }
        }
        /* create Product Selection */
        if (!$requiredProduct || count($data['products']) > 0) {
            $this->createSelection($adjustStock, $data);
        }

        return $adjustStock;
    }

    /**
     * Generate unique code of Stock Adjustment
     *
     * @return string
     */
    public function generateCode()
    {
        return parent::generateUniqueCode(AdjustStockInterface::PREFIX_CODE);
    }

    /**
     * Complete a stock adjustment
     *
     * @param AdjustStockInterface $adjustStock
     * @param bool $updateCatalog
     */
    public function complete(AdjustStockInterface $adjustStock, $updateCatalog = true)
    {
        $products = $this->getProducts($adjustStock);
        $productData = [];
        if ($products->getSize()) {
            foreach ($products as $product) {
                $productData[$product->getProductId()] = [
                    'old_qty' => $product->getOldQty(),
                    'adjust_qty' => $product->getAdjustQty(),
                    'change_qty' => $product->getChangeQty(),
                    'product_sku' => $product->getProductSku(),
                    'product_name' => $product->getProductName()
                ];
            }
        }
        /* adjust stocks in warehouse & global */
        $this->_stockChange->adjust($adjustStock->getWarehouseId(), $productData, Adjustment::STOCK_MOVEMENT_ACTION_CODE, $adjustStock->getId(), $updateCatalog);

        /* mark as completed */
        $confirmedAt = isset($this->data[AdjustStockInterface::CONFIRMED_AT]) ?
                $this->data[AdjustStockInterface::CONFIRMED_AT] :
                $this->_systemHelper->getCurTime();
        $confirmedBy = isset($this->data[AdjustStockInterface::CONFIRMED_BY]) ?
                $this->data[AdjustStockInterface::CONFIRMED_BY] :
                ($this->_systemHelper->getCurUser() ? $this->_systemHelper->getCurUser()->getUserName() : null);

        $adjustStock->setStatus(AdjustStockInterface::STATUS_COMPLETED)
                ->setConfirmedBy($confirmedBy)
                ->setConfirmedAt($confirmedAt);
        $this->getStockActivityResource($adjustStock)->save($adjustStock);

        //$this->addStockMovementAction($adjustStock, $products);
    }

    /**
     * @param $adjustStock
     * @param $adjustProducts
     * @return $this
     */
    protected function addStockMovementAction($adjustStock, $adjustProducts)
    {
        if ($adjustProducts->getSize()) {
            $stockMovementAction = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create('Magestore\InventorySuccess\Model\AdjustStock\StockMovementAction');
            $productFactory = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create('Magento\Catalog\Model\ProductFactory');
            foreach ($adjustProducts as $product) {
                $productId = $product->getProductId();
                $productSku = $product->getProductSku();
                if (!$productSku) {
                    $productSku = $productFactory->create()->load($productId)->getSku();
                }
                $stockMovementData = [
                    'product_id' => $productId,
                    'product_sku' => $productSku,
                    'action_id' => $adjustStock->getAdjuststockId(),
                    'reference_number' => $adjustStock->getAdjuststockCode(),
                    'qty' => $product->getAdjustQty() - $product->getOldQty(),
                    'des_warehouse_id' => $adjustStock->getWarehouseId(),
                    'des_warehouse' => $adjustStock->getWarehouseName() . ' (' . $adjustStock->getWarehouseCode() . ')'
                ];
                $stockMovementAction->addStockMovementActionLog($stockMovementData);
            }
        }
        return $this;
    }

}
