/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    'jquery',
    'mage/storage',
    'mage/template',
    'uiComponent',
    'ko',
    'Magento_Ui/js/modal/alert'
    ], function (
        $,
        storage,
        mageTemplate,
        Component,
        ko,
        alert
    ) {
        'use strict';
        return Component.extend({
            initialize: function () {
                var self = this;
                $('.notifications-action.marketplace-dropdown').on('click', function (event) {
                    event.preventDefault();
                    self._showNotificationBox($(this));
                });
                $('body').on('click', function (event) {
                    self._closeNotifyWindow(event);
                });
            },
            _showNotificationBox: function (element) {
                
                if ($(element).parent('.notification-block').length) {
                    if ($(element).hasClass('active')) {
                        $(element).removeClass('active');
                        $(element).parent('.notification-block').removeClass('active');
                        $(element).next('.marketplace-dropdown-menu').hide();
                    } else {
                        $('.marketplace-dropdown-menu').hide();
                        $('.notifications-action.marketplace-dropdown').removeClass('active');
                        $('.notification-block').removeClass('active');
                        $(element).addClass('active');
                        $(element).parent('.notification-block').addClass('active');
                        $(element).next('.marketplace-dropdown-menu').show();
                    }
                }
            },
            _closeNotifyWindow: function (event) {
                var className = event.target.className;
                if (className !== 'notification-block' &&
                    className !== 'notifications-action marketplace-dropdown' &&
                    className !== 'notifications-action marketplace-dropdown' &&
                    className !== 'notifications-img' &&
                    className !== 'marketplace-dropdown-menu' &&
                    className !== 'notifications-action marketplace-dropdown active' &&
                    className !== 'notification-count'
                ) {
                    $('.notifications-action').removeClass('active');
                    $('.notification-block').removeClass('active');
                    $('.marketplace-dropdown-menu').hide();
                }
            }

        });
    });