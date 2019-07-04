<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\TransferStock;

use Magento\Framework\Model\AbstractModel as AbstractModel;

class TransferActivity extends AbstractModel implements
    \Magestore\InventorySuccess\Api\Data\TransferStock\TransferActivityInterface,
    \Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface

{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var $_objectManager \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivity');
    }

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
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
        return $this->_objectManager->get('\Magestore\InventorySuccess\Model\TransferStock\TransferActivityProduct');
    }
}
