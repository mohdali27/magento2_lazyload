<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\StockActivity;

class StockMovementProvider
{
    /**
     * @var \Magestore\InventorySuccess\Api\StockMovement\StockMovementMaskInterface[]
     */
    protected $actionProviders;

    /**
     * @var []
     */
    protected $_actionConfig;

    /**
     * @param StockMovementActionInterface[] $actionProviders
     * @codeCoverageIgnore
     */
    public function __construct(
        array $actionProviders
    ) {
        $this->actionProviders = $actionProviders;
    }

    /**
     * Get all action config of stock movement
     * @return array
     */
    public function getActionConfig(){
        if(!$this->_actionConfig){
            $this->_actionConfig = [];
            foreach ($this->actionProviders as $actionProvider) {
                $this->_actionConfig[$actionProvider::STOCK_MOVEMENT_ACTION_CODE] = [
                    'label' => $actionProvider->getStockMovementActionLabel(),
                    'class' => get_class($actionProvider)
                ];
            }
        }
        return $this->_actionConfig;
    }

    /**
     * option hash from key to value of an action config
     * 
     * @return array
     */
    public function toActionOptionHash(){
        $result = [];
        foreach ($this->getActionConfig() as $key => $value){
            $result[$key] = $value['label'];
        }
        return $result;
    }
}