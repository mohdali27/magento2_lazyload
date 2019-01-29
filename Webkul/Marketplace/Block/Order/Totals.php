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
namespace Webkul\Marketplace\Block\Order;

use Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection;

class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var Collection
     */
    protected $orderCollection;

    /**
     * Associated array of seller order totals
     * array(
     *  $totalCode => $totalObject
     * )
     *
     * @var array
     */
    protected $_totals;

    /**
     * @param \Webkul\Marketplace\Helper\Data                   $helper
     * @param \Magento\Framework\Registry                       $coreRegistry
     * @param Collection                                        $orderCollection
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array                                             $data
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        Collection $orderCollection,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->orderCollection = $orderCollection;
        parent::__construct(
            $context,
            $coreRegistry,
            $data
        );
    }

    /**
     * Get totals source object
     *
     * @return Order
     */
    public function getSource()
    {
        $collection = $this->orderCollection
        ->addFieldToFilter(
            'main_table.order_id',
            $this->getOrder()->getId()
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->helper->getCustomerId()
        )->getSellerOrderTotals();
        return $collection;
    }


    protected function _initTotals()
    {
        $this->_totals = [];
        $source = $this->getSource();
        $order = $this->getOrder();
        if (isset($source[0])) {
            $source = $source[0];
            $taxToSeller = $source['tax_to_seller'];
            $currencyRate = $source['currency_rate'];
            $subtotal = $source['magepro_price'];
            $adminSubtotal = $source['total_commission'];
            $shippingamount = $source['shipping_charges'];
            $refundedShippingAmount = $source['refunded_shipping_charges'];
            $couponAmount = $source['applied_coupon_amount'];
            $totaltax = $source['total_tax'];
            $totalCouponAmount = $source['coupon_amount'];

            $admintotaltax = 0;
            $vendortotaltax = 0;
            if (!$taxToSeller) {
                $admintotaltax = $totaltax;
            } else {
                $vendortotaltax = $totaltax;
            }

            $totalOrdered = $this->getOrderedAmount($source);

            $vendorSubTotal = $this->getVendorSubTotal($source);

            $adminSubTotal = $this->getAdminSubTotal($source);

            $this->_totals = [];

            $this->_totals['subtotal'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'subtotal',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $subtotal),
                    'label' => __('Subtotal')
                ]
            );

            $this->_totals['shipping'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'shipping',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $shippingamount),
                    'label' => __('Shipping & Handling')
                ]
            );

            $this->_totals['discount'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'discount',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $totalCouponAmount),
                    'label' => __('Discount')
                ]
            );

            $this->_totals['tax'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'tax',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $totaltax),
                    'label' => __('Total Tax')
                ]
            );

            $this->_totals['ordered_total'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'ordered_total',
                    'strong' => 1,
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $totalOrdered),
                    'label' => __('Total Ordered Amount')
                ]
            );

            if ($order->isCurrencyDifferent()) {
                $this->_totals['base_ordered_total'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'base_ordered_total',
                        'is_base' => 1,
                        'strong' => 1,
                        'value' => $totalOrdered,
                        'label' => __('Total Ordered Amount(in base currency)')
                    ]
                );
            }

            $this->_totals['vendor_total'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'vendor_total',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $vendorSubTotal),
                    'label' => __('Total Vendor Amount')
                ]
            );

            if ($order->isCurrencyDifferent()) {
                $this->_totals['base_vendor_total'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'base_vendor_total',
                        'is_base' => 1,
                        'value' => $vendorSubTotal,
                        'label' => __('Total Vendor Amount(in base currency)')
                    ]
                );
            }

            $this->_totals['admin_commission'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'admin_commission',
                    'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $adminSubTotal),
                    'label' => __('Total Admin Commission')
                ]
            );

            if ($order->isCurrencyDifferent()) {
                $this->_totals['base_admin_commission'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'base_admin_commission',
                        'is_base' => 1,
                        'value' => $adminSubTotal,
                        'label' => __('Total Admin Commission(in base currency)')
                    ]
                );
            }
        }
    }

    /**
     * get seller order totals array
     *
     * @param array|null $area
     * @return array
     */
    public function getOrderTotals($area = null)
    {
        $orderTotals = [];
        if ($area === null) {
            $orderTotals = $this->_totals;
        } else {
            $area = (string)$area;
            foreach ($this->_totals as $orderTotal) {
                $totalArea = (string)$orderTotal->getArea();
                if ($totalArea == $area) {
                    $this->_totals[] = $orderTotal;
                }
            }
        }
        return $orderTotals;
    }

    public function getOrderedAmount($source)
    {
        $subtotal = $source['magepro_price'];
        $shippingamount = $source['shipping_charges'];
        $totalCouponAmount = $source['coupon_amount'];
        $totaltax = $source['total_tax'];
        return $subtotal+$shippingamount+$totaltax-$totalCouponAmount;
    }

    public function getVendorSubTotal($source)
    {
        $taxToSeller = $source['tax_to_seller'];
        $vendorSubtotal = $source['actual_seller_amount'];
        $shippingamount = $source['shipping_charges'];
        $refundedShippingAmount = $source['refunded_shipping_charges'];
        $couponamount = $source['applied_coupon_amount'];
        $totaltax = $source['total_tax'];

        $vendortotaltax = 0;
        if ($taxToSeller) {
            $vendortotaltax = $totaltax;
        }
        return $vendorSubtotal+$shippingamount+$vendortotaltax-$refundedShippingAmount-$couponamount;
    }

    public function getAdminSubTotal($source)
    {
        $taxToSeller = $source['tax_to_seller'];
        $adminSubtotal = $source['total_commission'];
        $totaltax = $source['total_tax'];

        $admintotaltax = 0;
        if (!$taxToSeller) {
            $admintotaltax = $totaltax;
        }
        return $adminSubtotal+$admintotaltax;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        $paymentCode = '';
        if($this->_order->getPayment()){
            $paymentCode = $this->getOrder()->getPayment()->getMethod();
        }
        if ($paymentCode == 'mpcashondelivery') {
            return 'colspan="8" class="mark"';
        }
        return 'colspan="7" class="mark"';
    }

    /**
     * Format total value based on order currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatValue($total)
    {
        if ($total->getIsBase()) {
            if (!$total->getIsFormated()) {
                return $this->getOrder()->formatBasePrice($total->getValue());
            }
        } else {
            if (!$total->getIsFormated()) {
                return $this->getOrder()->formatPrice($total->getValue());
            }
        }
        return $total->getValue();
    }
}
