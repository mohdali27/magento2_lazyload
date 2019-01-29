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
    'uiComponent',
    'mage/translate',
    'Magento_Ui/js/modal/confirm'
], function ($, Component, $t, confirm) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            var self = this;
            $("body").on("click", ".mp-edit", function() {
                var $url = $(this).attr('data-url');
                confirm({
                    content: $t(" Are you sure you want to edit this product ? "),
                    actions: {
                        confirm: function () {
                            window.location = $url;
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            });
            $("body").on("click", ".mp-delete", function() {
                var $url = $(this).attr('data-url');
                confirm({
                    content: $t(" Are you sure you want to delete this product ? "),
                    actions: {
                        confirm: function () {
                            window.location = $url;
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            });
        }
    });
});
