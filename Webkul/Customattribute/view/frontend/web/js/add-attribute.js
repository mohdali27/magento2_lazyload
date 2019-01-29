/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
        "jquery",
        'mage/translate',
        "mage/template",
        "mage/mage",
        "mage/calendar",
    ], function ($, $t,mageTemplate, alert) {
        'use strict';
        $.widget('mage.addAttribute', {

            options: {
                count : 0,
            },
            _create: function () {
                var self = this;
                $(self.options.dateTypeSelector).calendar({ dateFormat:'mm/dd/yy'});
                    var count = parseInt(self.options.tierCount);
                    self.options.count = count-1;
                $(self.options.tierPriceSelector).delegate(self.options.addTierPrice,'click',function () {
                    self.options.count += 1;
                    var progressTmpl = mageTemplate(self.options.tierTemplate),
                                      tmpl;
                     tmpl = progressTmpl({
                                data: {
                                    button: $t("Delete"),
                                    index: self.options.count,
                                }
                            });
                    $(self.options.tierPriceSelector+' .wk_mp_option-box #tiers_table').append(tmpl);
                });

                $('body').delegate(self.options.deleteOptionSelector,'click',function (event) {
                    $(this).parent().parent().remove();
                });
                $(self.options.tierPriceSelector).delegate(self.options.deleteButton,'click',function () {
                    var countTbody = $(this).parents('table').children('.wk_mp_headcus').length;
                    $(this).parents('.wk_mp_headcus').remove();
                    if (countTbody == 1) {
                        $(self.options.addTierPrice).click();
                    }
                });
                $(self.options.tierPriceSelector).delegate('.qty','keypress',function (event) {
                    self.numbersonly(event,true);
                });

                $(self.options.attSetidSelector).on("change", function () {
                    var setId = $(this).val();
                     $.ajax(self.options.actionUrl, {
                        method: 'post',
                        data:{setid:setId, type:self.options.productType ,productid:self.options.productId, url:self.options.currentUrl},
                        showLoader: true,
                        success: function (transport) {
                            window.location.href = transport.url;
                        }
                     });
                });
                
            },

            numbersonly: function (e, dec) {
                var key;
                var keychar;
                if (window.event) {
                    key = window.event.keyCode;
                } else if (e) {
                    key = e.which;
                } else {
                    return true;
                }
                keychar = String.fromCharCode(key);
                if ((key==null) || (key==0) || (key==8) ||
                    (key==9) || (key==13) || (key==27) ) {
                    return true;
                } else if ((("0123456789").indexOf(keychar) > -1)) {
                    return true;
                } else {
                    return false;
                }
            }
        });
    return $.mage.addAttribute;
    }
);
