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

    // Quick mapping helper for inputs and error containers
    const fields = [
        { input: firstNameInput, errId: 'err-first-name', name: 'First name' },
        { input: lastNameInput, errId: 'err-last-name', name: 'Last name' },
        { input: emailInput, errId: 'err-email', name: 'Email address' },
        { input: phoneInput, errId: 'err-phone', name: 'Phone number' },
        { input: serviceInput, errId: 'err-service', name: 'Service' },
        { input: messageInput, errId: 'err-message', name: 'Message' }
    ];

    // Clear error style and text when field is focused/edited
    fields.forEach(field => {
        if (!field.input) return;
        const errContainer = document.getElementById(field.errId);

        const clearError = () => {
            field.input.classList.remove('invalid-field');
            if (errContainer) {
                errContainer.style.opacity = '0';
                setTimeout(() => {
                    if (field.input.classList.contains('invalid-field')) return;
                    errContainer.textContent = '';
                }, 200);
            }
        };

        field.input.addEventListener('input', clearError);
        field.input.addEventListener('focus', clearError);
    });

    // Helper to display errors under specific input fields
    const showFieldError = (input, errId, msg) => {
        input.classList.add('invalid-field');
        const errContainer = document.getElementById(errId);
        if (errContainer) {
            errContainer.textContent = msg;
            errContainer.style.opacity = '1';
        }
    };

    // Client-side validator
    const validateForm = () => {
        let isValid = true;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const nameRegex = /^[\p{L}\s\-']{2,}$/u;

        fields.forEach(field => {
            if (!field.input) return;
            const val = field.input.value.trim();

            if (val === '') {
                let msg = '';
                if (field.input === firstNameInput) msg = inersialabContactData.messages.first_name_required;
                else if (field.input === lastNameInput) msg = inersialabContactData.messages.last_name_required;
                else if (field.input === emailInput) msg = inersialabContactData.messages.email_required;
                else if (field.input === phoneInput) msg = inersialabContactData.messages.phone_required;
                else if (field.input === serviceInput) msg = inersialabContactData.messages.service_required;
                else if (field.input === messageInput) msg = inersialabContactData.messages.message_required;

                showFieldError(field.input, field.errId, msg);
                isValid = false;
            } else if (field.input === firstNameInput && !nameRegex.test(val)) {
                showFieldError(field.input, field.errId, inersialabContactData.messages.first_name_invalid);
                isValid = false;
            } else if (field.input === lastNameInput && !nameRegex.test(val)) {
                showFieldError(field.input, field.errId, inersialabContactData.messages.last_name_invalid);
                isValid = false;
            } else if (field.input === emailInput && !emailRegex.test(val)) {
                showFieldError(field.input, field.errId, inersialabContactData.messages.email_invalid);
                isValid = false;
            } else if (field.input === phoneInput) {
                const cleanVal = val.replace(/[0-9+\s\-()]/g, '');
                if (cleanVal !== '' || val.replace(/[^0-9]/g, '').length < 6) {
                    showFieldError(field.input, field.errId, inersialabContactData.messages.phone_invalid);
                    isValid = false;
                }
            }
        });

        return isValid;
    };

    // Handle AJAX form submit
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        // 1. Validate Form client-side
        if (!validateForm()) {
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
