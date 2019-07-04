define([
    'jquery',
    'underscore'
], function ($) {
    'use strict';
    return function (config) {
        $('[data-amfeed-js="amfeed-taxonomy-input"]').click(function () {
            if ($('#feed_category_use_taxonomy').val() == '1') {
                $(this).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: config.ajaxUrl,
                            data: {
                                category: request.term,
                                source: $('#feed_category_taxonomy_source').val()
                            },

                            success: function (result) {
                                response(result);
                            }
                        });
                    },
                    appendTo: '[data-amfeed-js="amfeed-category-list"]',
                    messages: {
                        results: function () {}
                    }
                });
            }
        });
    }
});
