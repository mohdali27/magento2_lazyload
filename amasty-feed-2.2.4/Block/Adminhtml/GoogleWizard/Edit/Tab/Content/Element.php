<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab\Content;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Amasty\Feed\Model\Export\Product as ExportProduct;

class Element extends \Magento\Backend\Block\Template implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'googlewizard/content.phtml';

    /**
     * @var \Amasty\Feed\Model\Category
     */
    protected $category;

    /**
     * @var \Amasty\Feed\Model\Export\Product
     */
    protected $export;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\Feed\Model\Category $category,
        ExportProduct $export,
        array $data = []
    ) {
        $this->export = $export;
        $this->category = $category;
        parent::__construct($context, $data);
    }

    /**
     * Render element
     *
     * Render element for Basic and Optional steps.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);

        return $this->toHtml();
    }

    /**
     * Get types of field
     *
     * @return array
     */
    public function getFieldTypes()
    {
        $types = [
            \Amasty\Feed\Model\RegistryContainer::TYPE_ATTRIBUTE  => __('Attribute'),
            \Amasty\Feed\Model\RegistryContainer::TYPE_IMAGE  => __('Images'),
            \Amasty\Feed\Model\RegistryContainer::TYPE_TEXT  => __('Text')
        ];

        return $types;
    }

    /**
     * Check element is selected by type
     *
     * @param \Amasty\Feed\Model\GoogleWizard\Element $element
     * @param string $type
     * @return boolean
     */
    public function isSelectedType($element, $type)
    {
        return $element->getType() == $type;
    }

    /**
     * Check element is selected by attribute
     *
     * @param \Amasty\Feed\Model\GoogleWizard\Element $element
     * @param string $value
     * @return boolean
     */
    public function isSelectedAttribute($element, $value)
    {
        return $element->getValue() == $value;
    }

    /**
     * Get value of attribute
     *
     * @param \Amasty\Feed\Model\GoogleWizard\Element $element
     * @return string
     */
    public function getAttributeValue($element)
    {
        return $element->getValue();
    }

    /**
     * Get all attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            'basic' => [
                'label' => __("Basic"),
                'options' => $this->getBasicAttributes()
            ],
            'product' => [
                'label' => __("Product"),
                'options' => $this->getProductAttributes()
            ],
            'price' => [
                'label' => __("Inventory"),
                'options' => $this->getPriceAttributes()
            ],
            'category' => [
                'label' => __("Category"),
                'options' => $this->getCategoryAttributes()
            ],
            'image' => [
                'label' => __("Image"),
                'options' => $this->getImageAttributes()
            ],
            'gallery' => [
                'label' => __("Gallery"),
                'options' => $this->getGalleryAttributes()
            ],
            'url' => [
                'label' => __("Url"),
                'options' => $this->getUrlAttributes()
            ],
            'other' => [
                'label' => __('Other'),
                'options' => $this->getOtherAttributes()
            ]
        ];
    }

    /**
     * Get basic attributes
     *
     * @return array
     */
    public function getBasicAttributes()
    {
        return [
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|sku' => __('SKU'),
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|product_type' => __('Type'),
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|product_websites' => __('Websites'),
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|created_at' => __('Created'),
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|updated_at' => __('Updated'),
        ];
    }

    /**
     * Get category attributes
     *
     * @return array
     */
    public function getCategoryAttributes()
    {
        $attributes = [
            ExportProduct::PREFIX_CATEGORY_ATTRIBUTE . '|category' => __(
                'Default'
            ),
        ];

        foreach ($this->category->getSortedCollection() as $category) {
            $attributes[ExportProduct::PREFIX_MAPPED_CATEGORY_ATTRIBUTE . '|'
            . $category->getCode()]
                = $category->getName();
        }

        return $attributes;
    }

    /**
     * Get image attributes
     *
     * @return array
     */
    public function getImageAttributes()
    {
        return [
            ExportProduct::PREFIX_IMAGE_ATTRIBUTE . '|thumbnail'   => __('Thumbnail'),
            ExportProduct::PREFIX_IMAGE_ATTRIBUTE . '|image'       => __('Base Image'),
            ExportProduct::PREFIX_IMAGE_ATTRIBUTE . '|small_image' => __('Small Image'),
        ];
    }

    /**
     * Get gallery attributes
     *
     * @return array
     */
    public function getGalleryAttributes()
    {
        return [
            ExportProduct::PREFIX_GALLERY_ATTRIBUTE . '|image_1' => __('Image 1'),
            ExportProduct::PREFIX_GALLERY_ATTRIBUTE . '|image_2' => __('Image 2'),
            ExportProduct::PREFIX_GALLERY_ATTRIBUTE . '|image_3' => __('Image 3'),
            ExportProduct::PREFIX_GALLERY_ATTRIBUTE . '|image_4' => __('Image 4'),
            ExportProduct::PREFIX_GALLERY_ATTRIBUTE . '|image_5' => __('Image 5'),
        ];
    }

    /**
     * Get price attributes
     *
     * @return array
     */
    public function getPriceAttributes()
    {
        return [
            ExportProduct::PREFIX_PRICE_ATTRIBUTE . '|price'           => __('Price'),
            ExportProduct::PREFIX_PRICE_ATTRIBUTE . '|final_price'     => __('Final Price'),
            ExportProduct::PREFIX_PRICE_ATTRIBUTE . '|min_price'       => __('Min Price'),
            ExportProduct::PREFIX_PRICE_ATTRIBUTE . '|max_price'       => __('Max Price'),
            ExportProduct::PREFIX_PRICE_ATTRIBUTE . '|tax_price'       => __('Price with TAX(VAT)'),
            ExportProduct::PREFIX_PRICE_ATTRIBUTE . '|tax_final_price' => __('Final Price with TAX(VAT)'),
        ];
    }

    /**
     * Get url attributes
     *
     * @return array
     */
    public function getUrlAttributes()
    {
        return [
            ExportProduct::PREFIX_URL_ATTRIBUTE . '|short'         => __('Short'),
            ExportProduct::PREFIX_URL_ATTRIBUTE . '|with_category' => __('With Category'),
        ];
    }

    /**
     * Get product attributes
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $attributes = [];
        $codes = $this->export->getExportAttrCodesList();

        foreach ($codes as $code => $title) {
            $attributes[ExportProduct::PREFIX_PRODUCT_ATTRIBUTE . "|" . $code] = $title;
        }

        return $attributes;
    }

    /**
     * Get custom (not-classified) attributes
     *
     * @return array
     */
    public function getOtherAttributes()
    {
        return [
            ExportProduct::PREFIX_OTHER_ATTRIBUTES . '|tax_percents' => __('Tax Percents'),
            ExportProduct::PREFIX_OTHER_ATTRIBUTES . '|sale_price_effective_date' => __('Sale Price Effective Date')
        ];
    }
}
