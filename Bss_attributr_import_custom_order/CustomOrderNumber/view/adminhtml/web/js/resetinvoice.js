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
 * @package    Bss_CustomOrderNumber
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    "jquery",
    "prototype"
], function ($) {
        var invoiceSpan = $('#invoice_span');
        var urlInvoice = $('#urlInvoice').text();
        var storeIdInv = $('#storeIdInv').text();
        $('#resetnow_invoice').click(function () {
            var params = {storeId: storeIdInv};
            new Ajax.Request(urlInvoice, {
                parameters:     params,
                loaderArea:     false,
                asynchronous:   true,
                onCreate: function() {
                    invoiceSpan.find('.success').hide();
                    invoiceSpan.find('.error').hide();
                    invoiceSpan.find('.processing').show();
                    $('#invoice_message').text('');
                },
                onSuccess: function(response) {
                    invoiceSpan.find('.processing').hide();
                    var resultText = '';
                    if (response.status > 200) {
                        resultText = 'Request Timeout';
                        invoiceSpan.find('.success').show();
                    } else {
                        resultText = 'Success';
                        invoiceSpan.find('.success').show();
                    }
                    $('#invoice_message').text(resultText);
                },
                onFailure: function(response) {
                    invoiceSpan.find('.processing').hide();
                    var resultText = 'Not Allowed';
                    invoiceSpan.find('.error').show();
                    $('#invoice_message').text(resultText);
                }
            });
        });
});
