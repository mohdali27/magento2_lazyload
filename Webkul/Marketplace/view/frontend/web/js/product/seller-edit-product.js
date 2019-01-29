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
    "jquery/ui",
    'mage/calendar'
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.sellerEditProduct', {
        options: {
            errorMessageSku: $t("SKU can\'t be left empty"),
            ajaxErrorMessage: $t('There was error during fetching results.')
        },
        _create: function () {
            var self = this;
            if (self.options.productTypeId !=' configurable') {
                $("#edit-product").dateRange({
                    'dateFormat':'mm/dd/yy',
                    'from': {
                        'id': 'special-from-date'
                    },
                    'to': {
                        'id': 'special-to-date'
                    }
                });
            }
            $('#wk-mp-save-duplicate-btn').click(function () {
                $("#edit-product").append('<input type="hidden" name="back" value="duplicate">');
                $('#save-btn').trigger('click');
            });
            $('#save-btn').click(function (e) {
                if ($("#edit-product").valid()!==false) {
                    if ($('#description_ifr').length) {
                        var desc = $('#description_ifr').contents().find('#tinymce').text();
                        $('#description-error').remove();
                        if (desc === "" || desc === null) {
                            $('#description-error').remove();
                            $('#description').parent().append('<div class="mage-error" generated="true" id="description-error">This is a required field.</div>');
                        }
                        if (desc !== "" && desc !== null) {
                            $('.button').css('opacity','0.7');
                            $('.button').css('cursor','default');
                            $('.button').attr('disabled','disabled');
                            $('body').trigger('processStart');
                            $('#edit-product').submit();
                        } else {
                            return false;
                        }
                    }
                }
            });
            $('.input-text').change(function () {
                var validt = $(this).val();
                var regex = /(<([^>]+)>)/ig;
                var mainvald = validt .replace(regex, "");
                $(this).val(mainvald);
            });
            $('input#sku').change(function () {
                var len=$('input#sku').val();
                var len2=len.length;
                if (len2 === 0) {
                    alert({
                        content: self.options.errorMessageSku
                    });
                    $('div#skuavail').css('display','none');
                    $('div#skunotavail').css('display','none');
                } else {
                    self.callVerifySkuAjaxFunction();
                }
            });
            $('body').on('change','.wk-elements',function () {
                var category_id=$(this).val();
                if (this.checked === true) {
                    var $obj = $('<input/>').attr('type','hidden').attr('name','product[category_ids][]').attr('id','wk-cat-hide'+category_id).attr('value',category_id);
                    $('.wk-for-validation').append($obj);
                } else {
                    $('#wk-cat-hide'+category_id).remove();
                }
            });
            $("#wk-bodymain").delegate('.wk-plus ,.wk-plusend,.wk-minus, .wk-minusend ',"click",function () {
                var thisthis=$(this);
                if (thisthis.hasClass("wk-plus") || thisthis.hasClass("wk-plusend")) {
                    if (thisthis.hasClass("wk-plus")) {
                        thisthis.removeClass('wk-plus').addClass('wk-plus_click');
                    }
                    if (thisthis.hasClass("wk-plusend")) {
                        thisthis.removeClass('wk-plusend').addClass('wk-plusend_click');
                    }
                    thisthis.prepend("<span class='wk-node-loader'></span>");
                    self.callCategoryTreeAjaxFunction(thisthis);
                }
                if (thisthis.hasClass("wk-minus") || thisthis.hasClass("wk-minusend")) {
                    self.callRemoveCategoryNodeFunction(thisthis);
                }
            });
        },
        callVerifySkuAjaxFunction: function () {
            var self = this;
            $.ajax({
                url: self.options.verifySkuAjaxUrl,
                type: "POST",
                data: {sku:$('input#sku').val()},
                dataType: 'html',
                success:function ($data) {
                    $data=JSON.parse($data);
                    if ($data.avialability==1) {
                        $('div#skuavail').css('display','block');
                        $('div#skunotavail').css('display','none');
                    } else {
                        $('div#skunotavail').css('display','block');
                        $('div#skuavail').css('display','none');
                        $("input#sku").attr('value','');
                    }
                },
                error: function (response) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                }
            });
        },
        callCategoryTreeAjaxFunction: function (thisthis) {
            var self = this;
            var i, len, name, id, checkn;
            $.ajax({
                url     :   self.options.categoryTreeAjaxUrl,
                type    :   "POST",
                data    :   {
                    parentCategoryId : thisthis.siblings("input").val(),
                    categoryIds :   self.options.categories
                },
                dataType:   "html",
                success :   function (content) {
                    var newdata=  $.parseJSON(content);
                    len = newdata.length;
                    var pxl= parseInt(thisthis.parent(".wk-cat-container").css("margin-left").replace("px",""))+20;
                    thisthis.find(".wk-node-loader").remove();
                    if (thisthis.attr("class") == "wk-plus") {
                        thisthis.attr("class","wk-minus");
                    }
                    if (thisthis.attr("class") == "wk-plusend") {
                        thisthis.attr("class","wk-minusend");
                    }
                    if (thisthis.attr("class") == "wk-plus_click") {
                        thisthis.attr("class","wk-minus");
                    }
                    if (thisthis.attr("class") == "wk-plusend_click") {
                        thisthis.attr("class","wk-minusend");
                    }
                    for (i=0; i<len; i++) {
                        id=newdata[i].id;
                        checkn=newdata[i].check;
                        name=newdata[i].name;
                        if (checkn==1) {
                            if (newdata[i].counting === 0) {
                                thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-no"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements" type="checkbox" checked value='+ id+'></div>');
                            } else {
                                thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-plusend"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements" type="checkbox" checked value='+ id +'></div>');
                            }
                        } else {
                            if (newdata[i].counting === 0) {
                                thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-no"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements" type="checkbox" value='+ id+'></div>');
                            } else {
                                thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-plusend"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements" type="checkbox" value='+ id +'></div>');
                            }
                        }
                    }
                    thisthis.parent(".wk-cat-container").nextAll().slideDown(300);
                },
                error: function (response) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                }
            });
        },
        callRemoveCategoryNodeFunction: function (thisthis) {
            if (thisthis.attr("class") == "wk-minus") {
                thisthis.attr("class","wk-plus");
            }
            if (thisthis.attr("class") == "wk-minusend") {
                thisthis.attr("class","wk-plusend");
            }
            var thiscategory = thisthis.parent(".wk-cat-container");
            var marg= parseInt(thiscategory.css("margin-left").replace("px",""));
            while (thiscategory.next().hasClass("wk-removable")) {
                if (parseInt(thiscategory.next().css("margin-left").replace("px",""))>marg) {
                    thiscategory.next().slideUp("slow",function () {
                        $(this).remove();
                    });
                }
                thiscategory = thiscategory.next();
                if (typeof thiscategory.next().css("margin-left")!= "undefined") {
                    if (marg == thiscategory.next().css("margin-left").replace("px","")) {
                        break;
                    }
                }
            }
        }
    });
    return $.mage.sellerEditProduct;
});
