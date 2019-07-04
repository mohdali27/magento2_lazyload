<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Composite extends \Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite
{
    protected $_storeId;

    protected $_skipRelationCustomizer = false;

    protected $_objects = [];

    /**
     * @param ExportProduct $exportProduct
     */
    public function init(ExportProduct $exportProduct)
    {
        if (!$exportProduct->getAttributesByType(ExportProduct::PREFIX_IMAGE_ATTRIBUTE)) {
            unset($this->customizers['imagesData']);
        }

        if (!$exportProduct->getAttributesByType(ExportProduct::PREFIX_GALLERY_ATTRIBUTE)) {
            unset($this->customizers['galleryData']);
        }

        if (!$exportProduct->getAttributesByType(ExportProduct::PREFIX_CATEGORY_ATTRIBUTE)
            && !$exportProduct->getAttributesByType(ExportProduct::PREFIX_CATEGORY_PATH_ATTRIBUTE)
            && !$exportProduct->getAttributesByType(ExportProduct::PREFIX_MAPPED_CATEGORY_ATTRIBUTE)
            && !$exportProduct->getAttributesByType(
                ExportProduct::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE
            )
        ) {
            unset($this->customizers['categoryData']);
        }
        if (!$exportProduct->getAttributesByType(ExportProduct::PREFIX_CUSTOM_FIELD_ATTRIBUTE)) {
            unset($this->customizers['customFieldData']);
        }

        if (!$exportProduct->getAttributesByType(ExportProduct::PREFIX_ADVANCED_ATTRIBUTE)) {
            unset($this->customizers['advancedData']);
        }

        if (!$exportProduct->getAttributesByType(ExportProduct::PREFIX_URL_ATTRIBUTE)) {
            unset($this->customizers['urlData']);
        }

        if (!$exportProduct->getAttributesByType(ExportProduct::PREFIX_PRICE_ATTRIBUTE)) {
            unset($this->customizers['priceData']);
        }

        if (!$exportProduct->hasParentAttributes()) {
            unset($this->customizers['relationData']);
        }
    }

    /**
     * @param int $storeId
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    /**
     * @param bool $skipRelationCustomizer
     */
    public function skipRelationCustomizer($skipRelationCustomizer)
    {
        $this->_skipRelationCustomizer = $skipRelationCustomizer;
    }

    /**
     * @param string $className
     *
     * @return mixed
     */
    protected function _getObject($className)
    {
        if (!isset($this->_objects[$className])) {
            $this->_objects[$className] = $this->objectManager->create($className);
        }

        return $this->_objects[$className];
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        foreach ($this->customizers as $key => $className) {
            if ($this->_skipRelationCustomizer && $key == 'relationData') {
                continue;
            }
            $collection->setStoreId($this->_storeId);
            $this->_getObject($className)->prepareData(clone $collection, $productIds);
        }
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $dataRow['product_id'] = $productId;

        if (!isset($dataRow['amasty_custom_data'])) {
            $dataRow['amasty_custom_data'] = [];
        }

        foreach ($this->customizers as $key => $className) {
            if ($this->_skipRelationCustomizer && $key == 'relationData') {
                continue;
            }
            $dataRow = $this->_getObject($className)->addData($dataRow, $productId);
        }

        return $dataRow;
    }
}
