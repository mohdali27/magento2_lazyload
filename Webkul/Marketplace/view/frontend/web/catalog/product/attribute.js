define([
    'jquery',
    'jquery/ui',
    'Webkul_Marketplace/catalog/product-attributes'
], function ($) {
    'use strict';

    $.widget('mage.configurableAttribute', $.mage.productAttributes, {
        _prepareUrl: function () {
            var name = $('#configurable-attribute-selector').val();

            return this.options.url +
                (/\?/.test(this.options.url) ? '&' : '?') +
                'set=' + window.encodeURIComponent($('#attribute_set_id').val()) +
                '&attribute[frontend_label]=' +
                window.encodeURIComponent(name);
        }
    });

    return $.mage.configurableAttribute;
});
