<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\LowStockNotification\DataForm;


use Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\CollectionFactory;
use Magento\Framework\UrlInterface;

/**
 * Class DataProvider
 */
class NotificationDataProvider extends \Magestore\InventorySuccess\Ui\DataProvider\LowStockNotification\DataForm\AbstractDataProvider

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
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $pageCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->_coreRegistry = $registry;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var  \Magestore\InventorySuccess\Model\LowStockNotification\NotificationFactory $updateTypeArray */
        $updateTypeArray = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\NotificationFactory'
        )->create()->getAvailableUpdateType();

        /** @var Rule $rule */
        foreach ($items as $rule) {
            $rule->load($rule->getId());
            $this->loadedData[$rule->getId()] = $rule->getData();
//            $updateType = $rule->getData('update_type');
//            if ($updateType) {
//                $updateType = $updateTypeArray[$updateType];
//                $this->loadedData[$rule->getId()]['update_type'] = $updateType;
//            }
            if ($rule->getCreatedAt()) {
                /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
                $dateTime = \Magento\Framework\App\ObjectManager::getInstance()->create(
                    '\Magento\Framework\Stdlib\DateTime\DateTime'
                );
                $this->loadedData[$rule->getId()]['created_at'] = $dateTime->date("M d, Y", strtotime($rule->getCreatedAt()));
            }

        }
        return $this->loadedData;
    }

    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general' =>
                [
                    'warning_message',
                    'created_at',
                    'update_type',
                    'warehouse_name',
                    'notifier_emails',
                    'lowstock_threshold_type',
                    'lowstock_threshold',
                    'sales_period',
                ],
        ];
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
        $result['update_type']['options'] = $updateType->toOptionArray();
        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\LowStockThresholdType $lowStockThresholdType */
        $lowStockThresholdType = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule\LowStockThresholdType'
        );
        $result['lowstock_threshold_type']['options'] = $lowStockThresholdType->toOptionArray();
        return $result;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
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
        $lowStockByQtyShow = false;
        $lowStockBySaleShow = false;
        $notification = $this->getCurrentNotification();
        if ($notification && $notification->getId()) {
            if ($notification->getData('update_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Notification::NOTIFY_TYPE_SYSTEM)
                $warehouseShow = false;
            if ($notification->getData('lowstock_threshold_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY)
                $lowStockByQtyShow = true;
            if ($notification->getData('lowstock_threshold_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY)
                $lowStockBySaleShow = true;
        }
        $children = [
            'warning_message' => $this->getModifyField(__('Warning Message'), 'field', true, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'created_at' => $this->getModifyField(__('Generated at'), 'field', true, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'update_type' => $this->getModifyField(__('Notification Scope'), 'field', true, 'string', 'select', [], '', 'Magestore_InventorySuccess/form/element/selectlabel'),
            'warehouse_name' => $this->getModifyField(__('Location'), 'field', $warehouseShow, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'notifier_emails' => $this->getModifyField(__('Notifier emails'), 'field', true, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'lowstock_threshold_type' => $this->getModifyField(__('Low Stock Threshold Type'), 'field', true, 'string', 'select', [], '', 'Magestore_InventorySuccess/form/element/selectlabel'),
            'lowstock_threshold_qty' => $this->getModifyField(__('Threshold (quantity)'), 'field', $lowStockByQtyShow, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'lowstock_threshold' => $this->getModifyField(__('Threshold (days)'), 'field', $lowStockBySaleShow, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
            'sales_period' => $this->getModifyField(__('Sales Period (days)'), 'field', $lowStockBySaleShow, 'string', 'input', [], '', 'Magestore_InventorySuccess/form/element/text'),
        ];
        return $children;
    }

    /**
    * Get current lowstocknotification
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

}
