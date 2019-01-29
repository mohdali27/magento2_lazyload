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
    ], function ($, mageTemplate, Component, ko) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                var self = this;
                this.notifyTmp = mageTemplate('#wk_notification_template');
                this.productData = window.notificationConfig.productNotification;
                this.sellerData = window.notificationConfig.sellerNotification;
                this.feedbackData = window.notificationConfig.feedbackNotification;
                if (this.productData.length) {
                    this._showProductNotification(this.productData);
                }
                if (this.sellerData.length) {
                    this._showSellerNotification(this.sellerData);
                }
                if (this.feedbackData.length) {
                    this._showFeedbackNotification(this.feedbackData);
                }
                $('.wk-notifications-action.marketplace-dropdown').on('click', function (event) {
                    event.preventDefault();
                    self._showNotificationBox($(this));
                });
                $('body').on('click', function (event) {
                    self._closeNotifyWindow(event);
                });
            },
            _showProductNotification: function (productData) {
                $('[data-ui-id="menu-webkul-marketplace-product"]').css('position','relative');
                $('[data-ui-id="menu-webkul-marketplace-product"]').css('padding-right', '50px');
                var data = {},
                    notifyTmp;

                data.notificationCount = productData.length;
                data.notificationImage = window.notificationConfig.image;
                data.notifications = productData;
                data.notificationType = 'product';
                notifyTmp = this.notifyTmp({
                    data: data
                });
                $(notifyTmp)
                .appendTo($('[data-ui-id="menu-webkul-marketplace-product"]'));
            },
            _showSellerNotification: function (sellerData) {
                $('[data-ui-id="menu-webkul-marketplace-seller"]').css('position','relative');
                $('[data-ui-id="menu-webkul-marketplace-seller"]').css('position', '50px');
                var data = {},
                    notifyTmp;

                data.notificationCount = sellerData.length;
                data.notificationImage = window.notificationConfig.image;
                data.notifications = sellerData;
                data.notificationType = 'seller';
                notifyTmp = this.notifyTmp({
                    data: data
                });
                $(notifyTmp)
                .appendTo($('[data-ui-id="menu-webkul-marketplace-seller"]'));
            },
             _showFeedbackNotification: function (feedbackData) {
                $('[data-ui-id="menu-webkul-marketplace-feedback"]').css('position','relative');
                $('[data-ui-id="menu-webkul-marketplace-feedback"]').css('position', '50px');
                var data = {},
                    notifyTmp;

                data.notificationCount = feedbackData.length;
                data.notificationImage = window.notificationConfig.image;
                data.notifications = feedbackData;
                data.notificationType = 'feedback';
                notifyTmp = this.notifyTmp({
                    data: data
                });
                $(notifyTmp)
                .appendTo($('[data-ui-id="menu-webkul-marketplace-feedback"]'));
            },
            _showNotificationBox: function (element) {
                
                if ($(element).parent('.wk-notification-block').length) {
                    if ($(element).hasClass('active')) {
                        $(element).removeClass('active');
                        $(element).parent('.wk-notification-block').removeClass('active');
                        $(element).next('.marketplace-dropdown-menu').hide();
                    } else {
                        $('.marketplace-dropdown-menu').hide();
                        $('.wk-notifications-action.marketplace-dropdown').removeClass('active');
                        $('.wk-notification-block').removeClass('active');
                        $(element).addClass('active');
                        $(element).parent('.wk-notification-block').addClass('active');
                        $(element).next('.marketplace-dropdown-menu').show();
                    }
                }
            },
            _closeNotifyWindow: function (event) {
                var className = event.target.className;
                if (className !== 'wk-notification-block' &&
                    className !== 'wk-notifications-action marketplace-dropdown' &&
                    className !== 'wk-notifications-action marketplace-dropdown' &&
                    className !== 'wk-notification-img' &&
                    className !== 'marketplace-dropdown-menu' &&
                    className !== 'wk-notifications-action marketplace-dropdown active' &&
                    className !== 'wk-notification-count'
                ) {
                    $('.wk-notifications-action').removeClass('active');
                    $('.wk-notification-block').removeClass('active');
                    $('.marketplace-dropdown-menu').hide();
                }
            }
        });
    });