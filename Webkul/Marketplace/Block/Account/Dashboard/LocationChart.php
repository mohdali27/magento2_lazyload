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

namespace Webkul\Marketplace\Block\Account\Dashboard;

class LocationChart extends \Magento\Framework\View\Element\Template
{
    /**
     * Google Api URL.
     */
    const GOOGLE_API_URL = 'http://chart.apis.google.com/chart';

    /**
     * Seller statistics graph width.
     *
     * @var string
     */
    protected $_width = '650';

    /**
     * Seller statistics graph height.
     *
     * @var string
     */
    protected $_height = '350';

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @param Context                                   $context
     * @param array                                     $data
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getSale($dateType = 'year')
    {
        $sellerId = $this->_customerSession->getCustomerId();
        $data = [];
        if ($dateType == 'year') {
            $data = $this->getYearlySaleLocation($sellerId);
        } elseif ($dateType == 'month') {
            $data = $this->getMonthlySaleLocation($sellerId);
        } elseif ($dateType == 'week') {
            $data = $this->getWeeklySaleLocation($sellerId);
        } elseif ($dateType == 'day') {
            $data = $this->getDailySaleLocation($sellerId);
        }

        return $data;
    }

    public function getYearlySaleLocation()
    {
        $sellerId = $this->_customerSession->getCustomerId();
        $data = [];
        $curryear = date('Y');
        $date1 = $curryear.'-01-01 00:00:00';
        $date2 = $curryear.'-12-31 23:59:59';
        $sellerOrderCollection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
        ->getCollection()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )->addFieldToFilter(
            'main_table.order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        )->getPricebyorderData();
        $orderSaleArr = [];
        foreach ($sellerOrderCollection as $record) {
            // calculate order actual_seller_amount in base currency
            $actualSellerAmount = 0;
            $appliedCouponAmount = $record['applied_coupon_amount']*1;
            $shippingAmount = $record['shipping_charges']*1;
            $refundedShippingAmount = $record['refunded_shipping_charges']*1;
            $totalshipping = $shippingAmount - $refundedShippingAmount;
            if ($record['tax_to_seller']) {
                $vendorTaxAmount = $record['total_tax']*1;
            } else {
                $vendorTaxAmount = 0;
            }
            if ($record['actual_seller_amount'] * 1) {
                $taxShippingTotal = $vendorTaxAmount + $totalshipping - $appliedCouponAmount;
                $actualSellerAmount += $record['actual_seller_amount'] + $taxShippingTotal;
            } else {
                if ($totalshipping * 1) {
                    $actualSellerAmount += $totalshipping - $appliedCouponAmount;
                }
            }
            if (!isset($orderSaleArr[$record['order_id']])) {
                $orderSaleArr[$record['order_id']] = $actualSellerAmount;
            } else {
                $orderSaleArr[$record['order_id']] = $orderSaleArr[$record['order_id']] + $actualSellerAmount;
            }
        }
        $orderIds = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
        ->getCollection()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )->addFieldToFilter(
            'main_table.order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        )->getAllOrderIds();
        $collection = $this->_objectManager->create(
            'Magento\Sales\Model\Order'
        )
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $orderIds]
        )
        ->addFieldToFilter(
            'created_at',
            ['datetime' => true, 'from' => $date1, 'to' => $date2]
        );
        $data = $this->getArrayData($collection, $orderSaleArr);

        return $data;
    }

    public function getMonthlySaleLocation()
    {
        $sellerId = $this->_customerSession->getCustomerId();
        $data = [];
        $curryear = date('Y');
        $currMonth = date('m');
        $currDay = date('d');
        $date1 = $curryear.'-'.$currMonth.'-01 00:00:00';
        $date2 = $curryear.'-'.$currMonth.'-'.$currDay.' 23:59:59';
        $sellerOrderCollection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
        ->getCollection()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )->addFieldToFilter(
            'main_table.order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        )->getPricebyorderData();
        $orderSaleArr = [];
        foreach ($sellerOrderCollection as $record) {
            // calculate order actual_seller_amount in base currency
            $actualSellerAmount = 0;
            $appliedCouponAmount = $record['applied_coupon_amount']*1;
            $shippingAmount = $record['shipping_charges']*1;
            $refundedShippingAmount = $record['refunded_shipping_charges']*1;
            $totalshipping = $shippingAmount - $refundedShippingAmount;
            if ($record['tax_to_seller']) {
                $vendorTaxAmount = $record['total_tax']*1;
            } else {
                $vendorTaxAmount = 0;
            }
            if ($record['actual_seller_amount'] * 1) {
                $taxShippingTotal = $vendorTaxAmount + $totalshipping - $appliedCouponAmount;
                $actualSellerAmount += $record['actual_seller_amount'] + $taxShippingTotal;
            } else {
                if ($totalshipping * 1) {
                    $actualSellerAmount += $totalshipping - $appliedCouponAmount;
                }
            }
            if (!isset($orderSaleArr[$record['order_id']])) {
                $orderSaleArr[$record['order_id']] = $actualSellerAmount;
            } else {
                $orderSaleArr[$record['order_id']] = $orderSaleArr[$record['order_id']] + $actualSellerAmount;
            }
        }
        $orderIds = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
        ->getCollection()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )->addFieldToFilter(
            'main_table.order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        )->getAllOrderIds();
        $collection = $this->_objectManager->create(
            'Magento\Sales\Model\Order'
        )
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $orderIds]
        )
        ->addFieldToFilter(
            'created_at',
            ['datetime' => true, 'from' => $date1, 'to' => $date2]
        );
        $data = $this->getArrayData($collection, $orderSaleArr);

        return $data;
    }

    public function getWeeklySaleLocation()
    {
        $sellerId = $this->_customerSession->getCustomerId();
        $data = [];
        $curryear = date('Y');
        $currMonth = date('m');
        $currDay = date('d');
        $currWeekDay = date('N');
        $currWeekStartDay = $currDay - $currWeekDay;
        $fromMonth = $currMonth;
        $fromYear = $curryear;
        if ($currWeekStartDay <= 0) {
            $previousMonthDate = date('d', strtotime('last day of previous month'));
            $currWeekStartDay = $previousMonthDate + $currWeekStartDay;
            $fromMonth = date('m', strtotime('last day of previous month'));
            $fromYear = date('Y', strtotime('last day of previous month'));
        }
        $currWeekEndDay = $currWeekStartDay + 7;
        $currentDayOfMonth = date('j');
        if ($currWeekEndDay > $currentDayOfMonth) {
            $currWeekEndDay = $currentDayOfMonth;
        }
        $date1 = $fromYear.'-'.$fromMonth.'-'.$currWeekStartDay.' 00:00:00';
        $date2 = $curryear.'-'.$currMonth.'-'.$currWeekEndDay.' 23:59:59';
        $sellerOrderCollection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
        ->getCollection()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )->addFieldToFilter(
            'main_table.order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        )->getPricebyorderData();
        $orderSaleArr = [];
        foreach ($sellerOrderCollection as $record) {
            // calculate order actual_seller_amount in base currency
            $actualSellerAmount = 0;
            $appliedCouponAmount = $record['applied_coupon_amount']*1;
            $shippingAmount = $record['shipping_charges']*1;
            $refundedShippingAmount = $record['refunded_shipping_charges']*1;
            $totalshipping = $shippingAmount - $refundedShippingAmount;
            if ($record['tax_to_seller']) {
                $vendorTaxAmount = $record['total_tax']*1;
            } else {
                $vendorTaxAmount = 0;
            }
            if ($record['actual_seller_amount'] * 1) {
                $taxShippingTotal = $vendorTaxAmount + $totalshipping - $appliedCouponAmount;
                $actualSellerAmount += $record['actual_seller_amount'] + $taxShippingTotal;
            } else {
                if ($totalshipping * 1) {
                    $actualSellerAmount += $totalshipping - $appliedCouponAmount;
                }
            }
            if (!isset($orderSaleArr[$record['order_id']])) {
                $orderSaleArr[$record['order_id']] = $actualSellerAmount;
            } else {
                $orderSaleArr[$record['order_id']] = $orderSaleArr[$record['order_id']] + $actualSellerAmount;
            }
        }
        $orderIds = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
        ->getCollection()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )->addFieldToFilter(
            'main_table.order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        )->getAllOrderIds();
        $collection = $this->_objectManager->create(
            'Magento\Sales\Model\Order'
        )
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $orderIds]
        )
        ->addFieldToFilter(
            'created_at',
            ['datetime' => true, 'from' => $date1, 'to' => $date2]
        );
        $data = $this->getArrayData($collection, $orderSaleArr);

        return $data;
    }

    public function getDailySaleLocation()
    {
        $sellerId = $this->_customerSession->getCustomerId();
        $data = [];

        $curryear = date('Y');
        $currMonth = date('m');
        $currDay = date('d');
        $date1 = $curryear.'-'.$currMonth.'-'.$currDay.' 00:00:00';
        $date2 = $curryear.'-'.$currMonth.'-'.$currDay.' 23:59:59';
        $sellerOrderCollection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
        ->getCollection()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )->addFieldToFilter(
            'main_table.order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        )->getPricebyorderData();
        $orderSaleArr = [];
        foreach ($sellerOrderCollection as $record) {
            // calculate order actual_seller_amount in base currency
            $actualSellerAmount = 0;
            $appliedCouponAmount = $record['applied_coupon_amount']*1;
            $shippingAmount = $record['shipping_charges']*1;
            $refundedShippingAmount = $record['refunded_shipping_charges']*1;
            $totalshipping = $shippingAmount - $refundedShippingAmount;
            if ($record['tax_to_seller']) {
                $vendorTaxAmount = $record['total_tax']*1;
            } else {
                $vendorTaxAmount = 0;
            }
            if ($record['actual_seller_amount'] * 1) {
                $taxShippingTotal = $vendorTaxAmount + $totalshipping - $appliedCouponAmount;
                $actualSellerAmount += $record['actual_seller_amount'] + $taxShippingTotal;
            } else {
                if ($totalshipping * 1) {
                    $actualSellerAmount += $totalshipping - $appliedCouponAmount;
                }
            }
            if (!isset($orderSaleArr[$record['order_id']])) {
                $orderSaleArr[$record['order_id']] = $actualSellerAmount;
            } else {
                $orderSaleArr[$record['order_id']] = $orderSaleArr[$record['order_id']] + $actualSellerAmount;
            }
        }
        $orderIds = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )
        ->getCollection()
        ->addFieldToFilter(
            'main_table.seller_id',
            $sellerId
        )->addFieldToFilter(
            'main_table.order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        )->getAllOrderIds();
        $collection = $this->_objectManager->create(
            'Magento\Sales\Model\Order'
        )
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $orderIds]
        )
        ->addFieldToFilter(
            'created_at',
            ['datetime' => true, 'from' => $date1, 'to' => $date2]
        );
        $data = $this->getArrayData($collection, $orderSaleArr);

        return $data;
    }

    public function getArrayData($collection, $orderSaleArr)
    {
        $countryArr = [];
        $countryRegionArr = [];
        $countrySaleArr = [];
        $countryOrderCountArr = [];
        foreach ($collection as $record) {
            $addressData = $record->getBillingAddress()->getData();
            $countryId = $addressData['country_id'];
            $countryName = $this->_objectManager->create(
                'Magento\Framework\Locale\ListsInterface'
            )->getCountryTranslation($countryId);
            $countryArr[$countryId] = $countryName;
            if (isset($orderSaleArr[$record->getId()])) {
                if (!isset($countryRegionArr[$countryId])) {
                    $countryRegionArr[$countryId] = [];
                }
                if (!isset($countrySaleArr[$countryId])) {
                    $countrySaleArr[$countryId] = [];
                }
                if (!isset($countryOrderCountArr[$countryId])) {
                    $countryOrderCountArr[$countryId] = [];
                }
                if ($addressData['region_id']) {
                    $regionId = $addressData['region_id'];
                    $region = $this->_objectManager->create(
                        'Magento\Directory\Model\Region'
                    )->load($regionId);
                    $regionCode = $region->getCode();
                    $countryRegionArr[$countryId][$regionCode] = strtoupper($countryId).'-'.strtoupper($regionCode);

                    if (!isset($countrySaleArr[$countryId][$regionCode])) {
                        $countrySaleArr[$countryId][$regionCode] = $orderSaleArr[$record->getId()];
                        $countryOrderCountArr[$countryId][$regionCode] = 1;
                    } else {
                        $countrySaleArr[$countryId][$regionCode] =
                        $countrySaleArr[$countryId][$regionCode] + $orderSaleArr[$record->getId()];
                        $countryOrderCountArr[$countryId][$regionCode] =
                        $countryOrderCountArr[$countryId][$regionCode] + 1;
                    }
                } else {
                    $countryRegionArr[$countryId][$countryId] = strtoupper($countryId);
                    if (!isset($countrySaleArr[$countryId][$countryId])) {
                        $countrySaleArr[$countryId][$countryId] = $orderSaleArr[$record->getId()];
                        $countryOrderCountArr[$countryId][$countryId] = 1;
                    } else {
                        $countrySaleArr[$countryId][$countryId] =
                        $countrySaleArr[$countryId][$countryId] + $orderSaleArr[$record->getId()];
                        $countryOrderCountArr[$countryId][$countryId] =
                        $countryOrderCountArr[$countryId][$countryId] + 1;
                    }
                }
            }
        }
        $data['country_arr'] = $countryArr;
        $data['country_sale_arr'] = $countrySaleArr;
        $data['country_order_count_arr'] = $countryOrderCountArr;
        $data['country_region_arr'] = $countryRegionArr;

        return $data;
    }

    /**
     * Get seller statistics graph image url.
     *
     * @return string
     */
    public function getSellerStatisticsGraphUrl($dateType)
    {
        $params = [
            'cht' => 'map:fixed=-60,-180,85,180',
            'chma' => '0,110,0,0',
        ];
        $getSale = $this->getSale($dateType);
        $countryArr = $getSale['country_arr'];
        $totalContrySale = $getSale['country_sale_arr'];
        $countryOrderCountArr = $getSale['country_order_count_arr'];
        $countryRegionArr = $getSale['country_region_arr'];
        $chmArr = [];
        $chcoArr = [];
        $chdlArr = [];
        $i = 0;
        $saleArray = [];
        array_push($chcoArr, 'B3BCC0');
        foreach ($countryRegionArr as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $count = $countryOrderCountArr[$key][$key2];
                $amount = $totalContrySale[$key][$key2];
                $chmVal = 'f'.$value2.':Orders-'.$count.' Sales-'.$amount.',000000,0,'.$i.',10';
                array_push($chmArr, $chmVal);
                array_push($chdlArr, $value2);
                array_push($chcoArr, $this->randString());
                array_push($saleArray, $totalContrySale[$key][$key2]);
                $i++;
            }
        }
        $params['chm'] = implode('|', $chmArr);
        $params['chld'] = implode('|', $chdlArr);
        $params['chdl'] = implode('|', $chdlArr);
        $params['chco'] = implode('|', $chcoArr);

        if (count($saleArray)) {
            $totalSale = max($saleArray);
        } else {
            $totalSale = 0;
        }

        if ($totalSale) {
            $a = $totalSale / 10;
            $axisYArr = [];
            for ($i = 1; $i <= 10; ++$i) {
                array_push($axisYArr, $a * $i);
            }
            $axisY = implode('|', $axisYArr);
        } else {
            $axisY = '10|20|30|40|50|60|70|80|90|100';
        }

        $minvalue = 0;
        $maxvalue = $totalSale;
        $valueBuffer = [];
        // seller statistics graph size
        $params['chs'] = $this->_width.'x'.$this->_height;
        // return the encoded graph image url
        $_sellerDashboardHelperData = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Dashboard\Data'
        );
        $getParamData = urlencode(base64_encode(json_encode($params)));
        $getEncryptedHashData =
        $_sellerDashboardHelperData->getChartEncryptedHashData($getParamData);
        $params = [
            'param_data' => $getParamData,
            'encrypted_data' => $getEncryptedHashData,
        ];

        return $this->getUrl(
            '*/*/dashboard_tunnel',
            ['_query' => $params, '_secure' => $this->getRequest()->isSecure()]
        );
    }

    public function randString(
        $charset = 'ABC0123456789'
    ) {
        $length = 6;
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count - 1)];
        }

        return $str;
    }
}
