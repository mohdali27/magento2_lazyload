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
namespace Webkul\Marketplace\Block\Page;

use Webkul\Marketplace\Model\ResourceModel\Notification\CollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;
use Webkul\Marketplace\Model\Notification;

class Notifications extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_Marketplace::layout2/page/header.phtml';

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
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CollectionFactory                                $collectionFactory
     * @param HelperData                                       $helperData
     * @param NotificationHelper                               $notificationHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $collectionFactory,
        HelperData $helperData,
        NotificationHelper $notificationHelper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        $this->notificationHelper = $notificationHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get All notifications .
     *
     * @return array
     */
    public function getAllNotificationCount()
    {
        $sellerId = $this->helperData->getCustomerId();
        $ids = $this->notificationHelper->getAllNotificationIds($sellerId);
        $collectionData = $this->collectionFactory->create()
        ->addFieldToFilter(
            'entity_id',
            ["in" => $ids]
        );
        return count($collectionData);
    }

    /**
     * Get All notifications .
     *
     * @return array
     */
    public function getAllNotification()
    {
        $sellerId = $this->helperData->getCustomerId();
        $ids = $this->notificationHelper->getAllNotificationIds($sellerId);
        $collectionData = $this->collectionFactory->create()
        ->addFieldToFilter(
            'entity_id',
            ["in" => $ids]
        );
        $collectionData->setOrder('created_at', 'DESC');
        $collectionData->setPageSize(5) ->setCurPage(1);
        return $collectionData;
    }

    /**
     * getNotificationInfo.
     *
     * @param array $rowData
     *
     * @return string
     */
    public function getNotificationInfo($rowData)
    {
        $message = '';
        if (empty($rowData)) {
            return $message;
        }
        $timeArr = $this->notificationHelper->getCalculatedTimeDigits(
            $rowData['created_at']
        );
        $type = $timeArr[0];
        $timedigit = $timeArr[1];
        $time = $timeArr[2];
        if ($rowData['type'] == Notification::TYPE_PRODUCT) {
            $productNotificationDesc = $this->notificationHelper->getProductNotificationDesc(
                $rowData['notification_row_id']
            );
            $url = $this->getUrl(
                'marketplace/product/edit',
                [
                    "id" => $rowData['notification_row_id']
                ]
            );
            $message = '<li class="wk-mp-notification-row wk-mp-dropdown-notification-products">
                <a 
                href="'.$url.'" 
                class="wk-mp-notification-entry-description-start"
                title="'.__("View Product").'">
                    <span>'.$productNotificationDesc.'</span>
                </a>
                <small class="wk-mp-notification-time">'.$time.'</small>
            </li>';
        } elseif ($rowData['type'] == Notification::TYPE_ORDER) {
            $order = $this->notificationHelper->getOrder($rowData['notification_row_id']);
            $id = $order->getIncrementId();
            $status = $order->getStatus();
            $orderClass = "wk-mp-order-notification-".$order->getState();
            $url = $this->getUrl(
                "marketplace/order/view",
                [
                    "id" => $rowData['notification_row_id']
                ]
            );
            $message = '<li class="wk-mp-notification-row wk-mp-dropdown-notification-orders '.$orderClass.'">
                <a 
                href="'.$url.'" 
                class="wk-mp-notification-entry-description-start"
                title="'.__("View Order").'">
                    <span>'.__("Order #%1 is %2.", $id, $status).'</span>
                </a>
                <small class="wk-mp-notification-time">'.$time.'</small>
            </li>';
        } elseif ($rowData['type'] == Notification::TYPE_TRANSACTION) {
            $transactionNotificationDesc = $this->notificationHelper->getTransactionNotifyDesc(
                $rowData['notification_id']
            );
            $url = $this->getUrl(
                'marketplace/transaction/view',
                [
                    "id" => $rowData['notification_id']
                ]
            );
            $message = '<li class="wk-mp-notification-row wk-mp-dropdown-notification-transaction">
                <a 
                href="'.$url.'" 
                class="wk-mp-notification-entry-description-start"
                title="'.__("View Transaction").'">
                    <span>'.$transactionNotificationDesc.'</span>
                </a>
                <small class="wk-mp-notification-time">'.$time.'</small>
            </li>';
        } elseif ($rowData['type'] == Notification::TYPE_REVIEW) {
            $reviewNotification = $this->notificationHelper->getReviewNotificationDesc(
                $rowData['notification_id']
            );
            $reviewNotificationDesc = $reviewNotification['desc'];
            $reviewClass = $reviewNotification['feedsClass'];
            $url = $this->getUrl(
                'marketplace/account/review/'
            );
            $message = '<li class="wk-mp-notification-row wk-mp-dropdown-notification-review '.$reviewClass.'">
                <a 
                href="'.$url.'" 
                class="wk-mp-notification-entry-description-start"
                title="'.__("View Review").'">
                    <span>'.$reviewNotificationDesc.'</span>
                </a>
                <small class="wk-mp-notification-time">'.$time.'</small>
            </li>';
        }
        return $message;
    }
}
