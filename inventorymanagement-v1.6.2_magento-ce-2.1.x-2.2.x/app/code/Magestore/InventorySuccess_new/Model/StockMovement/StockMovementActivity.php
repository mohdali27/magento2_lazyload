<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\StockMovement;

use Magestore\InventorySuccess\Api\StockMovement\StockMovementActivityInterface;

class StockMovementActivity implements StockMovementActivityInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * StockMovementActivity constructor.
     * 
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ){
        $this->_urlBuilder = $urlBuilder;
        $this->objectManager = $objectManager;
    }

    const STOCK_MOVEMENT_ACTION_CODE = 'default';
    const STOCK_MOVEMENT_ACTION_LABEL = 'Default';

    /**
     * Get action code of stock movement
     *
     * @return string
     */
    public function getStockMovementActionCode(){
        return static::STOCK_MOVEMENT_ACTION_CODE;
    }
    
    /**
     * Get action label of stock movement
     *
     * @return string
     */
    public function getStockMovementActionLabel(){
        return static::STOCK_MOVEMENT_ACTION_LABEL;
    }

    /**
     * Get action reference of stock movement
     *
     * @return string
     */
    public function getStockMovementActionReference($id = null){
        return '';
    }
    
    /**
     * Get stock movement action URL
     *
     * @param $id
     * @return string|null
     */
    public function getStockMovementActionUrl($id = null){
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}