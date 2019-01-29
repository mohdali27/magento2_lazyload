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
    "jquery/ui",
    'mage/calendar'
], function ($) {
    'use strict';
    $.widget('mage.sellerOrderHistory', {
        _create: function () {
            var self = this;
            
            $('.wk-shipslip').click(function () {
                $('#wk-ship-light').hide();
                $('#wk-ship-fade').hide();
            });

            $('body').append($('#wk-mp-invoice-print-data'));

            $('#invoice-lightboxopen').click(function () {
                $('#form-invoice-print input, #form-invoice-print textarea').removeClass('error_border');
                $('.page-wrapper').css('opacity','0.4');
                $('#wk-mp-invoice-print-data').find('.wk-mp-model-popup').addClass('_show');
                $('#wk-mp-invoice-print-data').show();
            });
            $('.wk-close').click(function () {
                $('.page-wrapper').css('opacity','1');
                $('#resetbtn').trigger('click');
                $('#wk-mp-invoice-print-data').hide();
                $('#form-invoice-print .validation-failed').each(function () {
                    $(this).removeClass('validation-failed');
                });
                $('#form-invoice-print .validation-advice').each(function () {
                    $(this).remove();
                });
            });
            
            $('.wk-shipslip').click(function () {
                $('#wk-ship-light').hide();
                $('#wk-ship-fade').hide();
            });

            $('body').append($('#wk-mp-shipping-print-data'));

            $('#shiplightboxopen').click(function () {
                $('#form-shipping-print input,#form-shipping-print textarea').removeClass('error_border');
                $('.page-wrapper').css('opacity','0.4');
                $('#wk-mp-shipping-print-data').find('.wk-mp-model-popup').addClass('_show');
                $('#wk-mp-shipping-print-data').show();
            });
            $('.wk-close').click(function () {
                $('.page-wrapper').css('opacity','1');
                $('#resetbtn').trigger('click');
                $('#wk-mp-shipping-print-data').hide();
                $('#form-shipping-print .validation-failed').each(function () {
                    $(this).removeClass('validation-failed');
                });
                $('#form-shipping-print .validation-advice').each(function () {
                    $(this).remove();
                });
            });
            $(".wk-mp-body").dateRange({
                'dateFormat':'mm/dd/yy',
                'from': {
                    'id': 'special-from-date'
                },
                'to': {
                    'id': 'special-to-date'
                }
            });
            $("#form-shipping-print").dateRange({
                'dateFormat':'mm/dd/yy',
                'from': {
                    'id': 'editfromdatepicker'
                },
                'to': {
                    'id': 'edittodatepicker'
                }
            });
            $("#form-invoice-print").dateRange({
                'dateFormat':'mm/dd/yy',
                'from': {
                    'id': 'invoice_editfromdatepicker'
                },
                'to': {
                    'id': 'invoice_edittodatepicker'
                }
            });
        }
    });
    return $.mage.sellerOrderHistory;
});
