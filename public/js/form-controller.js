// Form AJAX Controller
// Handles form submissions via AJAX without page reload

export default class extends window.Stimulus.Controller {
    static targets = ['form', 'submitButton', 'errorContainer', 'successContainer'];
    static values = { method: 'POST', timeout: 10000 };

    connect() {
        if (this.hasFormTarget) {
            this.formTarget.addEventListener('submit', this.handleSubmit.bind(this));
        }
    }

    async handleSubmit(event) {
        event.preventDefault();

        const form = this.formTarget;
        const formData = new FormData(form);

        // Show loading state
        this.setSubmitButtonLoading(true);
        this.clearMessages();

        try {
            const response = await Promise.race([
                fetch(form.action, {
                    method: this.methodValue || 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }),
                new Promise((_, reject) =>
                    setTimeout(() => reject(new Error('Request timeout')), this.timeoutValue)
                ),
            ]);

            const data = await response.json();

            if (response.ok && data.success) {
                this.showSuccess(data.message || 'Form submitted successfully');
                
                // Reset form if requested
                if (data.resetForm !== false) {
                    form.reset();
                }

                // Redirect if needed
                if (data.redirect) {
                    window.location.href = data.redirect;
                }

                // Dispatch event for other controllers
                this.element.dispatchEvent(new CustomEvent('form-submitted', { detail: data }));
            } else {
                const errorMessage = data.message || 'An error occurred while submitting the form';
                this.showError(errorMessage);

                // Show field errors if provided
                if (data.errors) {
                    this.displayFieldErrors(data.errors, form);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError(error.message || 'An error occurred. Please try again.');
        } finally {
            this.setSubmitButtonLoading(false);
        }
    }

    displayFieldErrors(errors, form) {
        Object.entries(errors).forEach(([fieldName, errorMessage]) => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.add('is-invalid');
                const feedback = field.parentElement.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errorMessage;
                    feedback.style.display = 'block';
                }
            }
        });
    }

    showSuccess(message) {
        if (this.hasSuccessContainerTarget) {
            this.successContainerTarget.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            this.successContainerTarget.style.display = 'block';
            setTimeout(() => {
                this.successContainerTarget.style.display = 'none';
            }, 5000);
        }
    }

    showError(message) {
        if (this.hasErrorContainerTarget) {
            this.errorContainerTarget.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            this.errorContainerTarget.style.display = 'block';
        }
    }

    clearMessages() {
        if (this.hasErrorContainerTarget) {
            this.errorContainerTarget.innerHTML = '';
            this.errorContainerTarget.style.display = 'none';
        }
        if (this.hasSuccessContainerTarget) {
            this.successContainerTarget.innerHTML = '';
            this.successContainerTarget.style.display = 'none';
        }

        // Clear field errors
        const form = this.formTarget;
        form.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
            const feedback = field.parentElement.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'none';
            }
        });
    }

    setSubmitButtonLoading(isLoading) {
        if (!this.hasSubmitButtonTarget) return;

        const button = this.submitButtonTarget;
        if (isLoading) {
            button.disabled = true;
            const originalText = button.textContent;
            button.dataset.originalText = originalText;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        } else {
            button.disabled = false;
            if (button.dataset.originalText) {
                button.textContent = button.dataset.originalText;
            }
        }
    }
}
