define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    return function (config, element) {
        var ajaxUrl = config.ajaxUrl,
            contentElement = $('[data-amfeed-js="preview-content"]'),
            errorElement = $('[data-amfeed-js="preview-error"]'),
            downloadLink,

        showPreview = function () {
            previewModal.toggleModal();

            if (!contentElement.val()) {
                ajaxRefresh()
            }
        },

        ajaxRefresh = function () {
            new Ajax.Request(ajaxUrl, {
                onSuccess: function (transport) {
                    var response = transport.responseText;


                    if (response.isJSON()) {
                        response = response.evalJSON();

                        if (response.error) {
                            errorElement
                                .text(response.message)
                                .show();
                            contentElement.hide();
                            downloadButton.hide();
                        } else if (response.fileType) {
                            switch (response.fileType) {
                                case 'xml':
                                    contentElement.text(formatXml(response.content));
                                    break;
                                case 'csv':
                                case 'txt':
                                    contentElement.text(response.content);
                                    break;
                            }

                            errorElement.hide();
                            contentElement.show();
                            downloadButton.show();

                            createLink(response.content, response.fileType);
                        }
                    }
                }
            });
        },

        formatXml = function (xmlString) {
            var formatted = '',
                reg = /(>)(<)(\/*)/g,
                pad = 0;
            xmlString = xmlString.replace(reg, '$1\r\n$2$3');
            $.each(xmlString.split('\r\n'), function(index, node) {
                var indent = 0;

                if (node.match( /.+<\/\w[^>]*>$/ )) {
                    indent = 0;
                } else if (node.match( /^<\/\w/ )) {
                    if (pad != 0) {
                        pad -= 1;
                    }
                } else if (node.match( /^<\w([^>]*[^\/])?>.*$/ )) {
                    indent = 1;
                } else {
                    indent = 0;
                }

                var padding = '';

                for (var i = 0; i < pad; i++) {
                    padding += '  ';
                }

                formatted += padding + node + '\r\n';
                pad += indent;
            });

            return formatted;
        },

        createLink = function (content, type) {
            if (downloadLink !== undefined) {
                downloadLink.remove();
            }

            downloadLink = document.createElement('a');
            downloadLink.setAttribute('href', 'data:application/octet-stream;charset=utf-8,' + encodeURIComponent(content));
            downloadLink.setAttribute('download', 'preview.' + type);

            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
        },

        downloadFile = function() {
            if (downloadLink !== undefined) {
                downloadLink.click();
            }
        },

        modalParams = {
            type: 'slide',
            title: $.mage.__('Feed Preview'),
            buttons: [
                {
                    text : $.mage.__('Download'),
                    class: 'amfeed-preview-download',
                    attr: { 'data-amfeed-js' : 'preview-download', 'style' : 'display:none' },
                    click: downloadFile
                },
                {
                    text : $.mage.__('Reload'),
                    class: '',
                    attr: {},
                    click: ajaxRefresh
                },

            ]},

        previewModal = modal(modalParams, $('[data-amfeed-js="preview-block"]')),
        downloadButton = $('[data-amfeed-js="preview-download"]');

        $(element).on('click', function () {
            showPreview();
        });
    }
});
