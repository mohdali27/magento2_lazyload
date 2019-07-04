<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\TransferStock;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;



class TransferStockProduct extends AbstractDb
{

    const FIELD_QTY_DELIVERED = "qty_delivered";
    const FIELD_QTY_RECEIVED = "qty_received";
    const FIELD_QTY_RETURNED = "qty_returned";

    /**
     *
     * @var \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface
     */
    protected $_queryProcessor;

    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory */
    protected $_transferStockProductCollectionFactory;

    public function __construct(
        \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcessor,
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EntityManager $entityManager,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $transferStockProductCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->entityManager = $entityManager;
        $this->_transferStockProductCollectionFactory = $transferStockProductCollectionFactory;
        $this->_queryProcessor = $queryProcessor;

    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('os_transferstock_product', 'transferstock_product_id');
    }


    public function updateQty($transferstock_id, $qtys, $field)
    {
        /* start queries processing */
        $this->_queryProcessor->start();

        $productIds =  array_keys($qtys);

        $transferProductCollection = $this->_transferStockProductCollectionFactory->create();
        $products = $transferProductCollection->addFieldToFilter('transferstock_id', $transferstock_id);
        if (count($productIds)) {
            $products->addFieldToFilter('product_id', ['in' => $productIds]);
        }

        $connection = $this->getConnection();
        $changeQtys = [];
        $newQtys = $qtys;

        if ($products->getSize()) {

            $conditions = [];
            foreach ($products as $product) {

                $changeQty = (int)$qtys[$product->getProductId()][$product->getProductId()] + (int)$product->getData($field) ;

                $changeQtys[$product->getProductId()] = $changeQty;
                unset($newQtys[$product->getProductId()]);
                /* prepare update value */
                $case = $connection->quoteInto('?', $product->getProductId());

                $condition = $connection->quoteInto("?", $changeQty);
                $conditions[$case] = $condition;
            }


            $value = $connection->getCaseSql('product_id', $conditions, $field);
            $where = [
                'product_id IN (?)' => array_keys($changeQtys),
                'transferstock_id=?' => $transferstock_id
            ];

            //\Zend_Debug::dump($conditions);
            //\Zend_Debug::dump($where);die();
            /* add query to the processor */
            $this->_queryProcessor->addQuery(['type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
                'values' =>  [$field => $value],
                'condition' => $where,
                'table' => $this->getTable('os_transferstock_product')
            ]);
        }

        /* process queries in Processor */
        $this->_queryProcessor->process();

        return $this;
    }
    
}
