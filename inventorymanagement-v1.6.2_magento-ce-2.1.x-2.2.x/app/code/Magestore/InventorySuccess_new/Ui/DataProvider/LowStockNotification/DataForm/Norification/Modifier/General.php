<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\LowStockNotification\DataForm\Notification\Modifier;

use Magento\Framework\UrlInterface;
use Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\CollectionFactory;

/**
 * Class General
 * @package Magestore\InventorySuccess\Ui\DataProvider\LowStockNotification\DataForm\Notification\Modifier
 */
class General extends \Magestore\InventorySuccess\Ui\DataProvider\LowStockNotification\DataForm\AbstractDataModifierProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var \Magestore\InventorySuccess\Model\LowStockNotification\Notification
     */
    protected $_currentNotification;


    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        array $modifierConfig = []
    ) {
        parent::__construct($urlBuilder, $request, $modifierConfig);
        $this->collection = $collectionFactory->create();
        $this->_coreRegistry = $registry;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $notification = $this->getCurrentNotification();
        /** @var  \Magestore\InventorySuccess\Model\LowStockNotification\NotificationFactory $updateTypeArray */
        $updateTypeArray = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\NotificationFactory'
        )->create()->getAvailableUpdateType();
        $this->loadedData = [];
        if ($notification && $notification->getId()) {
            $this->loadedData[$notification->getId()] = $notification->getData();
            if ($notification->getCreatedAt()) {
                /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
                $dateTime = \Magento\Framework\App\ObjectManager::getInstance()->create(
                    '\Magento\Framework\Stdlib\DateTime\DateTime'
                );
                $this->loadedData[$notification->getId()]['created_at'] = $dateTime->date("M d, Y", strtotime($notification->getCreatedAt()));
            }
        }
        return $this->loadedData;
    }

    /**
     * Get current warehouse
     *
     * @return Adjustment
     * @throws NoSuchEntityException
     */
    public function getCurrentNotification()
    {
        if (!$this->_currentNotification)
            $this->_currentNotification = $this->_coreRegistry->registry('current_lowstock_notification');
        return $this->_currentNotification;
    }

    /**
     * modify data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        $data = array_replace_recursive(
            $data,
            $this->getData()
        );
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                'general' => [
                    'children' => $this->getGeneralChildren(),
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __(''),
                                'collapsible' => true,
                                'componentType' => \Magento\Ui\Component\Form\Fieldset::NAME,
                                'sortOrder' => 10,
                                'opened' => true
                            ]
                        ]
                    ]
                ]
            ]
        );
        $meta = $this->prepareMeta($meta);
        return $meta;
    }

    /**
     * Retrieve child meta configuration
     *
     * @return array
     */
    protected function getGeneralChildren()
    {
        $warehouseShow = true;
        $notification = $this->getCurrentNotification();
        if ($notification && $notification->getId()) {
            if ($notification->getData('update_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Notification::NOTIFY_TYPE_SYSTEM)
                $warehouseShow = false;
        }
        $children = [
            'created_at' => $this->getModifyField(__('Created at'), 'field', true, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'update_type' => $this->getModifyField(__('Update Type'), 'field', true, 'string', 'select', [], '', 'Magestore_InventorySuccess/form/element/selectlabel'),
            'warehouse_name' => $this->getModifyField(__('Location Name'), 'field', $warehouseShow, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'lowstock_threshold_type' => $this->getModifyField(__('Low Stock Notification Threshold Type'), 'field', true, 'string', 'select', [], '', 'Magestore_InventorySuccess/form/element/selectlabel'),
            'lowstock_threshold' => $this->getModifyField(__('Low Stock Threshold (days)'), 'field', true, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'sales_period' => $this->getModifyField(__('Sales Period (days)'), 'field', true, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'notifier_emails' => $this->getModifyField(__('Notifier emails'), 'field', true, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'warning_message' => $this->getModifyField(__('Warning Message'), 'field', true, 'string', 'textarea', [], '', 'Magestore_InventorySuccess/form/element/textarea')
        ];
        return $children;
    }


    /**
     * Get attributes meta
     *
     * @param
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getAttributesMeta()
    {
        $result = [];
        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Notification\UpdateType $updateType */
        $updateType = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Notification\UpdateType'
        );

        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\LowStockThresholdType $lowStockThresholdType */
        $lowStockThresholdType = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\LowStockThresholdType'
        );
        $result['lowstock_threshold_type']['options'] = $lowStockThresholdType->toOptionArray();
        return $result;
    }

    /**
     * Category's fields default values
     *
     * @param array $result
     * @return array
     */
    protected function getDefaultMetaData($result)
    {
//        $generatedCode = $this->adjustStockManagement->generateCode();
//        $result['adjuststock_code']['default'] = $generatedCode;
//        return $result;
    }

    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general' =>
                [
                    'created_at',
                    'update_type',
                    'warehouse_name',
                    'lowstock_threshold_type',
                    'lowstock_threshold',
                    'sales_period',
                    'notifier_emails',
                    'warning_message',
                ],
        ];
    }

}