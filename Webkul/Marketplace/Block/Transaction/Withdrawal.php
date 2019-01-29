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

namespace Webkul\Marketplace\Block\Transaction;

use Magento\Framework\View\Element\Template\Context;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistColl;
use Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory;

class Withdrawal extends \Magento\Framework\View\Element\Template
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var SaleslistColl
     */
    protected $saleslistColl;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context                    $context
     * @param HelperData                 $helper
     * @param SaleslistColl              $saleslistColl
     * @param CollectionFactory          $collectionFactory
     * @param array                      $data
     */
    public function __construct(
        Context $context,
        HelperData $helper,
        SaleslistColl $saleslistColl,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->saleslistColl = $saleslistColl;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Transactions'));
    }

    public function getFormatedPrice($price = 0)
    {
        return $this->helper->getFormatedPrice($price);
    }

    /**
     * @return int|float
     */
    public function getRemainTotal()
    {
        $sellerId = $this->helper->getCustomerId();
        $collection = $this->collectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        $total = 0;
        foreach ($collection->getTotalAmountRemain() as $data) {
            $total = $data['amount_remain'];
        }
        return $total;
    }

    /**
     * @return int|float
     */
    public function getTotalPayout()
    {
        $sellerId = $this->helper->getCustomerId();
        $collection = $this->collectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        $total = 0;
        foreach ($collection->getTotalAmountRemain() as $data) {
            $total = $data['amount_received'];
        }
        return $total;
    }

    /**
     * @return int|float
     */
    public function getSellerSalesCollection()
    {
        $sellerId = $this->helper->getCustomerId();
        $collection = $this->saleslistColl->create()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )
        ->addFieldToFilter(
            'cpprostatus',
            1
        );
        return $collection;
    }

    /**
     * @return int|float
     */
    public function getTotalSellerSale()
    {
        $actualSellerAmount = 0;
        $totalTaxAmount = 0;
        $totalShippingAmount = 0;
        $codCharges = 0;
        $couponAmount = 0;
        $refundedShippingAmount = 0;
        $collection = $this->getSellerSalesCollection();
        $sellerAmountArr = $collection->getTotalSellerAmount();
        $totalTaxAmountArr = $collection->getTotalSellerTaxAmount();
        $sellerOrderTotal = $collection->getSellerOrderTotal();
        if ($this->helper->isMpcashondeliveryModuleInstalled()) {
            $codChargesArr = $collection->getTotalSellerCodCharges();
            $codCharges = $codChargesArr[0]['cod_charges'];
        }
        if (!empty($sellerAmountArr[0]['actual_seller_amount'])) {
            $actualSellerAmount = $sellerAmountArr[0]['actual_seller_amount'];
        }
        if (!empty($totalTaxAmountArr[0]['total_tax'])) {
            $totalTaxAmount = $totalTaxAmountArr[0]['total_tax'];
        }
        if (!empty($sellerOrderTotal[0]['shipping_charges'])) {
            $totalShippingAmount = $sellerOrderTotal[0]['shipping_charges'];
        }
        if (!empty($sellerOrderTotal[0]['coupon_amount'])) {
            $couponAmount = $sellerOrderTotal[0]['coupon_amount'];
        }
        if (!empty($sellerOrderTotal[0]['refunded_shipping_charges'])) {
            $refundedShippingAmount = $sellerOrderTotal[0]['refunded_shipping_charges'];
        }
        $t1 = $actualSellerAmount + $totalTaxAmount + $totalShippingAmount + $codCharges;
        $t2 = $refundedShippingAmount + $couponAmount;
        $total = $t1 - $t2;
        return $total;
    }

    /**
     * @return int|float
     */
    public function getTotalSale()
    {
        $totalAmount = 0;
        $totalTaxAmount = 0;
        $totalShippingAmount = 0;
        $codCharges = 0;
        $couponAmount = 0;
        $refundedShippingAmount = 0;
        $collection = $this->getSellerSalesCollection();
        $totalAmountArr = $collection->getTotalSellerAmount();
        $sellerOrderTotal = $collection->getSellerOrderTotal();
        if ($this->helper->isMpcashondeliveryModuleInstalled()) {
            $codChargesArr = $collection->getTotalSellerCodCharges();
            $codCharges = $codChargesArr[0]['cod_charges'];
        }
        if (!empty($totalAmountArr[0]['total_amount'])) {
            $totalAmount = $totalAmountArr[0]['total_amount'];
        }
        if (!empty($totalAmountArr[0]['total_commission'])) {
            $totalCommission = $totalAmountArr[0]['total_commission'];
        }
        if (!empty($totalAmountArr[0]['total_tax'])) {
            $totalTaxAmount = $totalAmountArr[0]['total_tax'];
        }
        if (!empty($sellerOrderTotal[0]['shipping_charges'])) {
            $totalShippingAmount = $sellerOrderTotal[0]['shipping_charges'];
        }
        if (!empty($sellerOrderTotal[0]['coupon_amount'])) {
            $couponAmount = $sellerOrderTotal[0]['coupon_amount'];
        }
        if (!empty($sellerOrderTotal[0]['refunded_shipping_charges'])) {
            $refundedShippingAmount = $sellerOrderTotal[0]['refunded_shipping_charges'];
        }
        $t1 = $totalAmount + $totalTaxAmount + $totalShippingAmount + $codCharges;
        $t2 = $refundedShippingAmount + $couponAmount;
        $total = $t1 - $t2;
        return $total;
    }

    /**
     * @return int|float
     */
    public function getTotalTax()
    {
        $totalTaxAmount = 0;
        $collection = $this->getSellerSalesCollection();
        $totalAdminTaxAmountArr = $collection->getTotalAdminTaxAmount();
        if (!empty($totalAdminTaxAmountArr[0]['total_tax'])) {
            $totalTaxAmount = $totalAdminTaxAmountArr[0]['total_tax'];
        }
        return $totalTaxAmount;
    }

    /**
     * @return int|float
     */
    public function getTotalCommission()
    {
        $totalCommission = 0;
        $collection = $this->getSellerSalesCollection();
        $totalAmountArr = $collection->getTotalSellerAmount();
        if (!empty($totalAmountArr[0]['total_commission'])) {
            $totalCommission = $totalAmountArr[0]['total_commission'];
        }
        return $totalCommission;
    }

    /**
     * @return string
     */
    public function getAllRemainOrderIds()
    {
        $orderIds = '';
        $sellerId = $this->helper->getCustomerId();
        $collection = $this->saleslistColl->create()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )
        ->addFieldToFilter(
            'cpprostatus',
            1
        )
        ->addFieldToFilter(
            'paid_status',
            0
        );
        $orderArr = $collection->getAllRemainOrderRowIds();
        if (!empty($orderArr)) {
            $orderIds = implode(",", $orderArr);
        }
        return $orderIds;
    }
}
