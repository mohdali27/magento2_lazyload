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
    $.widget('mage.relatedProduct', {
        options: {
            backUrl: ''
        },
        _create: function () {
            var self = this;
            var indexValue = 0;
            var relatedProductData = $.parseJSON(self.options.relatedProducts);
            if ($.isArray(relatedProductData)) {
                $(document).ajaxComplete(function ( event, request, settings ) {
                    var responseData = $.parseJSON(request.responseText);
                    var currentAjaxUrl = settings.url;
                    if (currentAjaxUrl.indexOf("marketplace_related_product_listing") && responseData.totalRecords>0) {
                        setTimeout(function () {
                            if ($('#related-product-block-wrapper .data-row').length) {
                                relatedProductData.each(function (index, value) {
                                    var indexId = index;
                                    $("#relatedIdscheck"+indexId).trigger("click");
                                    relatedProductData = $.grep(relatedProductData, function (arrValue) {
                                      return indexId !== arrValue;
                                    });
                                });
                                $("#related-product-block-loader").hide();
                                $("#related-product-block-wrapper").show();
                            } else {
                                setTimeout(function () {
                                    if ($('#related-product-block-wrapper .data-row').length) {
                                        relatedProductData.each(function (index, value) {
                                            var indexId = index;
                                            $("#relatedIdscheck"+indexId).trigger("click");
                                        });
                                        $("#related-product-block-loader").hide();
                                        $("#related-product-block-wrapper").show();
                                    } else {
                                        $("#related-product-block-loader").hide();
                                        $("#related-product-block-wrapper").show();
                                    }
                                }, 2000);
                            }
                        }, 2000);
                    } else {
                        $("#related-product-block-loader").hide();
                        $("#related-product-block-wrapper").show();
                    }
                });
            }
            $(this.element).delegate(self.options.gridCheckbox, 'change', function () {
                var productId = $(this).val();
                var parentDivId = $(this).parents('div.admin__data-grid-wrap').parents('div').parents('div').attr('id');
                if (parentDivId == 'related-product-block-wrapper') {
                    if ($(this).is(":checked")) {
                        if (productId == 'on') {
                            $('#related-product-block-wrapper .data-row').each(function () {
                                var trElement = $(this);
                                var progressTmpl = mageTemplate(self.options.templateId),
                                  tmpl;
                                tmpl = progressTmpl({
                                    data: {
                                        index: indexValue,
                                        id: trElement.find('.wk-mp-grid-id-cell').find('div').text(),
                                        name: trElement.find('.wk-mp-grid-name-cell').find('div').text(),
                                        status: trElement.find('.wk-mp-grid-status-cell').find('div').text(),
                                        attribute_set: trElement.find('.wk-mp-grid-attributeset-cell').find('div').text(),
                                        sku: trElement.find('.wk-mp-grid-sku-cell').find('div').text(),
                                        price: trElement.find('.wk-mp-grid-price-cell').find('div').text(),
                                        thumbnail: trElement.find('.data-grid-thumbnail-cell').find('img').attr('src'),
                                        position: indexValue+1,
                                        record_id: trElement.find('.wk-mp-grid-id-cell').find('div').text()
                                    }
                                });
                                indexValue++;
                                $(self.options.relatedProductId).after(tmpl);
                            });
                        } else {
                            var trElement = $(this).parents('tr');
                            var progressTmpl = mageTemplate(self.options.templateId),
                              tmpl;
                            tmpl = progressTmpl({
                                data: {
                                    index: indexValue,
                                    id: trElement.find('.wk-mp-grid-id-cell').find('div').text(),
                                    name: trElement.find('.wk-mp-grid-name-cell').find('div').text(),
                                    status: trElement.find('.wk-mp-grid-status-cell').find('div').text(),
                                    attribute_set: trElement.find('.wk-mp-grid-attributeset-cell').find('div').text(),
                                    sku: trElement.find('.wk-mp-grid-sku-cell').find('div').text(),
                                    price: trElement.find('.wk-mp-grid-price-cell').find('div').text(),
                                    thumbnail: trElement.find('.data-grid-thumbnail-cell').find('img').attr('src'),
                                    position: indexValue+1,
                                    record_id: trElement.find('.wk-mp-grid-id-cell').find('div').text()
                                }
                            });
                            indexValue++;
                            $(self.options.relatedProductId).after(tmpl);
                        }
                    } else {
                        $('#related-product-record'+productId).remove();
                    }
                }
            });
        }
    });
    return $.mage.relatedProduct;
});
