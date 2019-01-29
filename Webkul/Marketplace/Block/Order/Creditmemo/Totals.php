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
namespace Webkul\Marketplace\Block\Order\Creditmemo;

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
        return $this->getCreditmemo();
    }

    /**
     * Retrieve current creditmemo model instance.
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }


    protected function _initTotals()
    {
        $this->_totals = [];
        $creditmemo = $this->getSource();
        $order = $this->getOrder();

        $this->_totals = [];

        $this->_totals['subtotal'] = new \Magento\Framework\DataObject(
            [
                'code' => 'subtotal',
                'value' => $creditmemo->getSubtotal(),
                'label' => __('Subtotal')
            ]
        );

        $this->_totals['discount'] = new \Magento\Framework\DataObject(
            [
                'code' => 'discount',
                'value' => $creditmemo->getDiscountAmount(),
                'label' => __('Discount')
            ]
        );

        $this->_totals['tax'] = new \Magento\Framework\DataObject(
            [
                'code' => 'tax',
                'value' => $creditmemo->getTaxAmount(),
                'label' => __('Total Tax')
            ]
        );

        $this->_totals['shipping'] = new \Magento\Framework\DataObject(
            [
                'code' => 'shipping',
                'value' => $creditmemo->getShippingAmount(),
                'label' => __('Shipping & Handling')
            ]
        );

        $this->_totals['adjustment_positive'] = new \Magento\Framework\DataObject(
            [
                'code' => 'adjustment_positive',
                'value' => $creditmemo->getAdjustmentPositive(),
                'label' => __('Adjustment Refund')
            ]
        );

        $this->_totals['adjustment_negative'] = new \Magento\Framework\DataObject(
            [
                'code' => 'adjustment_negative',
                'value' => $creditmemo->getAdjustmentNegative(),
                'label' => __('Adjustment Fee')
            ]
        );

        $this->_totals['grand_total'] = new \Magento\Framework\DataObject(
            [
                'code' => 'grand_total',
                'strong' => 1,
                'value' => $creditmemo->getGrandTotal(),
                'label' => __('Grand Total')
            ]
        );
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
            return 'colspan="7" class="mark"';
        }
        return 'colspan="6" class="mark"';
    }
}
