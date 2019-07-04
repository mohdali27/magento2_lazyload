define([
    'jquery',
    'prototype',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'Amasty_Feed/js/feed/amprogressbar',
    'Amasty_Feed/js/feed/amsteps'
], function (jQuery, prototype, alert, $translate, amprogressbar, amsteps) {
    'use strict';

    return function (config, element) {
        config = config || {};

        var validate = function () {
            var form = '#edit_form';
            return jQuery(form).validation() && jQuery(form).validation('isValid');
        };

        var stopGenerate = false,
            exported = 0,
            timerInterval = 0,
            requestSumTime = 0,
            generationStartTime = 0,
            generationEndTime = 0;

        var feedGenerate = function (progress, generateUrl, useAjax, page) {
            if (validate()) {
                var params = $('edit_form').serialize(true),
                    generationsErrorWrapper = progress.find('[data-amfeed-js="generation-error-wrapper"]'),
                    generationError = progress.find('[data-amfeed-js="generation-error"]'),
                    downloadLink = progress.find('[data-amfeed-js="download-link"]'),
                    ajaxCallTime;

                params.page = page;
                ajaxCallTime = new Date().getTime();

                new Ajax.Request(generateUrl, {
                    parameters: params,
                    onSuccess: function (transport) {
                        var response = transport.responseText,
                            progressContent = jQuery('[data-amfeed-js="progress-content"]'),
                            activeStep = jQuery('[data-amsteps-js*="-active-step"]'),
                            newRequestTime = new Date().getTime() - ajaxCallTime;

                        requestSumTime = requestSumTime + newRequestTime;

                        if (response.isJSON() && !response.evalJSON().error) {
                            response = response.evalJSON();
                            if (!stopGenerate && !response.isLastPage) {
                                //start Generation Step
                                amsteps.startStep(2);
                                if (newRequestTime > 1000) {
                                    clearInterval(timerInterval);
                                }
                                exported += response.exported;
                                amprogressbar.progressBar(exported / response.total);
                                var timeTotal = response.total * (requestSumTime / exported),
                                    timeLeft = timeTotal - requestSumTime;

                                if (Math.floor((timeLeft / 1000)) > 0 && newRequestTime > 1000) {
                                    amprogressbar.progressTimer(timeLeft);
                                    timerInterval = setInterval(function () {
                                        timeLeft -= 1000;
                                        amprogressbar.progressTimer(timeLeft, timeTotal);
                                    }, 1000);
                                }

                                feedGenerate(progress, generateUrl, useAjax, ++page);
                            } else if (response.download) {
                                //start Final Step
                                amsteps.startStep(3);
                                generationEndTime = new Date().getTime();
                                jQuery('[data-amfeed-js="generation-duration"]').html(amprogressbar.durationToTime(generationEndTime - generationStartTime));
                                jQuery('[data-amfeed-js="generation-count"]').html(response.total);
                                jQuery('[data-amfeed-js="copy-link"]').on('click', function () {
                                    jQuery('[data-amfeed-js="download-link"]').select();
                                    document.execCommand("copy");
                                });
                                downloadLink.val(response.download);
                                clearInterval(timerInterval);
                                timerInterval = 0;
                                requestSumTime = 0;
                                amsteps.completeStep(3, true);
                            }
                        } else {
                            amsteps.completeStep(activeStep.index() + 1);
                            progressContent.slideUp();
                            clearInterval(timerInterval);
                            generationsErrorWrapper.slideDown();
                            generationError.html((response.isJSON())
                                ? $translate('Something went wrong:<br/>') + response.evalJSON().error
                                : $translate('Something went wrong:<br/>') + response);
                        }
                    }
                });
            }
        };

        var progressFeedGenerate = function (url, useAjax) {
            var progress = alert({
                modalClass: 'amfeed-generation-progress',
                content: config.stepsHtml,
                title: config.profileTitle,
                buttons: []
            });

            stopGenerate = false;

            progress.bind('alertclosed', function () {
                stopGenerate = true;
            });

            generationStartTime = new Date().getTime();
            amsteps.variables.stepsContentPrefix = 'data-amfeed-js="progress-step-content-';
            //start Initialising Step
            amsteps.startStep(1);
            feedGenerate(progress, url, useAjax, 0);
        };

        jQuery(element).on('click', function (event) {
            exported = 0;
            progressFeedGenerate(config.ajaxUrl, config.ajax);
        });

        if (window.location.hash == "#forcegenerate") {
            exported = 0;
            window.location.hash = "";
            progressFeedGenerate(config.ajaxUrl, config.ajax);
        }
    };
});
