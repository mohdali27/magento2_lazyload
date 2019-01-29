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

use Magento\Framework\Session\SessionManager;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory as SalePerPartnerCollection;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SalesListCollection;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory as MpOrdersCollection;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Webkul Marketplace Helper Payment.
 */
class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
     /**
     * @var \Magento\Customer\Model\Address
     */
    private $modelCustomerAddress;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shippingConfig;

    /**
     * @var SessionManager
     */
    protected $coreSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MpProductCollection
     */
    private $mpProductCollection;

    /**
     * @var SalePerPartnerCollection
     */
    private $salePerPartnerCollection;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SalesListCollection
     */
    private $salesListCollection;

    /**
     * @var MpOrdersCollection
     */
    private $mpOrdersCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $dbTransaction;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var Transaction\BuilderInterface
     */
    private $transactionBuilder;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Address $modelCustomerAddress
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param SessionManager $coreSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param MpProductCollection $mpProductCollection
     * @param SalePerPartnerCollection $salePerPartnerCollection
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param SalesListCollection $salesListCollection
     * @param MpOrdersCollection $mpOrdersCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $dbTransaction
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Address $modelCustomerAddress,
        \Magento\Shipping\Model\Config $shippingConfig,
        SessionManager $coreSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        MpProductCollection $mpProductCollection,
        SalePerPartnerCollection $salePerPartnerCollection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        SalesListCollection $salesListCollection,
        MpOrdersCollection $mpOrdersCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        Transaction\BuilderInterface $transactionBuilder,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
    ) {
        parent::__construct($context);
        $this->modelCustomerAddress = $modelCustomerAddress;
        $this->shippingConfig = $shippingConfig;
        $this->coreSession = $coreSession;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        $this->mpProductCollection = $mpProductCollection;
        $this->salePerPartnerCollection = $salePerPartnerCollection;
        $this->orderRepository = $orderRepository;
        $this->salesListCollection = $salesListCollection;
        $this->mpOrdersCollection = $mpOrdersCollection;
        $this->storeManager = $storeManager;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
        $this->invoiceSender = $invoiceSender;
        $this->transactionBuilder = $transactionBuilder;
        $this->logger = $context->getLogger();
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * Prepare shipping data
     *
     * @param  $quote contains current quote
     * @return array
     */
    public function getShippingData($quote)
    {
        try {
            $allmethods = [];
            $shippingData = [];
            $newvar = '';

            $customerAddressData = $this->getCustomerAddressData($quote);

            $shippingMethod = $customerAddressData['shipping_method'];
            $customerAddressId = $customerAddressData['customer_address_id'];
            $shippingTaxAmount = $customerAddressData['shipping_tax_amount'];
            $shippingAmount = $customerAddressData['shipping_amount'];

            $customerAddress = $this->modelCustomerAddress->load($customerAddressId);
            $customerName = $customerAddress['firstname'].' '.$customerAddress['lastname'];

            //Guest User
            if (!$customerAddressId || $customerAddressId == null) {
                $customerName = $quote->getBillingAddress()->getFirstname()
                . ' '
                . $quote->getBillingAddress()->getLastname();

                $customerAddress = $quote->getBillingAddress();
            }

            $methods = $this->shippingConfig->getActiveCarriers();

            foreach ($methods as $_code => $_method) {
                array_push($allmethods, $_code);
            }

            if ($shippingMethod == 'mpmultishipping_mpmultishipping') {
                $newvar = 'webkul';
                $shippinginfo = $this->checkoutSession->getData('selected_shipping');
                // $shippinginfo = json_decode(json_encode($shippinginfo), true);

                foreach ($shippinginfo as $key => $val) {
                    $taxAmount = $this->calculateTaxByPercent($val['baseamount'], $quote);
                    $shippingData[] = [
                        'seller' => $val['sellerid'],
                        'amount' => $val['baseamount'] + $taxAmount,
                        'method' => $val['method']
                    ];
                }
            } else {
                $shipmethod = explode('_', $shippingMethod, 2);
                $shippinginfo = $this->checkoutSession->getShippingInfo();
                
                if (empty($shippinginfo) || $shippinginfo=="" || $shippinginfo==null) {
                    $shippinginfo = $this->coreSession->getShippingInfo();
                }
                
                if (in_array($shipmethod[0], $allmethods)
                    && !empty($shippinginfo[$shipmethod[0]])
                ) {
                    foreach ($shippinginfo[$shipmethod[0]] as $key) {
                        $newvar = 'webkul';
                        foreach ($key['submethod'] as $k => $v) {
                            if ($k == $shipmethod[1]) {
                                // if ($this->helper->getCurrentCurrencyCode() !== $quote->getBaseCurrencyCode()) {
                                //     $v['cost'] = $this->convertFromBaseToCurrentCurrency($v['cost']);
                                // }
                                $taxAmount = $this->calculateTaxByPercent($v['cost'], $quote);
                                $shippingData[] = [
                                    'seller' => $key['seller_id'],
                                    'amount' => $v['cost'] + $taxAmount,
                                    'method' => $shipmethod[1]
                                ];
                            }
                        }
                    }
                }
            }

            return [
                'newvar' => $newvar,
                'shippingTaxAmount' => $shippingTaxAmount,
                'shippingAmount' => $shippingAmount,
                'shipinf' => $shippingData,
                'customName' => $customerName,
                'customerAddress' => $customerAddress,
                'shipping_method' => $shippingMethod
            ];
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getShippingData : ".$e->getMessage());
        }
    }

    /**
     * Prepare data from customer address
     *
     * @param  $quote contains current quote
     * @return array
     */
    public function getCustomerAddressData($quote)
    {
        try {
            $shippingTaxAmount = 0;
            $shippingAmount = 0;
            $customerAddressId = 0;

            if (!empty($quote->getShippingAddress())) {
                $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

                $shippingData = $this->calculateShippingTax(
                    $quote->getShippingAddress()
                );
                $shippingTaxAmount = $shippingData['shippingTaxAmount'];
                $shippingAmount = $shippingData['shippingAmount'];

                $customerAddressId = $quote->getShippingAddress()
                    ->getCustomerAddressId();
            } else {
                $shippingMethod = '';
                $customerAddressId = $quote->getBillingAddress()
                    ->getCustomerAddressId();
            }
            if ($customerAddressId == null) {
                $customerAddressId = $quote->getBillingAddress()
                    ->getCustomerAddressId();
            }
            return [
                'shipping_method' => $shippingMethod,
                'customer_address_id' => $customerAddressId,
                'shipping_tax_amount' => $shippingTaxAmount,
                'shipping_amount' => $shippingAmount
            ];
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getCustomerAddressData : ".$e->getMessage());
        }
    }

    /**
     * calculate tax from a tax percentage
     *
     * @param  $quote contains current quote
     * @param  $amount contains an amount on which tax is calculated
     * @return int|float
     */
    public function calculateTaxByPercent($amount, $quote)
    {
        try {
            $shippingTaxConfig = $this->getShippingTaxCalculationConfig();

            if ($shippingTaxConfig == 0) {
                $percent = $this->getTaxPercent($quote);
                if ($percent !== 0 && $amount !== 0) {
                    $taxAmount = ( $amount * $percent ) / 100 ;
                    return round($taxAmount, 2);
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment calculateTaxByPercent : ".$e->getMessage());
        }
    }

    /**
     * get tax percentage from applied taxes
     *
     * @param  $quote contains current quote
     * @return int|float
     */
    public function getTaxPercent($quote)
    {
        try {
            $appliedTaxes = $quote->getShippingAddress()->getAppliedTaxes();
            if (!empty($appliedTaxes)) {
                foreach ($appliedTaxes as $type => $value) {
                    if ($type == "shipping") {
                        return $value['percent'];
                    }
                }
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getTaxPercent : ".$e->getMessage());
            return 0;
        }
    }

    /**
     * get shipping tax amount and shipping amount from shipping address
     *
     * @param  $shippingAddress contains current shipping address
     * @return array
     */
    public function calculateShippingTax($shippingAddress)
    {
        try {
            $shippingTaxAmount = $shippingAddress->getData(
                'base_shipping_tax_amount'
            );

            $shippingAmount = $shippingAddress->getData(
                'base_shipping_amount'
            );

            $shippingAmountInclTax = $shippingAddress->getData(
                'base_shipping_incl_tax'
            );

            if ($shippingAmount < $shippingAmountInclTax && $shippingTaxAmount!==0) {
                $shippingTaxAmount = 0;
                $shippingAmount = $shippingAddress->getData(
                    'base_shipping_incl_tax'
                );
            }

            return [
                'shippingTaxAmount' => $shippingTaxAmount,
                'shippingAmount' => $shippingAmount
            ];
        } catch (\Exception $e) {
            $this->logData("Helper_Payment calculateShippingTax : ".$e->getMessage());
        }
    }

    /**
     * get admin config of shipping includes tax or not
     *
     * @return boolean
     */
    public function getShippingTaxCalculationConfig()
    {
        try {
            return $this->scopeConfig->getValue(
                'tax/calculation/shipping_includes_tax',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getShippingTaxCalculationConfig : ".$e->getMessage());
        }
    }
    
    /**
     * calculate total discount amount of a bundle product
     *
     * @param  $itemId contains order item Id
     * @param  $order contains order
     * @return int|float
     */
    public function calculateBundleProductDiscount($itemId, $order)
    {
        $childDiscount = 0;
        try {
            foreach ($order->getAllItems() as $item) {
                if ($item->getParentItem() && $item->getParentItemId()==$itemId) {
                    $childDiscount += $item->getBaseDiscountAmount();
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment calculateBundleProductDiscount : ".$e->getMessage());
        }
        return $childDiscount;
    }

    /**
     * prepare commission data for admin
     *
     * @param  $item contains order item
     * @return array
     */
    public function getCommissionData($item)
    {
        try {
            $itemId = $item->getProductId();
            $tempcoms = 0;
            $commissionDetail = [];
            $mpAssignProductId = 0;

            $advanceCommissionRule = $this->customerSession->getData(
                'advancecommissionrule'
            );

            $commType = $this->helper->getCommissionType();
            $configCommissionRate = $this->helper->getConfigCommissionRate();
            $rowTotal = $item->getBaseRowTotal();

            $infoBuyRequest = $item->getProductOptionByCode('info_buyRequest');

            $mpAssignProductId = 0;
            if (!empty($infoBuyRequest['mpassignproduct_id'])) {
                $mpAssignProductId = $infoBuyRequest['mpassignproduct_id'];
            }
            if ($mpAssignProductId) {
                $mpassignModel = $this->objectManager->create(
                    'Webkul\MpAssignProduct\Model\Items'
                )->load($mpAssignProductId);
                $sellerId = $mpassignModel->getSellerId();
            } elseif (is_array($infoBuyRequest) && array_key_exists('seller_id', $infoBuyRequest)) {
                $sellerId = $infoBuyRequest['seller_id'];
            } else {
                $sellerId = '';
            }

            if ($sellerId == '') {
                $seller = $this->mpProductCollection->create()
                    ->addFieldToFilter(
                        'mageproduct_id',
                        $itemId
                    );

                if (!$this->helper->getUseCommissionRule()) {
                    $this->_eventManager->dispatch(
                        'mp_advance_commission',
                        ['id' => $itemId]
                    );
                    $advancecommission = $this->customerSession->getData(
                        'commission'
                    );
                    if ($advancecommission != '') {
                        $tempcoms = $this->calculateCommission($commType, $advancecommission, $rowTotal);
                        if ($tempcoms > $rowTotal) {
                            $tempcoms = $rowTotal * $configCommissionRate / 100;
                        }
                        foreach ($seller as $usr) {
                            $commissionDetail['id'] = $usr->getSellerId();
                        }
                    }
                } else {
                    if (!empty($advanceCommissionRule)) {
                        $tempcoms = $this->calculateCommission(
                            $advanceCommissionRule[$item->getId()]['type'],
                            $advanceCommissionRule[$item->getId()]['amount'],
                            $rowTotal
                        );
                        foreach ($seller as $usr) {
                            $commissionDetail['id'] = $usr->getSellerId();
                        }
                    }
                }
            }

            return [
                'tempcoms' => $tempcoms,
                'commissionDetail' => $commissionDetail,
                'row_total' => $rowTotal,
                'product_id' => $itemId
            ];
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getCommissionData : ".$e->getMessage());
        }
    }

    /**
     * calculate commission
     *
     * @param  $type type is fixed or in percentage
     * @param  $amount parameter to calculate commission
     * @param  $rowTotal on which commision is calculated
     * @return int|float
     */
    public function calculateCommission($type, $amount, $rowTotal)
    {
        try {
            if ($type == 'fixed') {
                $tempcoms = $amount;
            } else {
                $tempcoms = (
                    $rowTotal * $amount
                ) / 100;
            }
            return $tempcoms;
        } catch (\Exception $e) {
            $this->logData("Helper_Payment calculateCommission : ".$e->getMessage());
        }
    }

    /**
     * prepare seller wise commission data
     *
     * @param  $productId product Id
     * @return array
     */
    public function getSellerDetail($productId = '')
    {
        $data = [
            'id' => 0,
            'commission' => 0
        ];
        try {
            $sell = $this->mpProductCollection->create()
                    ->addFieldToFilter('mageproduct_id', $productId);
            if ($sell->getSize()) {
                foreach ($sell as $seller) {
                    $sellerdetails = $this->salePerPartnerCollection->create()
                        ->addFieldToFilter('seller_id', $seller->getSellerId());
                    if ($sellerdetails->getSize()) {
                        foreach ($sellerdetails as $temp) {
                            $data['id'] = $temp->getSellerId();
                            $data['commission'] = $temp->getCommissionRate();
                        }
                    } else {
                        $data['id'] = $seller->getSellerId();
                        $data['commission'] = $this->helper->getConfigCommissionRate();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getSellerDetail : ".$e->getMessage());
        }
        return $data;
    }

    /**
     * get real seller id if seller price comparison module installed
     *
     * @param  $item order item
     * @return int seller id
     */
    public function getRealSellerId($item)
    {
        $sellerId = '';
        try {
            $productId = $item->getProductId();
            $mpAssignProductId = 0;

            $infoBuyRequest = $item->getProductOptionByCode('info_buyRequest');

            $mpAssignProductId = 0;
            if (isset($infoBuyRequest['mpassignproduct_id'])) {
                $mpAssignProductId = $infoBuyRequest['mpassignproduct_id'];
            }
            if ($mpAssignProductId) {
                $mpassignModel = $this->objectManager->create(
                    'Webkul\MpAssignProduct\Model\Items'
                )->load($mpAssignProductId);
                $sellerId = $mpassignModel->getSellerId();
            } elseif (is_array($infoBuyRequest) && array_key_exists('seller_id', $infoBuyRequest)) {
                $sellerId = $infoBuyRequest['seller_id'];
            }

            if ($sellerId == '') {
                $seller = $this->mpProductCollection->create()
                    ->addFieldToFilter(
                        'mageproduct_id',
                        $productId
                    );
                foreach ($seller as $usr) {
                    $sellerId = $usr->getSellerId();
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getRealSellerId : ".$e->getMessage());
        }
        return $sellerId;
    }

    /**
     * get seller coupon amount if seller coupon module installed
     *
     * @param  $sellerId seller Id
     * @return int|float
     */
    public function getSellerCouponAmount($sellerId)
    {
        $amount = 0;
        try {
            if ($this->helper->isSellerCouponModuleInstalled() && $sellerId !== '') {
                $info = $this->checkoutSession->getCouponInfo();
                if (!empty($info[$sellerId]['seller_id'])
                    && $info[$sellerId]['seller_id'] == $sellerId
                    && !empty($info[$sellerId]['amount'])
                ) {
                    $amount = $info[$sellerId]['amount'];
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getSellerCouponAmount : ".$e->getMessage());
        }
        return $amount;
    }

    /**
     * get seller credit point amount if seller credit module installed
     *
     * @param  $sellerId seller Id
     * @return int|float
     */
    public function getCreditPoints($sellerId)
    {
        $amount = 0;
        try {
            if ($this->_moduleManager->isEnabled('Webkul_Mpsellercredits') && $sellerId !== '') {
                $creditInfo = $this->coreSession->getCreditInfo();
                if (!empty($creditInfo[$sellerId]['amount'])) {
                    $amount = $creditInfo[$sellerId]['amount'];
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getCreditPoints : ".$e->getMessage());
        }
        return $amount;
    }

    /**
     * calculate adjustment negative amount
     *
     * @param  $adjustmentNegative Adjustment Fee for credit memo
     * @param  $adjustmentPositive Adjustment Refund for credit memo
     * @return int|float
     */
    public function getAdjustmentNegative($adjustmentNegative, $adjustmentPositive)
    {
        try {
            if ($adjustmentNegative > $adjustmentPositive) {
                $adjustmentNegative -= $adjustmentPositive;
            } else {
                $adjustmentNegative = 0;
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getAdjustmentNegative : ".$e->getMessage());
        }
        return $adjustmentNegative;
    }

    /**
     * Calculation of the refund item ordered price
     *
     * @param  $productId Product Id
     * @param  $orderId Order Id
     * @return int|float
     */
    public function getItemPrice($productId, $orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductId() == $productId) {
                    // return $item->getPrice();
                    return $item->getBasePrice();
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getItemPrice : ".$e->getMessage());
        }
        return 0;
    }

    /**
     * prepare data for credit memo
     *
     * @param  $refundData request parameters for refund
     * @param  $orderid Order Id
     * @return array
     */
    public function getCreditmemoItemData(
        $refundData,
        $orderid
    ) {
        $creditmemoItemsIds = [];
        $creditmemoItemsQty = [];
        $creditmemoItemsPrice = [];
        $creditmemoCommissionRateArr = [];

        try {
            foreach ($refundData['creditmemo']['items'] as $key => $value) {
                $productId = '';
                $sellerProducts = $this->salesListCollection->create()
                    ->addFieldToFilter(
                        'order_item_id',
                        $key
                    )->addFieldToFilter(
                        'order_id',
                        $orderid
                    );
                foreach ($sellerProducts as $sellerProduct) {
                    $productId = $sellerProduct['mageproduct_id'];

                    if ($productId) {
                        $creditmemoItemsIds[$key] = $productId;
                        $creditmemoItemsQty[$key] = $value['qty'];
                        $creditmemoItemsPrice[$key] = $this->getItemPrice(
                            $productId,
                            $orderid
                        ) * $value['qty'];
                        $creditmemoCommissionRateArr[$key] = $sellerProduct->getData();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getCreditmemoItemData : ".$e->getMessage());
        }
        arsort($creditmemoItemsPrice);
        return [
            'creditmemoItemsIds'=>$creditmemoItemsIds,
            'creditmemoItemsQty'=>$creditmemoItemsQty,
            'creditmemoItemsPrice'=>$creditmemoItemsPrice,
            'creditmemoCommissionRateArr'=>$creditmemoCommissionRateArr,
        ];
    }

    /**
     * Prepare data to refund to seller and admin
     *
     * @param  $creditmemoItemsData credit memo item data
     * @param  $adjustmentNegative Adjustment Fee for credit memo
     * @param  $orderId Order Id
     * @return array
     */
    public function getAdminAmountAndSellerData(
        $creditmemoItemsData,
        $adjustmentNegative,
        $orderId
    ) {
        $sellerArr = [];
        $adminAmountToRefund = 0;
        try {
            $creditmemoItemsIds = $creditmemoItemsData['creditmemoItemsIds'];
            $creditmemoItemsQty = $creditmemoItemsData['creditmemoItemsQty'];
            $creditmemoItemsPrice = $creditmemoItemsData['creditmemoItemsPrice'];
            $creditmemoCommissionRateArr = $creditmemoItemsData['creditmemoCommissionRateArr'];
            
            foreach ($creditmemoItemsPrice as $key => $item) {
                $refundedQty = $creditmemoItemsQty[$key];
                $refundedPrice = $creditmemoItemsPrice[$key];
                $productId = $creditmemoItemsIds[$key];
                $sellerProduct = $creditmemoCommissionRateArr[$key];
                if ($adjustmentNegative * 1) {
                    if ($adjustmentNegative >= $sellerProduct['total_amount']) {
                        $adjustmentNegative -= $sellerProduct['total_amount'];
                        $updatedPrice = $sellerProduct['total_amount'];
                        $refundedPrice = 0;
                    } else {
                        $refundedPrice -=  $adjustmentNegative;
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
                    $commissionPercentage = (
                        $sellerProduct['total_commission'] * 100
                    ) / $sellerProduct['total_amount'];
                } else {
                    $commissionPercentage = 0;
                }
                $updatedCommission = ($refundedPrice * $commissionPercentage) / 100;
                $updatedSellerAmount = $refundedPrice - $updatedCommission;

                $taxAmount = $this->getFinalTaxAmount($refundedQty, $sellerProduct, $orderId);
                $updatedSellerAmount += $taxAmount;

                $couponAmountData = $this->getSellerDiscountAmount($refundedQty, $sellerProduct);
                $updatedSellerAmount -= $couponAmountData['discount'];

                if (!isset($sellerArr[$sellerProduct['seller_id']]['seller_refund'])) {
                    $sellerArr[$sellerProduct['seller_id']]['seller_refund'] = 0;
                }

                if (!isset($sellerArr[$sellerProduct['seller_id']]['updated_commission'])) {
                    $sellerArr[$sellerProduct['seller_id']]['updated_commission'] = 0;
                }

                $sellerArr[$sellerProduct['seller_id']]['seller_refund'] += $updatedSellerAmount;

                $sellerArr[$sellerProduct['seller_id']]['updated_commission'] += $updatedCommission;
                $adminAmountToRefund += $updatedCommission;

                if ($couponAmountData['remaining_coupon_amount']) {
                    $sellerProduct->setAppliedCouponAmount($couponAmountData['remaining_coupon_amount']);
                    $sellerProduct->save();
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getAdminAmountAndSellerData : ".$e->getMessage());
        }
        return [
            'sellerArr' => $sellerArr,
            'adminAmountToRefund' => $adminAmountToRefund
        ];
    }

    /**
     * calculate refund Tax Amount according to refunded quantity of item
     *
     * @param  $refundedQty item quantity to refund
     * @param  $sellerProduct seller's product's table data
     * @param  $orderId Order Id
     * @return int|float
     */
    public function getFinalTaxAmount(
        $refundedQty,
        $sellerProduct,
        $orderId
    ) {
        $taxAmount = 0;
        try {
            $order = $this->orderRepository->get($orderId);
            $orderItem = $this->orderItemRepository->get($sellerProduct['order_item_id']);
            if ($refundedQty) {
                if ((int)$sellerProduct['magequantity'] != ''
                    || (int)$sellerProduct['magequantity'] != 0
                ) {
                    $taxAmt = $sellerProduct['total_tax'];
                    
                    if ($orderItem->getBaseTaxAmount() > $taxAmt) {

                        $taxAmt = $orderItem->getBaseTaxAmount() - $taxAmt;
                        if ($taxAmt > 0 && $taxAmt < 1) {
                            $taxAmt = $orderItem->getBaseTaxAmount();
                        } else {
                            $taxAmt = $sellerProduct['total_tax'];
                        }
                    }
                    $taxAmount = (
                        $taxAmt / $sellerProduct['magequantity']
                    ) * $refundedQty;
                }
            }

            if (!$this->helper->getConfigTaxManage()) {
                $taxAmount = 0;
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getFinalTaxAmount : ".$e->getMessage());
        }
        return $taxAmount;
    }

    /**
     * calculate seller discount amount according to refunded quantity of item
     *
     * @param  $refundedQty item quantity to refund
     * @param  $sellerProduct seller's product's table data
     * @return array
     */
    public function getSellerDiscountAmount(
        $refundedQty,
        $sellerProduct
    ) {
        $discount = 0;
        $remainAppliedCouponAmount = false;
        try {
            if ($refundedQty) {
                if ((int)$sellerProduct['magequantity'] != ''
                    || (int)$sellerProduct['magequantity'] != 0
                ) {
                    $discount = (
                        $sellerProduct['applied_coupon_amount'] / $sellerProduct['magequantity']
                    ) * $refundedQty;
                    $remainAppliedCouponAmount = $sellerProduct['applied_coupon_amount'] - $discount;
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getSellerDiscountAmount : ".$e->getMessage());
        }
        return [
            'discount' => $discount,
            'remaining_coupon_amount' => $remainAppliedCouponAmount
        ];
    }

    /**
     * calculate admin shipping amount to refund
     *
     * @param  $shippingCharges shipping charges
     * @param  $orderData magento's order data
     * @param  $refundData request parameters for refund
     * @return int|float
     */
    public function getAdminShippingAmount(
        $shippingCharges,
        $orderData,
        $refundData
    ) {
        $adminShipping = 0;
        try {
            if ($shippingCharges == 0
                && (($orderData['shipping_amount'] * 1) >= ($orderData['shipping_refunded'] * 1))
            ) {
                $adminShipping = $refundData['creditmemo']['shipping_amount'];
                $shippingTaxAmount = $orderData['shipping_tax_amount'];
                $orderShippingTaxRefunded = $orderData['shipping_tax_refunded'];
                if (($shippingTaxAmount * 1) >= ($orderShippingTaxRefunded * 1) && $orderData['shipping_amount']>0) {
                    $shipTaxPercentage = ($shippingTaxAmount * 100) / $orderData['shipping_amount'];
                    $adminShipping += (
                        $refundData['creditmemo']['shipping_amount'] * $shipTaxPercentage / 100
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getAdminShippingAmount : ".$e->getMessage());
        }
        return $adminShipping;
    }

    /**
     * update shipping amount to refund
     *
     * @param  $orderId Order Id
     * @param  $sellerId Seller Id
     * @param  $refundData request parameters for refund
     * @return array
     */
    public function updateShippingRefundData(
        $orderId,
        $sellerId,
        $refundData
    ) {
        $shippingCharges = 0;
        $codCharges = 0;
        try {
            $trackingcoll = $this->mpOrdersCollection->create()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('seller_id', $sellerId);
            foreach ($trackingcoll as $tracking) {
                $codCharges = $tracking->getCodCharges();
                $shippingCharges = $tracking->getShippingCharges();
                if ($shippingCharges * 1) {
                    if ($shippingCharges > $refundData['creditmemo']['shipping_amount']) {
                        $shippingCharges = $refundData['creditmemo']['shipping_amount'];
                        $refundData['creditmemo']['shipping_amount'] = 0;
                    } else {
                        $refundData['creditmemo']['shipping_amount'] -= $shippingCharges;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment updateShippingRefundData : ".$e->getMessage());
        }
        return [
            'refund_data' => $refundData,
            'shipping_charges' => $shippingCharges
        ];
    }

    /**
     * Convert an amount from base currency to current currency
     *
     * @param  $amount amount to convert
     * @return int|float
     */
    public function convertFromBaseToCurrentCurrency($amount)
    {
        try {
            $amount = $this->storeManager->getStore()->getBaseCurrency()->convert(
                $amount,
                $this->storeManager->getStore()->getCurrentCurrency()
            );
        } catch (\Exception $e) {
            $this->logData("Helper_Payment convertFromBaseToCurrentCurrency : ".$e->getMessage());
        }
        return $amount;
    }
    
    /**
     * revert Seller Payment to sale per partner table
     *
     * @param  $order order
     * @param  $sellerid seller Id
     * @return void
     */
    public function revertSellerPayment($order, $sellerid)
    {
        try {
            $lastOrderId = $order->getId();
            $actparterprocost = 0;
            $totalamount = 0;
            /*
            * Calculate cod and shipping charges if applied
            */
            $codCharges = 0;
            $shippingCharges = 0;
            $sellerOrder = $this->mpOrdersCollection->create()
                ->addFieldToFilter('seller_id', $sellerid)
                ->addFieldToFilter('order_id', $lastOrderId);

            foreach ($sellerOrder as $info) {
                $codCharges = $info->getCodCharges();
                $shippingCharges = $info->getShippingCharges();
            }

            $collection = $this->salesListCollection->create()
                ->addFieldToFilter('seller_id', $sellerid)
                ->addFieldToFilter('order_id', $lastOrderId)
                ->addFieldToFilter('paid_status', 0)
                ->addFieldToFilter('cpprostatus', 0);

            foreach ($collection as $row) {
                $taxAmount = $row['total_tax'];
                $vendorTaxAmount = 0;
                if ($this->helper->getConfigTaxManage()) {
                    $vendorTaxAmount = $taxAmount;
                }
                $actparterprocost += $row->getActualSellerAmount() +
                $vendorTaxAmount +
                $codCharges +
                $shippingCharges;

                $totalamount += $row->getTotalAmount() +
                $taxAmount +
                $codCharges +
                $shippingCharges;

                $codCharges = 0;
                $shippingCharges = 0;
                $sellerId = $row->getSellerId();
            }
            if ($actparterprocost) {
                $collectionverifyread = $this->salePerPartnerCollection->create()
                    ->addFieldToFilter('seller_id', $sellerId);

                if ($collectionverifyread->getSize() >= 1) {
                    foreach ($collectionverifyread as $verifyrow) {
                        if ($verifyrow->getAmountRemain() >= $actparterprocost) {
                            $totalremain = $verifyrow->getAmountRemain() - $actparterprocost;
                        } else {
                            $totalremain = 0;
                        }
                        $totalcommission = $verifyrow->getTotalCommission() - (
                            $totalamount - $actparterprocost
                        );
                        $totalsale = $verifyrow->getTotalSale() - $totalamount;
                        $verifyrow->setTotalSale($totalsale);
                        $verifyrow->setAmountRemain($totalremain);
                        $verifyrow->setTotalCommission($totalcommission);
                        $verifyrow->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment revertSellerPayment : ".$e->getMessage());
        }
    }

    /**
     * get cash on delivery charges if applied
     *
     * @param  $paymentCode payment method
     * @param  $tracking Mp Order data
     * @param  $codCharges charges for cash on delivery
     * @return int|float
     */
    public function getCodChargesIfApplied($paymentCode, $tracking, $codCharges)
    {
        try {
            if ($paymentCode == 'mpcashondelivery') {
                $codCharges += $tracking->getCodCharges();
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getCodChargesIfApplied : ".$e->getMessage());
        }
        return $codCharges;
    }

    /**
     * Get Order item array data.
     *
     * @param  $order Order
     * @param  $items array of ordered item ids
     * @return array
     */
    public function getItemQtys($order, $items)
    {
        try {
            $data = [];
            $subtotal = 0;
            $baseSubtotal = 0;
            foreach ($order->getAllItems() as $item) {
                if (in_array($item->getItemId(), $items)) {
                    $data[$item->getItemId()] = (int)(
                        $item->getQtyOrdered() - $item->getQtyInvoiced()
                    );

                    $_item = $item;

                    // for bundle product
                    $bundleitems = array_merge(
                        [$_item],
                        $_item->getChildrenItems()
                    );

                    if ($_item->getParentItem()) {
                        continue;
                    }

                    if ($_item->getProductType() == 'bundle') {
                        foreach ($bundleitems as $_bundleitem) {
                            if ($_bundleitem->getParentItem()) {
                                $data[$_bundleitem->getItemId()] = (int)(
                                    $_bundleitem->getQtyOrdered() - $item->getQtyInvoiced()
                                );
                            }
                        }
                    }
                    $subtotal += $_item->getRowTotal();
                    $baseSubtotal += $_item->getBaseRowTotal();
                } else {
                    if (!$item->getParentItemId()) {
                        $data[$item->getItemId()] = 0;
                    }
                }
            }

            return [
                'data' => $data,
                'subtotal' => $subtotal,
                'baseSubtotal' => $baseSubtotal
            ];
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getItemQtys : ".$e->getMessage());
        }
    }

    /**
     * calculate total tax according to item quantities
     *
     * @param  $itemsarray array contains item ids with quantities to invoice
     * @param  $tax tax for single quantity of an item
     * @return int|float
     */
    public function calculateTax(
        $itemsarray,
        $tax
    ) {
        $newTax = 0;
        try {
            foreach ($itemsarray['data'] as $value) {
                if ((int)$value!==0
                    && $value!==""
                    && $newTax!==null
                ) {
                    $newTax += $tax * (int)$value;
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment calculateTax : ".$e->getMessage());
        }
        return $newTax;
    }

    /**
     * Create remaining order shipping amount invoice.
     *
     * @param  $order
     * @param  $transactionId
     * @param  $baseShippingAmount
     * @return void
     */
    public function createShippingInvoice($order, $transactionId, $adminBaseShippingAmount)
    {
        try {
            if ($order->getBaseTotalDue()) {
                $baseShippingAmount = $order->getBaseTotalDue();
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setTransactionId($transactionId);
                $invoice->setRequestedCaptureCase('online');
                if ($adminBaseShippingAmount>0) {
                    // $invoice->setShippingAmount($adminBaseShippingAmount);
                    $invoice->setBaseShippingInclTax($adminBaseShippingAmount);
                    $invoice->setBaseShippingAmount($adminBaseShippingAmount);
                }

                // $invoice->setGrandTotal($baseShippingAmount);
                $invoice->setBaseGrandTotal($baseShippingAmount);
                $invoice->register();
                $invoice->save();
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = $this->dbTransaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();

                $invoiceId = $invoice->getId();

                $this->invoiceSender->send($invoice);

                $orderId = $order->getId();
                $order = $this->orderRepository->get($orderId);
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice #%1.', $invoice->getId())
                );
                $order->setIsCustomerNotified(true);
                $order->setState('processing');
                $order->setStatus('processing');
                $order->save();
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment createShippingInvoice : ".$e->getMessage());
        }
    }

    /**
     * Create order invoice.
     *
     * @param  $order
     * @param  $transactionId
     * @return void
     */
    public function createOrderInvoice($order, $transactionId)
    {
        try {
            if ($order->canInvoice()) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setTransactionId($transactionId);
                $invoice->setRequestedCaptureCase('online');
                $invoice->register();
                $invoice->save();
                $transactionSave = $this->dbTransaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
                //send notification code
                $orderId = $order->getId();
                $order = $this->orderRepository->get($orderId);
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice #%1.', $invoice->getId())
                );
                $order->setIsCustomerNotified(true);
                $order->setState('processing');
                $order->setStatus('processing');
                $order->save();
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment createOrderInvoice : ".$e->getMessage());
        }
    }

    /**
     * prepare data to generate invoice seller wise
     *
     * @param  $orderId Order Id
     * @return array
     */
    public function getSellerOrderData($orderId = '')
    {
        try {
            $flag = 0;
            $idsToCreateInvoice = [];
            $sellerIdsData = [];

            $ordercollection = $this->salesListCollection->create()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('cpprostatus', 0);

            foreach ($ordercollection as $orderitem) {
                $flag = 1;
                array_push($sellerIdsData, $orderitem->getSellerId());
                if (isset($idsToCreateInvoice[$orderitem->getSellerId()])
                    && $idsToCreateInvoice[$orderitem->getSellerId()]
                ) {
                    $idsToCreateInvoice[$orderitem->getSellerId()] += $orderitem->getActualSellerAmount();
                } else {
                    $idsToCreateInvoice[$orderitem->getSellerId()] = $orderitem->getActualSellerAmount();
                }
            }

            return [
                'seller_ids_data' => $sellerIdsData,
                'seller_amount_data' => $idsToCreateInvoice,
                'flag' => $flag
            ];
        } catch (\Exception $e) {
            $this->logData("Helper_Payment getSellerOrderData : ".$e->getMessage());
        }
    }

    /**
     * Create seller items invoice.
     *
     * @param  $order
     * @param  $transactionId
     * @param  $shippingAmount
     * @return string
     */
    public function createSellerOrderInvoice(
        $order,
        $itemsarray,
        $transactionId,
        $paymentCode,
        $shippingAmount,
        $sellerId,
        $codCharges,
        $tax,
        $sellerCouponAmount
    ) {
        try {
            if ($order->canInvoice()) {
                $orderId = $order->getId();
                $newTax = 0;
                $subtotal = $itemsarray['subtotal'];
                $baseSubtotal = $itemsarray['baseSubtotal'];

                $baseShippingTaxAmount = $order->getBaseShippingTaxAmount();
                $totalQtyOrdered = $order->getTotalQtyOrdered();
                if ($baseShippingTaxAmount !== null
                    && $baseShippingTaxAmount !== ''
                ) {
                    $newtax = $baseShippingTaxAmount / $totalQtyOrdered;
                    $newTax = $this->calculateTax(
                        $itemsarray,
                        $newtax
                    );
                }

                $taxAmount = $tax+$newTax;

                $orderShippingAmount = $shippingAmount;
                $orderCouponAmount = $sellerCouponAmount;
                $ordererdTaxAmount = $taxAmount;
                $ordererdCodCharges = $codCharges;

                if ($order->getOrderCurrencyCode() !== $order->getBaseCurrencyCode()) {
                    $orderShippingAmount = $this->getCurrentCurrencyAmountbyorder(
                        $order,
                        $shippingAmount
                    );
                    $ordererdTaxAmount = $this->getCurrentCurrencyAmountbyorder(
                        $order,
                        $tax+$newTax
                    );
                    $ordererdCodCharges = $this->getCurrentCurrencyAmountbyorder(
                        $order,
                        $codCharges
                    );
                    if ($sellerCouponAmount > 0) {
                        $orderCouponAmount = $this->getCurrentCurrencyAmountbyorder(
                            $order,
                            $sellerCouponAmount
                        );
                    }
                }

                $grandTotal = $subtotal+$orderShippingAmount+$ordererdCodCharges+$ordererdTaxAmount-$orderCouponAmount;
                $grandTotal = round($grandTotal, 2);

                $baseGrandTotal = $baseSubtotal+$shippingAmount+$codCharges+$taxAmount-$sellerCouponAmount;
                $baseGrandTotal = round($baseGrandTotal, 2);

                $invoice = $this->invoiceService->prepareInvoice($order, $itemsarray['data']);
                $invoice->setTransactionId($transactionId);
                $invoice->setRequestedCaptureCase('online');

                $invoice->setShippingAmount($orderShippingAmount);
                $invoice->setBaseShippingAmount($shippingAmount);

                $invoice->setShippingInclTax($orderShippingAmount);
                $invoice->setBaseShippingInclTax($shippingAmount);
                
                // $invoice->setTaxAmount($ordererdTaxAmount);
                // $invoice->setBaseTaxAmount($taxAmount);

                $invoice->setSubtotal($subtotal);
                // $invoice->setSubtotalInclTax($subtotal);

                $invoice->setBaseSubtotal($baseSubtotal);
                // $invoice->setBaseSubtotalInclTax($baseSubtotal);
                
                if ($paymentCode == 'mpcashondelivery') {
                    $invoice->setMpcashondelivery($codCharges);
                }

                $invoice->setGrandTotal($grandTotal);
                $invoice->setBaseGrandTotal($baseGrandTotal);
                $newTax=0;

                $invoice->register();
                $invoice->save();
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = $this->dbTransaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();

                $invoiceId = $invoice->getId();

                $this->invoiceSender->send($invoice);

                $order = $this->orderRepository->get($orderId);
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice #%1.', $invoice->getId())
                );
                $order->setIsCustomerNotified(true);
                $order->setState('processing');
                $order->setStatus('processing');
                $order->save();

                /*--update mpcod table records--*/
                if ($paymentCode == 'mpcashondelivery') {
                    $saleslistColl = $this->salesListCollection->create()
                        ->addFieldToFilter(
                            'order_id',
                            ['eq' => $orderId]
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $sellerId]
                        );
                    foreach ($saleslistColl as $saleslist) {
                        $saleslist->setCollectCodStatus(1);
                        $saleslist->save();
                    }
                }
                $trackingcol1 = $this->mpOrdersCollection->create()
                    ->addFieldToFilter('order_id', $orderId)
                    ->addFieldToFilter('seller_id', $sellerId);
                foreach ($trackingcol1 as $row) {
                    $row->setInvoiceId($invoiceId);
                    $row->save();
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment createSellerOrderInvoice : ".$e->getMessage());
        }
    }

    /**
     * saves payment transaction
     *
     * @param  $order order
     * @param  $additionalInfo transaction data
     * @param  $transId payment gateway transaction Id
     * @return int
     */
    public function saveTransaction($order, $additionalInfo, $transId)
    {
        try {
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );

            $message = __('The captured amount is %1.', $formatedPrice);

            $payment = $order->getPayment();

            $payment->setLastTransId($transId);
            $payment->setTransactionId($transId);
            $payment->setAdditionalInformation(
                [Transaction::RAW_DETAILS => $additionalInfo]
            );

            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transId)
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => $additionalInfo]
                )
                ->setFailSafe(true)
                ->build(Transaction::TYPE_CAPTURE);

            $trId = $transaction->save()->getId();
            if ($trId) {
                $payment->addTransactionCommentsToOrder(
                    $transaction,
                    $message
                );
                $payment->setParentTransactionId(null);
                $payment->save();
                $order->save();
            }
            return $trId;
        } catch (\Exception $e) {
            $this->logData("Helper_Payment saveTransaction : ".$e->getMessage());
        }
    }

    /**
     * prepare shipping split data
     *
     * @param  $quote current quote
     * @return array
     */
    public function prepareFinalShipping($quote)
    {
        
        $finalShipping = [];

        try {
            $shipData = $this->getShippingData($quote);
            $newvar = $shipData['newvar'];
            $shipmeth = $shipData['shipping_method'];
            $shippingInfo = $shipData['shipinf'];

            if (!empty($shippingInfo)) {
                foreach ($shippingInfo as $key => $info) {
                    $sellerId = $info['seller'];
                    $finalShipping[$sellerId]['amount'] = $info['amount'];
                    $finalShipping[$sellerId]['method'] = $info['method'];
                }
            }
            
            if ($newvar == "" && empty($finalShipping)) {
                $shipmethod = explode('_', $shipmeth, 2);
                $groups =  $quote->getShippingAddress()
                    ->getGroupedAllShippingRates();

                foreach ($groups as $code => $rates) {
                    foreach ($rates as $rate) {
                        if ($rate->getCode() == $shipmeth && $quote->getShippingAddress()->getShippingMethod()==$shipmeth) {
                            $shipPrice = $quote->getShippingAddress()->getBaseShippingAmount();
                            // $shipPrice = $rate->getPrice();
                            $taxAmount = $this->calculateTaxByPercent($shipPrice, $quote);
                            $finalShipping[0]['amount'] = $shipPrice + $taxAmount;
                            $finalShipping[0]['method'] = $shipmethod[1];
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logData("Helper_Payment prepareFinalShipping : ".$e->getMessage());
        }
        return $finalShipping;
    }

    /**
     * prepare payment split data
     *
     * @param  $quote current quote
     * @return array
     */
    public function prepareSplitPaymentData($quote)
    {
        try {
            $cartdata = [];
            $commission = 0;
            $taxAmount = 0;
            $sellerTaxAmount = 0;
            $adminTaxAmount = 0;
            $commissionDetail =[];
            $onlyAdminTaxAmount = 0;

            $shippingData = $this->prepareFinalShipping($quote);

            foreach ($quote->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getProductType()=="bundle") {
                    $childDiscountAmount = $this->calculateBundleProductDiscount($item->getId(), $quote);
                }
                $product = $item->getProduct();
                $invoiceprice = $item->getBaseRowTotal();
                $singleItemPrice = $item->getBasePrice();

                $commissionData = $this->getCommissionData($item);
                $commissionData = $this->updateCommissionData($commissionData);
                $tempcoms = $commissionData['tempcoms'];
                $commissionDetail = $commissionData['commissionDetail'];

                $commission += $tempcoms;
                $price = $invoiceprice - $tempcoms;

                if (!isset($commissionDetail['id'])) {
                    $commissionDetail['id'] = 0;
                }
                $sellerdetails['id'] = $commissionDetail['id'];
                $sellerdetails['comission'] = $commission;
                $productprice = floatval($price);

                if (!$this->helper->getConfigTaxManage()) {
                    $adminTaxAmount += $item->getBaseTaxAmount();
                } else {
                    $sellerTaxAmount = $item->getBaseTaxAmount();
                }

                $realSellerId = $this->getRealSellerId($item);
                $couponAmount = $this->getSellerCouponAmount($realSellerId);
                $creditPoints = $this->getCreditPoints($realSellerId);
                $totalDiscountAmount = $couponAmount + $creditPoints;

                $totalSellerAmount = $productprice + $sellerTaxAmount;
                $onlyAdminAmount = $productprice;
                $onlyAdminTaxAmount += $sellerTaxAmount;
                $itemDiscountAmount = $item->getBaseDiscountAmount();

                if ($itemDiscountAmount <= 0 && isset($childDiscountAmount) && $childDiscountAmount > 0) {
                    $itemDiscountAmount = $childDiscountAmount;
                }
                if ($itemDiscountAmount > 0) {
                    $itemDiscountAmount = -$itemDiscountAmount;
                    $totalDiscountAmount += $itemDiscountAmount;
                }

                if (empty($cartdata)) {
                    if ($sellerdetails['id'] == 0) {
                        if ($this->helper->getConfigTaxManage()) {
                            $adminTaxAmount += $sellerTaxAmount;
                        }
                        $cartdata[$sellerdetails['id']]['items'][$item->getId()]=[
                            'product_id'=>$product->getId(),
                            'qty'=>$item->getQty(),
                            'amount'=>$onlyAdminAmount,
                            'discount' => ($totalDiscountAmount < 0) ? $totalDiscountAmount : 0
                        ];
                        $cartdata[$sellerdetails['id']]['total'] = $onlyAdminAmount;
                    } else {
                        $cartdata[$sellerdetails['id']]['items'][$item->getId()]=[
                            'product_id'=>$product->getId(),
                            'qty'=>$item->getQty(),
                            'amount'=>$totalSellerAmount,
                            'discount' => ($totalDiscountAmount < 0) ? $totalDiscountAmount : 0
                        ];
                        $cartdata[$sellerdetails['id']]['total'] = $totalSellerAmount;
                    }
                    if (!empty($shippingData[$sellerdetails['id']])) {
                        $cartdata[$sellerdetails['id']]['shipping'] = $shippingData[$sellerdetails['id']];
                        $cartdata[$sellerdetails['id']]['total'] += $shippingData[$sellerdetails['id']]['amount'];
                    }
                } else {
                    $flag=true;
                    foreach ($cartdata as $key => $values) {
                        if ($key==$sellerdetails['id']) {
                            if ($key == 0) {
                                if ($this->helper->getConfigTaxManage()) {
                                    $adminTaxAmount += $sellerTaxAmount;
                                }
                                $cartdata[$key]['items'][$item->getId()]=[
                                    'product_id'=>$product->getId(),
                                    'qty'=>$item->getQty(),
                                    'amount'=>$onlyAdminAmount,
                                    'discount' => ($totalDiscountAmount < 0) ? $totalDiscountAmount : 0
                                ];
                                $cartdata[$key]['total'] += $onlyAdminAmount;
                            } else {
                                $cartdata[$key]['items'][$item->getId()]=[
                                    'product_id'=>$product->getId(),
                                    'qty'=>$item->getQty(),
                                    'amount'=>$totalSellerAmount,
                                    'discount' => ($totalDiscountAmount < 0) ? $totalDiscountAmount : 0
                                ];
                                $cartdata[$key]['total'] += $totalSellerAmount;
                            }
                            if (!empty($shippingData[$key])) {
                                $cartdata[$key]['shipping'] = $shippingData[$key];
                                $cartdata[$key]['total'] += $shippingData[$key]['amount'];
                            }
                            $flag=false;
                        }
                    }
                    if ($flag) {
                        if ($sellerdetails['id'] == 0) {
                            if ($this->helper->getConfigTaxManage()) {
                                $adminTaxAmount += $sellerTaxAmount;
                            }
                            $cartdata[$sellerdetails['id']]['items'][$item->getId()]=[
                                'product_id'=>$product->getId(),
                                'qty'=>$item->getQty(),
                                'amount'=>$onlyAdminAmount,
                                'discount' => ($totalDiscountAmount < 0) ? $totalDiscountAmount : 0
                            ];
                            if (!empty($cartdata[$sellerdetails['id']]['total'])) {
                                $cartdata[$sellerdetails['id']]['total'] += $onlyAdminAmount;
                            } else {
                                $cartdata[$sellerdetails['id']]['total'] = $onlyAdminAmount;
                            }
                        } else {
                            $cartdata[$sellerdetails['id']]['items'][$item->getId()]=[
                                'product_id'=>$product->getId(),
                                'qty'=>$item->getQty(),
                                'amount'=>$totalSellerAmount,
                                'discount' => ($totalDiscountAmount < 0) ? $totalDiscountAmount : 0
                            ];
                            if (!empty($cartdata[$sellerdetails['id']]['total'])) {
                                $cartdata[$sellerdetails['id']]['total'] += $totalSellerAmount;
                            } else {
                                $cartdata[$sellerdetails['id']]['total'] = $totalSellerAmount;
                            }
                        }
                        if (!empty($shippingData[$sellerdetails['id']])) {
                            $cartdata[$sellerdetails['id']]['shipping'] = $shippingData[$sellerdetails['id']];
                            $cartdata[$sellerdetails['id']]['total'] += $shippingData[$sellerdetails['id']]['amount'];
                        }
                    }
                }
            }
            if ($commission > 0) {
                $cartdata[0]['commission'] = $commission + $adminTaxAmount;
                if (!empty($cartdata[0]['total'])) {
                    $cartdata[0]['total'] += $commission + $adminTaxAmount;
                } else {
                    $cartdata[0]['total'] = $commission + $adminTaxAmount;
                }
            } elseif (count($cartdata)==1 && !empty($cartdata[0])) {
                if ($onlyAdminTaxAmount > 0) {
                    $cartdata[0]['tax'] = $onlyAdminTaxAmount;
                }
            }
            return $cartdata;
        } catch (\Exception $e) {
            $this->logData("Helper_Payment prepareSplitPaymentData : ".$e->getMessage());
            return [];
        }
    }

    /**
     * updates commission data
     *
     * @param  $commissionData
     * @return array
     */
    public function updateCommissionData($commissionData)
    {
        try {
            $tempcoms = $commissionData['tempcoms'];
            $commissionDetail = $commissionData['commissionDetail'];
            if (!$tempcoms) {
                $commissionDetail = $this->getSellerDetail($commissionData['product_id']);

                if ($commissionDetail['id'] !== 0
                    && $commissionDetail['commission'] !== 0
                ) {
                    $tempcoms = round(
                        ($commissionData['row_total'] * $commissionDetail['commission']) / 100,
                        2
                    );
                }
            }
            return [
                'tempcoms' => $tempcoms,
                'commissionDetail' => $commissionDetail
            ];
        } catch (\Exception $e) {
            $this->logData("Helper_Payment updateCommissionData : ".$e->getMessage());
            return $commissionData;
        }
    }

    public function getCurrentCurrencyAmountbyorder($order, $price)
    {
        /*
        * Get Current Store Currency Rate
        */
        $currentCurrencyCode = $order->getOrderCurrencyCode();
        $baseCurrencyCode = $order->getBaseCurrencyCode();
        $allowedCurrencies = $this->helper->getConfigAllowCurrencies();
        $rates = $this->helper->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }
        return $price * $rates[$currentCurrencyCode];
    }

    public function logData($data)
    {
        $this->logger->info($data);
    }
}