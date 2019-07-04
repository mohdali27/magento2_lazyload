<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\Csv;

use Amasty\Feed\Model\Export\Product as ExportProduct;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Field extends Widget implements RendererInterface
{
    protected $_template = 'feed/fields.phtml';
    protected $_export;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ExportProduct $export,
        array $data = []
    ) {
        $this->_export = $export;

        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Add Field'),
                'onclick' => 'return fieldControl.addItem();',
                'class' => 'add'
            ]
        );

        $button->setName('add_field_item_button');

        $this->setChild('add_button', $button);

        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getFormats()
    {
        return [
            'as_is' => 'As Is',
            'strip_tags' => 'Strip Tags',
            'html_escape' => 'Html Escape',
            'date' => 'Date',
            'price' => 'Price',
            'lowercase' => 'Lowercase',
            'integer' => 'Integer',
        ];
    }

    public function getInventoryAttributes()
    {
        // all inventory qty,min_qty,use_config_min_qty,is_qty_decimal,backorders,use_config_backorders,
        // min_sale_qty,use_config_min_sale_qty,max_sale_qty,use_config_max_sale_qty,is_in_stock,notify_stock_qty,
        // use_config_notify_stock_qty,manage_stock,use_config_manage_stock,use_config_qty_increments,qty_increments,
        // use_config_enable_qty_inc,enable_qty_increments,is_decimal_divided,website_id
        return [
            ExportProduct::PREFIX_INVENTORY_ATTRIBUTE . '|qty' => 'Qty',
            ExportProduct::PREFIX_INVENTORY_ATTRIBUTE . '|backorders' => 'Allow Backorders',
            ExportProduct::PREFIX_INVENTORY_ATTRIBUTE . '|min_qty' => 'Out Of Stock Qty',
            ExportProduct::PREFIX_INVENTORY_ATTRIBUTE . '|min_sale_qty' => 'Min Cart Qty',
            ExportProduct::PREFIX_INVENTORY_ATTRIBUTE . '|max_sale_qty' => 'Max Cart Qty',
            ExportProduct::PREFIX_INVENTORY_ATTRIBUTE . '|notify_stock_qty' => 'Notify On Stock Below'
        ];
    }

    public function getBasicAttributes()
    {
        return [
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|sku' => 'SKU',
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|product_type' => 'Type',
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|product_websites' => 'Websites',
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|created_at' => 'Created',
            ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|updated_at' => 'Updated',

//            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE . '|product_id' => 'Product Id',
//            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE . '|store_id' => 'Store Id',
        ];
    }

    public function getCategoryAttributes()
    {
        return [
            ExportProduct::PREFIX_CATEGORY_ATTRIBUTE . '|last_category' => 'Last Category',
            ExportProduct::PREFIX_CATEGORY_ATTRIBUTE . '|categories' => 'Path of Categories',
        ];
    }

    public function getImageAttributes()
    {
        return [
            ExportProduct::PREFIX_IMAGE_ATTRIBUTE . '|thumbnail' => 'Thumbnail',
            ExportProduct::PREFIX_IMAGE_ATTRIBUTE . '|image' => 'Base Image',
            ExportProduct::PREFIX_IMAGE_ATTRIBUTE . '|small_image' => 'Small Image',
        ];
    }

    public function getProductAttributes()
    {
        $attributes = [];
        $codes = $this->_export->getExportAttrCodesList();

        foreach($codes as $code => $title){
            $attributes[ExportProduct::PREFIX_PRODUCT_ATTRIBUTE . "|" . $code] = $title;
        }

        return $attributes;
    }
}
