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

namespace Webkul\Marketplace\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Webkul\Marketplace\Model\Notification as ModelNotification;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Webkul\Marketplace\Model\ResourceModel\Notification\Collection as notificationColl;
use Webkul\Marketplace\Model\ResourceModel\Notification\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistColl;
use Webkul\Marketplace\Model\Sellertransaction;
use Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory as FeedbackColl;
use Webkul\Marketplace\Model\Feedback;
use Webkul\Marketplace\Api\NotificationRepositoryInterface;

/**
 * Marketplace helper Notification.
 */
class Notification extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Collection for getting table name
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Notification\Collection
     */
    protected $notificationColl;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ModelNotification
     */
    protected $modelNotification;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var SaleslistColl
     */
    public $saleslistCollection;

    /**
     * @var Sellertransaction
     */
    public $sellertransaction;

    /**
     * @var FeedbackColl
     */
    public $feedbackCollection;

    /**
     * @var Feedback
     */
    public $feedback;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var NotificationRepositoryInterface
     */
    protected $notificationRepository;

    /**
     * @param Magento\Framework\App\Helper\Context        $context
     * @param NotificationColl                            $notificationColl
     * @param CollectionFactory                           $collectionFactory
     * @param ModelNotification                           $modelNotification
     * @param ProductRepositoryInterface                  $productRepository
     * @param Product                                     $product
     * @param OrderRepositoryInterface                    $orderRepository
     * @param Order                                       $order
     * @param SaleslistColl                               $saleslistCollection
     * @param Sellertransaction                           $sellertransaction
     * @param FeedbackColl                                $feedbackCollection
     * @param Feedback                                    $feedback
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param NotificationRepositoryInterface             $notificationRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        NotificationColl $notificationColl,
        CollectionFactory $collectionFactory,
        ModelNotification $modelNotification,
        ProductRepositoryInterface $productRepository,
        Product $product,
        OrderRepositoryInterface $orderRepository,
        Order $order,
        SaleslistColl $saleslistCollection,
        Sellertransaction $sellertransaction,
        FeedbackColl $feedbackCollection,
        Feedback $feedback,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        NotificationRepositoryInterface $notificationRepository
    ) {
        $this->notificationColl = $notificationColl;
        $this->collectionFactory = $collectionFactory;
        $this->modelNotification = $modelNotification;
        $this->productRepository = $productRepository;
        $this->product = $product;
        $this->orderRepository = $orderRepository;
        $this->order = $order;
        $this->saleslistCollection = $saleslistCollection;
        $this->sellertransaction = $sellertransaction;
        $this->feedbackCollection = $feedbackCollection;
        $this->feedback = $feedback;
        $this->_date = $date;
        $this->notificationRepository = $notificationRepository;
        parent::__construct($context);
    }

    /**
     * Get all notifications id
     * @return array
     */
    public function getAllNotificationIds($sellerId)
    {
        try {
            $marketplaceProduct = $this->notificationColl->getTable(
                'marketplace_product'
            );
            $marketplaceOrders = $this->notificationColl->getTable(
                'marketplace_orders'
            );
            $marketplaceTransaction = $this->notificationColl->getTable(
                'marketplace_sellertransaction'
            );
            $marketplaceReview = $this->notificationColl->getTable(
                'marketplace_datafeedback'
            );
            // get all notification products ids
            $collectionData1 = $this->collectionFactory->create()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            $collectionData1->getSelect()->join(
                $marketplaceProduct.' as mp',
                'main_table.notification_id = mp.entity_id'
            )->where(
                'mp.seller_pending_notification = 1 AND main_table.type = '.ModelNotification::TYPE_PRODUCT
            );
            $ids1 = $collectionData1->getAllIds();
            // get all notification order ids
            $collectionData2 = $this->collectionFactory->create()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            $collectionData2->getSelect()->join(
                $marketplaceOrders.' as mo',
                'main_table.notification_id = mo.entity_id'
            )->where(
                'mo.seller_pending_notification = 1 AND main_table.type = '.ModelNotification::TYPE_ORDER
            );
            $ids2 = $collectionData2->getAllIds();
            // get all notification transaction ids
            $collectionData3 = $this->collectionFactory->create()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            $collectionData3->getSelect()->join(
                $marketplaceTransaction.' as mt',
                'main_table.notification_id = mt.entity_id'
            )->where(
                'mt.seller_pending_notification = 1 AND main_table.type = '.ModelNotification::TYPE_TRANSACTION
            );
            $ids3 = $collectionData3->getAllIds();
            // get all notification review ids
            $collectionData4 = $this->collectionFactory->create()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            $collectionData4->getSelect()->join(
                $marketplaceReview.' as mr',
                'main_table.notification_id = mr.entity_id'
            )->where(
                'mr.seller_pending_notification = 1 AND main_table.type = '.ModelNotification::TYPE_REVIEW
            );
            $ids4 = $collectionData4->getAllIds();
    
            $ids = array_merge($ids1, $ids2);
            $ids = array_merge($ids, $ids3);
            $ids = array_merge($ids, $ids4);
        } catch (\Exception $e) {
            $ids = [];
        }
        return $ids;
    }

    /**
     * Get product by id
     * @param  int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            $product = $this->product;
        }
        return $product;
    }

    /**
     * Get order by id
     * @param  int $orderId
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $order = $this->order;
        }
        return $order;
    }

    public function getCalculatedTimeDigits($createdAt = null)
    {
        $time = 0;
        $days = 0;
        $hours = 0;
        $minutes = 0;
        $time = 0;
        $type = '';
        $message = '';
        $startTimeStamp = $this->_date->gmtDate("m/d/Y H:i:s", strtotime($createdAt));
        $endTimeStamp = $this->_date->gmtDate("m/d/Y H:i:s");
        $timeDiff = strtotime($endTimeStamp) - strtotime($startTimeStamp);
        $numberDays = $timeDiff/(24*60*60);
        if ((int)$numberDays) {
            $days = (int)$numberDays;
            $type = "4";
            $time = $numberDays;
            $message = __("%1 days ago", $days);
        } else {
            $hoursData = $numberDays*24;
            if ((int)$hoursData) {
                $hours = (int)$hoursData;
                $type = "3";
                $time = $hoursData;
                $message = __("%1 hours ago", $hours);
            } else {
                $minutesData =$hoursData*60;
                if ((int)$minutesData) {
                    $minutes = (int)$minutesData;
                    $type = "2";
                    $time = $minutesData;
                    $message = __("%1 minutes ago", $minutes);
                } else {
                    $secondsData = $minutesData*60;
                    $seconds = (int)$secondsData;
                    $type = "1";
                    $time = $secondsData;
                    $message = __("%1 seconds ago", $seconds);
                }
            }
        }
        return [$type, $time, $message];
    }

    /**
     * Generate notification body according to product status.
     *
     * @param  int $productId
     * @param  int $productStatus
     * @return string
     */
    public function getProductNotificationDesc($productId, $productStatus = '')
    {
        $product = $this->getProduct($productId);
        $productStatus = $product->getStatus();
        if ($productStatus == 1) {
            return __('%1 is approved.', $product->getName());
        } else {
            return __('%1 is disapproved.', $product->getName());
        }
    }

    /**
     * generate notification description
     * @param  int $transactionId
     * @return string
     */
    public function getTransactionNotifyDesc($transactionId)
    {
        $transactionColl = $this->saleslistCollection->create()
        ->addFieldToFilter(
            'trans_id',
            $transactionId
        );
        $sellerTransation = $this->sellertransaction->load($transactionId);
        $orderId = $transactionColl->getFirstItem()->getMagerealorderId();
        $method = $sellerTransation->getMethod();
        $desc = __(
            'You have recieved payment for #%1 order. Mode of payment is %2.',
            $orderId,
            $method
        );
        return $desc;
    }

    /**
     * generate review notification description
     * @param  int $reviewId
     * @return string
     */
    public function getReviewNotificationDesc($reviewId)
    {
        $sellerReview = $this->feedback->load($reviewId);
        if (empty($sellerReview['feed_nickname'])) {
            if (!empty($sellerReview['buyer_id'])) {
                $buyerId = $sellerReview['buyer_id'];
                $buyer = $buyerId;
            } else {
                $buyer = $sellerReview['buyer_email'];
            }
        } else {
            $buyer = $sellerReview['feed_nickname'];
        }
        $feedPrice = $sellerReview['feed_price'];
        $feedValue = $sellerReview['feed_value'];
        $feedQuality = $sellerReview['feed_quality'];
        $totalFeeds = ($feedPrice+$feedValue+$feedQuality) / (3*20);
        if ($totalFeeds < 3) {
            $feedsClass = "wk-mp-notification-review-bad";
        } else if ($totalFeeds < 4) {
            $feedsClass = "wk-mp-notification-review-good";
        } else {
            $feedsClass = "wk-mp-notification-review-exellent";
        }
        if ($totalFeeds > 1 && $totalFeeds < 2) {
            $totalFeeds = sprintf("%.1f", $totalFeeds);
        } else if ($totalFeeds > 2 && $totalFeeds < 3) {
            $totalFeeds = sprintf("%.1f", $totalFeeds);
        } else if ($totalFeeds > 3 && $totalFeeds < 4) {
            $totalFeeds = sprintf("%.1f", $totalFeeds);
        } else if ($totalFeeds > 4 && $totalFeeds < 5) {
            $totalFeeds = sprintf("%.1f", $totalFeeds);
        }
        $desc = __(
            '%1 gives %2 star rating.',
            $buyer,
            $totalFeeds
        );
        return ['desc' => $desc, 'feedsClass' => $feedsClass];
    }

    /**
     * Updated notification collection.
     */
    public function updateNotificationCollection($collection, $type)
    {
        foreach ($collection as $modelData) {
            $isNotification = $modelData->getSellerPendingNotification();
            if ($isNotification) {
                $this->updateNotification($modelData, $type);
            }
        }
    }

    /**
     * Updated notification, mark as read.
     */
    public function updateNotification($modelData, $type)
    {
        $isNotification = $modelData->getSellerPendingNotification();
        if ($isNotification) {
            $notificationId = $modelData->getId();
            $modelData->setSellerPendingNotification(0);
            $modelData->save();
            $notifications = $this->notificationRepository->getByNotificationIdType(
                $type,
                $notificationId
            );
            foreach ($notifications as $notification) {
                $this->notificationRepository->delete($notification);
            }
        }
    }

    /**
     * Save notification data.
     */
    public function saveNotification($type, $notificationId, $notificationRowId = 0)
    {
        try {
            $notifications = $this->notificationRepository
            ->getByNotificationIdType(
                $type,
                $notificationId
            );
            $notificationExistedId = 0;
            foreach ($notifications as $notification) {
                $notificationExistedId = $notification->getId();
            }
            if (!$notificationExistedId) {
                $data = [
                    'notification_id' => $notificationId,
                    'notification_row_id' => $notificationRowId,
                    'type' => $type,
                ];
                $modelData = $this->modelNotification;
                $modelData->setData($data);
                $modelData->setCreatedAt($this->_date->gmtDate());
                $modelData->setUpdatedAt($this->_date->gmtDate());
                $modelData->save();
            } else {
                $modelData = $this->modelNotification->load($notificationExistedId);
                $modelData->setCreatedAt($this->_date->gmtDate());
                $modelData->setUpdatedAt($this->_date->gmtDate());
                $modelData->save();
            }
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * Get All review notifications .
     *
     * @return array
     */
    public function getAllReviewNotification($sellerId)
    {
        $marketplaceReview = $this->notificationColl->getTable(
            'marketplace_datafeedback'
        );
        // get all notification products ids
        $collectionData = $this->collectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        $collectionData->getSelect()->join(
            $marketplaceReview.' as mr',
            'main_table.notification_id = mr.entity_id'
        )->where(
            'mr.seller_pending_notification = 1 AND main_table.type = '.ModelNotification::TYPE_REVIEW
        );
        $collectionData->setOrder('main_table.created_at', 'DESC');
        $collectionData->setPageSize(5) ->setCurPage(1);
        return $collectionData;
    }

    /**
     * Get All notifications .
     *
     * @return array
     */
    public function getAllReviewNotificationCount($sellerId)
    {
        $marketplaceReview = $this->notificationColl->getTable(
            'marketplace_datafeedback'
        );
        // get all notification products ids
        $collectionData = $this->collectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        $collectionData->getSelect()->join(
            $marketplaceReview.' as mr',
            'main_table.notification_id = mr.entity_id'
        )->where(
            'mr.seller_pending_notification = 1 AND main_table.type = '.ModelNotification::TYPE_REVIEW
        );
        return count($collectionData);
    }

    /**
     * Get review notifications description.
     * @param object $rowData
     * @param string $url
     * @return string
     */
    public function getReviewNotificationDetails($rowData, $url)
    {
        $reviewNotification = $this->getReviewNotificationDesc(
            $rowData['notification_id']
        );
        $reviewNotificationDesc = $reviewNotification['desc'];

        $timeArr = $this->getCalculatedTimeDigits(
            $rowData['created_at']
        );
        $type = $timeArr[0];
        $timedigit = $timeArr[1];
        $time = $timeArr[2];
        $message = '<p class="notifications-entry-description _cutted">
            <a 
            href="'.$url.'" 
            class="notifications-entry-description-start"
            title="'.__("View Review").'">
                <span>'.$reviewNotificationDesc.'</span>
            </a>
        </p>
        <time class="notifications-entry-time">'.$time.'</time>';
        return $message;
    }
}
