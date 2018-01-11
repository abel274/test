/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/date'
], function (_, registry, Date) {
    'use strict';

    return Date.extend({
        defaults: {
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.schedule:value'
            }
        },

        initFilter: function () {
            var country = registry.get(this.parentName + '.' + 'schedule');
            if(country.value() == 5) {
                this.setVisible(true);
            } else {
                this.setVisible(false);
            }

            return this;
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            if(value == 5) {
                this.setVisible(true);
            } else {
                this.setVisible(false);
            }
        }
    });
});

