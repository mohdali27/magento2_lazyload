/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/**
 * Color picker function
 */
/*jshint jquery:true*/
define([
    "jquery",
    "colorpicker",
    "jquery/ui"
], function ($) {
    'use strict';
    $.widget('mage.colorPickerFunction', {
        options: {
            spanBackgroundColor: '',
            fadeInSpeed: 500,
            fadeOutSpeed: 500
        },
        _create: function () {
            var self = this;
            if (self.options.getActiveColorPickerStatus) {
                var thisElement = this.element;
                $(thisElement).css(
                    'background-color',
                    '#'+"'"+self.options.spanBackgroundColor+"'"
                );
                $(thisElement).ColorPicker({
                    color: "'"+self.options.spanBackgroundColor+"'",
                    onShow: function (colorPicker) {
                        $(colorPicker).fadeIn(self.options.fadeInSpeed);
                        return false;
                    },
                    onHide: function (colorPicker) {
                        $(colorPicker).fadeOut(self.options.fadeOutSpeed);
                        return false;
                    },
                    onChange: function (hsb, hex, rgb) {
                        $(self.options.backgroundWidthSelector).val('#' + hex);
                        $(thisElement).css('background-color','#'+hex);
                    }
                });
            }
        }
    });
    return $.mage.colorPickerFunction;
});
