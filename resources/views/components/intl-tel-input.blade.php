@props([
    'name',
    'id' => null,
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'class' => '',
])

@php
    $inputId = $id ?? $name;
@endphp

<input 
    type="tel" 
    id="{{ $inputId }}" 
    name="{{ $name }}" 
    value="{{ $value }}" 
    placeholder="{{ $placeholder }}" 
    class="form-control intl-tel-input-field {{ $class }}"
    {{ $required ? 'required' : '' }}
    data-intl-tel-input
    {{ $attributes->except(['name', 'id', 'value', 'placeholder', 'required', 'class']) }}
>

@once
    @push('pageCss')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/css/intlTelInput.min.css">
        <style>
            .iti {
                width: 100% !important;
                display: block !important;
            }
            .iti__country-list {
                z-index: 9999 !important;
                background-color: var(--bs-body-bg, #fff) !important;
                color: var(--bs-body-color, #212529) !important;
                border: 1px solid var(--bs-border-color, #dee2e6) !important;
            }
            .iti__search-input {
                background-color: var(--bs-body-bg, #fff) !important;
                color: var(--bs-body-color, #212529) !important;
                border: 1px solid var(--bs-border-color, #dee2e6) !important;
            }
            .iti__country {
                outline: none !important;
            }
            .iti__country:hover, .iti__country.iti__highlight {
                background-color: var(--bs-tertiary-bg, #f8f9fa) !important;
            }
            .iti__selected-country {
                pointer-events: none !important;
                cursor: default !important;
            }
            .iti__arrow {
                display: none !important;
            }
            .intl-tel-input-field.is-invalid {
                border-color: #dc3545 !important;
            }
            .intl-tel-input-field.is-invalid:focus {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
            }
        </style>
    @endpush
    @push('pageScripts')
        <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/intlTelInput.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Hook into jQuery valHooks to automatically return clean E.164 phone numbers
                if (typeof $ !== 'undefined' && $.valHooks) {
                    $.valHooks.tel = {
                        get: function(elem) {
                            if (elem.itiInstance) {
                                return elem.itiInstance.isValidNumber() ? elem.itiInstance.getNumber() : elem.value;
                            }
                            return elem.value;
                        },
                        set: function(elem, value) {
                            if (elem.itiInstance) {
                                elem.value = value;
                                elem.itiInstance.setNumber(value);
                                return elem;
                            }
                            elem.value = value;
                            return elem;
                        }
                    };
                }

                function initIntlTelInputs() {
                    const inputs = document.querySelectorAll('input[data-intl-tel-input]');
                    inputs.forEach(function(input) {
                        // Initialize only if not already initialized or if cloned without a valid instance
                        if (!input.hasAttribute('data-intl-initialized') || !input.itiInstance) {
                            
                            // Check if parent has iti class (which happens when cloning a repeater row)
                            if (input.parentNode && input.parentNode.classList.contains('iti')) {
                                const parent = input.parentNode;
                                const grandParent = parent.parentNode;
                                if (grandParent) {
                                    // Extract input out of old cloned wrapper and remove wrapper
                                    grandParent.insertBefore(input, parent);
                                    parent.remove();
                                }
                            }

                            // Initialize intlTelInput on the clean input field
                            const iti = window.intlTelInput(input, {
                                initialCountry: "in",
                                onlyCountries: ["in"],
                                separateDialCode: true,
                                strictMode: true,
                                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/utils.js",
                            });

                            input.setAttribute('data-intl-initialized', 'true');
                            input.itiInstance = iti;

                            // If element already has value, parse and load it
                            if (input.value) {
                                iti.setNumber(input.value);
                            }
                        }
                    });
                }

                // Initial load
                initIntlTelInputs();

                // MutationObserver to auto-initialize dynamically added inputs (e.g. from Repeaters)
                const observer = new MutationObserver(function(mutations) {
                    initIntlTelInputs();
                });
                observer.observe(document.body, { childList: true, subtree: true });

                // Before form submit, set value of all valid fields to the full clean E.164 number
                document.addEventListener("submit", function(event) {
                    const inputs = event.target.querySelectorAll('input[data-intl-tel-input]');
                    inputs.forEach(function(input) {
                        if (input.itiInstance && input.itiInstance.isValidNumber()) {
                            input.value = input.itiInstance.getNumber();
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce
