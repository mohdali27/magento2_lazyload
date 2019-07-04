<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\LowStockNotification\Notification\Edit;

use \Magento\Framework\App\Helper\AbstractHelper;

class Productgrid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\CollectionFactory
     */
    protected $_notificationCollectionFactory;
    /**
     * @var \Magestore\InventorySuccess\Model\LowStockNotification\Notification
     */
    protected $_currentNotification;

    /**
     * @var \Magestore\InventorySuccess\Helper\Data
     */
    protected $inventoryHelper;

    /**
     * Productgrid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magestore\InventorySuccess\Helper\Data $inventoryHelper
     * @param \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Product\CollectionFactory $productCollectionFactory
     * @param \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\CollectionFactory $notificationCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\InventorySuccess\Helper\Data $inventoryHelper,
        \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Product\CollectionFactory $productCollectionFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\CollectionFactory $notificationCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->inventoryHelper = $inventoryHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_coreRegistry = $registry;
        $this->_notificationCollectionFactory = $notificationCollectionFactory;
    }


    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_grid');
//        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection() {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->_productCollectionFactory->create();
        // get image
        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Store\Model\StoreManagerInterface');
        $path = $storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $path .= 'catalog/product';
        $edition = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ProductMetadataInterface')
            ->getEdition();
        $rowId = strtolower($edition) == 'enterprise' ? 'row_id' : 'entity_id';
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute */
        $eavAttribute = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magento\Eav\Model\ResourceModel\Entity\Attribute');
        $productImagesAttributeId = $eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'image');
        $collection->getSelect()->joinLeft(
            array('catalog_product_entity_varchar_img' => $collection->getTable('catalog_product_entity_varchar')),
            "main_table.product_id = catalog_product_entity_varchar_img.$rowId &&
                catalog_product_entity_varchar_img.attribute_id = $productImagesAttributeId &&
                catalog_product_entity_varchar_img.store_id = 0",
            array('')
        )->columns(array(
            'image' => 'catalog_product_entity_varchar_img.value',
            'image_url' => 'CONCAT("'.$path.'", catalog_product_entity_varchar_img.value)'
        ));
        $collection->addFieldToFilter('notification_id', $id);
        $this->setCollection($collection);
        return parent::_prepareCollection();
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
        if (!$this->_currentNotification) {
            if ($id = $this->getRequest()->getParam('id')) {
                $this->_currentNotification = $this->_notificationCollectionFactory->create()
                    ->addFieldToFilter('notification_id', $id)
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->getFirstItem();
            }
        }
        return $this->_currentNotification;
    }

    /**
     * @throws \Exception
     */
    protected function _prepareColumns() {
//        $this->addColumn(
//            'entity_id',
//            [
//                'header' => __('ID'),
//                'type' => 'number',
//                'index' => 'entity_id',
//                'header_css_class' => 'col-id',
//                'column_css_class' => 'col-id'
//            ]
//        );

        $notification = $this->getCurrentNotification();

        $this->addColumn(
            'product_id',
            [
                'header' => __('Id'),
                'align' => 'left',
                'index' => 'product_id'
            ]
        );

        $this->addColumn(
            'product_sku',
            [
                'header' => __('SKU'),
                'index' => 'product_sku'
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Name'),
                'index' => 'product_name'
            ]
        );
        if($this->inventoryHelper->getShowThumbnailProduct()) {
            $this->addColumn("image",
                [
                    "header" => __("Thumbnail"),
                    "index" => "image",
                    "sortable" => false,
                    'is_system' => true,
                    'renderer'  => 'Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Renderer\Image'
                ]
            );
        }

        $this->addColumn(
            'current_qty',
            [
                'header' => __('Current Qty'),
                'index' => 'current_qty',
                'type' => 'number'
            ]
        );
        if ($notification->getData('lowstock_threshold_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY) {
            $this->addColumn(
                'sold_per_day',
                [
                    'header' => __('Qty. Sold/day'),
                    'type' => 'number',
                    'index' => 'sold_per_day',
                    'renderer' => '\Magestore\InventorySuccess\Block\Adminhtml\SupplyNeeds\Edit\Renderer\FormatNumber'
                ]
            );

            $this->addColumn(
                'total_sold',
                [
                    'header' => __('Total Sold'),
                    'type' => 'number',
                    'index' => 'total_sold'
                ]
            );

            $this->addColumn(
                'availability_days',
                [
                    'header' => __('Availability Days'),
                    'type' => 'number',
                    'index' => 'availability_days'
                ]
            );

            $this->addColumn(
                'availability_date',
                [
                    'header' => __('Availability Date'),
                    'type' => 'date',
                    'index' => 'availability_date'
                ]
            );
        }

        $this->addExportType('*/*/exportNotificationCsv', __('CSV'));
        $this->addExportType('*/*/exportNotificationExcel', __('Excel XML'));
    }

    /**
     * @return mixed|string
     */
    public function getGridUrl() {
        return $this->getData(
            'grid_url'
        ) ? $this->getData(
            'grid_url'
        ) : $this->getUrl(
            '*/*/grid',
            ['_current' => true, 'id' => $this->getRequest()->getParam('id')]
        );
    }


    protected function _getSelectedProducts() {
        $products = '';
        return $products;
    }

    public function getSelectedRelatedProducts() {
        $products = [];
        return $products;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return bool
     */
    public function getRowUrl($row) {
        return false;
    }

    /** Export to XML - fix export negative value  */
    public function getExcelFile($sheetName = '')
    {
        $this->_isExport = true;
        $this->_prepareGrid();

        // Issue #180: Export all data (not only 1 page)
        $collection = clone $this->getCollection();
        $collection->setPageSize(null);
        $collection->setCurPage(1);
        $convert = new \Magestore\InventorySuccess\Rewrite\Magento\Framework\Convert\Excel(
            $collection->getIterator(),
            [$this, 'getRowRecord']
        );

        $name = md5(microtime());
        $file = $this->_path . '/' . $name . '.xml';

        $this->_directory->create($this->_path);
        $stream = $this->_directory->openFile($file, 'w+');
        $stream->lock();

        $convert->setDataHeader($this->_getExportHeaders());
        if ($this->getCountTotals()) {
            $convert->setDataFooter($this->_getExportTotals());
        }

        $convert->write($stream, $sheetName);
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true // can delete file after use
        ];
    }

}
