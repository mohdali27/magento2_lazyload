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
namespace Webkul\Marketplace\Block\Order\Totals;

use Magento\Sales\Model\Order;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection;

class Cod extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var Collection
     */
    protected $orderCollection;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data                  $helper
     * @param Collection                                       $orderCollection
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $helper,
        Collection $orderCollection,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->orderCollection = $orderCollection;
        parent::__construct($context, $data);
    }

    /**
     * Initialize seller's order totals relates with cod
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $source = $this->_source;
        if (isset($source[0])) {
            $source = $source[0];
            $currencyRate = $source['currency_rate'];

            $paymentCode = '';
            if($this->_order->getPayment()){
                $paymentCode = $this->getOrder()->getPayment()->getMethod();
            }
            if ($paymentCode == 'mpcashondelivery') {
                $this->_addCodCharges($currencyRate);
                $this->_initOrderedTotal($currencyRate);
                $this->_initVendorTotal($currencyRate);
                if ($this->_order->isCurrencyDifferent()) {
                    $this->_initBaseOrderedTotal($currencyRate);
                    $this->_initBaseVendorTotal($currencyRate);
                }
            }
        }

        return $this;
    }

    /**
     * Add Cod total string
     *
     * @param string $currencyRate
     * @param string $after
     */
    protected function _addCodCharges($currencyRate, $after = 'discount')
    {
        $codchargesData = $this->orderCollection
        ->addFieldToFilter(
            'main_table.order_id',
            $this->getOrder()->getId()
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->helper->getCustomerId()
        )->getTotalSellerCodCharges();

        $codchargesTotal = $codchargesData[0]['cod_charges'];

        $codTotal = new \Magento\Framework\DataObject(
            [
                'code' => 'cod',
                'base_value' => $codchargesTotal,
                'value' => $this->helper->getCurrentCurrencyPrice($currencyRate, $codchargesTotal),
                'label' => __('Total COD Charges')
            ]
        );
        $this->getParentBlock()->addTotal($codTotal, $after);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initOrderedTotal()
    {
        $parent = $this->getParentBlock();
        $cod = $parent->getTotal('cod');
        $total = $parent->getTotal('ordered_total');
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $cod->getValue());
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initBaseOrderedTotal()
    {
        $parent = $this->getParentBlock();
        $cod = $parent->getTotal('cod');
        $total = $parent->getTotal('base_ordered_total');
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $cod->getBaseValue());
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initVendorTotal()
    {
        $parent = $this->getParentBlock();
        $cod = $parent->getTotal('cod');
        $total = $parent->getTotal('vendor_total');
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $cod->getValue());
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initBaseVendorTotal()
    {
        $parent = $this->getParentBlock();
        $cod = $parent->getTotal('cod');
        $total = $parent->getTotal('base_vendor_total');
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $cod->getBaseValue());
        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }
}
