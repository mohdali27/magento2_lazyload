<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Model\ResourceModel\Saleslist;

use \Webkul\Marketplace\Model\ResourceModel\AbstractCollection;

/**
 * Webkul Marketplace ResourceModel Saleslist collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Webkul\Marketplace\Model\Saleslist',
            'Webkul\Marketplace\Model\ResourceModel\Saleslist'
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['created_at'] = 'main_table.created_at';
    }

    
    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Retrieve clear select
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getClearSelect()
    {
        return $this->_buildClearSelect();
    }

    /**
     * Build clear select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function _buildClearSelect($select = null)
    {
        if (null === $select) {
            $select = clone $this->getSelect();
        }
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);

        return $select;
    }

    /**
     * Retrieve all mageproduct_id for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getAllOrderIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('order_id');
        $idsSelect->distinct('order_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Retrieve all row ids for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getAllRemainOrderRowIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('entity_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Retrieve all mageproduct_id for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getAllOrderProducts()
    {
        $this->getSelect()
        ->columns('SUM(magequantity) AS qty')
        ->group('mageproduct_id')
        ->order('qty desc')
        ->limit(5);
        return $this;
    }
    
    /**
     * Retrieve all sold quantities of product
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getAllSoldQty($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns(['SUM(magequantity) AS qty', 'mageproduct_id']);
        $idsSelect->group('mageproduct_id');
        $idsSelect->order('qty desc');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
    
    /**
     * Retrieve seller Amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getTotalSellerAmount($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns(
            [
                'SUM(total_amount) AS total_amount',
                'SUM(actual_seller_amount) AS actual_seller_amount',
                'SUM(total_commission) AS total_commission',
                'SUM(total_tax) AS total_tax'
            ]
        );
        $idsSelect->group('seller_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
    
    /**
     * Retrieve seller tax Amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getTotalSellerTaxAmount($limit = null, $offset = null)
    {
        $marketplaceOrders = $this->getTable('marketplace_orders');
        $idsSelect = $this->_getClearSelect();
        $idsSelect->joinLeft(
            $marketplaceOrders.' as mo',
            'main_table.order_id = mo.order_id and main_table.seller_id = mo.seller_id',
            ['tax_to_seller' => 'tax_to_seller']
        )->Where('mo.tax_to_seller = 1');
        $idsSelect->columns(['SUM(main_table.total_tax) AS total_tax']);
        $idsSelect->group('main_table.seller_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
    
    /**
     * Retrieve admin tax Amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getTotalAdminTaxAmount($limit = null, $offset = null)
    {
        $marketplaceOrders = $this->getTable('marketplace_orders');
        $idsSelect = $this->_getClearSelect();
        $idsSelect->joinLeft(
            $marketplaceOrders.' as mo',
            'main_table.order_id = mo.order_id and main_table.seller_id = mo.seller_id',
            ['tax_to_seller' => 'tax_to_seller']
        )->Where('mo.tax_to_seller = 0');
        $idsSelect->columns(['SUM(main_table.total_tax) AS total_tax']);
        $idsSelect->group('main_table.seller_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
    
    /**
     * Retrieve seller Amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getTotalSellerCodCharges($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns(['SUM(cod_charges) AS cod_charges']);
        $idsSelect->group('seller_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
    
    /**
     * Retrieve seller Amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getTotalSellerInvoiceCodCharges($invoiceId, $limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $salesInvoiceItem = $this->getTable('sales_invoice_item');
        $idsSelect->join(
            $salesInvoiceItem.' as invoice_item',
            'invoice_item.order_item_id = main_table.order_item_id'
        )->where('invoice_item.parent_id = '.$invoiceId);
        $idsSelect->columns(['SUM(cod_charges) AS cod_charges']);
        $idsSelect->group('seller_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
    
    /**
     * Retrieve seller creditmemo cod amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getTotalSellerCreditmemoCodCharges($creditmemoId, $limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $salesCreditmemoItem = $this->getTable('sales_creditmemo_item');
        $idsSelect->join(
            $salesCreditmemoItem.' as creditmemo_item',
            'creditmemo_item.order_item_id = main_table.order_item_id'
        )->where('creditmemo_item.parent_id = '.$creditmemoId);
        $idsSelect->columns(['SUM(cod_charges) AS cod_charges']);
        $idsSelect->group('seller_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
    
    /**
     * Retrieve seller refunded Amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getSellerOrderTotal($limit = null, $offset = null)
    {
        $marketplaceOrders = $this->getTable('marketplace_orders');
        $idsSelect = $this->_getClearSelect();
        $idsSelect->joinLeft(
            $marketplaceOrders.' as mo',
            'main_table.order_id = mo.order_id and main_table.seller_id = mo.seller_id',
            [
                'shipping_charges' => 'shipping_charges',
                'coupon_amount' => 'coupon_amount',
                'refunded_shipping_charges' => 'refunded_shipping_charges'
            ]
        );
        $idsSelect->columns(
            [
                'SUM(mo.shipping_charges) AS shipping_charges',
                'SUM(main_table.applied_coupon_amount) AS coupon_amount',
                'SUM(mo.refunded_shipping_charges) AS refunded_shipping_charges'
            ]
        );
        $idsSelect->group('mo.seller_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
    
    /**
     * Retrieve seller Amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getPricebyorderData($limit = null, $offset = null)
    {
        $marketplaceOrders = $this->getTable('marketplace_orders');
        $idsSelect = $this->_getClearSelect();
        $idsSelect->joinLeft(
            $marketplaceOrders.' as mo',
            'main_table.order_id = mo.order_id and main_table.seller_id = mo.seller_id',
            [
                'tax_to_seller' => 'tax_to_seller',
                'shipping_charges' => 'shipping_charges',
                'refunded_shipping_charges' => 'refunded_shipping_charges'
            ]
        );
        $idsSelect->columns(
            [
                'main_table.order_id AS order_id',
                'SUM(main_table.total_amount) AS total_amount',
                'SUM(main_table.actual_seller_amount) AS total',
                'SUM(main_table.actual_seller_amount) AS actual_seller_amount',
                'SUM(main_table.total_commission) AS total_commission',
                'SUM(main_table.applied_coupon_amount) AS applied_coupon_amount',
                'SUM(main_table.total_tax) AS total_tax'
            ]
        );
        $idsSelect->group('main_table.order_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }

    /**
     * @return this
     */
    public function getTotalCustomersCount()
    {
        $this->getSelect()
        ->group('magebuyer_id');
        return $this;
    }

    /**
     * @return this
     */
    public function getTotalOrders()
    {
        $this->getSelect()
        ->group('order_id');
        return $this;
    }

    /**
     * Set seller order data for given condition
     *
     * @param array $condition
     * @param array $attributeData
     * @return void
     */
    public function setSalesListData($id, $attributeData)
    {
        $where = ['entity_id=?' => (int)$id];
        return $this->getConnection()->update(
            $this->getTable('marketplace_saleslist'),
            $attributeData,
            $where
        );
    }

    /**
     * Set seller order data for given condition
     *
     * @param array $condition
     * @param array $attributeData
     * @return void
     */
    public function setWithdrawalRequestData($condition, $attributeData)
    {
        return $this->getConnection()->update(
            $this->getTable('marketplace_saleslist'),
            $attributeData,
            $where = $condition
        );
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    public function getSellerOrderCollection()
    {
        $salesOrder = $this->getTable('sales_order');
        $salesOrderItem = $this->getTable('sales_order_item');

        $this->getSelect()->join(
            $salesOrder.' as so',
            'main_table.order_id = so.entity_id',
            ['status' => 'status']
        )->where("so.order_approval_status=1");

        $this->getSelect()->join(
            $salesOrderItem.' as soi',
            'main_table.order_item_id = soi.item_id AND main_table.order_id = soi.order_id',
            [
                'item_id' => 'item_id',
                'qty_canceled' => 'qty_canceled',
                'qty_invoiced' => 'qty_invoiced',
                'qty_ordered' => 'qty_ordered',
                'qty_refunded' => 'qty_refunded',
                'qty_shipped' => 'qty_shipped',
                'product_options' => 'product_options',
                'mage_parent_item_id' => 'parent_item_id',
                'product_type' => 'product_type'
            ]
        );
        return $this;
    }

    /**
     * Retrieve Ordered Product Id for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getOrderedProductId($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('mageproduct_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Retrieve all magebuyer_id for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getAllBuyerIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('magebuyer_id');
        $idsSelect->distinct('magebuyer_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    public function getSellerOrderTotalsQuery($idsSelect)
    {
        $salesOrderItem = $this->getTable('sales_order_item');

        $idsSelect->join(
            $salesOrderItem.' as soi',
            'main_table.order_item_id = soi.item_id AND main_table.order_id = soi.order_id',
            [
                'item_id' => 'item_id',
                'SUM(main_table.magepro_price*soi.qty_ordered) AS magepro_price'
            ]
        );

        $marketplaceOrders = $this->getTable('marketplace_orders');
        $idsSelect->joinLeft(
            $marketplaceOrders.' as mo',
            'main_table.order_id = mo.order_id and main_table.seller_id = mo.seller_id',
            [
                'tax_to_seller' => 'tax_to_seller',
                'coupon_amount' => 'coupon_amount',
                'shipping_charges' => 'shipping_charges',
                'refunded_shipping_charges' => 'refunded_shipping_charges'
            ]
        );
        $idsSelect->columns(
            [
                'main_table.currency_rate AS currency_rate',
                'main_table.order_id AS order_id',
                'SUM(main_table.total_amount) AS total_amount',
                'SUM(main_table.actual_seller_amount) AS total',
                'SUM(main_table.actual_seller_amount) AS actual_seller_amount',
                'SUM(main_table.total_commission) AS total_commission',
                'SUM(main_table.applied_coupon_amount) AS applied_coupon_amount',
                'SUM(main_table.total_tax) AS total_tax'
            ]
        );
        return $idsSelect;
    }
    
    /**
     * Retrieve seller Amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getSellerOrderTotals($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();

        $idsSelect = $this->getSellerOrderTotalsQuery($idsSelect);

        $idsSelect->group('main_table.order_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
    
    /**
     * Retrieve Seller Invoice Amount total
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getSellerInvoiceTotals($invoiceId, $limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $salesInvoiceItem = $this->getTable('sales_invoice_item');
        $idsSelect->join(
            $salesInvoiceItem.' as invoice_item',
            'invoice_item.order_item_id = main_table.order_item_id'
        )->where('invoice_item.parent_id = '.$invoiceId);

        $idsSelect = $this->getSellerOrderTotalsQuery($idsSelect);

        $idsSelect->group('main_table.order_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }
}
