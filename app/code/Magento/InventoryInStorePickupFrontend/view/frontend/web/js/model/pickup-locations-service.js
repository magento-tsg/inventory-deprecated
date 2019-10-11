/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'knockout',
    'Magento_InventoryInStorePickupFrontend/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/select-shipping-address',
    'underscore',
    'mage/translate'
], function(
    $,
    ko,
    resourceUrlManager,
    storage,
    customerData,
    checkoutData,
    addressConverter,
    selectShippingAddressAction,
    _,
    $t
) {
    'use strict';

    var websiteCode = window.checkoutConfig.websiteCode,
        countryData = customerData.get('directory-data');

    return {
        isLoading: ko.observable(false),
        selectedLocation: ko.observable(null),

        /**
         * Get shipping rates for specified address.
         *
         * @param {string} sourceCode
         */
        getLocation: function(sourceCode) {
            var serviceUrl = resourceUrlManager.getUrlForPickupLocation(websiteCode, sourceCode);

            this.isLoading(true);

            return storage
                .get(serviceUrl, {}, false)
                .then(function(address) {
                    return this.formatAddress(address);
                }.bind(this))
                .fail(function(response) {
                    this.processError(response);
                    return [];
                }.bind(this))
                .always(function() {
                    this.isLoading(false);
                }.bind(this));
        },

        /**
         * Get all pickup locations defined for given sales channel.
         */
        getLocations: function() {
            var self = this;
            var serviceUrl = resourceUrlManager.getUrlForPickupLocationsAssignedToSalesChannel('website', websiteCode);

            self.isLoading(true);

            return storage
                .get(serviceUrl, {}, false)
                .then(function(result) {
                    return _.map(result.items, function(address) {
                        return self.formatAddress(address);
                    });
                })
                .fail(function(response) {
                    self.processError(response);
                    return [];
                })
                .always(function() {
                    self.isLoading(false);
                });
        },

        /**
         * Get nearby pickup locations based on given search criteria.
         *
         * @param {object} searchCriteria Search criteria object.
         * @see Magento/InventoryInStorePickup/Model/SearchCriteria/GetNearbyLocationsCriteria.php
         */
        getNearbyLocations: function(searchCriteria) {
            var self = this,
                serviceUrl = resourceUrlManager.getUrlForNearbyPickupLocations(websiteCode, searchCriteria);

            self.isLoading(true);

            return storage
                .get(serviceUrl, {}, false)
                .then(function(result) {
                    return _.map(result.items, function(address) {
                        return self.formatAddress(address);
                    });
                })
                .fail(function(response) {
                    self.processError(response);
                    return [];
                })
                .always(function() {
                    self.isLoading(false);
                });
        },

        /**
         * Select location for sipping.
         *
         * @param {Object} location
         * @returns void
         */
        selectForShipping: function(location) {
            var address = $.extend(
                {},
                addressConverter.formAddressDataToQuoteAddress({
                    firstname: location.name,
                    lastname: 'Store',
                    street: location.street,
                    city: location.city,
                    postcode: location.postcode,
                    country_id: location.country_id,
                    telephone: location.telephone,
                    region_id: location.region_id,
                    save_in_address_book: 0,
                    custom_attributes: {
                        pickup_location_code: location.pickup_location_code
                    }
                }),
                {
                    canUseForBilling: function() {
                        return false;
                    },
                    getType: function() {
                        return 'store-pickup-address';
                    },
                }
            );

            this.selectedLocation(location);
            selectShippingAddressAction(address);
            checkoutData.setSelectedShippingAddress(address.getKey());
        },

        /**
         * Formats address returned by REST endpoint to match checkout address field naming.
         *
         * @param {object} address Address object returned by REST endpoint.
         */
        formatAddress: function(address) {
            return {
                name: address.name,
                description: address.description,
                latitude: address.latitude,
                longitude: address.longitude,
                street: [address.street],
                city: address.city,
                postcode: address.postcode,
                country_id: address.country_id,
                country: this.getCountryName(address.country_id),
                telephone: address.phone,
                region_id: address.region_id,
                region: this.getRegionName(
                    address.country_id,
                    address.region_id
                ),
                pickup_location_code: address.pickup_location_code,
            };
        },

        /**
         * Get country name by id.
         *
         * @param {*} countryId
         * @return {String}
         */
        getCountryName: function(countryId) {
            return countryData()[countryId] !== undefined
                ? countryData()[countryId].name
                : ''; //eslint-disable-line
        },

        /**
         * Returns region name based on given country and region identifiers.
         *
         * @param {string} countryId Country identifier.
         * @param {string} regionId Region identifier.
         */
        getRegionName: function(countryId, regionId) {
            var regions = countryData()[countryId]
                ? countryData()[countryId].regions
                : null;

            return regions && regions[regionId] ? regions[regionId].name : '';
        },

        /**
         * Process response errors.
         *
         * @param {Object} response
         * @returns void
         */
        processError: function(response) {
            var expr = /([%])\w+/g,
                error;

            if (response.status === 401) {
                //eslint-disable-line eqeqeq
                window.location.replace(url.build('customer/account/login/'));
                return;
            }

            try {
                error = JSON.parse(response.responseText);
            } catch (exception) {
                error = $t(
                    'Something went wrong with your request. Please try again later.'
                );
            }

            if (error.hasOwnProperty('parameters')) {
                error = error.message.replace(expr, function(varName) {
                    varName = varName.substr(1);

                    if (error.parameters.hasOwnProperty(varName)) {
                        return error.parameters[varName];
                    }

                    return error.parameters.shift();
                });
            }
        },
    };
});
