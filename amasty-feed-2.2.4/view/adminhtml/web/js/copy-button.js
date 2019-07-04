define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement("textarea"),
            result = false;
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();

        try {
            result = document.execCommand('copy');
        } catch (error) {
            console.error('Fallback: Oops, unable to copy', error);
        }

        document.body.removeChild(textArea);

        return result;
    }

    $(document).on('click', '.amasty-copy-on-clipboard-button', function () {
        var button = $(this),
            text = button.parent().find(".amasty-copy-on-clipboard-text"),
            result = fallbackCopyTextToClipboard(text.attr('href'));

        if (result) {
            button.html($.mage.__('Copied'));
        } else {
            button.html($.mage.__('Error'));
        }

        setTimeout(function () {
            button.html($.mage.__('Copy Link'));
        }, 3000);

        return false;
    });
});