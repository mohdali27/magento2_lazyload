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
        var orderSpan = $('#order_span');
        var urlOrder = $('#urlOrder').text();
        var storeIdOrd = $('#storeIdOrd').text();
        $('#resetnow_order').click(function () {
            var params = {storeId: storeIdOrd};
            new Ajax.Request(urlOrder, {
                parameters:     params,
                loaderArea:     false,
                asynchronous:   true,
                onCreate: function() {
                    orderSpan.find('.success').hide();
                    orderSpan.find('.error').hide();
                    orderSpan.find('.processing').show();
                    $('#order_message').text('');
                },
                onSuccess: function(response) {
                    orderSpan.find('.processing').hide();
                    var resultText = '';
                    if (response.status > 200) {
                        resultText = 'Request Timeout';
                        orderSpan.find('.success').show();
                    } else {
                        resultText = 'Success';
                        orderSpan.find('.success').show();
                    }
                    $('#order_message').text(resultText);
                },
                onFailure: function(response) {
                    orderSpan.find('.processing').hide();               
                    var resultText = 'Not Allowed';
                    orderSpan.find('.error').show();
                    $('#order_message').text(resultText);
                }
            });
        });
});
