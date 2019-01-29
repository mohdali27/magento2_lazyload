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
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.sellerCreateConfigurable', {
        _create: function () {
            var self = this;
            var fcop=0;
            $("button#add_new_defined_option").click(function () {
                $('#cust').show();
            });
            $("button#save").click(function () {
                if ($('#apply_to').is(":visible")) {
                    $('#protype').attr('disabled', 'disabled');
                }
            });
            var attr_options=0,select=0;
            $("#frontend_input").click(function () {
                if (attr_options !== 0 && select !== 1) {
                    attr_options=$(".wk-mp-option-box").clone();
                }
            });
            
            $("#associate-product").delegate('.wk-mp-headcus input','focusout',function () {
                    $(this).attr('value',$(this).val());
            });
            
            $("#associate-product").delegate('.wk-mp-headcus input[type="checkbox"]','focusout',function () {
                if ($(this).is(":checked")) {
                    $(this).attr('checked','checked');
                } else {
$(this).removeAttr("checked");
                }
            });

            $("#frontend_input").change(function () {
                $('.val_required').show();
                $(".wk-mp-option-box").remove();
                if ($("#frontend_input").val() == "multiselect" || $("#frontend_input").val() == "select") {
                    if (attr_options===0) {
                        var headone=$('<div/>').addClass("wk-mp-option-box")
                        .append(
                            $('<ul/>').addClass("wk-mp-headcus ul_first")
                            .append($('<li/>').text($t('Admin')))
                            .append($('<li/>').text($t('Default Store View')))
                            .append($('<li/>').text($t('Position')))
                            .append($('<li/>').text($t('Is Default')))
                            .append(
                                $('<li/>').append(
                                    $('<button/>').attr({type:'button', value:$t('Add Option'),title:$t('Add Option'),class:"attroptions button"}).append(
                                        "<span><span>"+$t('Add Option')+"</span></span>"
                                    )
                                )
                            )
                        );
                        $('#cust').append(headone);
                        $(".attroptions").trigger("click");
                        attr_options++;
                    } else {
                        $('#cust').append($('<div/>').addClass("wk-mp-option-box").append(attr_options.html()));
                    }
                } else {
                    select=1;
                }
            });

            $("#associate-product").delegate(".deletecusopt","click",function () {
                $(this).parents(".wk-mp-headcus").remove();
            });

            $("#associate-product").delegate(".attroptions","click",function () {
                var addcust = $('<ul/>').addClass('wk-mp-headcus')
                                .append($('<li/>')
                                        .append($('<input/>').attr({type:'text',class:"required-entry widthinput",name:'attroptions['+fcop+'][admin]'})))
                                .append($('<li/>')
                                        .append($('<input/>').attr({type:'text',class:"widthinput",name:'attroptions['+fcop+'][store]'})))
                                .append($('<li/>')
                                        .append($('<input/>').attr({type:'text',class:"widthinput",name:'attroptions['+fcop+'][position]'})))
                                .append($('<li/>')
                                        .append($('<input/>').attr({type:'checkbox',class:"widthinput",name:'attroptions['+fcop+'][isdefault]'})))
                                .append($('<li/>')
                                        .append($('<button/>').attr({type:'button', value:" Delete Row",title:$t('Delete Row'),class:"deletecusopt button"}).append("<span><span>"+$t('Delete')+"</span></span>")));
                $('.wk-mp-option-box').append(addcust);
                fcop++;
            });
            
            $(document).on('change','.widthinput',function () {
                var validt = $(this).val();
                var regex = /(<([^>]+)>)/ig;
                var mainvald = validt .replace(regex, "");
                $(this).val(mainvald);
            });
        }
    });
    return $.mage.sellerCreateConfigurable;
});
