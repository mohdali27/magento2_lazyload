<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\IncrementId;

use Magestore\InventorySuccess\Api\IncrementIdManagementInterface;

class IncrementIdManagement implements IncrementIdManagementInterface
{
    
    CONST DEFAULT_ID = 1;
    CONST CODE_LENGTH = 8;
    
    /**
     *
     * @var \Magestore\InventorySuccess\Model\IncrementId\IncrementIdFactory 
     */
    protected $_incrementIdFactory;
    
    public function __construct(
        \Magestore\InventorySuccess\Model\IncrementId\IncrementIdFactory $incrementIdFactory
    )
    {
        $this->_incrementIdFactory = $incrementIdFactory;
    }

    /**
     * Generate next code number
     * 
     * @param string $prefixCode
     * @return string
     */    
    public function getNextCode($prefixCode)
    {
        $nextId = $this->getNextId($prefixCode);
        
        /* generate the increment id */
        $formatId = pow(10, self::CODE_LENGTH + 1) + $nextId;
        $formatId = (string) $formatId;
        $formatId = substr($formatId, 0-self::CODE_LENGTH);
        
        /* update current Id */
        $this->updateId($prefixCode, $nextId);
        
        return $prefixCode . $formatId;
    }
    
    /**
     * Get next increment Id
     * 
     * @param string $prefixCode
     * @return int
     */
    public function getNextId($prefixCode)
    {
        /** @var \Magestore\InventorySuccess\Model\IncrementId\IncrementId $model */
        $model = $this->_incrementIdFactory->create();
        $model->getResource()->load($model, $prefixCode, 'code');
        $nextId = $model->getData('current_id') + 1;
        return $nextId;
    }

    /**
     * Update current increment Id
     * 
     * @param string $prefixCode
     * @param int $id
     */    
    public function updateId($prefixCode, $id = null)
    {
        /** @var \Magestore\InventorySuccess\Model\IncrementId\IncrementId $model */
        $model = $this->_incrementIdFactory->create();
        $model->getResource()->load($model, $prefixCode, 'code'); 
        if($id && $id > $model->getCurrentId()) {
            $model->setCode($prefixCode);
            $model->setCurrentId($id);
        } else {
            $model->setCode($prefixCode);
            $model->setCurrentId($model->getCurrentId() + 1);
        }
        $model->getResource()->save($model);
    }

}

