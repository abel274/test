define([
    'jquery',
    'ko',
    'mage/url',
    'uiComponent'
], function (
    $,
    ko,
    url,
    Component
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Magestore_InventorySuccess/checkout/shipping/delivery-date'
        },
        deliveryDate: ko.observable(new Date()),

        initialize: function () {
            this._super();
            ko.bindingHandlers.datepicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                    var $el = $(element);


                    //initialize datepicker with some optional options
                    var options = {
                        minDate: new Date()
                    };
                    $el.datepicker(options);

                    //handle the field changing
                    ko.utils.registerEventHandler(element, "change", function() {
                        var observable = valueAccessor();
                        observable($el.datepicker("getDate"));

                        var deliveryDate = $.datepicker.formatDate('yy-mm-dd', $el.datepicker("getDate"));
                        $('.overlay-bg-checkout').show();
                        $.ajax({
                            url: url.build("inventorysuccess/deliveryDate/saveSession"),
                            type: "post",
                            dateType: "json",
                            showLoader: true,
                            data: {
                                deliveryDate: deliveryDate
                            },
                            success: function (result) {
                            },
                            always: function () {
                                $('.overlay-bg-checkout').hide();
                            }
                        });
                    });

                    //handle disposal (if KO removes by the template binding)
                    ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
                        $el.datepicker("destroy");
                    });

                },
                update: function (element, valueAccessor) {
                    var value = ko.utils.unwrapObservable(valueAccessor()),
                        $el = $(element),
                        current = $el.datepicker("getDate");

                    if (value - current !== 0) {
                        $el.datepicker("setDate", value);
                        var deliveryDate = $.datepicker.formatDate('yy-mm-dd', value);
                        $('.overlay-bg-checkout').show();
                        $.ajax({
                            url: url.build("inventorysuccess/deliveryDate/saveSession"),
                            type: "post",
                            dateType: "json",
                            showLoader: true,
                            data: {
                                deliveryDate: deliveryDate
                            },
                            success: function (result) {
                            },
                            always: function () {
                                $('.overlay-bg-checkout').hide();
                            }
                        });
                    }
                }
            }
        },

        changeDeliveryDate: function () {
            $('#delivery-date-checkout').focus();
        }
    });
});