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

namespace Webkul\Marketplace\Ui\Component\Listing\Columns\Frontend;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Magento\Sales\Model\Order\ItemRepository;
use Magento\Sales\Model\OrderRepository;

/**
 * Class OrderHistoryProDetails.
 */
class OrderHistoryProDetails extends Column
{
    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * @var ItemRepository
     */
    protected $orderItemRepository;
    
    /**
     * @var OrderRepository
     */
    public $orderRepository;

    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param HelperData         $helper
     * @param CollectionFactory  $collectionFactory
     * @param ItemRepository     $orderItemRepository
     * @param OrderRepository    $orderRepository
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        HelperData $helper,
        CollectionFactory $collectionFactory,
        ItemRepository $orderItemRepository,
        OrderRepository $orderRepository,
        array $components = [],
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_collectionFactory = $collectionFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $taxToSeller = $this->_helper->getConfigTaxManage();
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                // calculate order actual_seller_amount in base currency
                $appliedCouponAmount = $item['applied_coupon_amount'];
                $taxToSeller = $item['tax_to_seller'];
                $shippingamount = $item['shipping_charges'];
                $refundedShippingAmount = $item['refunded_shipping_charges'];
                $totalshipping = $shippingamount - $refundedShippingAmount;
                $taxAmount = $item['total_tax'];
                $vendorTaxAmount = 0;
                if ($taxToSeller) {
                    $vendorTaxAmount = $taxAmount;
                }
                if ($item['actual_seller_amount'] * 1) {
                    $taxShippingTotal = $vendorTaxAmount + $totalshipping - $appliedCouponAmount;
                    $item['actual_seller_amount'] = $item['actual_seller_amount'] + $taxShippingTotal;
                } else {
                    if ($totalshipping * 1) {
                        $item['actual_seller_amount'] = $totalshipping - $appliedCouponAmount;
                    }
                }
                // calculate order total in ordered currency
                $order = $this->orderRepository->get($item['order_id']);

                $item['purchased_actual_seller_amount'] = $item['currency_rate'] * $item['actual_seller_amount'];

                // Updated product name
                $item['magepro_name'] = $this->getpronamebyorder(
                    $item['order_id'],
                    $item['seller_id']
                );
            }
        }

        return $dataSource;
    }

    public function getpronamebyorder($orderId, $sellerId)
    {
        $collection = $this->_collectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        )
        ->addFieldToFilter(
            'order_id',
            $orderId
        );
        $productName = '';
        foreach ($collection as $res) {
            if ($res->getParentItemId()) {
                continue;
            }
            $item = $this->orderItemRepository->get($res->getOrderItemId());
            $url = '';
            // Updated product name
            $result = [];
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
        }

        return $productName;
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
                if (!$this->getPrintStatus()) {
                    $_formatedOptionValue = $_option;
                    $class = '';
                    if (isset($_formatedOptionValue['full_view'])) {
                        $class = 'truncated';
                    }
                    $proOptionData .= '<dd class="'.$class.'">'.$_option['value'];
                    if (isset($_formatedOptionValue['full_view'])) {
                        $proOptionData .= '<div class="truncated_full_value">
                        <dl class="item-options"><dt>'.
                        $_option['label'].
                        '</dt><dd>'.
                        $_formatedOptionValue['full_view'].
                        '</dd></dl></div>';
                    }
                    $proOptionData .= '</dd>';
                } else {
                    $printValue = $_option['print_value'];
                    $proOptionData .= '<dd>'.
                    nl2br((isset($printValue) ? $printValue : $_option['value'])).
                    '</dd>';
                }
            }
            $proOptionData .= '</dl>';
            $productName = $productName.'<br/>'.$proOptionData;
        } else {
            $productName = $productName.'<br/>';
        }

        return $productName;
    }
}
