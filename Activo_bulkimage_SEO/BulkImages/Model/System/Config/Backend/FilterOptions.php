<?php
/**
 * Activo Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Activo Commercial License
 * that is available through the world-wide-web at this URL:
 * http://extensions.activo.com/license_professional
 *
 * @copyright   Copyright (c) 2017 Activo Extensions (http://extensions.activo.com)
 * @license     Commercial
 */
namespace Activo\BulkImages\Model\System\Config\Backend;

class FilterOptions implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Fetch options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Activo\BulkImages\Model\Import::FILTER_OPTIONS_ALL, 'label' => 'All Products'],
            ['value' => \Activo\BulkImages\Model\Import::FILTER_OPTIONS_VISIBLE_SEARCH_CATALOG, 'label' => 'Visible in Search and Catalog'],
            ['value' => \Activo\BulkImages\Model\Import::FILTER_OPTIONS_VISIBLE_SEARCH, 'label' => 'Visible in Search'],
            ['value' => \Activo\BulkImages\Model\Import::FILTER_OPTIONS_VISIBLE_CATALOG, 'label' => 'Visible in Catalog'],
        ];
    }
}
