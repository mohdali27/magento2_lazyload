<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\TransferStock;

use Magento\Framework\Model\AbstractModel as AbstractModel;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferActivityInterface;

use \Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;
use \Magestore\InventorySuccess\Model\StockActivity\ProductSelectionManagement;
use \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct as TransferStockProductResource;
use \Magestore\InventorySuccess\Model\TransferStock\StockMovementActivity\Transfer as TransferStockMovementActivity;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;

class TransferStockManagement extends ProductSelectionManagement implements
    \Magestore\InventorySuccess\Api\TransferStock\TransferStockManagementInterface
{
    /** @var  \Magestore\InventorySuccess\Model\TransferStock\TransferValidationFactory */
    protected $_transferValidationFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProductFactory */
    protected $_transferStockProductResourceFactory;

    /** @var  \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagementFactory */
    protected $_transferActivityManagementFactory;

    /**
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagementFactory $resourceProductSelectionManagementFactory,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegsitry,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\IncrementIdManagementInterface $incrementIdManagement,
        \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper,
        \Magestore\InventorySuccess\Model\TransferStock\TransferValidationFactory $transferValidationFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProductFactory $transferStockProductResourceFactory,
        \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagementFactory $transferActivityManagementFactory

    )
    {
        parent::__construct($resourceProductSelectionManagementFactory, $stockChange, $warehouseStockRegsitry, $warehouseFactory, $incrementIdManagement, $systemHelper);
        $this->_transferValidationFactory = $transferValidationFactory;
        $this->_objectManager = $objectManager;
        $this->_transferStockProductResourceFactory = $transferStockProductResourceFactory;
        $this->_transferActivityManagementFactory = $transferActivityManagementFactory;
    }


    /**
     * Generate unique code of Stock Adjustment
     *
     * @return string
     */
    public function generateCode()
    {
        return parent::generateUniqueCode(TransferStockInterface::TRANSFER_CODE_PREFIX);
    }


    /**
     * validate transfer stock general information form input
     * @param $data
     * @return array
     */
    public function validate($data)
    {
        $transferValidation = $this->_transferValidationFactory->create();
        return $transferValidation->validateTranferGeneralForm($data);
    }


    /**
     * update product stock in a warehouse by a transferStock
     * @param TransferStockInterface $transferStock
     */
    public function updateStock(TransferStockInterface $transferStock)
    {
        $products = $this->getProducts($transferStock);

        $productData = [];
        if ($products->getSize()) {

            foreach ($products as $product) {
                $productData[$product->getProductId()] = $product->getQty();
            }

            switch ($transferStock->getType()) {
                case TransferStockInterface::TYPE_SEND:
                    $warehouseId = $transferStock->getSourceWarehouseId();
                    $this->_stockChange->issue($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
                    break;
                case TransferStockInterface::TYPE_TO_EXTERNAL:
                    $warehouseId = $transferStock->getSourceWarehouseId();
                    $this->_stockChange->issue($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
                    break;
                case TransferStockInterface::TYPE_FROM_EXTERNAL:
                    $warehouseId = $transferStock->getDesWarehouseId();
                    $this->_stockChange->receive($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
                    break;
            }
        }
    }


    /**
     * decrease stock of source warehouse
     * increase stock of destination warehouse
     * @param TransferStockInterface $transferStock
     */
    public function directTransferStock(TransferStockInterface $transferStock)
    {
        $products = $this->getProducts($transferStock);

        $productData = [];
        $receivingQtys = [];

        if ($products->getSize()) {
            foreach ($products as $product) {
                $productData[$product->getProductId()] = $product->getQty();
                $receivingQtys[] = ['id' => $product->getProductId(), 'qty' => $product->getQty()];
            }

            $sourceWarehouseId = $transferStock->getSourceWarehouseId();
            $desWarehouseId = $transferStock->getDesWarehouseId();

            /* update stocks in warehouse & global */
            //$this->_stockChange->massChange($sourceWarehouseId, $sourceProductData);
            // $this->_stockChange->massChange($desWarehouseId, $desProductData);
            $this->_stockChange->receive($desWarehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
            $this->_stockChange->issue($sourceWarehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
            //$this->updateReceivingQty($transferStock->getTransferstockId(),$receivingQtys);
            /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagement $transferActivityManagement */
            $transferActivityManagement = $this->_transferActivityManagementFactory->create();
            $transferActivityManagement->updateTransferstockProductQtySummary($transferStock->getTransferstockId(), $receivingQtys, TransferActivityInterface::ACTIVITY_TYPE_RECEIVING);
        }
    }

    /** set product in $data into transferstock_product table
     *  set value of qty field
     * @param $transferstockId
     * @param $data
     */
    public function saveTransferStockProduct($transferstockId, $data)
    {
        $transferStock = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock');
        $transferStock->load($transferstockId);
        $this->setProducts($transferStock, $data);
        //update qty for this current transfer stock
        $totalQty = 0;
        foreach ($data as $item) {
            if (isset($item["qty"]) && is_numeric($item["qty"])) {
                $totalQty += $item["qty"];
            }
        }
        $transferStock->setData("qty", $totalQty);
        try {
            $transferStock->save();
        } catch (\Exception $e) {

        }
    }

    public function validateStockDelivery($product_stocks, $warehouseId)
    {
        $transferValidation = $this->_transferValidationFactory->create();
        return $transferValidation->validateStock($product_stocks, $warehouseId);
    }


    public function updateReceivingQty($transferstockId, $qtys)
    {
        $transferStockProductResoure = $this->_transferStockProductResourceFactory->create();

        $field = TransferStockProductResource::FIELD_QTY_RECEIVED;
        $transferStockProductResoure->updateQty($transferstockId, $qtys, $field);
    }

    public function getSelectBarcodeProductListJson($transferstockId = null)
    {
        $result = [];
        $collection = $this->getSelectBarcodeProductListCollection($transferstockId);
        foreach ($collection->getItems() as $item) {
            $result[(string)$item->getBarcode()] = $item->getData();
        }
        // set image url
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $path = $storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        foreach ($result as &$item) {
            if (isset($item['image'])) {
                $item['image_url'] = $path . 'catalog/product' . $item['image'];
            }
        }

        return $this->_objectManager
            ->create('Magento\Framework\Json\EncoderInterface')
            ->encode($result);
    }

    public function getSelectBarcodeProductListCollection($transferstockId = null)
    {
        $edition = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ProductMetadataInterface')
            ->getEdition();
        $rowId = strtolower($edition) == 'enterprise' ? 'row_id' : 'entity_id';
        $warehouseId = $this->getWarehouseId($transferstockId);
        $productNameAttributeId = $this->_objectManager
            ->create('Magento\Eav\Model\Config')
            ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, \Magento\Catalog\Api\Data\ProductInterface::NAME)
            ->getAttributeId();
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute */
        $eavAttribute = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magento\Eav\Model\ResourceModel\Entity\Attribute');
        $productImagesAttributeId = $eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'image');
        $collection = $this->_objectManager
            ->create('Magestore\BarcodeSuccess\Model\ResourceModel\Barcode\Collection')
            ->addFieldToSelect('barcode')
            ->addFieldToSelect('qty');
        $collection->getSelect()->joinLeft(
            ['product_entity' => $collection->getTable('catalog_product_entity')],
            'main_table.product_id = product_entity.entity_id',
            ['entity_id', 'sku']
        )->joinLeft(
            ['catalog_product_entity_varchar' => $collection->getTable('catalog_product_entity_varchar')],
            "catalog_product_entity_varchar.$rowId = product_entity.entity_id && 
            catalog_product_entity_varchar.attribute_id = $productNameAttributeId",
            ['']
        )->columns(['name' => 'catalog_product_entity_varchar.value'])
            ->joinLeft(
                array('catalog_product_entity_varchar_img' => $collection->getTable('catalog_product_entity_varchar')),
                "product_entity.entity_id = catalog_product_entity_varchar_img.$rowId && 
            catalog_product_entity_varchar_img.attribute_id = $productImagesAttributeId && 
            catalog_product_entity_varchar_img.store_id = 0",
                array('')
            )->columns(array('image' => 'catalog_product_entity_varchar_img.value'));
        if ($warehouseId) {
            $collection->getSelect()->joinLeft(
                ['warehouse_product' => $collection->getTable(WarehouseProductResource::MAIN_TABLE)],
                'main_table.product_id = warehouse_product.product_id  AND warehouse_product.' .
                WarehouseProductInterface::WAREHOUSE_ID . ' = ' . $warehouseId,
                '*'
            )->columns([
                'available_qty' => new \Zend_Db_Expr('warehouse_product.qty'),
            ])->where(
                'warehouse_product.' . WarehouseProductInterface::WAREHOUSE_ID . ' = ?',
                $warehouseId
            )->group('main_table.barcode');
        } else {
            $collection->getSelect()->joinLeft(
                ['stock_item' => $collection->getTable('cataloginventory_stock_item')],
                'main_table.product_id = stock_item.product_id AND stock_item.' .
                WarehouseProductInterface::WAREHOUSE_ID . ' = ' . WarehouseProductInterface::DEFAULT_SCOPE_ID,
                ['']
            )->columns([
                'available_qty' => 'stock_item.qty',
            ]);
        }
        return $collection;
    }

    public function getWarehouseId($transferstockId = null)
    {
        $warehouseId = 0;
        if ($transferstockId) {
            $transferStock = $this->_objectManager
                ->create('Magestore\InventorySuccess\Model\TransferStock')
                ->load($transferstockId);
            $warehouseId = $transferStock->getSourceWarehouseId();
            if ($transferStock->getType() == \Magestore\InventorySuccess\Model\TransferStock::TYPE_FROM_EXTERNAL) {
                $warehouseId = 0;
            }
        }
        return $warehouseId;
    }

}
