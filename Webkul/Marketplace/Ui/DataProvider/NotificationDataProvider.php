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

use Webkul\Marketplace\Model\Notification;
use Webkul\Marketplace\Model\ResourceModel\Notification\CollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;

/**
 * Class NotificationDataProvider
 */
class NotificationDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Notification collection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Notification\Collection
     */
    protected $collection;

    /**
     * @var HelperData
     */
    public $helperData;

    /**
     * @var NotificationHelper
     */
    public $notificationHelper;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param HelperData $helperData
     * @param NotificationHelper $notificationHelper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        HelperData $helperData,
        NotificationHelper $notificationHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $sellerId = $helperData->getCustomerId();
        $ids = $notificationHelper->getAllNotificationIds($sellerId);
        $collectionData = $collectionFactory->create()
        ->addFieldToFilter(
            'entity_id',
            ["in" => $ids]
        );
        if (!$helperData->getSellerProfileDisplayFlag()) {
            $collectionData->addFieldToFilter(
                'type',
                ["neq" => Notification::TYPE_REVIEW]
            );
        }
        $collectionData->setOrder('created_at', 'DESC');
        $this->collection = $collectionData;
    }
}
