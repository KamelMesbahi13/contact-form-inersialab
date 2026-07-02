/* InersiaLab Contact Form Client-side Script */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('inersialab-contact-form');
    if (!form) return;

    const firstNameInput = document.getElementById('inersialab-first-name');
    const lastNameInput = document.getElementById('inersialab-last-name');
    const emailInput = document.getElementById('inersialab-email');
    const phoneInput = document.getElementById('inersialab-phone');
    const serviceInput = document.getElementById('inersialab-service');
    const messageInput = document.getElementById('inersialab-message');
    const submitBtn = document.getElementById('inersialab-submit-btn');
    const responseBox = document.getElementById('inersialab-form-response')

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const nameRegex = /^[\p{L}\s\-']{2,}$/u;

    // Quick mapping helper for inputs and error containers
    const fields = [
        { input: firstNameInput, errId: 'err-first-name', name: 'First name' },
        { input: lastNameInput, errId: 'err-last-name', name: 'Last name' },
        { input: emailInput, errId: 'err-email', name: 'Email address' },
        { input: phoneInput, errId: 'err-phone', name: 'Phone number' },
        { input: serviceInput, errId: 'err-service', name: 'Service' },
        { input: messageInput, errId: 'err-message', name: 'Message' }
    ];

    // Track whether a field has been interacted with (touched)
    const touchedFields = new Set();

    // Helper to display errors under specific input fields
    const showFieldError = (input, errId, msg) => {
        input.classList.remove('valid-field');
        input.classList.add('invalid-field');
        const errContainer = document.getElementById(errId);
        if (errContainer) {
            errContainer.textContent = msg;
            errContainer.style.opacity = '1';
        }
    };

    // Helper to clear errors and optionally mark as valid
    const clearFieldError = (input, errId, markValid) => {
        input.classList.remove('invalid-field');
        const errContainer = document.getElementById(errId);
        if (errContainer) {
            errContainer.style.opacity = '0';
            setTimeout(() => {
                if (!input.classList.contains('invalid-field')) {
                    errContainer.textContent = '';
                }
            }, 200);
        }
        if (markValid) {
            input.classList.add('valid-field');
        } else {
            input.classList.remove('valid-field');
        }
    };

    // Validate a single field — returns true if valid
    const validateField = (field) => {
        if (!field.input) return true;
        const val = field.input.value.trim();

        // Empty check
        if (val === '') {
            let msg = '';
            if (field.input === firstNameInput) msg = inersialabContactData.messages.first_name_required;
            else if (field.input === lastNameInput) msg = inersialabContactData.messages.last_name_required;
            else if (field.input === emailInput) msg = inersialabContactData.messages.email_required;
            else if (field.input === phoneInput) msg = inersialabContactData.messages.phone_required;
            else if (field.input === serviceInput) msg = inersialabContactData.messages.service_required;
            else if (field.input === messageInput) msg = inersialabContactData.messages.message_required;
            showFieldError(field.input, field.errId, msg);
            return false;
        }

        // Format-specific checks
        if (field.input === firstNameInput && !nameRegex.test(val)) {
            showFieldError(field.input, field.errId, inersialabContactData.messages.first_name_invalid);
            return false;
        }
        if (field.input === lastNameInput && !nameRegex.test(val)) {
            showFieldError(field.input, field.errId, inersialabContactData.messages.last_name_invalid);
            return false;
        }
        if (field.input === emailInput && !emailRegex.test(val)) {
            showFieldError(field.input, field.errId, inersialabContactData.messages.email_invalid);
            return false;
        }
        if (field.input === phoneInput) {
            const cleanVal = val.replace(/[0-9+\s\-()]/g, '');
            if (cleanVal !== '' || val.replace(/[^0-9]/g, '').length < 6) {
                showFieldError(field.input, field.errId, inersialabContactData.messages.phone_invalid);
                return false;
            }
        }

        // All checks passed — mark valid
        clearFieldError(field.input, field.errId, true);
        return true;
    };

    // Debounce utility
    const debounceTimers = new Map();
    const debouncedValidate = (field, delay = 300) => {
        const key = field.errId;
        if (debounceTimers.has(key)) {
            clearTimeout(debounceTimers.get(key));
        }
        debounceTimers.set(key, setTimeout(() => {
            debounceTimers.delete(key);
            validateField(field);
        }, delay));
    };

    // Attach real-time validation listeners to each field
    fields.forEach(field => {
        if (!field.input) return;

        // Mark field as touched on first interaction
        const markTouched = () => {
            touchedFields.add(field.errId);
        };

        if (field.input === serviceInput) {
            // Select dropdown — validate immediately on change
            field.input.addEventListener('change', () => {
                markTouched();
                validateField(field);
            });
        } else {
            // Text/email/tel/textarea — debounced validation on input
            field.input.addEventListener('input', () => {
                markTouched();
                const val = field.input.value.trim();

                // If field was invalid and is now being corrected, validate with debounce
                if (touchedFields.has(field.errId)) {
                    // If field is empty after user cleared it, show error immediately
                    if (val === '') {
                        debouncedValidate(field, 500);
                    } else {
                        debouncedValidate(field, 300);
                    }
                }
            });

            // On blur, validate immediately if field was touched
            field.input.addEventListener('blur', () => {
                if (touchedFields.has(field.errId)) {
                    // Cancel any pending debounce
                    if (debounceTimers.has(field.errId)) {
                        clearTimeout(debounceTimers.get(field.errId));
                        debounceTimers.delete(field.errId);
                    }
                    validateField(field);
                }
            });
        }

        // On focus, if field has valid-field class, keep it; just clear invalid state temporarily
        field.input.addEventListener('focus', () => {
            if (field.input.classList.contains('invalid-field')) {
                // Don't clear error on focus — let the user see it while typing
            }
        });
    });

    // Full form validation (used on submit as safety net)
    const validateAllFields = () => {
        let isValid = true;
        fields.forEach(field => {
            touchedFields.add(field.errId); // Mark all as touched on submit
            if (!validateField(field)) {
                isValid = false;
            }
        });
        return isValid;
    };

    // Handle AJAX form submit
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        // 1. Validate Form client-side
        if (!validateAllFields()) {
            return;
        }

        // 2. Hide response panel
        responseBox.classList.remove('show-response', 'success', 'error');
        responseBox.textContent = '';

        // 3. Set loading UI state
        submitBtn.disabled = true;
        const submitTextNode = submitBtn.querySelector('.inersialab-btn-text');
        const originalText = submitTextNode.textContent;
        submitTextNode.textContent = inersialabContactData.messages.sending;

        // 4. Construct form data
        const websiteInput = document.getElementById('inersialab-website');
        const nonceInput = document.getElementById('inersialab_contact_nonce_field') || document.getElementsByName('inersialab_contact_nonce_field')[0];

        const formData = new FormData();
        formData.append('action', 'inersialab_send_contact');
        formData.append('nonce', inersialabContactData.nonce);
        formData.append('lang', inersialabContactData.lang_code);
        formData.append('website', websiteInput ? websiteInput.value : '');
        formData.append('inersialab_contact_nonce_field', nonceInput ? nonceInput.value : '');
        formData.append('first_name', firstNameInput.value.trim());
        formData.append('last_name', lastNameInput.value.trim());
        formData.append('email', emailInput.value.trim());
        formData.append('phone', phoneInput.value.trim());
        formData.append('service', serviceInput.value.trim());
        formData.append('message', messageInput.value.trim());

        // 5. Send POST request to admin-ajax.php
        fetch(inersialabContactData.ajax_url, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response error occurred.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Success: Display response and reset fields
                    responseBox.classList.add('show-response', 'success');
                    responseBox.textContent = data.data.message;
                    form.reset();
                    // Clear all validation states after successful submit
                    fields.forEach(field => {
                        if (field.input) {
                            field.input.classList.remove('valid-field', 'invalid-field');
                        }
                    });
                    touchedFields.clear();
                } else {
                    // Server-side validation or email failure
                    responseBox.classList.add('show-response', 'error');
                    responseBox.textContent = data.data.message || inersialabContactData.messages.error_general;
                }
            })
            .catch(err => {
                // General Network Error
                responseBox.classList.add('show-response', 'error');
                responseBox.textContent = inersialabContactData.messages.error_conn;
                console.error('InersiaLab Contact AJAX Error:', err);
            })
            .finally(() => {
                // Restore UI state
                submitBtn.disabled = false;
                submitTextNode.textContent = originalText;
            });
    });
});
