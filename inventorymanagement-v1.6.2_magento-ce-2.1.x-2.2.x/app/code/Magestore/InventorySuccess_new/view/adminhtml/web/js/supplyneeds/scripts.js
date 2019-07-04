/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

require([
    "jquery",
    "loader",
    'Magento_Ui/js/modal/confirm',
    'mage/translate'],
    function  ($,loader,confirm) {
    $(document).ready(function(){
        if (typeof $('select[name="sales_period"]') !='undefine') {
            setInterval(function () {
                if ($('select[name="sales_period"]').val() == 'custom') {
                    $('input[name="from_date"]').parent().parent().show();
                    $('input[name="to_date"]').parent().parent().show();
                } else {
                    $('input[name="from_date"]').parent().parent().hide();
                    $('input[name="to_date"]').parent().parent().hide();
                }
            }, 1000);
        }
        $('body').delegate('select[name="sales_period"]','click', function() {
            if ($(this).val() == 'custom') {
                $('input[name="from_date"]').parent().parent().show();
                $('input[name="to_date"]').parent().parent().show();
            } else {
                $('input[name="from_date"]').parent().parent().hide();
                $('input[name="to_date"]').parent().parent().hide();
            }
        });
    })
});