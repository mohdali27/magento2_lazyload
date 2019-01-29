/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/template',
    'uiComponent',
    'ko',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'mage/calendar'
    ], function ($, mageTemplate, Component, ko, $t, alert) {
        'use strict';
        var totalSelected = ko.observable(0);
        return Component.extend({
            initialize: function () {
                this._super();
                var self = this;
                $('body').find(".wk-mp-body").dateRange({
                    'dateFormat':'mm/dd/yy',
                    'from': {
                        'id': 'colender-check #special-from-date'
                    },
                    'to': {
                        'id': 'colender-check #special-to-date'
                    }
                });

                $('body').delegate('.mp-edit','click',function () {
                    var dicision=confirm($t(" Are you sure you want to edit this product ? "));
                    if (dicision === true) {
                        var $url=$(this).attr('data-url');
                        window.location = $url;
                    }
                });
                $('body').delegate('#mass-delete-butn','click', function (e) {
                    var flag =0;
                    $('.mpcheckbox').each(function () {
                        if (this.checked === true) {
                            flag =1;
                        }
                    });
                    if (flag === 0) {
                        alert({content : $t(' No Checkbox is checked ')});
                        return false;
                    } else {
                        var dicisionapp=confirm($t(" Are you sure you want to delete these product ? "));
                        if (dicisionapp === true) {
                            $('#form-customer-product-new').submit();
                        } else {
                            return false;
                        }
                    }
                });

                $('body').delegate('.mpcheckbox', 'click', function (event) {
                    var self = this;
                    if (this.checked) {
                        totalSelected(totalSelected()+1);
                    } else {
                       totalSelected(totalSelected()-1);
                    }
                });

                $('body').delegate('#mpselecctall', 'click', function (event) {
                    totalSelected(0);
                    if (this.checked) {
                        $('.mpcheckbox').each(function () {
                            this.checked = true;
                            totalSelected(totalSelected()+1);
                        });
                    } else {
                        $('.mpcheckbox').each(function () {
                            this.checked = false;
                            totalSelected(0);
                        });
                    }
                });

                $('body').delegate('.mp-delete', 'click', function () {
                    var dicisionapp=confirm($t(" Are you sure you want to delete this product ? "));
                    if (dicisionapp === true) {
                        var $url=$(this).attr('data-url');
                        window.location = $url;
                    }
                });
            },
            getTotalSelected: function () {
                return totalSelected();
            }
        });
    });
