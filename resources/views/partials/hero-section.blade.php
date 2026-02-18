<section class="hero-section" id="home">
    @foreach($section['section_content'] as $banner)
    <div class="hero-content position-relative overflow-hidden">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="wow fadeInUp" data-wow-duration="1s" data-wow-delay=".25s">
                        <h1 class="mb-2">{{ $section['section_title'] ?? '' }} <span class="typed"
                                data-type-text="Carpenters"></span></h1>

                        <p class="mb-3 sub-title">{{ $section['section_label'] ?? '' }}</p>
                        <div class="banner-form bg-white border mb-3">
                            @if ($leadStatus == 1)
                                <form id="searchForm">
                                    <div class="d-md-flex align-items-center">
                                        @if($banner->show_search == 1)

                                        <div class="input-group mb-2">
                                            <span class="input-group-text px-1"><i class="ti ti-search"></i></span>
                                            <select class="form-control" id="categoryDropdown" name="categoryId" required>
                                                @if ($homeCategories->isEmpty())
                                                <option value="" disabled selected>{{ __('No categories available') }}
                                                </option>
                                                @else
                                                <option value="" selected disabled>{{ __('Search for Service') }}</option>
                                                @foreach ($homeCategories as $category)
                                                <option value="{{ $category->id }}" data-slug="{{ $category->slug }}">
                                                    {{ $category->name }}</option>
                                                @endforeach
                                                @endif
                                            </select>

                                        </div>
                                        @endif
                                        @if($banner->show_location == 1)
                                        <div class="input-group mb-2">
                                            <span class="input-group-text px-1"><i class="ti ti-map-pin"></i></span>
                                            <input type="text" class="form-control" name="location"
                                                placeholder="{{ __('Enter Location') }}" maxlength="100">
                                        </div>
                                        @endif
                                        <div class="mb-2">
                                            <button type="submit" id="submitSearchForm"
                                                class="btn btn-linear-primary d-inline-flex align-items-center w-100">
                                                <i class="feather-search me-2"></i>
                                                {{ __('Search') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <form id="locationSearchForm">
                                    <div class="d-md-flex align-items-center">
                                        @if($banner->show_search == 1)

                                        <div class="input-group mb-2">
                                            <span class="input-group-text px-1"><i class="ti ti-search"></i></span>
                                            <select class="form-control" id="categoryDropdown" name="categoryId">
                                                @if ($homeCategories->isEmpty())
                                                <option value="" disabled selected>{{ __('No categories available') }}
                                                </option>
                                                @else
                                                <option value="" selected disabled>{{ __('Search for Service') }}</option>
                                                @foreach ($homeCategories as $category)
                                                <option value="{{ $category->id }}" data-slug="{{ $category->slug }}">
                                                    {{ $category->name }}
                                                </option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        @endif
                                        @if($banner->show_location == 1)
                                        <div class="input-group mb-2">
                                            <span class="input-group-text px-1"><i class="ti ti-map-pin"></i></span>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="location"
                                                id="location-input"
                                                placeholder="{{ __('Enter Location') }}"
                                                maxlength="100"
                                                autocomplete="off">
                                        </div>
                                        <ul id="location-suggestions" class="list-group position-absolute zindex-dropdown w-100" style="display: none;"></ul>
                                        @endif
                                        <div class="mb-2">
                                            <button type="submit" id="submitSearchLocationForm"
                                                class="btn btn-linear-primary d-inline-flex align-items-center w-100">
                                                <i class="feather-search me-2"></i>
                                                {{ __('Search') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                            <img src="{{ asset('front/img/bg/bg-06.svg') }}" alt="img" class="shape-06 round-animate">
                        </div>
                        <div class="d-flex align-items-center flex-wrap">
                            @if($banner->popular_search == 1 && $popularCategories->isNotEmpty())
                            <h6 class="mb-2 me-2 fw-medium">{{ __('Popular Searches') }}</h6>
                            @foreach($popularCategories->take(3) as $category)
                            <a href="{{ url('services/' . $category->slug) }}"
                                class="badge badge-dark-transparent fs-14 fw-normal mb-2 me-2">
                                {{ $category->name }}
                            </a>
                            @endforeach
                            @endif
                        </div>
                        <div class="d-flex align-items-center flex-wrap banner-info">
                            @if($banner->provider_count == 1)

                            <div class="d-flex align-items-center me-4 mt-4">
                                <img src="{{ asset('front/img/icons/success-01.svg') }}" alt="icon">
                                <div class="ms-2">
                                    <h6>215,292 +</h6>
                                    <p>{{ __('Verified Providers') }}</p>
                                </div>
                            </div>
                            @endif
                            @if($banner->services_count == 1)
                            <div class="d-flex align-items-center me-4 mt-4">
                                <img src="{{ asset('front/img/icons/success-02.svg') }}" alt="icon">
                                <div class="ms-2">
                                    <h6>90,000+</h6>
                                    <p>{{ __('Services Completed') }}</p>
                                </div>
                            </div>
                            @endif
                            @if($banner->review_count == 1)

                            <div class="d-flex align-items-center me-4 mt-4">
                                <img src="{{ asset('front/img/icons/success-03.svg') }}" alt="icon">
                                <div class="ms-2">
                                    <h6>2,390,968 </h6>
                                    <p>{{ __('Reviews Globally') }}</p>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
                <div class="banner-img wow fadeInUp" data-wow-duration="1s" data-wow-delay=".25s">
                    <img src="{{ $banner->thumbnail_image ? $banner->thumbnail_image : asset('front/img/banner.webp') }}"
                        alt="img" class="img-fluid animation-float" fetchpriority="high">
                </div>
            </div>
        </div>
        <div class="hero-image">
            <div class="d-inline-flex bg-white p-2 rounded align-items-center shape-01 floating-x">
                <span class="avatar avatar-md bg-warning rounded-circle me-2"><i class="ti ti-star-filled"></i></span>
                <span>4.9 / 5<small class="d-block">{{ __('(255 reviews)') }}</small></span>
                <i class="border-edge"></i>
            </div>
            <div class="d-inline-flex bg-white p-2 rounded align-items-center shape-02 floating-x">
                <span class="me-2"><img src="{{ asset('front/img/icons/tick-banner.svg') }}" alt="img"></span>
                <p class="fs-12 text-dark mb-0">{{ __('300 Booking Completed') }}</p>
                <i class="border-edge"></i>
            </div>
            <img src="{{ asset('front/img/bg/bg-03.svg') }}" alt="img" class="shape-03">
            <img src="{{ asset('front/img/bg/bg-04.svg') }}" alt="img" class="shape-04">
            <img src="{{ asset('front/img/bg/bg-05.svg') }}" alt="img" class="shape-05">
        </div>
    </div>
    <div class="modal fade wallet-modal home-modal" id="add-offer" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="modalBody">
                <div class="modal-header d-flex align-items-center justify-content-between border-0">
                    <h5>{{ __('Find the Best Professionals') }}</h5>
                    <a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
                            class="ti ti-circle-x-filled fs-24"></i></a>
                </div>
                <form>
                    <div class="modal-body " id="modal-body-content">
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" id="back-btn" class="btn btn-secondary">{{ __('Back') }}</button>
                    <button type="submit" id="continue-btn" class="btn btn-dark">{{ __('Continue') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</section>

@if ($locationStatus == 1 && $leadStatus != 1)
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('locationSearchForm');
        const input = document.getElementById('location-input');
        let selectedCityOrDistrict = '';
        let lat = '';
        let lang = '';

        // Helper: pick the best "city-like" name from address_components
        function extractCityOrDistrict(addressComponents) {
            if (!addressComponents || !addressComponents.length) return '';

            const find = (type) => addressComponents.find(c => Array.isArray(c.types) && c.types.indexOf(type) !== -1);

            // Prefer true city/locality (Memphis will normally appear here)
            const locality = find('locality') || find('postal_town') || find('sublocality') || find('neighborhood');
            if (locality) return locality.long_name;

            // Fallbacks
            const admin2 = find('administrative_area_level_2'); // county
            if (admin2) return admin2.long_name;

            const admin1 = find('administrative_area_level_1'); // state/province
            if (admin1) return admin1.long_name;

            const country = find('country');
            if (country) return country.long_name;

            return '';
        }

        // Auto-detect user location (reverse geocode) and prefill
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                lat = position.coords.latitude;
                lng = position.coords.longitude;
                const geocoder = new google.maps.Geocoder();
                const latlng = { lat: lat, lng: lng };

                geocoder.geocode({ location: latlng }, function(results, status) {
                    if (status === "OK" && results && results.length) {
                        let city = '';
                        for (let i = 0; i < results.length; i++) {
                            city = extractCityOrDistrict(results[i].address_components);
                            if (city) break;
                        }
                        if (!city) city = results[0].formatted_address || '';

                        // ✅ Prefill current city
                        selectedCityOrDistrict = city;
                        input.value = city;
                    } else {
                        console.warn("Geocoder failed or returned no results:", status);
                    }
                });
            }, function(error) {
                console.warn("Geolocation blocked/failed:", error);
            });
        }

        // Autocomplete initialization - worldwide
        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['geocode']
        });

        autocomplete.setFields(['address_components', 'formatted_address', 'name', 'geometry']);

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            let city = '';

            if (place.address_components && place.address_components.length) {
                city = extractCityOrDistrict(place.address_components);
                selectedCityOrDistrict = city || (place.formatted_address || place.name || '');
                input.value = place.formatted_address || place.name || city || '';
                return;
            }

            if (place.geometry && place.geometry.location) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: place.geometry.location }, function(results, status) {
                    if (status === "OK" && results && results.length) {
                        let c = '';
                        for (let i = 0; i < results.length; i++) {
                            c = extractCityOrDistrict(results[i].address_components);
                            if (c) break;
                        }
                        selectedCityOrDistrict = c || (place.formatted_address || place.name || '');
                        input.value = place.formatted_address || place.name || selectedCityOrDistrict;
                    } else {
                        selectedCityOrDistrict = place.formatted_address || place.name || '';
                        input.value = selectedCityOrDistrict;
                    }
                });
                return;
            }

            selectedCityOrDistrict = place.formatted_address || place.name || '';
            input.value = selectedCityOrDistrict;
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const categoryDropdown = document.getElementById('categoryDropdown');
            const selectedOption = categoryDropdown.options[categoryDropdown.selectedIndex];
            const categorySlug = selectedOption ? selectedOption.getAttribute('data-slug') : '';

            var queryParams = [];

            if (categorySlug) {
                queryParams.push('category=' + encodeURIComponent(categorySlug));
            }

            if (selectedCityOrDistrict) {
                queryParams.push('city=' + encodeURIComponent(selectedCityOrDistrict));
            } else {
                queryParams.push('keywords=' + encodeURIComponent(input.value));
            }

            if (lat && lng) {
                queryParams.push('lat=' + encodeURIComponent(lat));
                queryParams.push('lang=' + encodeURIComponent(lng));
            }

            var queryString = queryParams.length > 0 ? '?' + queryParams.join('&') : '';
            window.location.href = '/services' + queryString;
        });
    });
</script>
@endif