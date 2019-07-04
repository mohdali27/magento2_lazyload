<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Attribute
{
    public function getInventoryAttributes()
    {
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

    public function getCategoryPathsAttributes()
    {
        $attr = [
            ExportProduct::PREFIX_CATEGORY_PATH_ATTRIBUTE . '|category' => 'Default',
        ];

        foreach ($this->_category->getSortedCollection() as $category) {
            $attr[ExportProduct::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE . '|'.$category->getCode()] = $category->getName();
        }

        return $attr;
    }
}
