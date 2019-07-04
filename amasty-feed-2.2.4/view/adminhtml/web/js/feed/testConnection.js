define([
    'jquery'
], function (jQuery) {
    'use strict';
    var testButton = jQuery(".feed-connection-button"),
        messageSpan = jQuery(".feed-connection-result"),
        xhr = null;

    function changeButtonState() {
        if (jQuery("#feed_delivery_enabled").val() === "1") {
            testButton.show();
            messageSpan.show();
        } else {
            testButton.hide();
            messageSpan.hide();
        }
    }

    return function main(config) {
        changeButtonState();
        jQuery("#feed_delivery_enabled").change(changeButtonState);

        testButton.click(function () {
            if (!jQuery('form.feed-edit-form').validation('isValid')) {
                return;
            }

            if (xhr) {
                return;
            }
            messageSpan.empty().attr('class', 'feed-connection-result');

            xhr = jQuery.ajax({
                showLoader: true,
                url: config.ajaxUrl,
                dataType: 'JSON',
                data: {
                    'host': jQuery("#feed_delivery_host").val(),
                    'proto': jQuery("#feed_delivery_type").val(),
                    'user': jQuery("#feed_delivery_user").val(),
                    'pass': jQuery("#feed_delivery_password").val(),
                    'path': jQuery("#feed_delivery_path").val(),
                    'mode': jQuery("#feed_delivery_passive_mode").val()
                },
                type: "POST",
                success: function (data) {
                    if (data.type === 'error') {
                        messageSpan.addClass('message message-error error').text(data.message);
                    } else {
                        messageSpan.addClass('message message-success success').text(data);
                    }
                },
                complete: function () {
                    xhr = null;
                }
            });
        })
    };
});
