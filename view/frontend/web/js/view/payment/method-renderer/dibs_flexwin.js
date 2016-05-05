/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Dibs_Flexwin/js/action/set-payment-method',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Customer/js/customer-data',
        'ko',
        'jquery'
    ],
    function (Component, setPaymentMethodAction, selectPaymentMethodAction,
              checkoutData, storage, ko, $) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,

            defaults: {
                template: 'Dibs_Flexwin/payment/dibs_flexwin',
                requestData: [],
                imgWidth: window.checkoutConfig.payment.dibsFlexwin.logoWith
            },

            getDibsPaytype: ko.observable(function () {
                var obj = storage.get('checkout-data');
                return obj.paytypeId;

            }),

            getDibsActionFormUrl: function () {
                return window.checkoutConfig.payment.dibsFlexwin.formActionUrl;
            },

            getCardsPaytypes: function () {
                var configKey = window.checkoutConfig.payment.dibsFlexwin;
                var cardsArr =
                    [
                        {
                            id: 'dibs_flexwin_cards_visa',
                            title: $.mage.__('Visa'),
                            imgNumber: '07',
                            paytype: 'VISA',
                            enabled: configKey.paytype.visa
                        },
                        {
                            id: 'dibs_flexwin_cards_master',
                            title: $.mage.__('Master'),
                            imgNumber: '01',
                            paytype: 'MC',
                            enabled: configKey.paytype.master
                        },
                        {
                            id: 'dibs_flexwin_cards_amex',
                            title: $.mage.__('Amex'),
                            imgNumber: '06',
                            paytype: 'AMEX',
                            enabled: configKey.paytype.amex
                        },
                        {
                            id: 'dibs_flexwin_cards_diners',
                            title: $.mage.__('Diners'),
                            imgNumber: '04',
                            paytype: 'DIN',
                            enabled: configKey.paytype.diners
                        },
                        {
                            id: 'dibs_flexwin_cards_dankort',
                            title: $.mage.__('Dankort'),
                            imgNumber: '03',
                            paytype: 'DK',
                            enabled: configKey.paytype.dankort
                        },
                        {
                            id: 'dibs_flexwin_mobilepay',
                            title: $.mage.__('MobilePay'),
                            imgNumber: '10',
                            paytype: 'MPO_Nets',
                            enabled: configKey.paytype.mobilepay
                        }
                    ];

                return _.filter(cardsArr, function (card) {
                    return card.enabled == 1;
                });
            },

            initObservable: function () {
                this._super().observe('requestData');
                return this;
            },

            imgUrl: function (imgNumber) {
                var urlPrefix = window.checkoutConfig.payment.dibsFlexwin.cdnUrlLogoPrefix + imgNumber + '.png';
                return urlPrefix;
            },
            /*
             imgWidth : function() {
             return 125;
             },*/

            placeOrder: function () {
                var self = this;
                var obj = storage.get('checkout-data');
                if (self.validate()) {
                    self.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer, self.requestData,
                        _.find(this.getCardsPaytypes(), function (card) {
                            return card.id == obj.paytypeId;
                        }).paytype);
                }
            },

            getData: function () {
                var obj = storage.get('checkout-data');
                return {
                    "method": this.item.method,
                    'po_number': null,
                    "additional_data": null
                };
            },

            customMethodDisabled: function () {
                return false;
            },

            setCustomPaymentMethod: function (data, event) {
                var self = this;
                selectPaymentMethodAction({
                    "method": this.item.method,
                    "po_number": null,
                    "additional_data": null
                });
                checkoutData.setSelectedPaymentMethod(event.target.id);
                var obj = storage.get('checkout-data');
                obj.paytypeId = event.target.id;
                this.getDibsPaytype(obj.paytypeId);
                storage.set('checkout-data', obj);
                return true;
            },

            getMethodCode: function () {
                return this.item.method;
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            }
        });
    }
);
