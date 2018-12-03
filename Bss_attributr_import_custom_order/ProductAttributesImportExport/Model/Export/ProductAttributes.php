<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductAttributesImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttributesImportExport\Model\Export;

use Magento\Store\Model\Store;
use Magento\Catalog\Model\Product as CatalogProduct;

class ProductAttributes extends \Magento\CatalogImportExport\Model\Export\Product
{
    const ENTITY_PRODUCT_ATTRIBUTES = 'product_attributes';

    /**
     * @var string $entityTypeCode
     */
    protected $entityTypeCode;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * To get table name from database
     * @var array $tableNames
     */
    protected $tableNames = [];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Bss\ProductAttributesImportExport\Model\ResourceModel\Export
     */
    protected $exportResourceModel;

    /**
     * ProductAttributes constructor.
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $itemFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeColFactory
     * @param \Magento\CatalogImportExport\Model\Export\Product\Type\Factory $_typeFactory
     * @param CatalogProduct\LinkTypeProvider $linkTypeProvider
     * @param \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface $rowCustomizer
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\ProductAttributesImportExport\Model\ResourceModel\Export $exportResourceModel
     */
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
        \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface $rowCustomizer,
        \Magento\Framework\App\Request\Http $request,
        \Bss\ProductAttributesImportExport\Model\ResourceModel\Export $exportResourceModel
    ) {
        $this->resource = $resource;
        $this->readAdapter = $this->resource->getConnection('core_read');
        $this->writeAdapter = $this->resource->getConnection('core_write');
        $this->request = $request;
        $this->exportResourceModel = $exportResourceModel;
        parent::__construct(
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
            $rowCustomizer
        );
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initTypeModels()
    {
        $productTypes = $this->_exportConfig->getEntityTypes(CatalogProduct::ENTITY);
        foreach ($productTypes as $productTypeName => $productTypeConfig) {
            if (!($model = $this->_typeFactory->create($productTypeConfig['model']))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Entity type model \'%1\' is not found', $productTypeConfig['model'])
                );
            }
            if (!$model instanceof \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'Entity type model must be an instance of'
                        . ' \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType'
                    )
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$productTypeName] = $model;
                $this->_disabledAttrs = array_merge($this->_disabledAttrs, $model->getDisabledAttrs());
                $this->_indexValueAttributes = array_merge(
                    $this->_indexValueAttributes,
                    $model->getIndexValueAttributes()
                );
            }
        }
        if (!$this->_productTypeModels) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There are no product types available for export')
            );
        }
        $this->_disabledAttrs = array_unique($this->_disabledAttrs);
        return $this;
    }

    /**
     * @return string
     */
    public function export()
    {
        set_time_limit(0);

        $writer = $this->getWriter();

        $entityCollection = $this->_getEntityCollection(true);
        $entityCollection->setOrder('has_options', 'asc');
        $entityCollection->setStoreId(Store::DEFAULT_STORE_ID);
        $this->_prepareEntityCollection($entityCollection);
        $this->paginateCollection(1, $this->getItemsPerPage());
        $exportData = $this->getExportData();
        $writer->setHeaderCols($this->_getHeaderColumns());

        foreach ($exportData as $dataRow) {
            $writer->writeRow($dataRow);
        }
        return $writer->getContents();
    }

    /**
     * Get data to export
     * @return array
     */
    protected function getExportData()
    {
        $exportData = [];
        $collection = $this->exportResourceModel->getAllAttributeCollection();
        $attributeOptionIds = $this->exportResourceModel->getAttributeOptionId();
        $attributeOptionColumns = $this->exportResourceModel->getAttributeOptionColumn();
        $attributeOptionSwatchColumns = $this->exportResourceModel->getAttributeOptionSwatchColumn();

        try {
            foreach ($collection as $attribute) {
                $attribute['apply_to'] = str_replace(",", "|", $attribute['apply_to']);
                if (in_array($attribute['attribute_id'], $attributeOptionIds)) {
                    if (isset($attributeOptionColumns[$attribute['attribute_id']])) {
                        $attribute['attribute_options'] = rtrim(
                            $attributeOptionColumns[$attribute['attribute_id']],
                            "|"
                        );
                    }
                    if (isset($attributeOptionSwatchColumns[$attribute['attribute_id']])) {
                        if (strpos($attribute['additional_data'], 'text')!==false) {
                            $attribute['attribute_options_swatchtext'] = rtrim(
                                $attributeOptionSwatchColumns[$attribute['attribute_id']],
                                "|"
                            );
                            $attribute['attribute_options_swatchvisual'] = "";
                            $attribute['swatch_input_type'] = 'text';
                        }

                        if (strpos($attribute['additional_data'], 'visual')!==false) {
                            $attribute['attribute_options_swatchvisual'] = rtrim(
                                $attributeOptionSwatchColumns[$attribute['attribute_id']],
                                "|"
                            );
                            $attribute['attribute_options_swatchtext'] = "";
                            $attribute['swatch_input_type'] = 'visual';
                        }
                    }
                } else {
                    $attribute['attribute_options'] = '';
                    $attribute['attribute_options_swatchtext'] = '';
                    $attribute['attribute_options_swatchvisual'] = '';
                }
                $exportData[] = $attribute;
            }
            if (isset($exportData)) {
                asort($exportData);
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $exportData;
    }

    /**
     * @return array
     */
    public function _getHeaderColumns()
    {
        return [
            'entity_type_id',
            'attribute_code',
            'attribute_set',
            'attribute_group_name',
            'is_global',
            'is_user_defined',
            'is_filterable',
            'is_visible',
            'is_required',
            'is_visible_on_front',
            'is_searchable',
            'is_unique',
            'frontend_class',
            'is_visible_in_advanced_search',
            'is_comparable',
            'is_filterable_in_search',
            'is_used_for_price_rules',
            'is_used_for_promo_rules',
            'sort_order',
            'position',
            'frontend_input',
            'backend_type',
            'backend_model',
            'source_model',
            'frontend_label',
            'default_value',
            'apply_to',
            'is_wysiwyg_enabled',
            'is_required_in_admin_store',
            'is_used_in_grid',
            'is_visible_in_grid',
            'is_filterable_in_grid',
            'search_weight',
            'is_html_allowed_on_front',
            'used_in_product_listing',
            'used_for_sort_by',
            'swatch_input_type',
            'attribute_options',
            'attribute_options_swatchvisual',
            'attribute_options_swatchtext'
        ];
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        if (!$this->entityTypeCode) {
            $this->entityTypeCode = CatalogProduct::ENTITY;
        } else {
            $this->entityTypeCode = self::ENTITY_PRODUCT_ATTRIBUTES;
        }
        return $this->entityTypeCode;
    }
}
