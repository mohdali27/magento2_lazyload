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
namespace Bss\ProductAttributesImportExport\Model\Import;

use Bss\ProductAttributesImportExport\Model\Import\ProductAttributes\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import as ImportExport;

class ProductAttributes extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{

    const COL_ATTRIBUTE_CODE = 'attribute_code';

    const ENTITY_TYPE_CODE = 'product_attributes';

    const VALIDATOR_MAIN = 'validator';

    const DEFAULT_OPTION_VALUE_SEPARATOR = ';';

    const ERROR_CODE_MISSING_COLUMNS = 'missingColumns';

    protected $errorMessageTemplates = [
        self::ERROR_CODE_SYSTEM_EXCEPTION => 'General system exception happened',
        self::ERROR_CODE_COLUMN_NOT_FOUND => 'We can\'t find required columns: %s.',
        self::ERROR_CODE_COLUMN_EMPTY_HEADER => 'Columns number: "%s" have empty headers',
        self::ERROR_CODE_COLUMN_NAME_INVALID => 'Column names: "%s" are invalid',
        self::ERROR_CODE_ATTRIBUTE_NOT_VALID => "Please correct the value for '%s'.",
        self::ERROR_CODE_DUPLICATE_UNIQUE_ATTRIBUTE => "Duplicate Unique Attribute for '%s'",
        self::ERROR_CODE_ILLEGAL_CHARACTERS => "Illegal character used for attribute %s",
        self::ERROR_CODE_INVALID_ATTRIBUTE => 'Header contains invalid attribute(s): "%s"',
        self::ERROR_CODE_WRONG_QUOTES => "Curly quotes used instead of straight quotes",
        self::ERROR_CODE_COLUMNS_NUMBER => "Number of columns does not correspond to the number of rows in the header",
        self::ERROR_CODE_MISSING_COLUMNS => "Missing Column(s): %s"
    ];

    /**
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_INVALID_ATTRIBUTE_CODE => 'Invalid value in attribute code column',
        ValidatorInterface::ERROR_ATTRIBUTE_CODE_IS_EMPTY => 'Empty attribute code',
        ValidatorInterface::ERROR_INVALID_ROW => 'Invalid row(s)',
        ValidatorInterface::ERROR_INVALID_YES_NO_ATTRIBUTE => 'Invalid Yes/No attribute',
        ValidatorInterface::ERROR_INVALID_ENTITY_TYPE_ID => 'Entity Type ID of product attributes must be 4',
        ValidatorInterface::ERROR_INVALID_INPUT_TYPE => 'Invalid columns frontend_input or backend_type',
        ValidatorInterface::ERROR_INVALID_SOURCE_MODEL => 'Invalid source_model or backend_model',
        ValidatorInterface::ERROR_INVALID_MULTI_SEPARATOR_VALUE => 'Wrong format or Invalid multiple separator value',
        ValidatorInterface::ERROR_INVALID_SWATCH_INPUT_TYPE => "Swatch_input_type must be 'text' or 'visual'",
        ValidatorInterface::ERROR_STORE_ID_NOT_EXIST => "Store id of swatch value does not exist"
    ];

    /**
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * @var array
     */
    protected $swatchAttributes = [];

    /**
     * @var array
     */
    protected $validColumnNames = [
        self::COL_ATTRIBUTE_CODE,
        'entity_type_id',
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
        'swatch_input_type',
        'attribute_options',
        'attribute_options_swatchvisual',
        'attribute_options_swatchtext',
        'is_html_allowed_on_front',
        'used_in_product_listing',
        'used_for_sort_by'
    ];

    /**
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory
     */
    protected $resourceFactory;

    /**
     * @var array
     */
    protected $_permanentAttributes = [self::COL_ATTRIBUTE_CODE];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $attributeModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeResourceModel;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $attributeSet;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attSetFactory;

    /**
     * @var \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory
     */
    protected $swatchCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attributeOptionCollection;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $attributeGroupCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     */
    protected $attributeSetArr;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Bss\ProductAttributesImportExport\Model\ResourceModel\Import
     */
    protected $importResourceModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @var ProductAttributes\Validator\Attribute
     */
    protected $attributeValidator;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Eav\Api\AttributeManagementInterface
     */
    protected $attributeManagement;

    /**
     * ProductAttributes constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productModel
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeResourceModel
     * @param \Magento\Eav\Model\Entity\Attribute $attributeModel
     * @param \Magento\Eav\Model\Entity\Attribute\Set $attributeSet
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attSetFactory
     * @param \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $swatchCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributeOptionCollection
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attributeGroupCollection
     * @param \Magento\Catalog\Model\Product\AttributeSet\OptionsFactory $attributeSetArr
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Bss\ProductAttributesImportExport\Model\ResourceModel\Import $importResourceModel
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection
     * @param ProductAttributes\Validator\Attribute $attributeValidator
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param \Magento\Eav\Api\AttributeManagementInterface $attributeManagement
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeResourceModel,
        \Magento\Eav\Model\Entity\Attribute $attributeModel,
        \Magento\Eav\Model\Entity\Attribute\Set $attributeSet,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attSetFactory,
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $swatchCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributeOptionCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attributeGroupCollection,
        \Magento\Catalog\Model\Product\AttributeSet\OptionsFactory $attributeSetArr,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Bss\ProductAttributesImportExport\Model\ResourceModel\Import $importResourceModel,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection,
        \Bss\ProductAttributesImportExport\Model\Import\ProductAttributes\Validator\Attribute $attributeValidator,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \Magento\Eav\Api\AttributeManagementInterface $attributeManagement
    ) {
        $this->dateTime = $dateTime;
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->config = $config;
        $this->_connection = $resource;
        $this->resourceFactory = $resourceFactory;
        $this->errorAggregator = $errorAggregator;
        $this->productModel = $productModel;
        $this->productFactory = $productFactory;
        $this->attributeResourceModel = $attributeResourceModel;
        $this->attributeModel = $attributeModel;
        $this->attributeSet = $attributeSet;
        $this->attSetFactory = $attSetFactory;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->attributeOptionCollection = $attributeOptionCollection;
        $this->attributeGroupCollection = $attributeGroupCollection;
        $this->attributeSetArr = $attributeSetArr;
        $this->eavAttribute = $eavAttribute;
        $this->importResourceModel = $importResourceModel;
        $this->attributeCollection = $attributeCollection;
        $this->attributeValidator = $attributeValidator;
        $this->validatorFactory = $validatorFactory;
        $this->productMetadata = $productMetadata;
        $this->attributeManagement = $attributeManagement;

        foreach (array_merge($this->errorMessageTemplates, $this->_messageTemplates) as $errorCode => $message) {
            $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
        }
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'product_attributes';
    }

    /**
     * Validate data.
     *
     * @return ProcessingErrorAggregatorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateData()
    {
        if (!$this->_dataValidated) {
            $this->getErrorAggregator()->clear();
            $absentColumns = array_diff($this->_permanentAttributes, $this->getSource()->getColNames());
            $this->addErrors(self::ERROR_CODE_COLUMN_NOT_FOUND, $absentColumns);

            if (ImportExport::BEHAVIOR_DELETE != $this->getBehavior()) {
                $columnNumber = 0;
                $emptyHeaderColumns = [];
                $invalidColumns = [];
                $invalidAttributes = [];

                foreach ($this->getSource()->getColNames() as $columnName) {
                    $columnNumber++;
                    if (!$this->isAttributeParticular($columnName)) {
                        if (trim($columnName) == '') {
                            $emptyHeaderColumns[] = $columnNumber;
                        } elseif (!preg_match('/^[a-z][a-z0-9_]*$/', $columnName)) {
                            $invalidColumns[] = $columnName;
                        } elseif ($this->needColumnCheck && !in_array($columnName, $this->getValidColumnNames())) {
                            $invalidAttributes[] = $columnName;
                        }
                    }
                }

                $difference = array_diff($this->getValidColumnNames(), $this->getSource()->getColNames());
                if (!empty($difference)) {
                    $this->addErrors(self::ERROR_CODE_MISSING_COLUMNS, $difference);
                }
                $this->addErrors(self::ERROR_CODE_INVALID_ATTRIBUTE, $invalidAttributes);
                $this->addErrors(self::ERROR_CODE_COLUMN_EMPTY_HEADER, $emptyHeaderColumns);
                $this->addErrors(self::ERROR_CODE_COLUMN_NAME_INVALID, $invalidColumns);
            }

            if (!$this->getErrorAggregator()->getErrorsCount()) {
                $this->_saveValidatedBunches();
                $this->_dataValidated = true;
            }
        }
        return $this->getErrorAggregator();
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if ($rowData[self::COL_ATTRIBUTE_CODE] == "") {
            $this->addRowError(ValidatorInterface::ERROR_ATTRIBUTE_CODE_IS_EMPTY, $rowNum);
            return false;
        }

        if (!$this->attributeValidator->validateIsGlobal($rowData)) {
            $this->addRowError(ValidatorInterface::ERROR_INVALID_IS_GLOBAL, $rowNum);
            return false;
        }

        if (!$this->attributeValidator->validateSwatchInputType($rowData)) {
            $this->addRowError(ValidatorInterface::ERROR_INVALID_SWATCH_INPUT_TYPE, $rowNum);
            return false;
        }

        if ($rowData['entity_type_id'] != '4') {
            $this->addRowError(ValidatorInterface::ERROR_INVALID_ENTITY_TYPE_ID, $rowNum);
            return false;
        }

        if (!$this->attributeValidator->validateBackendModel($rowData) ||
            !$this->attributeValidator->validateSourceModel($rowData)
        ) {
            $this->addRowError(ValidatorInterface::ERROR_INVALID_SOURCE_MODEL, $rowNum);
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteProductAttributes();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceProductAttributes();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveProductAttributes();
        }

        return true;
    }

    /**
     * Save product attribute
     * @return void
     */
    protected function saveProductAttributes()
    {
        $entityTypeId = $this->productModel->getTypeId();
        $entityType = $this->productModel->getType();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_INVALID_ROW, $rowNum);
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated() && version_compare($this->productMetadata->getVersion(), '2.2.0', '<')) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                if (!$this->attributeValidator->validateBackendType($rowData) ||
                    !$this->attributeValidator->validateFrontendInput($rowData)
                ) {
                    $this->addRowError(
                        ValidatorInterface::ERROR_INVALID_INPUT_TYPE,
                        $rowNum,
                        null,
                        null,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    );
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $separator = $this->getMultipleValueSeparator();

                if (!$this->attributeValidator->checkAttributeOptions($rowData['attribute_options'], $separator) ||
                    !$this->attributeValidator->checkAttributeOptionsSwatch(
                        $rowData['attribute_options_swatchvisual'],
                        $separator
                    )
                ) {
                    $this->addRowError(
                        ValidatorInterface::ERROR_INVALID_MULTI_SEPARATOR_VALUE,
                        $rowNum,
                        null,
                        null,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    );
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $attributeId = $this->eavAttribute->getIdByCode($entityType, $rowData['attribute_code']);

                $optionDataValue = $this->getOptionDataValue($rowData, $attributeId);
                $optionSwatchText = $this->getOptionSwatchText($rowData, $entityType);

                if ($optionDataValue === false || $optionSwatchText === false) {
                    $this->addRowError(
                        ValidatorInterface::ERROR_STORE_ID_NOT_EXIST,
                        $rowNum,
                        null,
                        null,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    );
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $this->processData($rowData, $entityTypeId, $entityType, $optionDataValue, $optionSwatchText);
            }
        }
    }

    /**
     * @param $rowData
     * @param $entityTypeId
     * @param $entityType
     * @param $optionDataValue
     * @param $optionSwatchText
     */
    protected function processData($rowData, $entityTypeId, $entityType, $optionDataValue, $optionSwatchText)
    {
        $attributeId = $this->eavAttribute->getIdByCode($entityType, $rowData['attribute_code']);
        $value[0] = '0';
        $value[1] = '1';
        $columns = [
            'is_user_defined',
            'is_filterable',
            'is_visible',
            'is_required',
            'is_visible_on_front',
            'is_searchable',
            'is_visible_in_advanced_search',
            'is_comparable',
            'is_filterable_in_search',
            'is_used_for_price_rules',
            'is_used_for_promo_rules',
            'is_html_allowed_on_front',
            'used_in_product_listing',
            'used_for_sort_by',
            'is_wysiwyg_enabled',
            'is_required_in_admin_store',
            'is_used_in_grid',
            'is_visible_in_grid',
            'is_filterable_in_grid'
        ];
        foreach ($columns as $column) {
            if (!in_array($rowData[$column], $value)) {
                $rowData[$column] = 0;
            }
        }

        if (empty($attributeId)) {
            /**
             * Add new attribute
             */
            $applyTo = $this->attributeValidator->validateProductType($rowData, $this->getMultipleValueSeparator());
            $newData = [
                'attribute_code' => $rowData['attribute_code'],
                'is_global' => $rowData['is_global'],
                'is_user_defined' => $rowData['is_user_defined'],
                'is_filterable' => $rowData['is_filterable'],
                'is_visible' => $rowData['is_visible'],
                'is_required' => $rowData['is_required'],
                'is_visible_on_front' => $rowData['is_visible_on_front'],
                'is_searchable' => $rowData['is_searchable'],
                'is_unique' => $rowData['is_unique'],
                'frontend_class' => $rowData['frontend_class'],
                'is_visible_in_advanced_search' => $rowData['is_visible_in_advanced_search'],
                'is_comparable' => $rowData['is_comparable'],
                'is_filterable_in_search' => $rowData['is_filterable_in_search'],
                'is_used_for_price_rules' => $rowData['is_used_for_price_rules'],
                'is_used_for_promo_rules' => $rowData['is_used_for_promo_rules'],
                'sort_order' => $rowData['sort_order'],
                'position' => $rowData['position'],
                'frontend_input' => $rowData['frontend_input'],
                'backend_model' => $rowData['backend_model'],
                'source_model' => $rowData['source_model'],
                'backend_type' => $rowData['backend_type'],
                'default_value' => $rowData['default_value'],
                'apply_to' => $applyTo,
                'is_html_allowed_on_front' => $rowData['is_html_allowed_on_front'],
                'used_in_product_listing' => $rowData['used_in_product_listing'],
                'used_for_sort_by' => $rowData['used_for_sort_by'],
                'is_wysiwyg_enabled' => $rowData['is_wysiwyg_enabled'],
                'is_required_in_admin_store' => $rowData['is_required_in_admin_store'],
                'is_used_in_grid' => $rowData['is_used_in_grid'],
                'is_visible_in_grid' => $rowData['is_visible_in_grid'],
                'is_filterable_in_grid' => $rowData['is_filterable_in_grid'],
                'search_weight' => $rowData['search_weight'],
            ];

            if ($rowData['swatch_input_type'] != "") {
                if (strpos($rowData['swatch_input_type'], 'text') !== false) {
                    $newData['optiontext'] = $optionDataValue;
                    $newData['swatchtext'] = $optionSwatchText;
                    $newData['swatch_input_type'] = 'text';
                }

                if (strpos($rowData['swatch_input_type'], 'visual') !== false) {
                    $newData['optionvisual'] = $optionDataValue;
                    $newData['swatchvisual'] = $this->getOptionSwatchVisual($rowData, $entityType);
                    $newData['swatch_input_type'] = 'visual';
                }
            } else {
                $newData['option'] = $this->getOptionDataValue($rowData, $attributeId);
            }

            $attributeResourceModel = $this->attributeResourceModel->create();
            $attributeResourceModel->addData($newData);
            $attributeResourceModel->setEntityTypeId($entityTypeId);
            $this->addFrontendLabel($rowData, $attributeResourceModel);
            $attributeResourceModel->save();
        } else {
            /**
             * Update attribute
             */
            $applyTo = $this->attributeValidator->validateProductType($rowData, $this->getMultipleValueSeparator());
            $updateData = [
                'is_global' => $rowData['is_global'],
                'is_user_defined' => $rowData['is_user_defined'],
                'is_filterable' => $rowData['is_filterable'],
                'is_visible' => $rowData['is_visible'],
                'is_required' => $rowData['is_required'],
                'is_visible_on_front' => $rowData['is_visible_on_front'],
                'is_searchable' => $rowData['is_searchable'],
                'is_unique' => $rowData['is_unique'],
                'frontend_class' => $rowData['frontend_class'],
                'is_visible_in_advanced_search' => $rowData['is_visible_in_advanced_search'],
                'is_comparable' => $rowData['is_comparable'],
                'is_filterable_in_search' => $rowData['is_filterable_in_search'],
                'is_used_for_price_rules' => $rowData['is_used_for_price_rules'],
                'is_used_for_promo_rules' => $rowData['is_used_for_promo_rules'],
                'sort_order' => $rowData['sort_order'],
                'position' => $rowData['position'],
                'frontend_input' => $rowData['frontend_input'],
                'backend_model' => $rowData['backend_model'],
                'source_model' => $rowData['source_model'],
                'backend_type' => $rowData['backend_type'],
                'default_value' => $rowData['default_value'],
                'apply_to' => $applyTo,
                'is_html_allowed_on_front' => $rowData['is_html_allowed_on_front'],
                'used_in_product_listing' => $rowData['used_in_product_listing'],
                'used_for_sort_by' => $rowData['used_for_sort_by'],
                'is_wysiwyg_enabled' => $rowData['is_wysiwyg_enabled'],
                'is_required_in_admin_store' => $rowData['is_required_in_admin_store'],
                'is_used_in_grid' => $rowData['is_used_in_grid'],
                'is_visible_in_grid' => $rowData['is_visible_in_grid'],
                'is_filterable_in_grid' => $rowData['is_filterable_in_grid'],
                'search_weight' => $rowData['search_weight'],
            ];

            if ($rowData['swatch_input_type'] != "") {
                if (strpos($rowData['swatch_input_type'], 'text') !== false) {
                    $updateData['optiontext'] = $optionDataValue;
                    $updateData['swatchtext'] = $optionSwatchText;
                    $updateData['swatch_input_type'] = 'text';
                }

                if (strpos($rowData['swatch_input_type'], 'visual') !== false) {
                    $updateData['optionvisual'] = $optionDataValue;
                    $updateData['swatchvisual'] = $this->getOptionSwatchVisual($rowData, $entityType);
                    $updateData['swatch_input_type'] = 'visual';
                }
            } else {
                $updateData['option'] = $this->getOptionDataValue($rowData, $attributeId);
            }

            $attributeResourceModel = $this->attributeResourceModel->create();
            $attributeResourceModel->load($attributeId);
            $attributeResourceModel->addData($updateData);
            $attributeResourceModel->setEntityTypeId($entityTypeId);
            $this->addFrontendLabel($rowData, $attributeResourceModel);
            $attributeResourceModel->save();
        }
        $attributeSetIds = $this->getAttributeSetIds($rowData, $entityTypeId);
        $this->assignToAttributeSets($attributeSetIds, $rowData);
    }

    /**
     * @param int $attributeSetIds
     * @param array $rowData
     * @return void
     */
    protected function assignToAttributeSets($attributeSetIds, $rowData)
    {
        if (!empty($attributeSetIds)) {
            foreach ($attributeSetIds as $attributeSetId) {
                $newGroupId = $this->getGroupId($rowData, $attributeSetId);
                $this->attributeManagement->assign(
                    'catalog_product',
                    $attributeSetId,
                    $newGroupId,
                    $rowData['attribute_code'],
                    null
                );
            }
        }
    }

    /**
     * @param array $rowData
     * @param int $entityTypeId
     * @return array|null
     */
    protected function getAttributeSetIds($rowData, $entityTypeId)
    {
        if ($rowData['attribute_set'] == "") {
            return null;
        }
        $attributeSetNames = explode($this->getMultipleValueSeparator(), $rowData['attribute_set']);
        $attributeSetIds = [];

        foreach ($attributeSetNames as $attributeSetName) {
            $attributeSets = $this->attributeSetArr->create()->toOptionArray();
            $attributeSetArr = [];
            foreach ($attributeSets as $attributeSet) {
                $attributeSetArr[] = $attributeSet['label'];
            }
            if (!in_array($attributeSetName, $attributeSetArr)) {
                $attributeSet = $this->attSetFactory->create();
                $attributeSet->setEntityTypeId($entityTypeId);
                $attributeSet->setAttributeSetName($attributeSetName);
                $attributeSet->save();
                $attributeSetIds[] = $attributeSet->getId();
            } else {
                $attributeSet = $this->attSetFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('entity_type_id', $entityTypeId)
                    ->addFieldToFilter('attribute_set_name', $attributeSetName)
                    ->getFirstItem();

                $attributeSetIds[] = $attributeSet->getData('attribute_set_id');
            }
        }
        return $attributeSetIds;
    }

    /**
     * Get group id
     * @param array $rowData
     * @param int $attributeSetId
     * @return mixed|null|string
     */
    protected function getGroupId($rowData, $attributeSetId)
    {
        if ($attributeSetId == null) {
            return null;
        }
        if ($rowData['attribute_group_name'] != "") {
            $groupCode = $rowData['attribute_group_name'];
            $groupCollection = $this->attributeGroupCollection->create()
                ->setAttributeSetFilter($attributeSetId)
                ->addFieldToFilter('attribute_group_name', $groupCode)
                ->setPageSize(1);

            $group = $groupCollection->getFirstItem();
            $newGroupId = $group->getData('attribute_group_id');
            if (empty($newGroupId)) {
                $group->setSortOrder(1);
                $group->setAttributeGroupName($rowData['attribute_group_name']);
                $group->setAttributeSetId($attributeSetId);
                $group->setTabGroupCode("basic");
                $group->save();
                return $group->getId();
            } else {
                return $newGroupId;
            }
        } else {
            return $this->importResourceModel->getNewGroupId($attributeSetId);
        }
    }

    /**
     * Get attribute option data value
     * @param array $rowData
     * @param int $attributeId
     * @return mixed|null
     */
    protected function getOptionDataValue($rowData, $attributeId)
    {
        $multiValueSeparator = $this->getMultipleValueSeparator();
        $optionDataValues = [];
        if ($rowData['attribute_options'] != "") {
            $countValue = 0;
            $optionValues = explode($multiValueSeparator, $rowData['attribute_options']);
            $optionId = $this->importResourceModel->getOptionIds($attributeId);
            $countOptionId = count($optionId);
            foreach ($optionValues as $optionValue) {
                $optionAttributeValues = explode(SELF::DEFAULT_OPTION_VALUE_SEPARATOR, $optionValue);
                foreach ($optionAttributeValues as $optionAttributeValue) {
                    $value = explode(":", $optionAttributeValue);
                    if (!in_array($value[0], $this->importResourceModel->getAllStoreIds())) {
                        return false;
                    }
                    if (isset($value[1])) {
                        if ($countValue < $countOptionId) {
                            $optionDataValues['value'][$optionId[$countValue]][$value[0]] = $value[1];
                        } else {
                            $optionDataValues['order']['option_' . $countValue] = $countValue + 1;
                            $optionDataValues['value']['option_' . $countValue][$value[0]] = $value[1];
                        }
                    }
                }
                $countValue++;
            }
        }

        return $optionDataValues;
    }

    /**
     * Add frontend label for attribute
     * @param array $rowData
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeModel
     */
    protected function addFrontendLabel($rowData, $attributeModel)
    {
        if ($rowData['frontend_label'] != "") {
            $frontEndLabel = [];
            $multiValueSeparator = $this->getMultipleValueSeparator();
            $frontEndLabelByStore = explode($multiValueSeparator, $rowData['frontend_label']);
            foreach ($frontEndLabelByStore as $labelByStore) {
                $frontEndLabelValue = explode(':', $labelByStore);
                if (isset($frontEndLabelValue[1])) {
                    $frontEndLabel[$frontEndLabelValue[0]] = $frontEndLabelValue[1];
                } else {
                    $frontEndLabel[0] = $labelByStore;
                }
            }
            $attributeModel->setFrontendLabel($frontEndLabel);
        }
    }

    /**
     * Get attribute option swatch
     * @param array $rowData
     * @param string $entityType
     * @return array|null
     */
    protected function getOptionSwatchVisual($rowData, $entityType)
    {
        $swatchOptionValues = [];
        if ($rowData['attribute_options_swatchvisual'] != "") {
            if (!in_array($rowData['attribute_code'], $this->swatchAttributes)) {
                $this->swatchAttributes[] = $rowData['attribute_code'];
                $attributeId = $this->attributeModel->getIdByCode($entityType, $rowData['attribute_code']);
                $optionIds = $this->importResourceModel->getOptionIds($attributeId);
                $countOptionIds = count($optionIds);
                $countSwatch = 0;
                $swatchValues = explode($this->getMultipleValueSeparator(), $rowData['attribute_options_swatchvisual']);
                foreach ($swatchValues as $swatchValue) {
                    $optionAttributeSwatches = explode(SELF::DEFAULT_OPTION_VALUE_SEPARATOR, $swatchValue);
                    $i = 0;
                    foreach ($optionAttributeSwatches as $optionAttributeSwatch) {
                        $value = explode(':', $optionAttributeSwatch);
                        $addValue = !empty($value[1]) ? $value[1] : null;
                        if ($countSwatch < $countOptionIds) {
                            $swatchOptionValues['value'][$optionIds[$countSwatch]] = $addValue;
                        } else {
                            $swatchOptionValues['value']['option_' . $countSwatch] = $addValue;
                        }
                        $countSwatch++;
                    }
                }
            }
        }
        return $swatchOptionValues;
    }

    /**
     * Get attribute option swatch
     * @param array $rowData
     * @param string $entityType
     * @return array|null|bool
     */
    protected function getOptionSwatchText($rowData, $entityType)
    {
        $swatchOptionValues = [];
        if ($rowData['attribute_options_swatchtext'] != "") {
            if (!in_array($rowData['attribute_code'], $this->swatchAttributes)) {
                $this->swatchAttributes[] = $rowData['attribute_code'];
                $attributeId = $this->attributeModel->getIdByCode($entityType, $rowData['attribute_code']);
                $optionIds = $this->importResourceModel->getOptionIds($attributeId);
                $countOptionIds = count($optionIds);
                $countSwatch = 0;
                $swatchValues = explode($this->getMultipleValueSeparator(), $rowData['attribute_options_swatchtext']);
                foreach ($swatchValues as $swatchValue) {
                    $addValueArr = [];
                    $optionAttributeSwatches = explode(SELF::DEFAULT_OPTION_VALUE_SEPARATOR, $swatchValue);
                    $i = version_compare($this->productMetadata->getVersion(), '2.1.8', '>') ? 0 : 1;
                    foreach ($optionAttributeSwatches as $optionAttributeSwatch) {
                        $value = explode(':', $optionAttributeSwatch);
                        if (!in_array($value[0], $this->importResourceModel->getAllStoreIds())) {
                            return false;
                        }
                        $addValue = !empty($value[1]) ? $value[1] : null;
                        $addValueArr[$value[0]] = $addValue;
                        $i++;
                    }
                    if ($countSwatch < $countOptionIds) {
                        $swatchOptionValues['value'][$optionIds[$countSwatch]] = $addValueArr;
                    } else {
                        $swatchOptionValues['value']['option_' . $countSwatch] = $addValueArr;
                    }
                    $countSwatch++;
                }
            }
        }
        return $swatchOptionValues;
    }

    /**
     * Replace product attributes
     * @return void
     */
    protected function replaceProductAttributes()
    {
        $this->deleteForReplace();
        $this->saveProductAttributes();
    }

    /**
     * Delete product attributes
     * @return $this|bool
     */
    protected function deleteProductAttributes()
    {
        $listAttributeCode = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowAttributeCode = $rowData[self::COL_ATTRIBUTE_CODE];
                    $listAttributeCode[$rowNum] = $rowAttributeCode;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        foreach ($listAttributeCode as $rowNum => $attributeCode) {
            try {
                $attribute = $this->attributeResourceModel->create()->loadByCode('catalog_product', $attributeCode);
                if ($attribute->getId()==null) {
                    $this->addRowError(
                        ValidatorInterface::ERROR_INVALID_ATTRIBUTE_CODE,
                        $rowNum,
                        null,
                        null,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    );
                    continue;
                } else {
                    $attribute->delete();
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this;
    }

    /**
     * Delete product attributes for replace
     * @return $this|bool
     */
    protected function deleteForReplace()
    {
        $listAttributeCode = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowAttributeCode = $rowData[self::COL_ATTRIBUTE_CODE];
                    $listAttributeCode[$rowNum] = $rowAttributeCode;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        foreach ($listAttributeCode as $rowNum => $attributeCode) {
            try {
                $attribute = $this->attributeResourceModel->create()->loadByCode('catalog_product', $attributeCode);
                $attribute->delete();
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this;
    }

    /**
     * Get multiple value separator for import
     * @return string
     */
    public function getMultipleValueSeparator()
    {
        if (!empty($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR])) {
            return $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR];
        }
        return Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;
    }
}
