<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

use Magestore\InventorySuccess\Api\Warehouse\WarehouseRepositoryInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory as WarehouseCollectionFactory;
use Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface;

/**
 * Class WarehouseManagement
 * @package Magestore\InventorySuccess\Model\Warehouse
 */
class WarehouseRepository implements WarehouseRepositoryInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $warehouseCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        WarehouseCollectionFactory $warehouseCollectionFactory,
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger
    )
    {
        $this->_objectManager = $objectManager;
        $this->warehouseFactory = $warehouseFactory;
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Get warehouse information
     *
     * @param string $warehouseCode
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($warehouseCode)
    {
        /** @var \Magestore\InventorySuccess\Model\Warehouse $warehouse */
        $warehouse = $this->warehouseFactory->create();
        $warehouse->getResource()->load($warehouse, $warehouseCode, 'warehouse_code');
        if (!$warehouse->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Location with code "%1" does not exist',
                    $warehouseCode
                )
            );
        }
        return $warehouse;
    }

    /**
     * @inheritdoc
     */
    public function create(\Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse)
    {
        /** @var \Magestore\InventorySuccess\Model\Warehouse $newWarehouse */
        $newWarehouse = $this->warehouseFactory->create();
        if ($warehouse->getWarehouseCode()) {
            $newWarehouse->getResource()->load($newWarehouse, $warehouse->getWarehouseCode(), 'warehouse_code');
        }

        if ($newWarehouse->getWarehouseId()) {
            throw new \Magento\Framework\Exception\AlreadyExistsException(
                __(
                    'The warehouse code (' . $warehouse->getWarehouseCode() . ') is existed.'
                )
            );
        }

        try {
            $newWarehouse
                ->setData('warehouse_name', $warehouse->getWarehouseName())
                ->setData('warehouse_code', $warehouse->getWarehouseCode())
                ->setData('contact_email', $warehouse->getContactEmail())
                ->setData('telephone', $warehouse->getTelephone())
                ->setData('street', $warehouse->getStreet())
                ->setData('city', $warehouse->getCity())
                ->setData('country_id', $warehouse->getCountryId())
                ->setData('region', $warehouse->getRegion())
                ->setData('region_id', $warehouse->getRegionId())
                ->setData('postcode', $warehouse->getPostcode())
                ->setData('status', $warehouse->getStatus())
                ->setData('is_primary', $warehouse->getIsPrimary())
                ->setData('created_at', $warehouse->getCreatedAt())
                ->setData('updated_at', $warehouse->getUpdatedAt());

            $newWarehouse->getResource()->save($newWarehouse);

            if ($warehouse->getIsPrimary()) {
                $warehouseCollection = $this->warehouseCollectionFactory->create();
                /** @var \Magestore\InventorySuccess\Model\Warehouse $warehouseObj */
                foreach ($warehouseCollection as $warehouseObj) {
                    if ($warehouseObj->getWarehouseCode() != $newWarehouse->getWarehouseCode() && $warehouseObj->getIsPrimary()) {
                        $warehouseObj->setIsPrimary(false);
                        $warehouseObj->getResource()->save($warehouseObj);
                    }
                }
            }

            return $this->get($newWarehouse->getWarehouseCode());
        } catch (\Magento\Framework\Exception\ValidatorException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage(), 'apiCreateWarehouse');
        }
    }

    /**
     * @inheritdoc
     */
    public function update($code, \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse)
    {
        $existingWarehouse = $this->get($code);

        try {
            $existingWarehouse
                ->setData('warehouse_name', $warehouse->getWarehouseName())
                ->setData('warehouse_code', $warehouse->getWarehouseCode())
                ->setData('contact_email', $warehouse->getContactEmail())
                ->setData('telephone', $warehouse->getTelephone())
                ->setData('street', $warehouse->getStreet())
                ->setData('city', $warehouse->getCity())
                ->setData('country_id', $warehouse->getCountryId())
                ->setData('region', $warehouse->getRegion())
                ->setData('region_id', $warehouse->getRegionId())
                ->setData('postcode', $warehouse->getPostcode())
                ->setData('status', $warehouse->getStatus())
                ->setData('is_primary', $warehouse->getIsPrimary())
                ->setData('created_at', $warehouse->getCreatedAt())
                ->setData('updated_at', $warehouse->getUpdatedAt())
                ->save();

            if ($warehouse->getIsPrimary()) {
                $warehouseCollection = $this->warehouseCollectionFactory->create();
                /** @var \Magestore\InventorySuccess\Model\Warehouse $warehouseObj */
                foreach ($warehouseCollection as $warehouseObj) {
                    if ($warehouseObj->getWarehouseCode() != $existingWarehouse->getWarehouseCode() && $warehouseObj->getIsPrimary()) {
                        $warehouseObj->setIsPrimary(false);
                        $warehouseObj->getResource()->save($warehouseObj);
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\AlreadyExistsException(
                __(
                    $e->getMessage()
                )
            );
        }

        return $this->get($existingWarehouse->getWarehouseCode());
    }
}