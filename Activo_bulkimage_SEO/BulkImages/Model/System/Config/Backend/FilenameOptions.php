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

class FilenameOptions implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Fetch options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Activo\BulkImages\Model\Import::FILENAME_OPTIONS_NOCHANGE,
                'label' => 'No Change'],
            ['value' => \Activo\BulkImages\Model\Import::FILENAME_OPTIONS_NAME,
                'label' => '[PRODUCT NAME]'],
            ['value' => \Activo\BulkImages\Model\Import::FILENAME_OPTIONS_NAME_SKU,
                'label' => '[PRODUCT NAME]-[SKU]'],
            ['value' => \Activo\BulkImages\Model\Import::FILENAME_OPTIONS_SKU_NAME,
                'label' => '[SKU]-[PRODUCT NAME]'],
        ];
    }
}
