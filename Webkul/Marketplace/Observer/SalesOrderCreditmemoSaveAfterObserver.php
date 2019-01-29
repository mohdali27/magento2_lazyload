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

namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Webkul\Marketplace\Model\Saleslist;
use Magento\Framework\Session\SessionManager;

/**
 * Webkul Marketplace SalesOrderCreditmemoSaveAfterObserver Observer.
 */
class SalesOrderCreditmemoSaveAfterObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * [$_coreSession description].
     *
     * @var SessionManager
     */
    protected $_coreSession;

    /**
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param SessionManager                              $coreSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param CollectionFactory                           $collectionFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        SessionManager $coreSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        CollectionFactory $collectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_coreSession = $coreSession;
        $this->_collectionFactory = $collectionFactory;
        $this->_date = $date;
    }

    public function getRefundedItemSellerFlag(
        $orderId,
        $mageproductId,
        $orderItemId
    ) {
        $flag = 0;
        $sellerOrderslist = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )->getCollection()
        ->addFieldToFilter('order_id', $orderId)
        ->addFieldToFilter('mageproduct_id', $mageproductId)
        ->addFieldToFilter('order_item_id', $orderItemId)
        ->setOrder('order_id', 'DESC');
        if ($sellerOrderslist->getSize() > 0) {
            $flag = 1;
        }

        return $flag;
    }

    /**
     * Sales Order Creditmemo Save After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditmemo = $observer->getCreditmemo();
        $creditmemoId = $creditmemo->getId();
        $refundedShippingCharges = $creditmemo->getBaseShippingAmount();
        $orderId = $creditmemo->getOrderId();
        $order = $creditmemo->getOrder();

        $paymentCode = '';
        $paymentMethod = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
        }

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        // refund calculation check

        $adjustmentPositive = $creditmemo['base_adjustment_positive'];
        $adjustmentNegative = $creditmemo['base_adjustment_negative'];
        if ($adjustmentNegative > $adjustmentPositive) {
            $adjustmentNegative = $adjustmentNegative - $adjustmentPositive;
        } else {
            $adjustmentNegative = 0;
        }

        $refundQtyArr = [];

        $creditmemoItemsIds = [];
        $creditmemoItemsQty = [];
        $creditmemoItemsPrice = [];

        $_items = $order->getItemsCollection();

        foreach ($creditmemo->getAllItems() as $item) {
            $refundQtyArr[$item->getOrderItemId()] = $item->getQty();
            if ($item->getQty()) {
                $availableSellerFlag = $this->getRefundedItemSellerFlag(
                    $orderId,
                    $item->getProductId(),
                    $item->getOrderItemId()
                );
                if ($availableSellerFlag == 1) {
                    $creditmemoItemsIds[$item->getOrderItemId()] = $item->getProductId();
                    $creditmemoItemsQty[$item->getOrderItemId()] = $item->getQty();
                    $creditmemoItemsPrice[$item->getOrderItemId()] = $item->getBasePrice() * $item->getQty();
                }
            }
        }
        arsort($creditmemoItemsPrice);
        $creditmemoCommissionRateArr = [];
        foreach ($creditmemoItemsPrice as $key => $item) {
            $refundedQty = $creditmemoItemsQty[$key];
            $refundedPrice = $creditmemoItemsPrice[$key];
            $productId = $creditmemoItemsIds[$key];
            $sellerProducts = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )->addFieldToFilter(
                'order_item_id',
                $key
            )->addFieldToFilter(
                'mageproduct_id',
                $productId
            );
            foreach ($sellerProducts as $sellerProduct) {
                $updatedQty = $sellerProduct['magequantity'] - $refundedQty;
                if ($adjustmentNegative * 1) {
                    if ($adjustmentNegative >= $refundedPrice) {
                        $adjustmentNegative = $adjustmentNegative - $sellerProduct['total_amount'];
                        $updatedPrice = $sellerProduct['total_amount'];
                        $refundedPrice = 0;
                    } else {
                        $refundedPrice = $refundedPrice - $adjustmentNegative;
                        $updatedPrice = $sellerProduct['total_amount'] - $refundedPrice;
                        $adjustmentNegative = 0;
                    }
                } else {
                    $updatedPrice = $sellerProduct['total_amount'] - $refundedPrice;
                }
                if (!($sellerProduct['total_amount'] * 1)) {
                    $sellerProduct['total_amount'] = 1;
                }
                if ($sellerProduct['total_commission'] * 1) {
                    $commissionPercentage = ($sellerProduct['total_commission'] * 100) / $sellerProduct['total_amount'];
                } else {
                    $commissionPercentage = 0;
                }
                if (empty($creditmemoCommissionRateArr[$key])) {
                    $creditmemoCommissionRateArr[$key] = [];
                }
                $creditmemoCommissionRateArr[$key] = $sellerProduct->getData();
                $updatedCommission = ($updatedPrice * $commissionPercentage) / 100;
                $updatedSellerAmount = $updatedPrice - $updatedCommission;

                if ($updatedQty < 0) {
                    $updatedQty = 0;
                }
                if ($updatedPrice < 0) {
                    $updatedPrice = 0;
                }
                if ($updatedSellerAmount < 0) {
                    $updatedSellerAmount = 0;
                }
                if ($updatedCommission < 0) {
                    $updatedCommission = 0;
                }
                if ($refundedQty) {
                    $taxAmount = ($sellerProduct['total_tax'] / $sellerProduct['magequantity']) * $refundedQty;
                    $remainTaxAmount = $sellerProduct['total_tax'] - $taxAmount;

                    $appliedCouponAmount =
                    ($sellerProduct['applied_coupon_amount'] / $sellerProduct['magequantity']) * $refundedQty;
                    $remainAppliedCouponAmount = $sellerProduct['applied_coupon_amount'] - $appliedCouponAmount;
                } else {
                    $taxAmount = 0;
                    $remainTaxAmount = 0;
                    $appliedCouponAmount = 0;
                    $remainAppliedCouponAmount = 0;
                }
                $taxToSeller = $helper->getConfigTaxManage();
                $marketplaceOrders = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Orders'
                )
                ->getCollection()
                ->addFieldToFilter('order_id', $sellerProduct['order_id'])
                ->addFieldToFilter('seller_id', $sellerProduct['seller_id']);
                foreach ($marketplaceOrders as $tracking) {
                    $taxToSeller = $tracking['tax_to_seller'];
                }
                if (!$taxToSeller) {
                    $taxAmount = 0;
                }
                $refundedPrice = $refundedPrice + $taxAmount - $appliedCouponAmount;
                $partnerRemainSeller = ($sellerProduct->getActualSellerAmount() + $taxAmount) -
                $updatedSellerAmount - $appliedCouponAmount;

                $sellerArr[$sellerProduct['seller_id']]['updated_commission'] = $updatedCommission;
                if ($sellerProduct['cpprostatus'] == Saleslist::PAID_STATUS_COMPLETE && $sellerProduct['paid_status'] == Saleslist::PAID_STATUS_PENDING) {
                    if (!isset($sellerArr[$sellerProduct['seller_id']]['total_sale'])) {
                        $sellerArr[$sellerProduct['seller_id']]['total_sale'] = 0;
                    }
                    if (!isset($sellerArr[$sellerProduct['seller_id']]['totalremain'])) {
                        $sellerArr[$sellerProduct['seller_id']]['totalremain'] = 0;
                    }
                    if (!isset($sellerArr[$sellerProduct['seller_id']]['totalcommission'])) {
                        $sellerArr[$sellerProduct['seller_id']]['totalcommission'] = 0;
                    }
                    $sellerArr[$sellerProduct['seller_id']]['total_sale'] =
                    $sellerArr[$sellerProduct['seller_id']]['total_sale'] + $refundedPrice;
                    $sellerArr[$sellerProduct['seller_id']]['totalremain'] =
                    $sellerArr[$sellerProduct['seller_id']]['totalremain'] + $partnerRemainSeller;
                    $sellerArr[$sellerProduct['seller_id']]['totalcommission'] =
                    $sellerArr[$sellerProduct['seller_id']]['totalcommission'] +
                    ($refundedPrice - $partnerRemainSeller);
                } elseif ($sellerProduct['cpprostatus'] == Saleslist::PAID_STATUS_COMPLETE && $sellerProduct['paid_status'] == Saleslist::PAID_STATUS_COMPLETE) {
                    if (!isset($sellerArr[$sellerProduct['seller_id']]['total_sale'])) {
                        $sellerArr[$sellerProduct['seller_id']]['total_sale'] = 0;
                    }
                    if (!isset($sellerArr[$sellerProduct['seller_id']]['totalpaid'])) {
                        $sellerArr[$sellerProduct['seller_id']]['totalpaid'] = 0;
                    }
                    if (!isset($sellerArr[$sellerProduct['seller_id']]['totalcommission'])) {
                        $sellerArr[$sellerProduct['seller_id']]['totalcommission'] = 0;
                    }
                    $sellerArr[$sellerProduct['seller_id']]['total_sale'] =
                    $sellerArr[$sellerProduct['seller_id']]['total_sale'] + $refundedPrice;
                    $sellerArr[$sellerProduct['seller_id']]['totalpaid'] =
                    $sellerArr[$sellerProduct['seller_id']]['totalpaid'] + $partnerRemainSeller;
                    $sellerArr[$sellerProduct['seller_id']]['totalcommission'] =
                    $sellerArr[$sellerProduct['seller_id']]['totalcommission'] +
                    ($refundedPrice - $partnerRemainSeller);
                }
                if ($sellerProduct['is_shipping'] == 1) {
                    $sellerArr[$sellerProduct['seller_id']]['is_shipping'] = 1;
                } else {
                    $sellerArr[$sellerProduct['seller_id']]['is_shipping'] = 0;
                }
                $sellerProduct->setMagequantity($updatedQty);
                $sellerProduct->setTotalAmount($updatedPrice);
                $sellerProduct->setTotalCommission($updatedCommission);
                $sellerProduct->setActualSellerAmount($updatedSellerAmount);
                $sellerProduct->setTotalTax($remainTaxAmount);
                $sellerProduct->setAppliedCouponAmount($remainAppliedCouponAmount);
                if ($updatedSellerAmount == 0) {
                    $sellerProduct->setPaidStatus(Saleslist::PAID_STATUS_REFUNDED);
                    if ($paymentCode == 'mpcashondelivery') {
                        $sellerProduct->setCollectCodStatus(Saleslist::PAID_STATUS_REFUNDED);
                    }
                }
                $sellerProduct->save();
            }
        }
        $this->_coreSession->setMpCreditmemoCommissionRate(
            $creditmemoCommissionRateArr
        );

        if (!isset($sellerArr)) {
            if ($adjustmentNegative * 1) {
                if ($adjustmentNegative >= $refundedShippingCharges) {
                    $adjustmentNegative = $adjustmentNegative - $refundedShippingCharges;
                    $refundedShippingCharges = 0;
                } else {
                    $refundedShippingCharges = $refundedShippingCharges - $adjustmentNegative;
                    $adjustmentNegative = 0;
                }
            }
            $sellerArr = [];
            $trackingcoll = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Orders'
            )
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('invoice_id', ['neq' => 0]);
            foreach ($trackingcoll as $tracking) {
                $sellerId = $tracking->getSellerId();
                $shippingamount = $tracking->getShippingCharges();
                $refundedShippingAmount = $tracking->getRefundedShippingCharges();
                $shippingCharges = $shippingamount - $refundedShippingAmount;
                if ($shippingCharges * 1) {
                    if ($tracking->getShipmentId()) {
                        $sellerProducts = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Saleslist'
                        )->getCollection()
                        ->addFieldToFilter('is_shipping', 1)
                        ->addFieldToFilter('seller_id', $sellerId)
                        ->addFieldToFilter('order_id', $orderId);
                        foreach ($sellerProducts as $sellerProduct) {
                            if ($sellerProduct['cpprostatus'] == Saleslist::PAID_STATUS_COMPLETE && $sellerProduct['paidstatus'] == Saleslist::PAID_STATUS_PENDING) {
                                if (!isset($sellerArr[$sellerId]['total_sale'])) {
                                    $sellerArr[$sellerId]['total_sale'] = 0;
                                }
                                if (!isset($sellerArr[$sellerId]['totalremain'])) {
                                    $sellerArr[$sellerId]['totalremain'] = 0;
                                }
                                $sellerArr[$sellerId]['total_sale'] = 0;
                                $sellerArr[$sellerId]['totalremain'] = 0;
                            } else {
                                if ($sellerProduct['cpprostatus'] == Saleslist::PAID_STATUS_COMPLETE && $sellerProduct['paidstatus'] == Saleslist::PAID_STATUS_COMPLETE) {
                                    if (!isset($sellerArr[$sellerId]['total_sale'])) {
                                        $sellerArr[$sellerId]['total_sale'] = 0;
                                    }
                                    if (!isset($sellerArr[$sellerId]['totalpaid'])) {
                                        $sellerArr[$sellerId]['totalpaid'] = 0;
                                    }
                                    if (!isset($sellerArr[$sellerId]['totalcommission'])) {
                                        $sellerArr[$sellerId]['totalcommission'] = 0;
                                    }
                                    $sellerArr[$sellerId]['total_sale'] = 0;
                                    $sellerArr[$sellerId]['totalpaid'] = 0;
                                    $sellerArr[$sellerId]['totalcommission'] = 0;
                                } elseif ($sellerProduct['cpprostatus'] == Saleslist::PAID_STATUS_COMPLETE && $sellerProduct['paidstatus'] != Saleslist::PAID_STATUS_COMPLETE && $sellerProduct['is_paid'] == Saleslist::PAID_STATUS_COMPLETE) {
                                    if (!isset($sellerArr[$sellerId]['total_sale'])) {
                                        $sellerArr[$sellerId]['total_sale'] = 0;
                                    }
                                    if (!isset($sellerArr[$sellerId]['totalpaid'])) {
                                        $sellerArr[$sellerId]['totalpaid'] = 0;
                                    }
                                    if (!isset($sellerArr[$sellerId]['totalcommission'])) {
                                        $sellerArr[$sellerId]['totalcommission'] = 0;
                                    }
                                    $sellerArr[$sellerId]['total_sale'] = 0;
                                    $sellerArr[$sellerId]['totalpaid'] = 0;
                                    $sellerArr[$sellerId]['totalcommission'] = 0;
                                } else {
                                    if (!isset($sellerArr[$sellerId]['total_sale'])) {
                                        $sellerArr[$sellerId]['total_sale'] = 0;
                                    }
                                    if (!isset($sellerArr[$sellerId]['totalremain'])) {
                                        $sellerArr[$sellerId]['totalremain'] = 0;
                                    }
                                    if (!isset($sellerArr[$sellerId]['totalcommission'])) {
                                        $sellerArr[$sellerId]['totalcommission'] = 0;
                                    }
                                    $sellerArr[$sellerId]['total_sale'] = 0;
                                    $sellerArr[$sellerId]['totalremain'] = 0;
                                    $sellerArr[$sellerId]['totalcommission'] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($sellerArr as $sellerId => $value) {
            $shippingCharges = 0;
            $codCharges = 0;
            /*update records*/
            $creditmemoIds = [];
            $trackingcoll = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Orders'
            )
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            foreach ($trackingcoll as $tracking) {
                if ($tracking->getCreditmemoId()) {
                    $creditmemoIds = explode(',', $tracking->getCreditmemoId());
                }
                if ($creditmemoId && !in_array($creditmemoId, $creditmemoIds)) {
                    array_push($creditmemoIds, $creditmemoId);
                    $tracking->setCreditmemoId(implode(',', $creditmemoIds));
                }
                if ($paymentCode == 'mpcashondelivery') {
                    $codCharges = $tracking->getCodCharges();
                }
                $shippingCharges = $tracking->getShippingCharges();
                $sellerRefundedShipping = 0;
                if ($refundedShippingCharges >= $shippingCharges) {
                    $sellerRefundedShipping = $shippingCharges;
                } else {
                    $sellerRefundedShipping = $refundedShippingCharges;
                }
                $isAllRefunded = 1;
                // check for if all products are refunded
                $saleslistColl = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleslist'
                )->getCollection()
                ->addFieldToFilter('seller_id', $sellerId)
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('parent_item_id', ['eq'=>null]);
                foreach ($saleslistColl as $saleslist) {
                    if ($saleslist->getPaidStatus() != Saleslist::PAID_STATUS_REFUNDED) {
                        $isAllRefunded = 0;
                        break;
                    }
                }
                if ($isAllRefunded) {
                    $tracking->setOrderStatus('closed');
                } else {
                    $tracking->setOrderStatus('complete');
                }
                $tracking->setRefundedShippingCharges(
                    $sellerRefundedShipping + $tracking->getRefundedShippingCharges()
                )->save();
                $refundedShippingCharges = $refundedShippingCharges - $sellerRefundedShipping;
            }
            $collectionverifyread = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleperpartner'
            )->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            foreach ($collectionverifyread as $verifyrow) {
                if (isset($sellerArr[$sellerId]['total_sale'])) {
                    $verifyrow->setTotalSale(
                        $verifyrow->getTotalSale() - (
                            $sellerArr[$sellerId]['total_sale'] + $codCharges + $sellerRefundedShipping
                        )
                    );
                }
                if (isset($sellerArr[$sellerId]['totalremain'])) {
                    $sellerRemainRefundedShipping = 0;
                    // in case of seller is not paid and shipping is also not paid
                    if (isset($sellerArr[$sellerId]['is_shipping']) && ($sellerArr[$sellerId]['is_shipping'] == 1)) {
                        $sellerRemainRefundedShipping = $sellerRefundedShipping;
                        $sellerRefundedShipping = 0;
                    }
                    $verifyrow->setAmountRemain(
                        $verifyrow->getAmountRemain() - (
                            $sellerArr[$sellerId]['totalremain'] + $codCharges + $sellerRemainRefundedShipping
                        )
                    );
                } else {
                    // in case of seller is paid but shipping is not paid
                    if (isset($sellerArr[$sellerId]['is_shipping']) && ($sellerArr[$sellerId]['is_shipping'] == 0)) {
                        $verifyrow->getAmountRemain($verifyrow->getAmountRemain() - $sellerRefundedShipping);
                        $sellerRefundedShipping = 0;
                    }
                }
                // in case of seller and shipping both are paid
                if (isset($sellerArr[$sellerId]['totalpaid'])) {
                    $verifyrow->setAmountReceived(
                        $verifyrow->getAmountReceived() - (
                            $sellerArr[$sellerId]['totalpaid'] + $codCharges + $sellerRefundedShipping
                        )
                    );
                }
                if (isset($sellerArr[$sellerId]['totalcommission'])) {
                    $verifyrow->setTotalCommission(
                        $verifyrow->getTotalCommission() - $sellerArr[$sellerId]['totalcommission']
                    );
                }
                $verifyrow->setLastAmountPaid(0);
                $verifyrow->save();
            }
        }
    }
}
