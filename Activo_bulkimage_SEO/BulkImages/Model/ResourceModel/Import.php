<?php
/**
 * Activo Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Activo Commercial License
 * that is available through the world-wide-web at this URL:
 * http://extensions.activo.com/license_professional
 *
 * @copyright   Copyright (c) 2017 Activo Extensions (http://extensions.activo.com)
 * @license     Commercial
 */
namespace Activo\BulkImages\Model\ResourceModel;

use Magento\Eav\Model\Config;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Import extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected $connection;
    protected $_resourceIterator;
    protected $_resource;
    protected $_logger;
    protected $_storeManager;
    protected $eavConfig;
    protected $catalogProduct;
    protected $productRepository;

    public function __construct(
    \Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator, \Activo\BulkImages\Logger\Logger $logger, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Store\Model\StoreManagerInterface $storeManager, Config $eavConfig, \Magento\Catalog\Model\Product $productFactory, ProductRepositoryInterface $productRepository, $connectionName = null
    )
    {

        $this->_resourceIterator = $resourceIterator;
        $this->_resource = $context->getResources();
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
        $this->catalogProduct = $productFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('activo_bulkimages_import', 'id');
        $eavConfig = $this->eavConfig;
        $this->attrCodes[] = $eavConfig->getAttribute('catalog_product', 'image')->getId();
        $this->attrCodes[] = $eavConfig->getAttribute('catalog_product', 'small_image')->getId();
        $this->attrCodes[] = $eavConfig->getAttribute('catalog_product', 'thumbnail')->getId();

        $this->attrMediaGallery = $eavConfig->getAttribute('catalog_product', 'media_gallery')->getId();
    }

    public function deleteMediaGalleryByProductId($product)
    {
        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
        foreach ($existingMediaGalleryEntries as $key => $entry) {
            unset($existingMediaGalleryEntries[$key]);
        }
        $product->setMediaGalleryEntries($existingMediaGalleryEntries);
        $product->save($product);
    }

    public function addImage($image, $product, $position, $excludefirst = 1)
    {
        if ($position == 1) {
            $this->setMainImages($image, $product);
            if ($excludefirst == 1) {
                $this->setGalleryImage($product->getId(), $image, 1, 1);
            } else {
                $this->setGalleryImage($product->getId(), $image, 1);
            }
        } else {
            $this->setGalleryImage($product->getId(), $image, $position);
        }
    }

    public function setGalleryImage($entityId, $filename, $position, $disabled = '0')
    {
        $write = $this->getConnection();
        $tableMG = $this->_resource->getTableName('catalog_product_entity_media_gallery');
        $tableMGV = $this->_resource->getTableName('catalog_product_entity_media_gallery_value');
        $tableMGVTE = $this->_resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');

        $write->beginTransaction();
        try {
            $sql = "INSERT INTO $tableMG (attribute_id, value) VALUES ";
            $sql.= "({$this->attrMediaGallery},'$filename') ";
            $write->query($sql);

            $value_id = $write->lastInsertId();
            $sql = "INSERT INTO $tableMGV (value_id, entity_id, position, disabled) VALUES ";
            $sql.= "({$value_id},$entityId,$position,$disabled) ";
            $write->query($sql);

            $sql = "INSERT INTO $tableMGVTE (value_id, entity_id) VALUES ";
            $sql.= "({$value_id},'$entityId') ";
            $write->query($sql);
            $write->commit();
        } catch (\Exception $e) {
            $write->rollback();
            $m = "setGalleryImage: Error trying to set gallery image:\n";
            $m.= "Filename/Image = $filename\n";
            $m.= "product Id = " . $entityId . "\n";
            $m.= "Error message from exception: " . $e->getMessage() . "\n";
            $m.= $e->getTraceAsString();
            $this->_logger->info($m);
        }
    }

    public function setMainImages($image, $product)
    {
        $write = $this->getConnection();
        $table = $this->_resource->getTableName('catalog_product_entity_varchar');
        $entityTypeId = $product->getEntityTypeId();

        // Get list of stores where the product is represented, also include the default store
        $storeIds = $product->getStoreIds();
        $storeId = $product->getStoreId();
        if (!in_array($storeId, $storeIds)) {
            array_unshift($storeIds, $storeId);
        }
        
        /* Add admin store id to effect changes of images roles same as frontend */
        $adminStoreId = 0;

        /* Add Admin Store id */
        if (!in_array($adminStoreId, $storeIds)) {
            array_unshift($storeIds, $adminStoreId);
        }

        /* Add admin store id to effect changes of images roles same as frontend */

        $write->beginTransaction();
        try {
            foreach ($storeIds as $storeId) {
                foreach ($this->attrCodes as $attrCode) {
                    $sql = "INSERT INTO " . $table . " (attribute_id,store_id,entity_id,value) ";
                    $sql.= "VALUES ($attrCode,$storeId,{$product->getId()},'$image') ";
                    $sql.= "ON DUPLICATE KEY UPDATE value='$image'";
                    $write->query($sql);
                }
            }

            //commit transaction or rollback
            $write->commit();
        } catch (\Exception $e) {
            $write->rollback();
            $m = "setProductAttribute: Error trying to set attributes:\n";
            $m.= "image = $image\n";
            $m.= "product Id = " . $product->getId() . "\n";
            $m.= "Error message from exception: " . $e->getMessage() . "\n";
            $m.= $e->getTraceAsString();
            $this->_logger->info($m);
        }
    }

    public function addMediaGalleryAttributeToCollection(\Magento\Catalog\Model\ResourceModel\Product\Collection $_productCollection)
    {
        $_mediaGalleryAttributeId = $this->eavConfig->getAttribute('catalog_product', 'media_gallery')->getAttributeId();
        $_read = $this->getConnection();
        $tableMG = $this->_resource->getTableName('catalog_product_entity_media_gallery');
        $tableMGV = $this->_resource->getTableName('catalog_product_entity_media_gallery_value');

        $_mediaGalleryData = $_read->fetchAll('
            SELECT
                value.entity_id, `main`.`value_id`, `main`.`value` AS `file`,
                `value`.`label`, `value`.`position`, `value`.`disabled`, `default_value`.`label` AS `label_default`,
                `default_value`.`position` AS `position_default`,
                `default_value`.`disabled` AS `disabled_default`
            FROM `' . $tableMG . '` AS `main`
                LEFT JOIN `' . $tableMGV . '` AS `value`
                    ON main.value_id=value.value_id AND value.store_id=' . $this->_storeManager->getStore()->getId() . '
                LEFT JOIN `' . $tableMGV . '` AS `default_value`
                    ON main.value_id=default_value.value_id AND default_value.store_id=0
            WHERE (
                main.attribute_id = ' . $_read->quote($_mediaGalleryAttributeId) . ') 
                AND (value.entity_id IN (' . $_read->quote($_productCollection->getAllIds()) . '))
            ORDER BY IF(value.position IS NULL, default_value.position, value.position) ASC    
        ');

        $_mediaGalleryByProductId = [];

        foreach ($_mediaGalleryData as $_galleryImage) {
            $k = $_galleryImage['entity_id'];
            unset($_galleryImage['entity_id']);
            if (!isset($_mediaGalleryByProductId[$k])) {
                $_mediaGalleryByProductId[$k] = [];
            }
            $_mediaGalleryByProductId[$k][] = $_galleryImage;
        }
        unset($_mediaGalleryData);

        foreach ($_productCollection as &$_product) {
            $_productId = $_product->getData('entity_id');
            if (isset($_mediaGalleryByProductId[$_productId])) {
                $_product->setData('media_gallery', ['images' => $_mediaGalleryByProductId[$_productId]]);
            }
        }
        unset($_mediaGalleryByProductId);

        return $_productCollection;
    }

    public function getConnection()
    {
        return $this->_resource->getConnection();
    }
}
