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
namespace Webkul\Marketplace\Ui\DataProvider;

use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Orders\Collection as OrderColl;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistCollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Class OrdersHistoryDataProvider
 */
class OrdersHistoryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Collection for getting table name
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Orders\Collection
     */
    protected $orderColl;

    /**
     * Saleslist Orders collection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Orders\Collection
     */
    protected $collection;

    /**
     * @var HelperData
     */
    public $helperData;

    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var SaleslistCollectionFactory
     */
    public $saleslistCollectionFactory;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param OrderColl $orderColl
     * @param CollectionFactory $collectionFactory
     * @param HelperData $helperData
     * @param \Magento\Framework\App\RequestInterface $request
     * @param SaleslistCollectionFactory $saleslistCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        OrderColl $orderColl,
        CollectionFactory $collectionFactory,
        HelperData $helperData,
        \Magento\Framework\App\RequestInterface $request,
        SaleslistCollectionFactory $saleslistCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $sellerId = $helperData->getCustomerId();

        $marketplaceSaleslist = $orderColl->getTable('marketplace_saleslist');
        $orderGridFlat = $orderColl->getTable('sales_order_grid');
        $collectionData = $collectionFactory->create()
        ->addFieldToSelect('seller_id')
        ->addFieldToSelect('order_id')
        ->addFieldToSelect('product_ids')
        ->addFieldToSelect('shipment_id')
        ->addFieldToSelect('invoice_id')
        ->addFieldToSelect('creditmemo_id')
        ->addFieldToSelect('is_canceled')
        ->addFieldToSelect('order_status')
        ->addFieldToSelect('shipping_charges')
        ->addFieldToSelect('carrier_name')
        ->addFieldToSelect('tracking_number')
        ->addFieldToSelect('updated_at')
        ->addFieldToSelect('tax_to_seller')
        ->addFieldToSelect('coupon_amount')
        ->addFieldToSelect('refunded_coupon_amount')
        ->addFieldToSelect('refunded_shipping_charges')
        ->addFieldToSelect('seller_pending_notification');
        $collectionData->getSelect()->where('main_table.seller_id = '.$sellerId);
        $collectionData->getSelect()->join(
            $marketplaceSaleslist.' as ms',
            'main_table.order_id = ms.order_id AND main_table.seller_id = ms.seller_id',
            [
                "magerealorder_id" => "magerealorder_id",
                "magebuyer_id" => "magebuyer_id",
                "currency_rate" => "currency_rate",
                "paid_status" => "paid_status",
                "cpprostatus" => "cpprostatus",
                'SUM(ms.total_tax) AS total_tax'
            ]
        )
        ->columns(
            [
                'SUM(actual_seller_amount) AS actual_seller_amount',
                'SUM(actual_seller_amount) AS purchased_actual_seller_amount',
                'SUM(applied_coupon_amount) AS applied_coupon_amount'
            ]
        )
        ->group('ms.order_id');

        if ($buyerId = $request->getParam('customer_id')) {
            $collectionOrders = $saleslistCollectionFactory->create()
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            )
            ->addFieldToSelect('order_id')
            ->distinct(true);
            $buyerIds = $collectionOrders->getAllBuyerIds();
            if (in_array($buyerId, $buyerIds)) {
                $collectionData->getSelect()->where('ms.magebuyer_id = '.$buyerId);
            }
        }

        $collectionData->getSelect()->join(
            $orderGridFlat.' as ogf',
            'main_table.order_id = ogf.entity_id',
            [
                "customer_name" => "customer_name",
                "status" => "status",
                "created_at" => "created_at",
                "order_currency_code" => "order_currency_code",
                "base_currency_code" => "base_currency_code"
            ]
        );
        $collectionData->getSelect()->where(
            'ogf.order_approval_status = 1'
        );
        $this->collection = $collectionData;
    }
}
