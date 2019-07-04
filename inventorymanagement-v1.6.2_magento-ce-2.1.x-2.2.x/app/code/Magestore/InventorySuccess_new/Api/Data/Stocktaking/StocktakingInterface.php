<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Data\Stocktaking;

interface StocktakingInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const STOCKTAKING_ID = 'stocktaking_id';
    const STOCKTAKING_CODE = 'stocktaking_code';
    const WAREHOUSE_ID = 'warehouse_id';
    const WAREHOUSE_NAME = 'warehouse_name';
    const WAREHOUSE_CODE = 'warehouse_code';
    const PARTICIPANTS = 'participants';
    const STOCKTAKE_AT = 'stocktake_at';
    const REASON = 'reason';
    const CREATED_BY = 'created_by';
    const CREATED_AT = 'created_at';
    const VERIFIED_BY = 'verified_by';
    const VERIFIED_AT = 'verified_at';
    const CONFIRMED_BY = 'confirmed_by';
    const CONFIRMED_AT = 'confirmed_at';
    const STATUS = 'status';
    
    /**
     * Constants defined Statuses
     */
    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_PROCESSING = 2;      
    const STATUS_VERIFIED = 3;
    const STATUS_CANCELED = 4;
    
    /**
     * Prefix code (using for generate the stocktaking code)
     */
    const PREFIX_CODE = 'STA';

    /**
     * Get Id
     * 
     * @return int|null
     */
    public function getId();
    
    /**
     * Get stocktaking code
     * 
     * @return string
     */
    public function getStocktakingCode();
    
    /**
     * Get Warehouse Id
     * 
     * @return int
     */
    public function getWarehouseId();
    
    /**
     * @return string
     */
    public function getWarehouseCode();
    
    /**
     * Get Warehouse Name
     * 
     * @return string|null
     */
    public function getWarehouseName();

    /**
     * Get Reason
     * 
     * @return string|null
     */
    public function getReason();

    /**
     * Get Participants
     *
     * @return string|null
     */
    public function getParticipants();

    /**
     * @return string
     */
    public function getStocktakeAt();

    /**
     * @return string
     */
    public function getCreatedBy();
    
    /**
     * @return string
     */
    public function getCreatedAt();
    
    /**
     * @return string
     */
    public function getConfirmedBy();
    
    /**
     * @return string
     */
    public function getConfirmedAt();

    /**
     * @return string
     */
    public function getVerifiedBy();

    /**
     * @return string
     */
    public function getVerifiedAt();
    
    /**
     * @return int
     */
    public function getStatus();
    
    /**
     * 
     * @param int $id
     * @return StocktakingInterface
     */
    public function setId($id);
    
    /**
     * 
     * @param string $stocktakingCode
     * @return StocktakingInterface
     */
    public function setStocktakingCode($stocktakingCode);
    
    /**
     * 
     * @param int $warehouseId
     * @return StocktakingInterface
     */
    public function setWarehouseId($warehouseId);
    
    /**
     * 
     * @param string $warehouseCode
     * @return StocktakingInterface
     */
    public function setWarehousecode($warehouseCode);
    
    /**
     * 
     * @param string $warehouseName
     * @return StocktakingInterface
     */
    public function setWarehouseName($warehouseName);
    
    /**
     * 
     * @param string $reason
     * @return StocktakingInterface
     */
    public function setReason($reason);

    /**
     *
     * @param string $participants
     * @return StocktakingInterface
     */
    public function setParticipants($participants);

    /**
     *
     * @param string $stocktakeAt
     * @return StocktakingInterface
     */
    public function setStocktakeAt($stocktakeAt);
    
    /**
     * 
     * @param string $createdBy
     * @return StocktakingInterface
     */
    public function setCreatedBy($createdBy);
    
    /**
     * 
     * @param string $createdAt
     * @return StocktakingInterface
     */
    public function setCreatedAt($createdAt);
    
    /**
     * 
     * @param string $verifiedBy
     * @return StocktakingInterface
     */
    public function setVerifiedBy($verifiedBy);
    
    /**
     * 
     * @param string $verifiedat
     * @return StocktakingInterface
     */
    public function setVerifiedAt($verifiedAt);
    /**
     *
     * @param string $confirmedBy
     * @return StocktakingInterface
     */
    public function setConfirmedBy($confirmedBy);

    /**
     *
     * @param string $confirmedAt
     * @return StocktakingInterface
     */
    public function setConfirmedAt($confirmedAt);
    
    /**
     * 
     * @param int $status
     * @return StocktakingInterface
     */
    public function setStatus($status);
}