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
    'mage/template',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, mageTemplate, alert) {
    'use strict';
    $.widget('mage.verifySellerShop', {
        options: {
            backUrl: '',
            shopUrl: '[data-role="shop-url"]',
            becomeSellerBoxWrapper: '[data-role="wk-mp-become-seller-box-wrapper"]',
            available: '.available',
            unavailable: '.unavailable',
            emailAddress: '#email_address',
            wantSellerDiv: '#wantptr',
            wantSellerTemplate: '#wantptr-template',
            profileurlClass: '.profileurl',
            wantpartnerClass: '.wantpartner',
            pageLoader: '#wk-load',
            shopLabel: $t('Shop URL'),
            shopTitle: $t(' Shop URL For Your Marketplace Shop '),
            shopText: $t(' (This will be used to display your public profile) ')
        },
        _create: function () {
            var self = this;
            $(self.options.emailAddress).parents('div.field').after($(self.options.wantSellerDiv));
            $(self.options.wantSellerDiv).show();
            $(self.options.wantpartnerClass).on('change', function () {
                self.callAppendShopBlockFunction(this.element, $(this).val());
            });
            $(this.element).delegate(self.options.shopUrl, 'keyup', function () {
                var shopUrlVal = $(this).val();
                $(self.options.shopUrl).val(shopUrlVal.replace(/[^a-z^A-Z^0-9\.\-]/g,''));
            });
            $(this.element).delegate(self.options.shopUrl, 'change', function () {
                self.callAjaxFunction();
            });
        },
        callAppendShopBlockFunction: function (parentelem, elem) {
            var self = this;
            if (elem==1) {
                $(self.options.pageLoader).parents(parentelem)
                .find('button.submit').addClass('disabled');
                var progressTmpl = mageTemplate(self.options.wantSellerTemplate),
                          tmpl;
                tmpl = progressTmpl({
                    data: {
                        label: self.options.shopLabel,
                        src: self.options.loaderImage,
                        title: self.options.shopTitle,
                        text: self.options.shopText
                    }
                });
                $(self.options.wantSellerDiv).after(tmpl);
            } else {
                $(self.options.pageLoader).parents(parentelem)
                .find('button.submit').removeClass('disabled');
                $(self.options.profileurlClass).remove();
            }
        },
        callAjaxFunction: function () {
            var self = this;
            $(self.options.button).addClass('disabled');
            var shopUrlVal = $(self.options.shopUrl).val();
            $(self.options.available).remove();
            $(self.options.unavailable).remove();
            if (shopUrlVal) {
                $(self.options.pageLoader).removeClass('no-display');
                $.ajax({
                    type: "POST",
                    url: self.options.ajaxSaveUrl,
                    data: {
                        profileurl: shopUrlVal
                    },
                    success: function (response) {
                        $(self.options.pageLoader).addClass('no-display');
                        if (response===0) {
                            $(self.options.button).removeClass('disabled');
                            $(self.options.becomeSellerBoxWrapper).append(
                                $('<div/>').addClass('available message success')
                                .text(self.options.successMessage)
                            );
                        } else {
                            $(self.options.button).addClass('disabled');
                            $(self.options.shopUrl).val('');
                            $(self.options.becomeSellerBoxWrapper).append(
                                $('<div/>').addClass('available message error')
                                .text(self.options.errorMessage)
                            );
                        }
                    },
                    error: function (response) {
                        alert({
                            content: $t('There was error during verifying seller shop data')
                        });
                    }
                });
            }
        }
    });
    return $.mage.verifySellerShop;
});
