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

use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection as OrderColl;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Class CustomerHistoryDataProvider
 */
class CustomerHistoryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Collection for getting table name
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */
    protected $orderColl;

    /**
     * Saleslist Orders collection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */
    protected $collection;

    /**
     * @var HelperData
     */
    public $helperData;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param OrderColl $orderColl
     * @param CollectionFactory $collectionFactory
     * @param HelperData $helperData
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
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $sellerId = $helperData->getCustomerId();

        $customerGridFlat = $orderColl->getTable('customer_grid_flat');
        $collectionData = $collectionFactory->create()
        ->addFieldToFilter('seller_id', $sellerId);

        $collectionData->getSelect()
        ->columns('SUM(actual_seller_amount) AS customer_base_total')
        ->columns('count(distinct(order_id)) AS order_count')
        ->group('magebuyer_id');

        $collectionData->getSelect()->join(
            $customerGridFlat.' as cgf',
            'main_table.magebuyer_id = cgf.entity_id',
            [
                'name' => 'name',
                'email' => 'email',
                'billing_telephone' => 'billing_telephone',
                'gender' => 'gender',
                'billing_full' => 'billing_full'
            ]
        );
        $this->collection = $collectionData;
    }
}
