/* InersiaLab Contact Form Client-side Script */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('inersialab-contact-form');
    if (!form) return;

    const firstNameInput = document.getElementById('inersialab-first-name');
    const lastNameInput  = document.getElementById('inersialab-last-name');
    const emailInput     = document.getElementById('inersialab-email');
    const messageInput   = document.getElementById('inersialab-message');
    const submitBtn      = document.getElementById('inersialab-submit-btn');
    const responseBox    = document.getElementById('inersialab-form-response');

    // Quick mapping helper for inputs and error containers
    const fields = [
        { input: firstNameInput, errId: 'err-first-name', name: 'First name' },
        { input: lastNameInput,  errId: 'err-last-name',  name: 'Last name' },
        { input: emailInput,     errId: 'err-email',      name: 'Email address' },
        { input: messageInput,   errId: 'err-message',    name: 'Message' }
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

        fields.forEach(field => {
            if (!field.input) return;
            const val = field.input.value.trim();

            if (val === '') {
                showFieldError(field.input, field.errId, `${field.name} is required.`);
                isValid = false;
            } else if (field.input === emailInput && !emailRegex.test(val)) {
                showFieldError(field.input, field.errId, 'Please enter a valid email address.');
                isValid = false;
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
        const submitTextNode = submitBtn.querySelector('.btn-text');
        const originalText = submitTextNode.textContent;
        submitTextNode.textContent = 'Sending...';

        // 4. Construct form data
        const formData = new FormData();
        formData.append('action', 'inersialab_send_contact');
        formData.append('nonce', inersialabContactData.nonce);
        formData.append('first_name', firstNameInput.value.trim());
        formData.append('last_name', lastNameInput.value.trim());
        formData.append('email', emailInput.value.trim());
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
                responseBox.textContent = data.data.message || 'An error occurred. Please try again.';
            }
        })
        .catch(err => {
            // General Network Error
            responseBox.classList.add('show-response', 'error');
            responseBox.textContent = 'Unable to send message due to a connection error. Please try again later.';
            console.error('InersiaLab Contact AJAX Error:', err);
        })
        .finally(() => {
            // Restore UI state
            submitBtn.disabled = false;
            submitTextNode.textContent = originalText;
        });
    });
});
