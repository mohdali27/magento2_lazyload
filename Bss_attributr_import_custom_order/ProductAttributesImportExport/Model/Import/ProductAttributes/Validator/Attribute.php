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
namespace Bss\ProductAttributesImportExport\Model\Import\ProductAttributes\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Bss\ProductAttributesImportExport\Model\Import\ProductAttributes\RowValidatorInterface;

class Attribute extends AbstractValidator implements RowValidatorInterface
{

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product $context
     */
    protected $context;

    /**
     * Initialize validator
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product $context
     * @return $this
     */
    public function init($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        parent::isValid($value);
    }

    /**
     * Validate column is_global
     * @param array $rowData
     * @return bool
     */
    public function validateIsGlobal($rowData)
    {
        $value[0] = '0';
        $value[1] = '1';
        $value[2] = '2';
        if (!in_array($rowData['is_global'], $value)) {
            return false;
        }
        return true;
    }

    /**
     * Validate column swatch_input_type
     * @param array $rowData
     * @return bool
     */
    public function validateSwatchInputType($rowData)
    {
        $value[0] = 'text';
        $value[1] = 'visual';
        if (!in_array($rowData['swatch_input_type'], $value) && $rowData['swatch_input_type']!="") {
            return false;
        }
        return true;
    }

    /**
     * Validate column frontend_input
     * @param array $rowData
     * @return bool
     */
    public function validateFrontendInput($rowData)
    {
        $validFrontendInput = [
            'boolean',
            'select',
            'text',
            'image',
            'media_image',
            'price',
            'date',
            'textarea',
            'gallery' ,
            'multiselect',
            'hidden',
            'multiline',
            'weight'
        ];
        if (!in_array($rowData['frontend_input'], $validFrontendInput) &&
            $rowData['frontend_input']!=""
        ) {
            return false;
        }
        return true;
    }

    /**
     * Validate column backend_type
     * @param array $rowData
     * @return bool
     */
    public function validateBackendType($rowData)
    {
        $validBackendType = [
            'static',
            'varchar',
            'int',
            'text',
            'datetime',
            'decimal'
        ];
        if (!in_array($rowData['backend_type'], $validBackendType) &&
            $rowData['backend_type']!=""
        ) {
            return false;
        }
        return true;
    }

    /**
     * Validate column source_model
     * @param array $rowData
     * @return bool
     */
    public function validateSourceModel($rowData)
    {
        if ($rowData['source_model']!="") {
            return class_exists($rowData['source_model']);
        }
        return true;
    }

    /**
     * Validate column backend_model
     * @param array $rowData
     * @return bool
     */
    public function validateBackendModel($rowData)
    {
        if ($rowData['backend_model']!="") {
            return class_exists($rowData['backend_model']);
        }
        return true;
    }

    /**
     * Validate product type in column apply_to
     * @param array $rowData
     * @param string $separator
     * @return null|string
     */
    public function validateProductType($rowData, $separator)
    {
        $applyTo = "";
        $validProductType = [
            'simple',
            'virtual',
            'downloadable',
            'bundle',
            'configurable',
            'grouped'
        ];

        $inputProductType = explode($separator, $rowData['apply_to']);
        foreach ($inputProductType as $productType) {
            if (in_array($productType, $validProductType)) {
                $applyTo .= $productType.",";
            }
        }
        $applyTo = rtrim($applyTo, ",");
        if (empty($applyTo)) {
            return null;
        }
        return $applyTo;
    }

    /**
     * Validate correct columns
     * @param array $rowData
     * @param array $headers
     * @return bool
     */
    public function isMissingColumn($rowData, $headers)
    {
        foreach ($headers as $header) {
            if (!isset($rowData[$header])) {
                return $header;
            }
        }
        return false;
    }

    /**
     * @param string $attributesOptions
     * @param string $separator
     * @return bool
     */
    public function checkAttributeOptions($attributesOptions, $separator)
    {
        $pattern = "/^[0-9]:(.){1,30}/";
        if ($attributesOptions!="") {
            if (!preg_match($pattern, $attributesOptions)) {
                return false;
            }

            if (strpos($attributesOptions, "0:") === false) {
                return false;
            }

            if (strpos($attributesOptions, $separator)===false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $attributeOptionsSwatch
     * @param string $separator
     * @return bool
     */
    public function checkAttributeOptionsSwatch($attributeOptionsSwatch, $separator)
    {
        $swatchLength = 20;
        if (strlen($attributeOptionsSwatch)>50) {
            $swatchImage = strtolower(substr($attributeOptionsSwatch, 0, 49));
            if (strpos($swatchImage, '.jpg') !== false ||
                strpos($swatchImage, '.jpeg') !== false ||
                strpos($swatchImage, '.png') !== false ||
                strpos($swatchImage, '.gif') !== false ||
                strpos($swatchImage, '.tiff') !== false
            ) {
                $swatchLength = 50;
            }
        }
        $pattern = "/^[0-9]:(.){0,$swatchLength}:[0-9]/";
        if ($attributeOptionsSwatch!="") {
            if (!preg_match($pattern, $attributeOptionsSwatch)) {
                return false;
            }

            if (strpos($attributeOptionsSwatch, $separator)===false) {
                return false;
            }
        }
        return true;
    }
}
