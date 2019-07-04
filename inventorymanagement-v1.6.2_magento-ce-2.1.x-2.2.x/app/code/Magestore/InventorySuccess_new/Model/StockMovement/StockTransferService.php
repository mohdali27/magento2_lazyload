<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\StockMovement;

use Magestore\InventorySuccess\Api\StockMovement\StockTransferServiceInterface;
use Magestore\InventorySuccess\Api\Data\StockMovement\StockTransferInterface;
use Magestore\InventorySuccess\Api\Data\StockMovement\StockMovementInterface;

class StockTransferService implements StockTransferServiceInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\StockMovement\StockTransfer\CollectionFactory
     */
    protected $stockTransferCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\StockMovement\CollectionFactory
     */
    protected $stockMovementCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\StockMovement\StockTransferFactory
     */
    protected $stockTransferFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\StockMovementFactory
     */
    protected $stockMovementResourceFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\StockMovement\StockTransferFactory
     */
    protected $stockTransferResourceFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\IncrementIdManagementInterface
     */
    protected $incrementIdManagement;


    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockMovementActionFactory
     */
    protected $stockMovementActionFactory;


    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\StockMovement\StockTransfer\CollectionFactory $stockTransferCollectionFactory,
        \Magestore\InventorySuccess\Model\StockMovement\StockTransferFactory $stockTransferFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\StockMovement\StockTransferFactory $stockTransferResourceFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\StockMovementFactory $stockMovementResourceFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\StockMovement\CollectionFactory $stockMovementCollectionFactory,
        \Magestore\InventorySuccess\Api\IncrementIdManagementInterface $incrementIdManagement,
        \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockMovementActionFactory $stockMovementActionFactory

    )
    {
        $this->stockTransferCollectionFactory = $stockTransferCollectionFactory;
        $this->stockTransferFactory = $stockTransferFactory;
        $this->stockTransferResourceFactory = $stockTransferResourceFactory;
        $this->stockMovementResourceFactory = $stockMovementResourceFactory;
        $this->stockMovementCollectionFactory = $stockMovementCollectionFactory;
        $this->incrementIdManagement = $incrementIdManagement;
        $this->stockMovementActionFactory = $stockMovementActionFactory;
    }

    /**
     * Add stock movement record to stock transfer
     *
     * @param \Magestore\InventorySuccess\Api\Data\StockMovement\StockMovementInterface $stockMovement
     * @return $this
     */
    public function addStockMovement(StockMovementInterface $stockMovement)
    {
        if ($this->isAddedStockMovement($stockMovement)) {
            return $this;
        }

        $stockTransfer = $this->getStockTransferFromStockMovement($stockMovement);

        if (!$stockTransfer->getStockTransferId()) {
            $stockTransfer = $this->createStockTransferFromStockMovement($stockMovement);
        } else {

            $total_qty = $stockMovement->getTotalQty() ? $stockMovement->getTotalQty() : $stockMovement->getQty();
            $total_sku = $stockMovement->getTotalSku() ? $stockMovement->getTotalSku() : 1;
            $stockTransfer->setQty($stockTransfer->getQty() + $total_qty)
                ->setTotalSku($stockTransfer->getTotalSku() + $total_sku);
            $this->getStockTransferResource()->save($stockTransfer);
        }

        /* resave stockTransferId for all item */
        $data = array();
        $data['values'] = array(
            StockMovementInterface::STOCK_TRANSFER_ID  => $stockTransfer->getStockTransferId()
        );
        $data['condition'] = array(
            StockMovementInterface::ACTION_CODE .'=?'  => $stockMovement->getActionCode(),
            StockMovementInterface::ACTION_ID .'=?' => $stockMovement->getActionId(),
            StockMovementInterface::ACTION_NUMBER .'=?' => $stockMovement->getActionNumber(),
        );

        $this->stockMovementActionFactory->create()->updateStockMovements($data);
        return $this;
    }

    /**
     *
     * @param StockMovementInterface $stockMovement
     * @return StockTransferInterface
     */
    public function createStockTransferFromStockMovement(StockMovementInterface $stockMovement)
    {
        $stockTransfer = $this->stockTransferFactory->create();
        $stockTransfer->setQty( $stockMovement->getTotalQty() ? $stockMovement->getTotalQty() : $stockMovement->getQty())
            ->setTotalSku( $stockMovement->getTotalSku() ? $stockMovement->getTotalSku() : 1)
            ->setActionCode($stockMovement->getActionCode())
            ->setActionId($stockMovement->getActionId())
            ->setActionNumber($stockMovement->getActionNumber())
            ->setWarehouseId($stockMovement->getWarehouseId())
            ->setCreatedAt($stockMovement->getCreatedAt())
            ->setTransferCode($this->incrementIdManagement->getNextCode(StockTransferInterface::PREFIX_CODE));
        $this->getStockTransferResource()->save($stockTransfer);

        $stockMovement->setStockTransferId($stockTransfer->getId());
        $this->getStockMovementResource()->save($stockMovement);
        return $stockTransfer;
    }

    /**
     *
     * @param \Magestore\InventorySuccess\Model\StockMovement\StockMovementInterface $stockMovement
     * @return boolean
     */
    public function isAddedStockMovement(StockMovementInterface $stockMovement)
    {
        if (!$stockMovement->getStockTransferId()) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param StockMovementInterface $stockMovement
     * @return StockTransferInterface
     */
    public function getStockTransferFromStockMovement(StockMovementInterface $stockMovement)
    {
        $stockTransferCollection = $this->stockTransferCollectionFactory->create();
        $stockTransferCollection->addFieldToFilter(StockTransferInterface::WAREHOUSE_ID, $stockMovement->getWarehouseId())
            ->addFieldToFilter(StockTransferInterface::ACTION_CODE, $stockMovement->getActionCode())
            ->addFieldToFilter(StockTransferInterface::ACTION_ID, $stockMovement->getActionId())
            ->addFieldToFilter(StockTransferInterface::CREATED_AT, $stockMovement->getCreatedAt());
        return $stockTransferCollection->getFirstItem();
    }


    /**
     *
     * @param \Magestore\InventorySuccess\Model\StockMovement\StockMovementInterface $stockMovement
     * @return boolean
     */
    public function isExistedStockTransfer(StockMovementInterface $stockMovement)
    {
        $stockTransferCollection = $this->stockTransferCollectionFactory->create();
        $stockTransferCollection->addFieldToFilter(StockTransferInterface::WAREHOUSE_ID, $stockMovement->getWarehouseId())
            ->addFieldToFilter(StockTransferInterface::ACTION_CODE, $stockMovement->getActionCode())
            ->addFieldToFilter(StockTransferInterface::ACTION_ID, $stockMovement->getActionId())
            ->addFieldToFilter(StockTransferInterface::CREATED_AT, $stockMovement->getCreatedAt());
        if ($stockTransferCollection->getSize()) {
            return true;
        }
        return false;
    }


    /**
     * Add all stock movement records to transfer
     *
     */
    public function addAllStockMovement()
    {
        $stockMovementCollection = $this->stockMovementCollectionFactory->create();
        $stockMovementCollection->addFieldToFilter(StockMovementInterface::STOCK_TRANSFER_ID, ['null' => true]);

        /* group by the same transaction */
        $stockMovementCollection->getSelect()->columns(array(
            'total_qty' => new \Zend_Db_Expr('Sum(qty)'),
            'total_sku' => new \Zend_Db_Expr('COUNT(DISTINCT product_sku)'),
        ));
        $stockMovementCollection->getSelect()->group(StockMovementInterface::ACTION_CODE);
        $stockMovementCollection->getSelect()->group(StockMovementInterface::ACTION_ID);
        $stockMovementCollection->getSelect()->group(StockMovementInterface::ACTION_NUMBER);

        if ($stockMovementCollection->getSize()) {
            foreach ($stockMovementCollection as $stockMovement) {
                $this->addStockMovement($stockMovement);
            }
        }
        return $this;
    }


    /**
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockMovement\StockTransfer
     */
    public function getStockTransferResource()
    {
        return $this->stockTransferResourceFactory->create();
    }

    /**
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockMovement
     */
    public function getStockMovementResource()
    {
        return $this->stockMovementResourceFactory->create();
    }

}