<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product;
use Magento\Framework\UrlInterface;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class Gallery implements RowCustomizerInterface
{
    protected $_storeManager;

    protected $_urlPrefix;

    protected $_gallery = [];

    protected $_export;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Product $export
    ) {
        $this->_storeManager = $storeManager;
        $this->_export = $export;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->_export->hasAttributes(Product::PREFIX_GALLERY_ATTRIBUTE)) {
            $this->_urlPrefix = $this->_storeManager->getStore($collection->getStoreId())
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product';

            $this->_gallery = $this->_export->getMediaGallery($productIds);
        }
    }

    /**
     * @return array
     */
    public function getGallery()
    {
        return $this->_gallery;
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];

        $gallery = isset($this->_gallery[$productId]) ? $this->_gallery[$productId] : [];

        $customData[Product::PREFIX_GALLERY_ATTRIBUTE] = [
            'image_1' => isset($gallery[0]) ? $this->_urlPrefix . $gallery[0]['_media_image'] : null,
            'image_2' => isset($gallery[1]) ? $this->_urlPrefix . $gallery[1]['_media_image'] : null,
            'image_3' => isset($gallery[2]) ? $this->_urlPrefix . $gallery[2]['_media_image'] : null,
            'image_4' => isset($gallery[3]) ? $this->_urlPrefix . $gallery[3]['_media_image'] : null,
            'image_5' => isset($gallery[4]) ? $this->_urlPrefix . $gallery[4]['_media_image'] : null,
        ];

        return $dataRow;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
