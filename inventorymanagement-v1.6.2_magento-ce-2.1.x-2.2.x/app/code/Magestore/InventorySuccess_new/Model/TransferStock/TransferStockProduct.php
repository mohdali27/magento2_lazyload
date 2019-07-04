<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\TransferStock;

use Magento\Framework\Model\AbstractModel as AbstractModel;

class TransferStockProduct extends AbstractModel implements
    \Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockProductInterface,
     \Magestore\InventorySuccess\Api\StockActivity\StockActivityProductInterface
{

    /**
     * @var $_objectManager \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function getStockActivityProductModel() {
        return $this->_objectManager->get('\Magestore\InventorySuccess\Model\TransferStock\TransferStockProduct');
    }

    public function getProducts($transferstock_id){
        $collection = $this->getCollection();
        $collection->addFieldToFilter("transferstock_id", $transferstock_id);
        return $collection;
    }
}
