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
use Magento\Framework\Session\SessionManager;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;

/**
 * Webkul Marketplace SalesOrderSuccessObserver Observer Model.
 */
class SalesOrderSuccessObserver implements ObserverInterface
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * [$_coreSession description].
     *
     * @var SessionManager
     */
    protected $_coreSession;

    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var MarketplaceHelper
     */
    protected $_marketplaceHelper;

    /**
     * @var NotificationHelper
     */
    protected $notificationHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Event\Manager            $eventManager
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param SessionManager                              $coreSession
     * @param QuoteRepository                             $quoteRepository
     * @param OrderRepositoryInterface                    $orderRepository
     * @param CustomerRepositoryInterface                 $customerRepository
     * @param ProductRepositoryInterface                  $productRepository
     * @param MarketplaceHelper                           $marketplaceHelper
     * @param NotificationHelper                          $notificationHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        SessionManager $coreSession,
        QuoteRepository $quoteRepository,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        MarketplaceHelper $marketplaceHelper,
        NotificationHelper $notificationHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_coreSession = $coreSession;
        $this->_quoteRepository = $quoteRepository;
        $this->_orderRepository = $orderRepository;
        $this->_customerRepository = $customerRepository;
        $this->_productRepository = $productRepository;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->notificationHelper = $notificationHelper;
        $this->_date = $date;
    }

    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getOrderIds();

        foreach ($orderIds as $lastOrderId) {
            $sellerOrder = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Orders'
            )
            ->getCollection()
            ->addFieldToFilter('order_id', $lastOrderId);
            if (!count($sellerOrder)) {
                /** @var $orderInstance Order */
                $order = $this->_orderRepository->get($lastOrderId);
                $this->orderPlacedOperations($order, $lastOrderId);
            }
        }
    }

    /**
     * Order Place Operation method.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int                        $lastOrderId
     */
    public function orderPlacedOperations($order, $lastOrderId)
    {
        $this->productSalesCalculation($order);

        /*send placed order mail notification to seller*/

        $paymentCode = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
        }

        $shippingInfo = '';
        $shippingDes = '';

        $billingId = $order->getBillingAddress()->getId();

        $billaddress = $this->_objectManager->create(
            'Magento\Sales\Model\Order\Address'
        )->load($billingId);
        $billinginfo = $billaddress['firstname'].'<br/>'.
        $billaddress['street'].'<br/>'.
        $billaddress['city'].' '.
        $billaddress['region'].' '.
        $billaddress['postcode'].'<br/>'.
        $this->_objectManager->create(
            'Magento\Directory\Model\Country'
        )->load($billaddress['country_id'])->getName().'<br/>T:'.
        $billaddress['telephone'];

        $order->setOrderApprovalStatus(1)->save();

        $payment = $order->getPayment()->getMethodInstance()->getTitle();

        if ($order->getShippingAddress()) {
            $shippingId = $order->getShippingAddress()->getId();
            $address = $this->_objectManager->create(
                'Magento\Sales\Model\Order\Address'
            )->load($shippingId);
            $shippingInfo = $address['firstname'].'<br/>'.
            $address['street'].'<br/>'.
            $address['city'].' '.
            $address['region'].' '.
            $address['postcode'].'<br/>'.
            $this->_objectManager->create(
                'Magento\Directory\Model\Country'
            )->load($address['country_id'])->getName().'<br/>T:'.
            $address['telephone'];
            $shippingDes = $order->getShippingDescription();
        }

        $adminStoremail = $this->_marketplaceHelper->getAdminEmailId();
        $defaultTransEmailId = $this->_marketplaceHelper->getDefaultTransEmailId();
        $adminEmail = $adminStoremail ? $adminStoremail : $defaultTransEmailId;
        $adminUsername = 'Admin';

        $sellerOrder = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )
        ->getCollection()
        ->addFieldToFilter('order_id', $lastOrderId)
        ->addFieldToFilter('seller_id', ['neq' => 0]);
        foreach ($sellerOrder as $info) {
            $userdata = $this->_customerRepository->getById($info['seller_id']);
            $username = $userdata->getFirstname();
            $useremail = $userdata->getEmail();

            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $username,
                'email' => $useremail,
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $totalprice = 0;
            $totalTaxAmount = 0;
            $codCharges = 0;
            $shippingCharges = 0;
            $orderinfo = '';

            $saleslistIds = [];
            $collection1 = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )->getCollection()
            ->addFieldToFilter('order_id', $lastOrderId)
            ->addFieldToFilter('seller_id', $info['seller_id'])
            ->addFieldToFilter('parent_item_id', ['null' => 'true'])
            ->addFieldToFilter('magerealorder_id', ['neq' => 0])
            ->addFieldToSelect('entity_id');

            $saleslistIds = $collection1->getData();

            $fetchsale = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )
            ->getCollection()
            ->addFieldToFilter(
                'entity_id',
                ['in' => $saleslistIds]
            );
            $fetchsale->getSellerOrderCollection();
            foreach ($fetchsale as $res) {
                /* product name */
                $productName = $res->getMageproName();
                $result = [];
                $result = $this->getProductOptionData($res, $result);
                $productName = $this->getProductNameHtml($result, $productName);
                /* end */
                if ($res->getProductType() == 'configurable') {
                    $configurableSalesItem = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Saleslist'
                    )->getCollection()
                    ->addFieldToFilter('order_id', $lastOrderId)
                    ->addFieldToFilter('seller_id', $info['seller_id'])
                    ->addFieldToFilter('parent_item_id', $res->getOrderItemId());
                    $configurableItemArr = $configurableSalesItem->getOrderedProductId();
                    $configurableItemId = $res['mageproduct_id'];
                    if (!empty($configurableItemArr)) {
                        $configurableItemId = $configurableItemArr[0];
                    }
                    $product = $this->_productRepository->getById($configurableItemId);
                } else {
                    $product = $this->_productRepository->getById($res['mageproduct_id']);
                }

                $sku = $product->getSku();
                $orderinfo = $orderinfo."<tbody><tr>
                                <td class='item-info'>".$productName."</td>
                                <td class='item-info'>".$sku."</td>
                                <td class='item-qty'>".($res['magequantity'] * 1)."</td>
                                <td class='item-price'>".
                                    $order->formatPrice(
                                        $res['magepro_price'] * $res['magequantity']
                                    ).
                                '</td>
                             </tr></tbody>';
                $totalTaxAmount = $totalTaxAmount + $res['total_tax'];
                $totalprice = $totalprice + ($res['magepro_price'] * $res['magequantity']);

                /*
                * Low Stock Notification mail to seller
                */
                if ($this->_marketplaceHelper->getlowStockNotification()) {
                    if (!empty($product['quantity_and_stock_status']['qty'])) {
                        $stockItemQty = $product['quantity_and_stock_status']['qty'];
                    } else {
                        $stockItemQty = $product->getQty();
                    }
                    if ($stockItemQty <= $this->_marketplaceHelper->getlowStockQty()) {
                        $orderProductInfo = "<tbody><tr>
                                <td class='item-info'>".$productName."</td>
                                <td class='item-info'>".$sku."</td>
                                <td class='item-qty'>".($stockItemQty * 1).'</td>
                             </tr></tbody>';

                        $emailTemplateVariables = [];
                        $emailTemplateVariables['myvar1'] = $orderProductInfo;
                        $emailTemplateVariables['myvar2'] = $username;

                        $this->_objectManager->get(
                            'Webkul\Marketplace\Helper\Email'
                        )->sendLowStockNotificationMail(
                            $emailTemplateVariables,
                            $senderInfo,
                            $receiverInfo
                        );
                    }
                }
            }
            $shippingCharges = $info->getShippingCharges();
            $couponAmount = $info->getCouponAmount();
            $totalCod = 0;

            if ($paymentCode == 'mpcashondelivery') {
                $totalCod = $info->getCodCharges();
                $codRow = "<tr class='subtotal'>
                            <th colspan='3'>".__('Cash On Delivery Charges')."</th>
                            <td colspan='3'><span>".
                                $order->formatPrice($totalCod).
                            '</span></td>
                            </tr>';
            } else {
                $codRow = '';
            }

            $orderinfo = $orderinfo."<tfoot class='order-totals'>
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Shipping & Handling Charges')."</th>
                                    <td colspan='3'><span>".
                                    $order->formatPrice($shippingCharges)."</span></td>
                                </tr>
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Discount')."</th>
                                    <td colspan='3'><span> -".
                                        $order->formatPrice($couponAmount).
                                    "</span></td>
                                </tr>
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Tax Amount')."</th>
                                    <td colspan='3'><span>".
                                    $order->formatPrice($totalTaxAmount).'</span></td>
                                </tr>'.$codRow."
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Grandtotal')."</th>
                                    <td colspan='3'><span>".
                                    $order->formatPrice(
                                        $totalprice +
                                        $totalTaxAmount +
                                        $shippingCharges +
                                        $totalCod -
                                        $couponAmount
                                    ).'</span></td>
                                </tr></tfoot>';

            $emailTemplateVariables = [];
            if ($shippingInfo != '') {
                $isNotVirtual = 1;
            } else {
                $isNotVirtual = 0;
            }
            $emailTempVariables['myvar1'] = $order->getRealOrderId();
            $emailTempVariables['myvar2'] = $order['created_at'];
            $emailTempVariables['myvar4'] = $billinginfo;
            $emailTempVariables['myvar5'] = $payment;
            $emailTempVariables['myvar6'] = $shippingInfo;
            $emailTempVariables['isNotVirtual'] = $isNotVirtual;
            $emailTempVariables['myvar9'] = $shippingDes;
            $emailTempVariables['myvar8'] = $orderinfo;
            $emailTempVariables['myvar3'] = $username;

            if ($this->_marketplaceHelper->getOrderApprovalRequired()) {
                $emailTempVariables['seller_id'] = $info['seller_id'];
                $emailTempVariables['order_id'] = $lastOrderId;
                $emailTempVariables['sender_name'] = $senderInfo['name'];
                $emailTempVariables['sender_email'] = $senderInfo['email'];
                $emailTempVariables['receiver_name'] = $receiverInfo['name'];
                $emailTempVariables['receiver_email'] = $receiverInfo['email'];

                $orderPendingMailsCollection = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\OrderPendingMails'
                );
                $orderPendingMailsCollection->setData($emailTempVariables);
                $orderPendingMailsCollection->setCreatedAt($this->_date->gmtDate());
                $orderPendingMailsCollection->setUpdatedAt($this->_date->gmtDate());
                $orderPendingMailsCollection->save();
                $order->setOrderApprovalStatus(0)->save();
            } else {
                $this->_objectManager->get(
                    'Webkul\Marketplace\Helper\Email'
                )->sendPlacedOrderEmail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );
            }
        }
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
        $productOptionsData = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Orders'
        )->getProductOptions(
            $item->getProductOptions()
        );
        if ($options = $productOptionsData) {
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

    /**
     * Seller Product Sales Calculation Method.
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function productSalesCalculation($order)
    {
        /*
        * Marketplace Order details save before Observer
        */
        $this->_eventManager->dispatch(
            'mp_order_save_before',
            ['order' => $order]
        );

        /*
        * Get Global Commission Rate for Admin
        */
        $percent = $this->_marketplaceHelper->getConfigCommissionRate();

        /*
        * Get Current Store Currency Rate
        */
        $currentCurrencyCode = $order->getOrderCurrencyCode();
        $baseCurrencyCode = $order->getBaseCurrencyCode();
        $allowedCurrencies = $this->_marketplaceHelper->getConfigAllowCurrencies();
        $rates = $this->_marketplaceHelper->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }

        $lastOrderId = $order->getId();

        /*
        * Marketplace Credit Management module Observer
        */
        $this->_eventManager->dispatch(
            'mp_discount_manager',
            ['order' => $order]
        );

        $this->_eventManager->dispatch(
            'mp_advance_commission_rule',
            ['order' => $order]
        );

        $sellerData = $this->getSellerProductData($order, $rates[$currentCurrencyCode]);

        $sellerProArr = $sellerData['seller_pro_arr'];
        $sellerTaxArr = $sellerData['seller_tax_arr'];
        $sellerCouponArr = $sellerData['seller_coupon_arr'];

        $taxToSeller = $this->_marketplaceHelper->getConfigTaxManage();
        $shippingAll = $this->_coreSession->getData('shippinginfo');
        $shippingAllCount = count($shippingAll);
        foreach ($sellerProArr as $key => $value) {
            $productIds = implode(',', $value);
            $data = [
                'order_id' => $lastOrderId,
                'product_ids' => $productIds,
                'seller_id' => $key,
                'total_tax' => $sellerTaxArr[$key],
                'tax_to_seller' => $taxToSeller,
            ];
            if (!$shippingAllCount && $key == 0) {
                $shippingCharges = $order->getBaseShippingAmount();
                $data = [
                    'order_id' => $lastOrderId,
                    'product_ids' => $productIds,
                    'seller_id' => $key,
                    'shipping_charges' => $shippingCharges,
                    'total_tax' => $sellerTaxArr[$key],
                    'tax_to_seller' => $taxToSeller,
                ];
            }
            if (!empty($sellerCouponArr) && !empty($sellerCouponArr[$key])) {
                $data['coupon_amount'] = $sellerCouponArr[$key];
            }
            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Orders'
            );
            $collection->setData($data);
            $collection->setCreatedAt($this->_date->gmtDate());
            $collection->setSellerPendingNotification(1);
            $collection->setUpdatedAt($this->_date->gmtDate());
            $collection->save();
            $sellerOrderId = $collection->getId();
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Notification'
            )->saveNotification(
                \Webkul\Marketplace\Model\Notification::TYPE_ORDER,
                $sellerOrderId,
                $lastOrderId
            );
        }
        /*
        * Marketplace Order details save after Observer
        */
        $this->_eventManager->dispatch(
            'mp_order_save_after',
            ['order' => $order]
        );
    }

    /**
     * Get Seller's Product Data.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int                        $ratesPerCurrency
     *
     * @return array
     */
    public function getSellerProductData($order, $ratesPerCurrency)
    {
        $lastOrderId = $order->getId();
        /*
        * Get Global Commission Rate for Admin
        */
        $percent = $this->_marketplaceHelper->getConfigCommissionRate();

        $sellerProArr = [];
        $sellerTaxArr = [];
        $sellerCouponArr = [];
        $isShippingFlag = [];
        /*
        * Marketplace Credit discount data
        */
        $discountDetails = [];
        $discountDetails = $this->_coreSession->getData('salelistdata');

        foreach ($order->getAllItems() as $item) {
            $itemData = $item->getData();
            $sellerId = $this->getSellerIdPerProduct($item);
            if ($itemData['product_type'] != 'bundle') {
                $isShippingFlag = $this->getShippingFlag($item, $sellerId, $isShippingFlag);

                $price = $itemData['base_price'];

                $taxamount = $itemData['base_tax_amount'];
                $qty = $item->getQtyOrdered();

                $totalamount = $qty * $price;

                $advanceCommissionRule = $this->_customerSession->getData(
                    'advancecommissionrule'
                );
                $commission = $this->getCommission($sellerId, $totalamount, $item, $advanceCommissionRule);

                $actparterprocost = $totalamount - $commission;
            } else {
                if (empty($isShippingFlag[$sellerId])) {
                    $isShippingFlag[$sellerId] = 0;
                }
                $price = 0;
                $taxamount = 0;
                $qty = $item->getQtyOrdered();
                $totalamount = 0;
                $commission = 0;
                $actparterprocost = 0;
            }

            $collectionsave = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            );
            $collectionsave->setMageproductId($item->getProductId());
            $collectionsave->setOrderItemId($item->getItemId());
            $collectionsave->setParentItemId($item->getParentItemId());
            $collectionsave->setOrderId($lastOrderId);
            $collectionsave->setMagerealorderId($order->getIncrementId());
            $collectionsave->setMagequantity($qty);
            $collectionsave->setSellerId($sellerId);
            $collectionsave->setCpprostatus(\Webkul\Marketplace\Model\Saleslist::PAID_STATUS_PENDING);
            $collectionsave->setMagebuyerId($this->_customerSession->getCustomerId());
            $collectionsave->setMageproPrice($price);
            $collectionsave->setMageproName($item->getName());
            if ($totalamount != 0) {
                $collectionsave->setTotalAmount($totalamount);
                $commissionRate = ($commission * 100) / $totalamount;
            } else {
                $collectionsave->setTotalAmount($price);
                $commissionRate = $percent;
            }
            $collectionsave->setTotalTax($taxamount);
            if (!$this->_marketplaceHelper->isSellerCouponModuleInstalled()) {
                if (isset($itemData['base_discount_amount']) && (int) $itemData['base_discount_amount']) {
                    $baseDiscountAmount = $itemData['base_discount_amount'];
                    $collectionsave->setIsCoupon(1);
                    $collectionsave->setAppliedCouponAmount($baseDiscountAmount);

                    if (!isset($sellerCouponArr[$sellerId])) {
                        $sellerCouponArr[$sellerId] = 0;
                    }
                    $sellerCouponArr[$sellerId] = $sellerCouponArr[$sellerId] + $baseDiscountAmount;
                }
            }
            $collectionsave->setTotalCommission($commission);
            $collectionsave->setActualSellerAmount($actparterprocost);
            $collectionsave->setCommissionRate($commissionRate);
            $collectionsave->setCurrencyRate($ratesPerCurrency);
            if (isset($isShippingFlag[$sellerId])) {
                $collectionsave->setIsShipping($isShippingFlag[$sellerId]);
            }
            $collectionsave->setCreatedAt($this->_date->gmtDate());
            $collectionsave->setUpdatedAt($this->_date->gmtDate());
            $collectionsave->save();
            $qty = 0;
            if (!isset($sellerTaxArr[$sellerId])) {
                $sellerTaxArr[$sellerId] = 0;
            }
            $sellerTaxArr[$sellerId] = $sellerTaxArr[$sellerId] + $taxamount;
            if ($price != 0.0000) {
                if (!isset($sellerProArr[$sellerId])) {
                    $sellerProArr[$sellerId] = [];
                }
                array_push($sellerProArr[$sellerId], $item->getProductId());
            } else {
                if (!$item->getParentItemId()) {
                    if (!isset($sellerProArr[$sellerId])) {
                        $sellerProArr[$sellerId] = [];
                    }
                    array_push($sellerProArr[$sellerId], $item->getProductId());
                }
            }
        }

        return [
            'seller_pro_arr' => $sellerProArr,
            'seller_tax_arr' => $sellerTaxArr,
            'seller_coupon_arr' => $sellerCouponArr
        ];
    }

    /**
     * Get Commission Amount.
     *
     * @param int                             $sellerId
     * @param float                           $totalamount
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $advanceCommissionRule
     *
     * @return float
     */
    public function getCommission($sellerId, $totalamount, $item, $advanceCommissionRule)
    {
        /*
        * Get Global Commission Rate for Admin
        */
        $percent = $this->_marketplaceHelper->getConfigCommissionRate();

        $commission = 0;

        $collection1 = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleperpartner'
        )
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        if ($collection1->getSize() != 0) {
            foreach ($collection1 as $rowdatasale) {
                $commission = ($totalamount * $rowdatasale->getCommissionRate()) / 100;
            }
        } else {
            $commission = ($totalamount * $percent) / 100;
        }

        if (!$this->_marketplaceHelper->getUseCommissionRule()) {
            $wholedata['id'] = $item->getProductId();
            $this->_eventManager->dispatch(
                'mp_advance_commission',
                [$wholedata]
            );

            $advancecommission = $this->_customerSession->getData('commission');
            if ($advancecommission != '') {
                $percent = $advancecommission;
                $commType = $this->_marketplaceHelper->getCommissionType();
                if ($commType == 'fixed') {
                    $commission = $percent;
                } else {
                    $commission = ($totalamount * $advancecommission) / 100;
                }
                if ($commission > $totalamount) {
                    $commission = $totalamount * $this->_marketplaceHelper->getConfigCommissionRate() / 100;
                }
            }
        } else {
            if (count($advanceCommissionRule)) {
                if ($advanceCommissionRule[$item->getId()]['type'] == 'fixed') {
                    $commission = $advanceCommissionRule[$item->getId()]['amount'];
                } else {
                    $commission =
                    ($totalamount * $advanceCommissionRule[$item->getId()]['amount']) / 100;
                }
            }
        }

        return $commission;
    }

    /**
     * Get Seller ID Per Product.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return int
     */
    public function getSellerIdPerProduct($item)
    {
        $infoBuyRequest = $item->getProductOptionByCode('info_buyRequest');

        $mpassignproductId = 0;
        if (isset($infoBuyRequest['mpassignproduct_id'])) {
            $mpassignproductId = $infoBuyRequest['mpassignproduct_id'];
        }
        if ($mpassignproductId) {
            $mpassignModel = $this->_objectManager->create(
                'Webkul\MpAssignProduct\Model\Items'
            )->load($mpassignproductId);
            $sellerId = $mpassignModel->getSellerId();
        } elseif (array_key_exists('seller_id', $infoBuyRequest)) {
            $sellerId = $infoBuyRequest['seller_id'];
        } else {
            $sellerId = '';
        }
        if ($sellerId == '') {
            $collectionProduct = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )
            ->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                $item->getProductId()
            );
            foreach ($collectionProduct as $value) {
                $sellerId = $value->getSellerId();
            }
        }
        if ($sellerId == '') {
            $sellerId = 0;
        }

        return $sellerId;
    }

    /**
     * Get Shipping Flag Per Seller Method.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int                             $sellerId
     * @param array                           $result
     *
     * @return array
     */
    public function getShippingFlag($item, $sellerId, $isShippingFlag = [])
    {
        if (($item->getProductType() != 'virtual') && ($item->getProductType() != 'downloadable')) {
            if (!isset($isShippingFlag[$sellerId])) {
                $isShippingFlag[$sellerId] = 1;
            } else {
                $isShippingFlag[$sellerId] = 0;
            }
        }

        return $isShippingFlag;
    }
}
