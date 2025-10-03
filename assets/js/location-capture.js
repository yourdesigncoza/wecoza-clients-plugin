(function ($) {
    $(function () {
        var config = window.wecoza_locations || {};
        var container = document.getElementById('google_address_container');
        var searchInput = document.getElementById('google_address_search');
        var form = $('.wecoza-locations-form-container form');

        if (!form.length || !container || !searchInput) {
            return;
        }

        var provinceSelect = form.find('#province');
        var suburbInput = form.find('#suburb');
        var townInput = form.find('#town');
        var postalInput = form.find('#postal_code');
        var latitudeInput = form.find('#latitude');
        var longitudeInput = form.find('#longitude');

        var provinceLookup = {};
        if ($.isArray(config.provinces)) {
            config.provinces.forEach(function (province) {
                provinceLookup[province.toLowerCase()] = province;
            });
        }

        if (!config.googleMapsEnabled) {
            return;
        }

        waitForGoogleMaps(function () {
            initializeAutocomplete();
        });

        function waitForGoogleMaps(callback) {
            var attempts = 0;
            var maxAttempts = 60;

            function check() {
                attempts++;

                if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                    callback();
                    return;
                }

                if (attempts >= maxAttempts) {
                    console.error('Google Maps API failed to load in time.');
                    return;
                }

                setTimeout(check, 100);
            }

            check();
        }

        function initializeAutocomplete() {
            try {
                if (google.maps.importLibrary) {
                    google.maps.importLibrary('places').then(function (library) {
                        if (library && library.PlaceAutocompleteElement) {
                            initializeNewAutocomplete(library.PlaceAutocompleteElement);
                        } else {
                            initializeLegacyAutocomplete();
                        }
                    }).catch(function (error) {
                        console.error('PlaceAutocompleteElement failed to load', error);
                        initializeLegacyAutocomplete();
                    });
                } else {
                    initializeLegacyAutocomplete();
                }
            } catch (error) {
                console.error('Failed to initialise Google Places', error);
                initializeLegacyAutocomplete();
            }
        }

        function initializeNewAutocomplete(PlaceAutocompleteElement) {
            var originalInput = document.getElementById('google_address_search');
            if (!originalInput) {
                return;
            }

            originalInput.style.display = 'none';
            originalInput.style.visibility = 'hidden';

            var placeAutocomplete = new PlaceAutocompleteElement({
                includedRegionCodes: ['za'],
                requestedLanguage: 'en',
                requestedRegion: 'za'
            });

            placeAutocomplete.className = 'form-control form-control-sm';
            placeAutocomplete.setAttribute('placeholder', originalInput.getAttribute('placeholder') || '');

            container.replaceChild(placeAutocomplete, originalInput);

            placeAutocomplete.addEventListener('gmp-select', function (event) {
                if (!event || !event.placePrediction) {
                    return;
                }

                var place = event.placePrediction.toPlace();

                place.fetchFields({
                    fields: ['addressComponents', 'formattedAddress', 'location']
                }).then(function () {
                    populateFromPlace(place.addressComponents || [], place.location || null);
                }).catch(function (error) {
                    console.error('Failed fetching place fields', error);
                });
            });
        }

        function initializeLegacyAutocomplete() {
            var input = document.getElementById('google_address_search');
            if (!input) {
                return;
            }

            input.style.display = 'block';
            input.style.visibility = 'visible';

            var autocomplete = new google.maps.places.Autocomplete(input, {
                componentRestrictions: { country: 'za' },
                fields: ['address_components', 'geometry', 'formatted_address']
            });

            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                if (!place || !place.address_components) {
                    return;
                }

                var location = place.geometry && place.geometry.location ? place.geometry.location : null;
                populateFromPlace(place.address_components, location);
            });
        }

        function populateFromPlace(components, location) {
            var data = {
                suburb: '',
                town: '',
                province: '',
                postalCode: ''
            };

            components.forEach(function (component) {
                if (!component || !component.types) {
                    return;
                }

                if (component.types.indexOf('sublocality_level_1') !== -1 || component.types.indexOf('sublocality') !== -1 || component.types.indexOf('neighborhood') !== -1) {
                    data.suburb = component.longText || component.long_name || data.suburb;
                }

                if (component.types.indexOf('locality') !== -1 || component.types.indexOf('administrative_area_level_2') !== -1) {
                    data.town = component.longText || component.long_name || data.town;
                }

                if (component.types.indexOf('administrative_area_level_1') !== -1) {
                    data.province = component.longText || component.long_name || data.province;
                }

                if (component.types.indexOf('postal_code') !== -1) {
                    data.postalCode = component.longText || component.long_name || data.postalCode;
                }
            });

            if (data.suburb && suburbInput.length) {
                suburbInput.val(data.suburb).trigger('change');
            }

            if (data.town && townInput.length) {
                townInput.val(data.town).trigger('change');
            }

            if (data.postalCode && postalInput.length) {
                postalInput.val(data.postalCode).trigger('change');
            }

            if (data.province && provinceSelect.length) {
                var canonicalProvince = provinceLookup[data.province.toLowerCase()] || '';
                if (!canonicalProvince && provinceLookup[data.province.replace(/\s+/g, '').toLowerCase()]) {
                    canonicalProvince = provinceLookup[data.province.replace(/\s+/g, '').toLowerCase()];
                }

                if (canonicalProvince) {
                    provinceSelect.val(canonicalProvince).trigger('change');
                }
            }

            if (location) {
                var lat = typeof location.lat === 'function' ? location.lat() : location.lat;
                var lng = typeof location.lng === 'function' ? location.lng() : location.lng;

                if (latitudeInput.length && typeof lat === 'number') {
                    latitudeInput.val(lat.toFixed(6));
                }

                if (longitudeInput.length && typeof lng === 'number') {
                    longitudeInput.val(lng.toFixed(6));
                }
            }
        }
    });
})(jQuery);
