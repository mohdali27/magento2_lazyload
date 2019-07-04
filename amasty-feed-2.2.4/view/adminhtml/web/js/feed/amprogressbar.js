define([
    'jquery'
], function ($) {
    'use strict';

    return {
        default: {
            variables: {
                hourInMilliseconds: 3600000
            },

            selectors: {
                timerSelector: '[data-amprogressbar-js="timer"]',
                progressBar: '[data-amprogressbar-js="bar"]',
                progressBarValue: '[data-amprogressbar-js="progressbar-value"]'
            }
        },

        /**
         * Setting up the timer value
         *
         * @param timeLeft
         */
        progressTimer: function (timeLeft) {
            var timer = $(this.default.selectors.timerSelector);

            if (timeLeft > 0) {
                timer.html(this.durationToTime(timeLeft));
            } else {
                timer.html(this.durationToTime(0));
            }
        },

        /**
         * Converting the timer from milliseconds to string
         *
         * @param duration
         * @returns {string}
         */
        durationToTime: function (duration) {
            var minutesOfDuration = Math.floor(((duration / (1000 * 60)) % 60)),
                secondsOfDuration = Math.floor(((duration / 1000) % 60)),
                hours = '',
                minutes,
                seconds;

            minutes = (minutesOfDuration > 9) ? minutesOfDuration + ':' : '0' + minutesOfDuration + ':';
            seconds = (secondsOfDuration > 9) ? secondsOfDuration : '0' + secondsOfDuration;

            if (duration >= this.default.variables.hourInMilliseconds) {
                var hoursOfDuration = Math.floor(((duration / (1000 * 60 * 60)) % 24));

                hours = (hoursOfDuration > 9) ? hoursOfDuration + ':' : '0' + hoursOfDuration + ':';
            }

            return hours + minutes + seconds;
        },

        /**
         * Setting up a progress bar value
         *
         * @param value
         */
        progressBar: function (value) {
            $(this.default.selectors.progressBar).val(Math.floor(value * 100));
            $(this.default.selectors.progressBarValue).html(Math.floor(value * 100) + '%').css('left', Math.floor(value * 100) + '%');
        }
    }
});
