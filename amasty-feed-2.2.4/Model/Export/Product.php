<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\Export;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\ImportExport\Model\Import as Import;
use Amasty\Feed\Model\Config\Source\NumberFormat;

class Product extends \Magento\CatalogImportExport\Model\Export\Product
{
    /**#@+
     * Attributes prefixes
     */
    const PREFIX_CATEGORY_ATTRIBUTE = 'category';

    const PREFIX_CATEGORY_PATH_ATTRIBUTE = 'category_path';

    const PREFIX_MAPPED_CATEGORY_ATTRIBUTE = 'mapped_category';

    const PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE = 'mapped_category_path';

    const PREFIX_CUSTOM_FIELD_ATTRIBUTE = 'custom_field';

    const PREFIX_PRODUCT_ATTRIBUTE = 'product';

    const PREFIX_BASIC_ATTRIBUTE = 'basic';

    const PREFIX_INVENTORY_ATTRIBUTE = 'inventory';

    const PREFIX_IMAGE_ATTRIBUTE = 'image';

    const PREFIX_GALLERY_ATTRIBUTE = 'gallery';

    const PREFIX_PRICE_ATTRIBUTE = 'price';

    const PREFIX_URL_ATTRIBUTE = 'url';

    const PREFIX_OTHER_ATTRIBUTES = 'other';

    const PREFIX_ADVANCED_ATTRIBUTE = 'advanced';
    /**#@-*/

    /**
     * The shift position of the separator
     */
    const SHIFT_OF_SEPARATOR_POSITION = 1;

    /**
     * @var array
     */
    protected $_attributes;

    /**
     * @var array
     */
    protected $_parentAttributes;

    /**
     * @var int|string
     */
    protected $_storeId;

    /**
     * @var \Amasty\Feed\Model\Export\RowCustomizer\Composite
     */
    protected $_rowCustomizer;

    /**
     * @var array
     */
    protected $_categoriesPath = [];

    /**
     * @var array
     */
    protected $_categoriesLast = [];

    /**
     * @var array
     */
    protected $_multiRowData;

    /**
     * @var array
     */
    protected $_attrCodes;

    /**
     * @var array
     */
    protected $_matchingProductIds;

    /**
     * @var int
     */
    protected $_page = 1;

    /**
     * @var int
     */
    protected $_itemsCount = 1;

    /**
     * @var bool
     */
    protected $_isLastPage = false;

    /**
     * @var array
     */
    protected $_fieldsMap = [
        Product::COL_TYPE => 'product_type',
        Product::COL_PRODUCT_WEBSITES => 'product_websites'
    ];

    /**
     * @var array
     */
    protected $_bannedAttributes = [
        'media_gallery',
        'gallery',
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'page_layout',
        'pattern'
    ];

    /**
     * @var array
     */
    protected $_utmParams;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionAmastyFactory;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var bool
     */
    private $currencyShow;

    /**
     * @var string
     */
    private $decimals;

    /**
     * @var string
     */
    private $separator;

    /**
     * @var string
     */
    private $thousandSeparator;

    /**
     * @var NumberFormat
     */
    private $numberFormat;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $itemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeColFactory,
        \Magento\CatalogImportExport\Model\Export\Product\Type\Factory $_typeFactory,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Amasty\Feed\Model\Export\RowCustomizer\Composite $rowCustomizer,
        ScopeConfigInterface $scopeConfig,
        \Amasty\Feed\Model\ResourceModel\Product\CollectionFactory $collectionAmastyFactory,
        NumberFormat $numberFormat
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_rowCustomizer = $objectManager->create('\Amasty\Feed\Model\Export\RowCustomizer\Composite');
        $this->_scopeConfig = $scopeConfig;
        $this->collectionAmastyFactory = $collectionAmastyFactory;
        $this->numberFormat = $numberFormat;

        return parent::__construct(
            $localeDate,
            $config,
            $resource,
            $storeManager,
            $logger,
            $collectionFactory,
            $exportConfig,
            $productFactory,
            $attrSetColFactory,
            $categoryColFactory,
            $itemFactory,
            $optionColFactory,
            $attributeColFactory,
            $_typeFactory,
            $linkTypeProvider,
            $this->_rowCustomizer
        );
    }

    protected function _initStores()
    {
        $this->_storeIdToCode = [
            $this->_storeId => $this->_storeManager->getStore($this->_storeId)->getCode()
        ];

        $this->_storeManager->setCurrentStore($this->_storeId);

        return $this;
    }

    public function exportParents($ids)
    {
        $this->_initStores();

        $this->_rowCustomizer->skipRelationCustomizer(true);

        $entityCollection = $this->_getEntityCollection(true);

        $entityCollection->setStoreId($this->_storeId);

        $this->_rowCustomizer->setStoreId($this->_storeId);

        $entityCollection->addAttributeToFilter(
            'entity_id',
            ['in' => $ids]
        );

        parent::_prepareEntityCollection($entityCollection);

        $ret = $this->getExportData();

        $this->_rowCustomizer->skipRelationCustomizer(false);

        return $ret;
    }

    public function getPage()
    {
        return $this->_page;
    }

    public function getIsLastPage()
    {
        return $this->_isLastPage;
    }

    public function getItemsCount()
    {
        return $this->_itemsCount;
    }

    public function setPage($page)
    {
        $this->_page = $page;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormatPriceCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setFormatPriceCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCurrencyShow()
    {
        return $this->currencyShow;
    }

    /**
     * @param bool $currencyShow
     * @return $this
     */
    public function setCurrencyShow($currencyShow)
    {
        $this->currencyShow = $currencyShow;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormatPriceDecimals()
    {
        return $this->decimals;
    }

    /**
     * @param string $decimals
     * @return $this
     */
    public function setFormatPriceDecimals($decimals)
    {
        $this->decimals = $decimals;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormatPriceDecimalPoint()
    {
        return $this->separator;
    }

    /**
     * @param string $separator
     * @return $this
     */
    public function setFormatPriceDecimalPoint($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormatPriceThousandsSeparator()
    {
        return $this->thousandSeparator;
    }

    /**
     * @param string $thousandSeparator
     * @return $this
     */
    public function setFormatPriceThousandsSeparator($thousandSeparator)
    {
        $this->thousandSeparator = $thousandSeparator;

        return $this;
    }

    public function getExported()
    {
        return $this->getItemsPerPage() * $this->getPage();
    }

    protected function getItemsPerPage()
    {
        return $this->_scopeConfig->getValue('amasty_feed/general/batch_size');
    }

    public function export($lastPage = false)
    {
        $this->_initStores();

        $writer = $this->getWriter();

        $entityCollection = $this->_getEntityCollection(true);

        $entityCollection->setStoreId($this->_storeId);

        $this->_rowCustomizer->setStoreId($this->_storeId);

        $this->_prepareEntityCollection($entityCollection);

        $exportData = $this->getExportData();

        if ($this->_page == 0) {
            $writer->writeHeader();
        }

        foreach ($exportData as &$dataRow) {
            $exportRow = $this->_prepareRowBeforeWrite($dataRow);
            $writer->writeDataRow($exportRow);
        }

        if ($lastPage) {
            $writer->writeFooter();
            $this->_isLastPage = true;
        }

        $this->_itemsCount = $entityCollection->getSize();

        return $writer->getContents();
    }

    /**
     * {@inheritdoc}
     */
    protected function _getEntityCollection($resetCollection = false)
    {
        if ($resetCollection || empty($this->_entityCollection)) {
            $this->_entityCollection = $this->collectionAmastyFactory->create();
        }

        return $this->_entityCollection;
    }

    protected function addMultiRowCustomizerData(&$dataRow, &$multiRowData)
    {
        if (array_key_exists('product_id', $dataRow)) {
            $productId = $dataRow['product_id'];

            $this->updateDataWithCategoryColumns($dataRow, $multiRowData['rowCategories'], $productId);

            $dataRow = $this->rowCustomizer->addData($dataRow, $productId);
        }

        return [$dataRow];
    }

    protected function getExportData()
    {
        $exportData = [];

        $rawData = $this->collectRawData();
        $multiRowData = $this->collectMultiRowData();

        $productIds = array_keys($rawData);

        $stockItemRows = $this->prepareCatalogInventory($productIds);

        $this->rowCustomizer->init($this);

        $this->rowCustomizer->prepareData($this->_getEntityCollection(), $productIds);

        $this->setHeaderColumns($multiRowData['customOptionsData'], $stockItemRows);
        $this->_headerColumns = $this->rowCustomizer->addHeaderColumns($this->_headerColumns);

        foreach ($rawData as $productId => $productData) {
            foreach ($productData as $storeId => $dataRow) {
                if (isset($stockItemRows[$productId])) {
                    $dataRow = array_merge($dataRow, $stockItemRows[$productId]);
                }
                $exportData = array_merge($exportData, $this->addMultiRowCustomizerData($dataRow, $multiRowData));
            }
        }

        return $exportData;
    }

    public function setParentAttributes($attributes)
    {
        $this->_parentAttributes = $attributes;

        return $this;
    }

    public function getParentAttributes()
    {
        return $this->_parentAttributes;
    }

    public function hasParentAttributes()
    {
        $ret = false;

        $parentAttributes = $this->getParentAttributes();

        if (is_array($parentAttributes)) {
            foreach ($this->getParentAttributes() as $group) {
                foreach ($group as $attrs) {
                    if (isset($attrs)) {
                        $ret = true;
                        break;
                    }
                }
                if ($ret) {
                    break;
                }
            }
        }

        return $ret;
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function hasAttributes($key)
    {
        return isset($this->_attributes[$key]) && count($this->_attributes[$key]) > 0;
    }

    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;

        return $this;
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }

    protected function _prepareEntityCollection(\Magento\Eav\Model\Entity\Collection\AbstractCollection $collection)
    {
        $ret = parent::_prepareEntityCollection($collection);
        $ret->addFieldToFilter('entity_id', ['in' => $this->_matchingProductIds]);

        return $ret;
    }

    public function setMatchingProductIds($matchingProductIds)
    {
        $this->_matchingProductIds = $matchingProductIds;

        return $this;
    }

    protected function _prepareRowBeforeWrite(&$dataRow)
    {
        $exportRow = [];

        $dataRow = $this->_customFieldsMapping($dataRow);

        $basicTypes = [
            self::PREFIX_BASIC_ATTRIBUTE,
            self::PREFIX_PRODUCT_ATTRIBUTE,
            self::PREFIX_INVENTORY_ATTRIBUTE
        ];

        $customTypes = [
            self::PREFIX_CATEGORY_ATTRIBUTE,
            self::PREFIX_CATEGORY_PATH_ATTRIBUTE,
            self::PREFIX_MAPPED_CATEGORY_ATTRIBUTE,
            self::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE,
            self::PREFIX_CUSTOM_FIELD_ATTRIBUTE,
            self::PREFIX_IMAGE_ATTRIBUTE,
            self::PREFIX_GALLERY_ATTRIBUTE,
            self::PREFIX_URL_ATTRIBUTE,
            self::PREFIX_PRICE_ATTRIBUTE,
            self::PREFIX_OTHER_ATTRIBUTES,
            self::PREFIX_ADVANCED_ATTRIBUTE
        ];

        if (is_array($this->_attributes) && count($this->_attributes) > 0) {
            $this->_createExportRow($this->_attributes, $dataRow, [], $basicTypes, $customTypes, $exportRow);
        }

        if (is_array($this->_parentAttributes) && count($this->_parentAttributes) > 0) {
            $parentDataRow =
                isset($dataRow['amasty_custom_data']['parent_data']) ? $dataRow['amasty_custom_data']['parent_data'] : [];
            $this->_createExportRow(
                $this->_parentAttributes,
                $parentDataRow,
                $dataRow,
                $basicTypes,
                $customTypes,
                $exportRow
            );
        }

        return $exportRow;
    }

    protected function _createExportRow($attributes, $dataRow, $childDataRow, $basicTypes, $customTypes, &$exportRow)
    {
        $postfix = count($childDataRow) > 0 ? '|parent' : '';

        foreach ($basicTypes as $type) {
            if (isset($attributes[$type]) && is_array($attributes[$type])) {
                foreach ($attributes[$type] as $code) {
                    $attributeValue = $this->getAttributeValue($dataRow, $code)
                        ?: $this->getAttributeValue($childDataRow, $code);

                    if ($code === 'is_in_stock') {
                        $attributeValue = $this->getAttributeValue($dataRow, $code);
                    }

                    if ($attributeValue !== false) {
                        $exportRow[$type . '|' . $code . $postfix] = $attributeValue;
                    }
                }
            }
        }

        $customData = isset($dataRow['amasty_custom_data']) ? $dataRow['amasty_custom_data'] : [];
        $childCustomData = isset($childDataRow['amasty_custom_data']) ? $childDataRow['amasty_custom_data'] : [];

        foreach ($customTypes as $type) {
            if (isset($attributes[$type]) && is_array($attributes[$type])) {
                foreach ($attributes[$type] as $code) {
                    if (isset($customData[$type]) && isset($customData[$type][$code])
                        && $customData[$type][$code] !== ""
                    ) {
                        $exportRow[$type . '|' . $code . $postfix] = $customData[$type][$code];
                    } elseif (isset($childCustomData[$type]) && isset($childCustomData[$type][$code])) {
                        $exportRow[$type . '|' . $code . $postfix] = $childCustomData[$type][$code];
                    }
                }
            }
        }
    }

    /**
     * @param array $dataRow
     * @param string $code
     *
     * @return bool|string
     */
    private function getAttributeValue($dataRow, $code)
    {
        if (isset($dataRow[$code])) {
            return $dataRow[$code];
        } elseif ($this->getValueUseAdditionalAttr($dataRow, $code)) {
            return $this->getAttrValueFromAdditionalAttr($dataRow[parent::COL_ADDITIONAL_ATTRIBUTES], $code);
        }

        return false;
    }

    /**
     * @param array $dataRow
     * @param string $code
     * @return bool
     */
    private function getValueUseAdditionalAttr($dataRow, $code)
    {
        return isset($dataRow[parent::COL_ADDITIONAL_ATTRIBUTES])
            && strpos(
                $dataRow[parent::COL_ADDITIONAL_ATTRIBUTES],
                $code . ImportProduct::PAIR_NAME_VALUE_SEPARATOR
            ) !== false;
    }

    /**
     * @param $additionalAttributesValue
     * @param $code
     *
     * @return bool|string
     */
    private function getAttrValueFromAdditionalAttr($additionalAttributesValue, $code)
    {
        $attributes = explode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalAttributesValue);

        foreach ($attributes as $attribute) {
            if (strpos($attribute, $code) !== false) {
                $delimiterPosition = strpos($attribute, ImportProduct::PAIR_NAME_VALUE_SEPARATOR)
                    + SELF::SHIFT_OF_SEPARATOR_POSITION;

                return $delimiterPosition ? substr($attribute, $delimiterPosition) : false;
            }
        }

        return false;
    }

    protected function _getExportAttrCodes()
    {
        if (null === $this->_attrCodes) {
            if (!empty($this->_parameters[Export::FILTER_ELEMENT_SKIP])
                && is_array($this->_parameters[Export::FILTER_ELEMENT_SKIP])
            ) {
                $skipAttr = array_flip($this->_parameters[Export::FILTER_ELEMENT_SKIP]);
            } else {
                $skipAttr = [];
            }
            $attrCodes = [];

            foreach ($this->filterAttributeCollection($this->getAttributeCollection()) as $attribute) {
                if (!isset($skipAttr[$attribute->getAttributeId()])
                    || in_array(
                        $attribute->getAttributeCode(),
                        $this->_permanentAttributes
                    )
                ) {
                    $attrCodes[] = $attribute->getAttributeCode();
                }
            }
            $this->_attrCodes = $attrCodes;
        }

        return $this->_attrCodes;
    }

    public function getExportAttrCodesList()
    {
        $list = [];
        $exportAttrCodes = $this->_getExportAttrCodes();

        foreach ($this->filterAttributeCollection($this->getAttributeCollection()) as $attribute) {
            $attrCode = $attribute->getAttributeCode();

            if (in_array($attrCode, $exportAttrCodes)) {
                $list[$attrCode] = $attribute->getFrontendLabel();
            }
        }

        return $list;
    }

    public function getAttributesByType($type)
    {
        return isset($this->_attributes[$type]) ?
            $this->_attributes[$type] : [];
    }

    public function filterAttributeCollection(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection)
    {
        $basicAttributes = $this->getAttributesByType(self::PREFIX_BASIC_ATTRIBUTE);

        $productAttributes = $this->getAttributesByType(self::PREFIX_PRODUCT_ATTRIBUTE);

        $imageAttributes = $this->getAttributesByType(self::PREFIX_IMAGE_ATTRIBUTE);

        $urlAttributes = $this->getAttributesByType(self::PREFIX_URL_ATTRIBUTE);

        $attributes = array_merge($basicAttributes, $productAttributes, $imageAttributes);

        $attributes['url_key'] = 'url_key';

        foreach (parent::filterAttributeCollection($collection) as $attribute) {

            if ($this->_attributes
                && !isset($attributes[$attribute->getAttributeCode()])
            ) {
                $collection->removeItemByKey($attribute->getId());
            }
        }

        return $collection;
    }

    protected function updateDataWithCategoryColumns(&$dataRow, &$rowCategories, $productId)
    {
        if (isset($dataRow['amasty_custom_data'])) {
            $dataRow['amasty_custom_data'] = [];
        }

        $customData = &$dataRow['amasty_custom_data'];

        if (isset($rowCategories[$productId]) && count($rowCategories[$productId]) > 0) {
            $categories = $rowCategories[$productId];
            $lastCategoryId = end($categories);

            $customData[self::PREFIX_CATEGORY_ATTRIBUTE]['category'] = isset($this->_categoriesLast[$lastCategoryId])
                ? $this->_categoriesLast[$lastCategoryId]
                : '';
        }

        $ret = parent::updateDataWithCategoryColumns($dataRow, $rowCategories, $productId);

        if (isset($dataRow[self::COL_CATEGORY])) {
            $customData[self::PREFIX_CATEGORY_PATH_ATTRIBUTE]['category'] = $dataRow[self::COL_CATEGORY];
        }

        return $ret;
    }

    protected function initCategories()
    {
        $collection = $this->_categoryColFactory->create()->addNameToResult();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        foreach ($collection as $category) {
            $structure = preg_split('#/+#', $category->getPath());
            $pathSize = count($structure);

            if ($pathSize > 1) {
                $path = [];

                for ($i = 1; $i < $pathSize; $i++) {
                    if ($collection->getItemById($structure[$i])) {
                        $path[$structure[$i]] = $collection->getItemById($structure[$i])->getName();
                    } else {
                        $path[$structure[$i]] = null;
                    }
                }
                $this->_categoriesPath[$category->getId()] = $path;
                $this->_rootCategories[$category->getId()] = array_shift($path);

                if ($pathSize > 2) {
                    $this->_categories[$category->getId()] = implode('/', $path);
                }

                $this->_categoriesLast[$category->getId()] = end($this->_categoriesPath[$category->getId()]);
            }
        }

        return $this;
    }

    public function getMultiRowData()
    {
        return $this->_multiRowData;
    }

    protected function collectMultiRowData()
    {
        if (!$this->_multiRowData) {
            $data = [];
            $productIds = [];
            $rowWebsites = [];
            $rowCategories = [];

            $collection = $this->_getEntityCollection();
            $collection->setStoreId($this->_storeId);
            $collection->addCategoryIds()->addWebsiteNamesToResult();
            /** @var \Magento\Catalog\Model\Product $item */
            foreach ($collection as $item) {
                $productIds[] = $item->getId();
                $rowWebsites[$item->getId()] = array_intersect(
                    array_keys($this->_websiteIdToCode),
                    $item->getWebsites()
                );
                $rowCategories[$item->getId()] = $item->getCategoryIds();
            }
            $collection->clear();

            $allCategoriesIds = array_merge(array_keys($this->_categories), array_keys($this->_rootCategories));

            foreach ($rowCategories as &$categories) {
                $categories = array_intersect($categories, $allCategoriesIds);
            }

            $data['rowCategories'] = $rowCategories;
            $data['linksRows'] = $this->prepareLinks($productIds);
            $data['customOptionsData'] = $this->getCustomOptionsData($productIds);

            $this->_multiRowData = $data;
        }

        return $this->_multiRowData;
    }

    public function getMediaGallery(array $productIds)
    {
        return parent::getMediaGallery($productIds);
    }

    public function getRootCategories()
    {
        return $this->_rootCategories;
    }

    public function getCategoriesPath()
    {
        return $this->_categoriesPath;
    }

    public function getCategoriesLast()
    {
        return $this->_categoriesLast;
    }

    public function setUtmParams($utmParams)
    {
        $this->_utmParams = $utmParams;

        return $this;
    }

    public function getUtmParams()
    {
        return $this->_utmParams;
    }
}
