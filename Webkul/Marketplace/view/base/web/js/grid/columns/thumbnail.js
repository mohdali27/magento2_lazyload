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
    './column',
    'jquery',
    'mage/template',
    'text!Webkul_Marketplace/templates/grid/cells/thumbnail/preview.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, thumbnailPreviewTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/thumbnail',
            fieldClass: {
                'data-grid-thumbnail-cell': true
            }
        },
        getSrc: function (row) {
            return row[this.index + '_src']
        },
        getOrigSrc: function (row) {
            return row[this.index + '_orig_src'];
        },
        getName: function (row) {
            return row[this.index + '_name'];
        },
        getPrice: function (row) {
            return row[this.index + '_price'];
        },
        getDescription: function (row) {
            return row[this.index + '_description'];
        },
        getLink: function (row) {
            return row[this.index + '_link'];
        },
        getAlt: function (row) {
            return row[this.index + '_alt']
        },
        preview: function (row) {
            var modalHtml = mageTemplate(
                thumbnailPreviewTemplate,
                {
                    src: this.getOrigSrc(row),
                    alt: this.getAlt(row),
                    link: this.getLink(row),
                    name: this.getName(row),
                    price: this.getPrice(row),
                    description: this.getDescription(row),
                    linkText: $.mage.__('Go to Details Page')
                }
            );
            var previewPopup = $('<div/>').html(modalHtml);
            previewPopup.modal({
                //title: this.getAlt(row),
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []}).trigger('openModal');
        },
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        }
    });
});
