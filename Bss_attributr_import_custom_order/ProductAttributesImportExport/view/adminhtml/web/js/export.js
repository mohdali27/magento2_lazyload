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
require([
    'jquery'
], function ($) {
    $('#entity').change(function () {
        if ($('#entity').val()=='product_attributes') {
            $('#export_filter_grid_container').css('display','none');
            $('.field-fields_enclosure').css('display', 'none');
            $('#attribute-set').appendTo('#export_filter_form');
            $('#attribute-set').css('display','block');
            $('.action-.scalable').appendTo('#export_filter_container');
        } else {
            $('#export_filter_grid_container').css('display','block');
            $('#attribute-set').css('display','none');
        }
    })
})
