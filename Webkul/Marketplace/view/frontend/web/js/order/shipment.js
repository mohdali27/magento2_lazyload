/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
 define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'mage/template'
], function ($, $t, alert, mageTemplate) {
    'use strict';
    $.widget('mage.sellerOrderShipment', {
        _create: function () {
            var self = this;
            $('#wk-mp-tracking-carrier').change(function () {
                if ($('select#wk-mp-tracking-carrier option:selected').val() != 'custom') {
                    var val = $('select#wk-mp-tracking-carrier option:selected').text();
                    $('#wk-mp-tracking-title').attr('value', $.trim(val));
                } else {
                    $('#wk-mp-tracking-title').attr('value', '');
                }
            });
            $('body').on('click', '.wk-mp-tracking-action-delete', function (e) {
                var thisObj = $(this);
                $.ajax({
                    url: thisObj.attr('data-url'),
                    type: "POST",
                    showLoader: true,
                    success:function ($data) {
                        if ($data.error) {
                            alert({
                                content: $data.message
                            });
                        } else {
                            thisObj.parents('tr').remove();
                        }
                    },
                    error: function (response) {
                        alert({
                            content: self.options.ajaxErrorMessage
                        });
                    }
                });
            });
            $('#wk-mp-tracking-add').click(function (e) {
                $.ajax({
                    url: self.options.addTrackingAjaxUrl,
                    type: "POST",
                    data: {
                        carrier: $('#wk-mp-tracking-carrier').val(),
                        title: $('#wk-mp-tracking-title').val(),
                        number: $('#wk-mp-tracking-number').val()
                    },
                    showLoader: true,
                    success:function ($data) {
                        if ($data.error) {
                            alert({
                                content: $data.message
                            });
                        } else {
                            var progressTmpl = mageTemplate('#sellerOrderShipmentTemplate'),tmpl;
                            tmpl = progressTmpl({
                                data: {
                                    carrier: $data.carrier,
                                    title: $data.title,
                                    number: $data.number,
                                    numberclass: $data.numberclass,
                                    numberclasshref: $data.numberclasshref,
                                    trackingPopupUrl: $data.trackingPopupUrl,
                                    trackingDeleteUrl: $data.trackingDeleteUrl
                                }
                            });
                            $('#wk-mp-shipment-tracking-info-tbody').append(tmpl);
                            $('#wk-mp-tracking-carrier').val('custom');
                            $('#wk-mp-tracking-title').val('');
                            $('#wk-mp-tracking-number').val('');
                        }
                    },
                    error: function (response) {
                        alert({
                            content: self.options.ajaxErrorMessage
                        });
                    }
                });
            });
        }
    });
    return $.mage.sellerOrderShipment;
});
