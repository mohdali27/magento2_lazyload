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

namespace Webkul\Marketplace\Helper;

/**
 * Marketplace helper Orders.
 */
class Orders extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Unserialize\Unserialize
     */
    protected $unserializer;

    /**
     * @var \Magento\Sales\Model\Order\ItemRepository
     */
    protected $orderItemRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Json\Helper\Data         $jsonHelper
     * @param \Magento\Framework\Unserialize\Unserialize  $unserializer
     * @param \Magento\Sales\Model\Order\ItemRepository   $orderItemRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Unserialize\Unserialize $unserializer,
        \Magento\Sales\Model\Order\ItemRepository $orderItemRepository
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_date = $date;
        $this->jsonHelper = $jsonHelper;
        $this->unserializer = $unserializer;
        $this->orderItemRepository = $orderItemRepository;
        parent::__construct($context);
    }

    /**
     * Return the Customer seller status.
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function getOrderStatusData()
    {
        $model = $this->_objectManager->create(
            'Magento\Sales\Model\Order\Status'
        )->getResourceCollection()->getData();

        return $model;
    }

    /**
     * Return the seller Order data.
     *
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function getOrderinfo($orderId = '')
    {
        $data = [];
        $model = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $this->_customerSession->getCustomerId()
        )
        ->addFieldToFilter(
            'order_id',
            $orderId
        );

        $salesOrder = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Orders\Collection'
        )->getTable('sales_order');

        $model->getSelect()->join(
            $salesOrder.' as so',
            'main_table.order_id = so.entity_id',
            ["order_approval_status" => "order_approval_status"]
        )->where("so.order_approval_status=1");
        foreach ($model as $tracking) {
            $data = $tracking;
        }

        return $data;
    }

    /**
     * @param string $sellerId, $order
     *                          Cancel order
     *
     * @return bool
     */
    public function cancelorder($order, $sellerId)
    {
        $flag = 0;
        if ($order->canCancel()) {
            $order->getPayment()->cancel();
            $flag = $this->mpregisterCancellation($order, $sellerId);
        }

        return $flag;
    }

    /**
     * @param string $order, $sellerid, $comment
     *
     * @return bool
     *
     * @throws Mage_Core_Exception
     */
    public function mpregisterCancellation($order, $sellerId, $comment = '')
    {
        $flag = 0;
        if ($order->canCancel()) {
            $cancelState = 'canceled';
            $items = [];
            $shippingAmount = 0;
            $orderId = $order->getId();
            $trackingsdata = $this->_objectManager->create(
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
            foreach ($trackingsdata as $tracking) {
                $shippingAmount = $tracking->getShippingCharges();
                $items = explode(',', $tracking->getProductIds());

                $itemsarray = $this->_getItemQtys($order, $items);
                foreach ($order->getAllItems() as $item) {
                    if (in_array($item->getProductId(), $items)) {
                        $flag = 1;
                        $item->cancel();
                    }
                }
                foreach ($order->getAllItems() as $item) {
                    if ($cancelState != 'processing' && $item->getQtyToRefund()) {
                        if ($item->getQtyToShip() > $item->getQtyToCancel()) {
                            $cancelState = 'processing';
                        } else {
                            $cancelState = 'complete';
                        }
                    } elseif ($item->getQtyToInvoice()) {
                        $cancelState = 'processing';
                    }
                }
                $order->setState($cancelState, true, $comment)
                    ->setStatus($cancelState)
                    ->save();
            }
        }

        return $flag;
    }

    /**
     * Get requested items qtys.
     *
     * @return []
     */
    protected function _getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getProductId(), $items)) {
                $data[$item->getItemId()] = intval($item->getQtyOrdered());
                $subtotal += $item->getRowTotal();
                $baseSubtotal += $item->getBaseRowTotal();
            } else {
                $data[$item->getItemId()] = 0;
            }
        }

        return [
            'data' => $data,
            'subtotal' => $subtotal,
            'basesubtotal' => $baseSubtotal,
        ];
    }

    public function getCommssionCalculation($order)
    {
        $percent = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        )->getConfigCommissionRate();
        $lastOrderId = $order->getId();
        /*
        * Calculate cod and shipping charges if applied
        */
        $codCharges = 0;
        $shippingCharges = 0;
        $codChargesArr = [];
        $shippingChargesArr = [];
        $sellerOrder = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )->getCollection()
            ->addFieldToFilter('order_id', $lastOrderId);
        foreach ($sellerOrder as $info) {
            $infoCodCharges = $info->getCodCharges();
            if (!empty($infoCodCharges)) {
                $codCharges = $info->getCodCharges();
            }
            $shippingCharges = $info->getShippingCharges();
            $codChargesArr[$info->getSellerId()] = $codCharges;
            $shippingChargesArr[$info->getSellerId()] = $shippingCharges;
        }

        $ordercollection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )->getCollection()
            ->addFieldToFilter(
                'order_id',
                $lastOrderId
            )
            ->addFieldToFilter(
                'cpprostatus',
                0
            );
        $getConfigTaxManageStatus = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        )->getConfigTaxManage();
        foreach ($ordercollection as $item) {
            $sellerId = $item->getSellerId();
            $taxAmount = $item['total_tax'];
            if (!$getConfigTaxManageStatus) {
                $taxAmount = 0;
            }
            if (empty($codChargesArr[$sellerId])) {
                $codChargesArr[$sellerId] = 0;
            }
            if (empty($shippingChargesArr[$sellerId])) {
                $shippingChargesArr[$sellerId] = 0;
            }
            $actualSellerAmount = $item->getActualSellerAmount() +
            $taxAmount +
            $codChargesArr[$sellerId] +
            $shippingChargesArr[$sellerId];
            $totalamount = $item->getTotalAmount() +
            $taxAmount +
            $codChargesArr[$sellerId] +
            $shippingChargesArr[$sellerId];

            $codChargesArr[$sellerId] = 0;
            $shippingChargesArr[$sellerId] = 0;

            $collectionverifyread = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleperpartner'
            )
                ->getCollection();
            $collectionverifyread
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            if ($collectionverifyread->getSize() >= 1) {
                foreach ($collectionverifyread as $verifyrow) {
                    $totalsale = $verifyrow->getTotalSale() + $totalamount;
                    $totalremain = $verifyrow->getAmountRemain() + $actualSellerAmount;
                    $verifyrow->setTotalSale($totalsale);
                    $verifyrow->setAmountRemain($totalremain);
                    $totalcommission = $verifyrow->getTotalCommission() + (
                        $totalamount - $actualSellerAmount
                    );
                    $verifyrow->setTotalCommission($totalcommission);
                    $verifyrow->setUpdatedAt($this->_date->gmtDate());
                    $verifyrow->save();
                }
            } else {
                $percent = $this->_objectManager->create(
                    'Webkul\Marketplace\Helper\Data'
                )->getConfigCommissionRate();
                $collectionf = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleperpartner'
                );
                $collectionf->setSellerId($sellerId);
                $collectionf->setTotalSale($totalamount);
                $collectionf->setAmountRemain($actualSellerAmount);
                $collectionf->setCommissionRate($percent);
                $totalcommission = $totalamount - $actualSellerAmount;
                $collectionf->setTotalCommission($totalcommission);
                $collectionf->save();
            }
            if ($sellerId) {
                $ordercount = 0;
                $feedbackcount = 0;
                $feedcountid = 0;
                $collectionfeed = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Feedbackcount'
                )
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                )->addFieldToFilter(
                    'buyer_id',
                    $order->getCustomerId()
                );
                foreach ($collectionfeed as $value) {
                    $feedcountid = $value->getEntityId();
                    $ordercount = $value->getOrderCount();
                    $feedbackcount = $value->getFeedbackCount();
                }
                $collectionfeed = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Feedbackcount'
                )->load($feedcountid);
                $collectionfeed->setBuyerId($order->getCustomerId());
                $collectionfeed->setSellerId($sellerId);
                $collectionfeed->setOrderCount($ordercount + 1);
                $collectionfeed->setFeedbackCount($feedbackcount);
                $collectionfeed->save();
            }
            $item->setCpprostatus(1)->save();
        }
    }

    public function getTotalSellerShipping($orderId)
    {
        $sellerOrder = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )->getCollection();
        $sellerOrder->getSelect()
            ->where('order_id ='.$orderId)
            ->columns('SUM(shipping_charges) AS shipping')
            ->group('order_id');
        foreach ($sellerOrder as $coll) {
            if ($coll->getOrderId() == $orderId) {
                return $coll->getShipping();
            }
        }

        return 0;
    }

    public function paysellerpayment($order, $sellerid, $trid)
    {
        $lastOrderId = $order->getId();
        $actparterprocost = 0;
        $totalamount = 0;
        /*
        * Calculate cod and shipping charges if applied
        */
        $codCharges = 0;
        $shippingCharges = 0;
        $sellerOrder = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $sellerid
            )
            ->addFieldToFilter(
                'order_id',
                $lastOrderId
            );
        foreach ($sellerOrder as $info) {
            $codCharges = $info->getCodCharges();
            $shippingCharges = $info->getShippingCharges();
        }
        $helper = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Data'
        );
        $orderinfo = '';
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $sellerid
            )
            ->addFieldToFilter(
                'order_id',
                $lastOrderId
            )
            ->addFieldToFilter(
                'paid_status',
                0
            )
            ->addFieldToFilter(
                'cpprostatus',
                1
            );
        foreach ($collection as $row) {
            $order = $this->_objectManager->create(
                'Magento\Sales\Model\Order'
            )->load($row['order_id']);
            $taxAmount = $row['total_tax'];
            $vendorTaxAmount = 0;
            if ($helper->getConfigTaxManage()) {
                $vendorTaxAmount = $taxAmount;
            }
            $actparterprocost = $actparterprocost +
            $row->getActualSellerAmount() +
            $vendorTaxAmount +
            $codCharges +
            $shippingCharges;
            $totalamount = $totalamount +
            $row->getTotalAmount() +
            $taxAmount +
            $codCharges +
            $shippingCharges;
            $codCharges = 0;
            $shippingCharges = 0;
            $sellerId = $row->getSellerId();
            $orderinfo = $orderinfo."<tr>
                <td class='item-info'>".$row['magerealorder_id']."</td>
                <td class='item-info'>".$row['magepro_name']."</td>
                <td class='item-qty'>".$row['magequantity']."</td>
                <td class='item-price'>".$order->formatPrice($row['magepro_price'])."</td>
                <td class='item-price'>".$order->formatPrice($row['total_commission'])."</td>
                <td class='item-price'>".$order->formatPrice($row['actual_seller_amount']).'</td>
            </tr>';
        }
        if ($actparterprocost) {
            $collectionverifyread = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleperpartner'
            )
                ->getCollection();
            $collectionverifyread
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            if ($collectionverifyread->getSize() >= 1) {
                foreach ($collectionverifyread as $verifyrow) {
                    if ($verifyrow->getAmountRemain() >= $actparterprocost) {
                        $totalremain = $verifyrow->getAmountRemain() - $actparterprocost;
                    } else {
                        $totalremain = 0;
                    }
                    $verifyrow->setAmountRemain($totalremain);
                    $verifyrow->save();
                    $amountpaid = $verifyrow->getAmountReceived();
                    $totalrecived = $actparterprocost + $amountpaid;
                    $verifyrow->setLastAmountPaid($actparterprocost);
                    $verifyrow->setAmountReceived($totalrecived);
                    $verifyrow->setAmountReceived($totalrecived);
                    $verifyrow->setAmountRemain($totalremain);
                    $verifyrow->setUpdatedAt($this->_date->gmtDate());
                    $verifyrow->save();
                }
            } else {
                $percent = $helper->getConfigCommissionRate();
                $collectionf = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleperpartner'
                );
                $collectionf->setSellerId($sellerId);
                $collectionf->setTotalSale($totalamount);
                $collectionf->setLastAmountPaid($actparterprocost);
                $collectionf->setAmountReceived($actparterprocost);
                $collectionf->setAmountRemain(0);
                $collectionf->setCommissionRate($percent);
                $collectionf->setTotalCommission($totalamount - $actparterprocost);
                $collectionf->setCreatedAt($this->_date->gmtDate());
                $collectionf->setUpdatedAt($this->_date->gmtDate());
                $collectionf->save();
            }

            $uniqueId = $this->checktransid();
            $transid = '';
            $transactionNumber = '';
            if ($uniqueId != '') {
                $sellerTrans = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Sellertransaction'
                )
                    ->getCollection()
                    ->addFieldToFilter(
                        'transaction_id',
                        $uniqueId
                    );
                if ($sellerTrans->getSize()) {
                    foreach ($sellerTrans as $value) {
                        $id = $value->getId();
                        if ($id) {
                            $this->_objectManager->create(
                                'Webkul\Marketplace\Model\Sellertransaction'
                            )
                                ->load($id)
                                ->delete();
                        }
                    }
                }
                if ($order->getPayment()) {
                    $paymentCode = $order->getPayment()->getMethod();
                    $paymentType = $this->scopeConfig->getValue(
                        'payment/'.$paymentCode.'/title',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                } else {
                    $paymentType = 'Manual';
                }
                $sellerTrans = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Sellertransaction'
                );
                $sellerTrans->setTransactionId($uniqueId);
                $sellerTrans->setTransactionAmount($actparterprocost);
                $sellerTrans->setOnlinetrId($trid);
                $sellerTrans->setType('Online');
                $sellerTrans->setMethod($paymentType);
                $sellerTrans->setSellerId($sellerId);
                $sellerTrans->setCustomNote('None');
                $sellerTrans->setCreatedAt($this->_date->gmtDate());
                $sellerTrans->setUpdatedAt($this->_date->gmtDate());
                $sellerTrans->setSellerPendingNotification(1);
                $sellerTrans = $sellerTrans->save();
                $transid = $sellerTrans->getId();
                $transactionNumber = $sellerTrans->getTransactionId();
                $this->_objectManager->create(
                    'Webkul\Marketplace\Helper\Notification'
                )->saveNotification(
                    \Webkul\Marketplace\Model\Notification::TYPE_TRANSACTION,
                    $transid,
                    $transid
                );
            }

            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerid
                )
                ->addFieldToFilter(
                    'order_id',
                    $lastOrderId
                )
                ->addFieldToFilter(
                    'cpprostatus',
                    1
                )
                ->addFieldToFilter(
                    'paid_status',
                    0
                );
            foreach ($collection as $row) {
                $row->setPaidStatus(1);
                $row->setTransId($transid)->save();
                $data['trans_id'] = $transactionNumber;
                $data['mp_trans_row_id'] = $transid;
                $data['mp_saleslist_row_id'] = $row->getId();
                $data['id'] = $row->getOrderId();
                $data['seller_id'] = $row->getSellerId();
                $this->_eventManager->dispatch(
                    'mp_pay_seller',
                    [$data]
                );
            }

            $seller = $this->_objectManager->create(
                'Magento\Customer\Model\Customer'
            )->load($sellerId);

            $emailTempVariables = [];

            $adminStoremail = $helper->getAdminEmailId();
            $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
            $adminUsername = 'Admin';

            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];

            $emailTempVariables['myvar1'] = $seller->getName();
            $emailTempVariables['myvar2'] = $transactionNumber;
            $emailTempVariables['myvar3'] = $this->_date->gmtDate();
            $emailTempVariables['myvar4'] = $actparterprocost;
            $emailTempVariables['myvar5'] = $orderinfo;
            $emailTempVariables['myvar6'] = __('Seller has been paid online');

            $this->_objectManager->get(
                'Webkul\Marketplace\Helper\Email'
            )->sendSellerPaymentEmail(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
        }
    }

    public function randString(
        $length,
        $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
    ) {
        $str = 'tr-';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count - 1)];
        }

        return $str;
    }

    public function checktransid()
    {
        $uniqueId = $this->randString(11);
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Sellertransaction'
        )
            ->getCollection()
            ->addFieldToFilter(
                'transaction_id',
                $uniqueId
            );
        $i = 0;
        foreach ($collection as $value) {
            ++$i;
        }
        if ($i != 0) {
            $this->checktransid();
        } else {
            return $uniqueId;
        }
    }

    public function getProductOptions($optionData)
    {
        try {
            if ($optionData) {
                if (!is_array($optionData)) {
                    return $this->jsonHelper->jsonDecode(
                        $optionData
                    );
                } else {
                    return $optionData;
                }
            } else {
                return $optionData;
            }
        } catch (\Exception $e) {
            return $this->unserializer->unserialize(
                $optionData
            );
        }
    }

    /**
     * Retrieve all seller's order count
     *
     * @return int
     */
    public function getSellerOrders($sellerId)
    {
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        return count($collection);
    }

    /**
     * Get Order Product Option Data Method.
     *
     * @param \Webkul\Marketplace\Model\Saleslist $res
     *
     * @return array
     */
    public function getOrderedProductName($res, $productName)
    {
        $item = $this->orderItemRepository->get($res->getOrderItemId());
        $url = '';
        // Updated product name
        $result = [];
        $result = $this->getProductOptionData($item, $result);
        if ($item->getProduct()) {
            $url = $item->getProduct()->getProductUrl();
            $productName = $productName."<a href='".$url."' target='blank'>".$item['name']."</a>";
        } else {
            $productName = $productName.$item['name'];
        }
        $productName = $this->getProductNameHtml($result, $productName);
        /*prepare product quantity status*/
        $isForItemPay = 0;
        if ($item['qty_ordered'] > 0) {
            $productName = $productName.__('Ordered').
            ': <strong>'.($item['qty_ordered'] * 1).'</strong><br />';
        }
        if ($item['qty_invoiced'] > 0) {
            ++$isForItemPay;
            $productName = $productName.
            __('Invoiced').
            ': <strong>'.
            ($item['qty_invoiced'] * 1).
            '</strong><br />';
        }
        if ($item['qty_shipped'] > 0) {
            ++$isForItemPay;
            $productName = $productName.__('Shipped').
            ': <strong>'.($item['qty_shipped'] * 1).'</strong><br />';
        }
        if ($item['qty_canceled'] > 0) {
            $isForItemPay = 4;
            $productName = $productName.
            __('Canceled').
            ': <strong>'.
            ($item['qty_canceled'] * 1).
            '</strong><br />';
        }
        if ($item['qty_refunded'] > 0) {
            $isForItemPay = 3;
            $productName = $productName.
            __('Refunded').
            ': <strong>'.
            ($item['qty_refunded'] * 1).
            '</strong><br />';
        }
        return $productName;
    }

    /**
     * Get Order Product Option Data Method.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $result
     *
     * @return array
     */
    public function getProductOptionData($item, $result = [])
    {
        if ($options = $item['product_options']) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    /**
     * Get Order Product Name Html Data Method.
     *
     * @param array  $result
     * @param string $productName
     *
     * @return string
     */
    public function getProductNameHtml($result, $productName)
    {
        if ($_options = $result) {
            $proOptionData = '<dl class="item-options">';
            foreach ($_options as $_option) {
                $proOptionData .= '<dt>'.$_option['label'].'</dt>';

                $proOptionData .= '<dd>'.$_option['value'];
                $proOptionData .= '</dd>';
            }
            $proOptionData .= '</dl>';
            $productName = $productName.'<br/>'.$proOptionData;
        } else {
            $productName = $productName.'<br/>';
        }

        return $productName;
    }
}
