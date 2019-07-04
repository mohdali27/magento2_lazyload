require([
    'jquery',
    'prototype'
], function (jQuery) {
    'use strict';

    function resetForm() {
        jQuery('#edit_form').trigger('reset');

        var oEvent = document.createEvent('Event');
        oEvent.initEvent('change', true, true);

        $('feed_execute_mode').dispatchEvent(oEvent);
        $('feed_delivery_enabled').dispatchEvent(oEvent);
    }

    window.resetForm = resetForm;
});