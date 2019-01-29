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

namespace Webkul\Marketplace\Model\Plugin\Order\Invoice\Total;

class Shipping
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $currentInvoice
     * @return $this
     */
    public function aroundCollect(
        \Magento\Sales\Model\Order\Invoice\Total\Shipping $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Invoice $currentInvoice
    ) {
        $order = $currentInvoice->getOrder();
        $orderShippingAmount = $order->getShippingAmount();
        $baseOrderShippingAmount = $order->getBaseShippingAmount();
        $shippingInclTax = $order->getShippingInclTax();
        $baseShippingInclTax = $order->getBaseShippingInclTax();
        if ($orderShippingAmount) {
            /**
             * Calculate shipping amount in previous invoices
             */
            foreach ($order->getInvoiceCollection() as $previousInvoice) {
                $orderShippingAmount = $orderShippingAmount - $previousInvoice->getShippingAmount();
                $baseOrderShippingAmount = $baseOrderShippingAmount - $previousInvoice->getBaseShippingAmount();
                $shippingInclTax = $shippingInclTax - $previousInvoice->getShippingInclTax();
                $baseShippingInclTax = $baseShippingInclTax - $previousInvoice->getBaseShippingInclTax();
            }
            $currentInvoice->setShippingAmount($orderShippingAmount);
            $currentInvoice->setBaseShippingAmount($baseOrderShippingAmount);
            $currentInvoice->setShippingInclTax($shippingInclTax);
            $currentInvoice->setBaseShippingInclTax($baseShippingInclTax);

            $currentInvoice->setGrandTotal($currentInvoice->getGrandTotal() + $orderShippingAmount);
            $currentInvoice->setBaseGrandTotal($currentInvoice->getBaseGrandTotal() + $baseOrderShippingAmount);
        }
        return $this;
    }
}
