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
    $.widget('mage.sellerDashboard', {
        options: {
            backUrl: '',
            opacityLow: '0.4',
            opacityHigh: '1',
            ajaxSuccessMessage: $t('Your mail has been sent.'),
            ajaxErrorMessage: $t('There was error during fetching results.'),
            wrongCaptchaErrorMessage: $t('Wrong verification number.')
        },
        _create: function () {
            var self = this;

            $('body').append($(self.options.mpAskDataSelector));

            $(self.options.askQueSelector).on('click', function () {
                $(self.options.askFormInputSelector, self.options.askFormTextareaSelector).removeClass(self.options.mageErrorClass);
                $(self.options.pageWrapperSelector).css('opacity', self.options.opacityLow);
                $(self.options.mpModelPopupSelector).addClass(self.options.showClass);
                $(self.options.mpAskDataSelector).show();
            });

            $(self.options.wkCloseSelector).click(function () {
                $('body').removeClass('_has-modal');
                $('.modals-overlay').remove();                
                $(self.options.pageWrapperSelector).css('opacity', self.options.opacityHigh);
                $(self.options.resetBtnSelector).trigger('click');
                $(self.options.mpAskDataSelector).hide();
                $(self.options.askFormValidationFailedSelector).each(function () {
                    $(this).removeClass(self.options.validationFailedSelector);
                });
                $(self.options.askFormValidationAdviceSelector).each(function () {
                    $(this).remove();
                });
            });

            $(self.options.askBtnSelector).click(function () {
                var askDataForm = $(self.options.askFormSelector);
                askDataForm.mage('validation', {});
                if (askDataForm.valid()!=false) {
                    var thisthis = $(this);
                    if (thisthis.hasClass("clickask")) {
                        if (parseInt(self.options.captchaEnableStatus)) {
                            var total = parseInt($(self.options.mpCaptcha1Selector).text()) + parseInt($(self.options.mpCaptcha2Selector).text());
                            var wkMpCaptcha = $(self.options.mpCaptchaSelector).val();
                            if (total != wkMpCaptcha) {
                                $(self.options.mpCaptcha1Selector).text(Math.floor((Math.random()*10)+1));
                                $(self.options.mpCaptcha2Selector).text(Math.floor((Math.random()*100)+1));
                                $(self.options.mpCaptchaSelector).val('');
                                $(self.options.mpCaptchaSelector).addClass(self.options.mageErrorClass);
                                $(this).addClass(self.options.mageErrorClass);
                                $(self.options.askFormErrorMailSelector)
                                    .text(self.options.wrongCaptchaErrorMessage)
                                    .slideDown('slow')
                                    .delay(2000)
                                    .slideUp('slow');
                                return false;
                            }
                        }
                        self.callAjaxFunction(thisthis);
                    }
                    return false;
                }
            });

            $(self.options.mpYearLocationChartSelector).on('click', function () {
                var thisthis = $(this);
                self.callChartAjaxFunction(thisthis, 'location', 'year');
            });
            
            $('#wk-location-chart-month').on('click', function () {
                var thisthis = $(this);
                self.callChartAjaxFunction(thisthis, 'location', 'month');
            });

            $('#wk-location-chart-week').on('click', function () {
                var thisthis = $(this);
                self.callChartAjaxFunction(thisthis, 'location', 'week');
            });

            $('#wk-location-chart-day').on('click', function () {
                var thisthis = $(this);
                self.callChartAjaxFunction(thisthis, 'location', 'day');
            });

            $('#wk-diagram-chart-year').on('click', function () {
                var thisthis = $(this);
                self.callChartAjaxFunction(thisthis, 'diagram', 'year');
            });

            $('#wk-diagram-chart-month').on('click', function () {
                var thisthis = $(this);
                self.callChartAjaxFunction(thisthis, 'diagram', 'month');
            });

            $('#wk-diagram-chart-week').on('click', function () {
                var thisthis = $(this);
                self.callChartAjaxFunction(thisthis, 'diagram', 'week');
            });

            $('#wk-diagram-chart-day').on('click', function () {
                var thisthis = $(this);
                self.callChartAjaxFunction(thisthis, 'diagram', 'day');
            });

            $('#wk-mp-dashboard-chart-select').change(function() {
                $("#wk-mp-dashboard-chart-select option:selected").each(function() {
                    if ($(this).val() == 'yearly') {
                        var thisthis = $(this);
                        self.callChartAjaxFunction(thisthis, 'location', 'year');
                    } else if ($(this).val() == 'monthly') {
                        var thisthis = $(this);
                        self.callChartAjaxFunction(thisthis, 'location', 'month');
                    } else if ($(this).val() == 'weekly') {
                        var thisthis = $(this);
                        self.callChartAjaxFunction(thisthis, 'location', 'week');
                    } else if ($(this).val() == 'daily') {
                        var thisthis = $(this);
                        self.callChartAjaxFunction(thisthis, 'location', 'day');
                    }
                });
            }).trigger( "change" );
        },

        callAjaxFunction: function (thisthis) {
            var self = this;
            thisthis.removeClass('clickask');
            $(self.options.mpAskDataSelector).addClass(self.options.mailProcssClass);
            $.ajax({
                url : self.options.ajaxMailSendUrl,
                data : $(self.options.askFormSelector).serialize(),
                type : 'post',
                dataType : 'json',
                success: function (d) {
                    thisthis.addClass('clickask');
                    $(self.options.mpAskDataSelector).removeClass(self.options.mailProcssClass);
                    alert({
                        content: self.options.ajaxSuccessMessage,
                        actions: {
                            always: function () {
                                $(self.options.wkCloseSelector).trigger('click');
                            }
                        }
                    });
                },
                error: function (response) {
                    alert({
                        content: self.options.ajaxErrorMessage,
                        actions: {
                            always: function () {
                                $(self.options.wkCloseSelector).trigger('click');
                            }
                        }
                    });
                }
            });
        },

        callChartAjaxFunction: function (thisthis, chartType, type) {
            var self = this;
            $('#wk-'+chartType+'-chart').attr('src', self.options.loader);
            $.ajax({
                url : self.options.ajaxChartUrl,
                data : {chartType: chartType, dateType: type},
                type : 'post',
                dataType : 'json',
                success: function (d) {
                    $('#wk-'+chartType+'-chart').attr('src', d);
                },
                error: function (response) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                }
            });
        }
    });
    return $.mage.sellerDashboard;
});
