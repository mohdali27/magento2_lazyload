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
namespace Bss\ProductAttributesImportExport\Model\Import\ProductAttributes;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    const ERROR_INVALID_ATTRIBUTE_CODE= 'invalidAttributeCode';

    const ERROR_INVALID_SOURCE_MODEL= 'invalidSourceModel';

    const ERROR_EMPTY_ATTRIBUTE_SET_NAME= 'emptyAttributeSetName';

    const ERROR_INVALID_FRONTEND_INPUT= 'invalidFrontendInput';

    const ERROR_INVALID_IS_GLOBAL= 'invalidIsGlobal';

    const ERROR_INVALID_SWATCH_INPUT_TYPE= 'invalidSwatchInputType';

    const ERROR_INVALID_INPUT_TYPE= 'invalidInputType';

    const ERROR_VALUE_IS_REQUIRED = 'isRequired';

    const ERROR_ATTRIBUTE_CODE_IS_EMPTY = 'attributeCodeEmpty';

    const ERROR_INVALID_YES_NO_ATTRIBUTE = 'invalidYesNoAttribute';

    const ERROR_INVALID_ROW = 'invalidRow';

    const ERROR_INVALID_ENTITY_TYPE_ID = 'invalidEntityType_Id';

    const ERROR_MISSING_COLUMN = 'missingColumn';

    const ERROR_INVALID_MULTI_SEPARATOR_VALUE = 'errorInvalidMultiSeparatorValue';

    const ERROR_STORE_ID_NOT_EXIST = 'errorStoreIdNotExist';

    const VALUE_ALL = 'all';

    /**
     * Initialize validator
     * @param \Magento\CatalogImportExport\Model\Import\Product $context
     * @return $this
     */
    public function init($context);
}
