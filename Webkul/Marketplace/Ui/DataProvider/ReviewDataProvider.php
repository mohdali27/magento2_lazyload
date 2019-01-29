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

use Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Class ReviewDataProvider
 */
class ReviewDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Feedback collection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Feedback\Collection
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
     * @param CollectionFactory $collectionFactory
     * @param HelperData $helperData
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        HelperData $helperData,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $sellerId = $helperData->getCustomerId();
        $collectionData = $collectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        $this->collection = $collectionData;
    }
}
