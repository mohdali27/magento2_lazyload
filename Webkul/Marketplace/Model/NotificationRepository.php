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
namespace Webkul\Marketplace\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\Marketplace\Api\Data\NotificationInterface;
use Webkul\Marketplace\Model\ResourceModel\Notification\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Notification as ResourceModelNotification;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class NotificationRepository implements \Webkul\Marketplace\Api\NotificationRepositoryInterface
{
    /**
     * @var NotificationFactory
     */
    protected $notificationFactory;

    /**
     * @var Notification[]
     */
    protected $instancesById = [];

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceModelNotification
     */
    protected $resourceModel;

    /**
     * @param NotificationFactory       $notificationFactory
     * @param CollectionFactory         $collectionFactory
     * @param ResourceModelNotification $resourceModel
     */
    public function __construct(
        NotificationFactory $notificationFactory,
        CollectionFactory $collectionFactory,
        ResourceModelNotification $resourceModel
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $notificationData = $this->notificationFactory->create();
        $notificationData->load($id);
        if (!$notificationData->getId()) {
            $this->instancesById[$id] = $notificationData;
        }
        $this->instancesById[$id] = $notificationData;

        return $this->instancesById[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getByType($type = null)
    {
        $notificationCollection = $this->collectionFactory->create()
                ->addFieldToFilter('type', $type);
        $notificationCollection->load();

        return $notificationCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getByNotificationIdType($type, $notificationId = null)
    {
        $notificationCollection = $this->collectionFactory->create()
        ->addFieldToFilter('notification_id', $notificationId)
        ->addFieldToFilter('type', $type);
        $notificationCollection->load();

        return $notificationCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        /** @var \Webkul\Marketplace\Model\ResourceModel\Notification\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->load();

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(NotificationInterface $notification)
    {
        $id = $notification->getId();
        try {
            $this->resourceModel->delete($notification);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove notification data record with id %1', $id)
            );
        }
        unset($this->instancesById[$id]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        $notification = $this->get($id);

        return $this->delete($notification);
    }
}
