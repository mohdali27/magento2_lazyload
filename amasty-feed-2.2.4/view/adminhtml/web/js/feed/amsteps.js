define([
    'jquery',
    'mage/translate'
], function ($, $translate) {
    'use strict';

    return {
        variables: {
            stepDataPrefix: '[data-amsteps-js*="step-',
            stepsContentPrefix: ''
        },

        /**
         * Start the step
         *
         * @param stepNumber
         */
        startStep: function (stepNumber) {
            var stepToStart = $(this.variables.stepDataPrefix + stepNumber + '"]'),
                stepToStartTitle = $(this.variables.stepDataPrefix + 'title-' + stepNumber + '"]'),
                stepToStartContent = $('[' + this.variables.stepsContentPrefix + stepNumber + '"]'),
                isStarted = stepToStart.is('.-active');

            if (!isStarted) {
                if (!stepToStart.is(':nth-of-type(1)')) {
                    this.completeStep(stepNumber - 1, true);
                }

                stepToStart.addClass('-active').attr('data-amsteps-js', stepToStart.attr('data-amsteps-js') + ' -active-step');
                stepToStartTitle.addClass('-active');
                stepToStartContent.show();
            }
        },

        /**
         * Complete the step
         *
         * @param stepNumber
         * @param successStatus
         */
        completeStep: function (stepNumber, successStatus) {
            var stepToComplete = $(this.variables.stepDataPrefix + stepNumber + '"]'),
                stepToCompleteTitle = $(this.variables.stepDataPrefix + 'title-' + stepNumber + '"]'),
                stepToCompleteContent = $('[' + this.variables.stepsContentPrefix + stepNumber + '"]'),
                isCompleted = stepToComplete.is('.-done'),
                previousStepCompleteStatus = $(this.variables.stepDataPrefix + (stepNumber - 1) + '"]').is('.-done');

            if (stepNumber != 1 && !previousStepCompleteStatus) {
                this.completeStep(stepNumber - 1, true);
            }

            if (!isCompleted) {
                if (successStatus) {
                    if (stepToComplete.is(':last-of-type')) {
                        stepToComplete.addClass('-done');
                    } else {
                        stepToComplete
                            .removeClass('-active')
                            .addClass('-done')
                            .attr('data-amsteps-js', 'step-' + stepNumber);
                        stepToCompleteTitle.removeClass('-active');
                        stepToCompleteContent.hide();
                    }
                } else {
                    stepToComplete.addClass('-error');
                    stepToCompleteTitle.addClass('-error').html($translate('Unsuccess'));
                }
            }
        }
    }
});
