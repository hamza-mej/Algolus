// Form Controller - Handle form submissions without page reload

class FormController {
  constructor() {
    this.setupEventListeners();
  }

  setupEventListeners() {
    // All AJAX forms
    document.querySelectorAll("[data-ajax-form]").forEach(form => {
      form.addEventListener("submit", (e) => this.handleSubmit(e));
    });

    // Real-time validation
    document.querySelectorAll("[data-validate]").forEach(field => {
      field.addEventListener("blur", (e) => this.validateField(e.target));
      field.addEventListener("change", (e) => this.validateField(e.target));
    });
  }

  async handleSubmit(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const method = form.method || "POST";
    const action = form.action || window.location.href;
    const successClass = form.dataset.successClass || "alert-success";
    const errorClass = form.dataset.errorClass || "alert-danger";
    const successMessage = form.dataset.successMessage || "✅ Form submitted successfully!";
    const errorMessage = form.dataset.errorMessage || "❌ Error submitting form";

    // Validate form before submit
    if (!this.validateForm(form)) {
      this.showFormError(form, "⚠️ Please fix the errors in the form");
      return;
    }

    this.disableForm(form);
    this.showFormLoading(form);

    try {
      const formData = new FormData(form);
      const response = await fetch(action, {
        method,
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || "Form submission failed");
      }

      const data = await response.json();

      if (data.success) {
        this.showFormSuccess(form, successMessage, successClass);
        form.reset();
        this.clearFormErrors(form);

        // Redirect if specified
        if (data.redirect) {
          setTimeout(() => {
            window.location.href = data.redirect;
          }, 1500);
        }

        // Trigger custom success callback
        if (form.dataset.onSuccess) {
          window[form.dataset.onSuccess]?.(data);
        }
      } else {
        this.showFormError(form, data.message || errorMessage);
        
        // Show field-specific errors
        if (data.errors) {
          this.showFieldErrors(form, data.errors);
        }
      }
    } catch (error) {
      console.error("Form error:", error);
      this.showFormError(form, error.message || errorMessage);
    } finally {
      this.hideFormLoading(form);
      this.enableForm(form);
    }
  }

  validateField(field) {
    const rule = field.dataset.validate;
    if (!rule) return true;

    let isValid = true;
    const value = field.value.trim();
    const errorMsg = field.dataset.errorMessage || "Invalid input";

    switch (rule) {
      case "required":
        isValid = value.length > 0;
        break;
      case "email":
        isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        break;
      case "phone":
        isValid = /^\d{10,}$/.test(value.replace(/\D/g, ""));
        break;
      case "password":
        isValid = value.length >= 8;
        break;
      case "min":
        const minLength = parseInt(field.dataset.min);
        isValid = value.length >= minLength;
        break;
      case "max":
        const maxLength = parseInt(field.dataset.max);
        isValid = value.length <= maxLength;
        break;
      case "match":
        const matchField = document.querySelector(`[name="${field.dataset.match}"]`);
        isValid = value === matchField?.value;
        break;
    }

    if (isValid) {
      this.clearFieldError(field);
      field.classList.remove("is-invalid");
      field.classList.add("is-valid");
    } else {
      this.showFieldError(field, errorMsg);
    }

    return isValid;
  }

  validateForm(form) {
    let isValid = true;
    form.querySelectorAll("[data-validate]").forEach(field => {
      if (!this.validateField(field)) {
        isValid = false;
      }
    });
    return isValid;
  }

  showFieldError(field, message) {
    field.classList.add("is-invalid");
    field.classList.remove("is-valid");
    
    let errorDiv = field.nextElementSibling;
    if (!errorDiv || !errorDiv.classList.contains("invalid-feedback")) {
      errorDiv = document.createElement("div");
      errorDiv.className = "invalid-feedback";
      field.insertAdjacentElement("afterend", errorDiv);
    }
    errorDiv.textContent = message;
    errorDiv.style.display = "block";
  }

  clearFieldError(field) {
    field.classList.remove("is-invalid");
    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains("invalid-feedback")) {
      errorDiv.style.display = "none";
    }
  }

  showFieldErrors(form, errors) {
    for (const [fieldName, message] of Object.entries(errors)) {
      const field = form.querySelector(`[name="${fieldName}"]`);
      if (field) {
        this.showFieldError(field, message);
      }
    }
  }

  clearFormErrors(form) {
    form.querySelectorAll(".invalid-feedback").forEach(div => {
      div.style.display = "none";
    });
    form.querySelectorAll(".is-invalid").forEach(field => {
      field.classList.remove("is-invalid");
    });
  }

  showFormSuccess(form, message, className) {
    this.clearFormMessages(form);
    const alert = document.createElement("div");
    alert.className = `alert ${className} alert-dismissible fade show`;
    alert.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    form.insertBefore(alert, form.firstChild);
  }

  showFormError(form, message) {
    this.clearFormMessages(form);
    const alert = document.createElement("div");
    alert.className = "alert alert-danger alert-dismissible fade show";
    alert.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    form.insertBefore(alert, form.firstChild);
  }

  showFormLoading(form) {
    const loader = document.createElement("div");
    loader.className = "form-loader";
    loader.innerHTML = '<span class="spinner"></span> Submitting...';
    form.insertBefore(loader, form.firstChild);
  }

  hideFormLoading(form) {
    const loader = form.querySelector(".form-loader");
    if (loader) loader.remove();
  }

  clearFormMessages(form) {
    form.querySelectorAll(".alert").forEach(alert => {
      alert.remove();
    });
  }

  disableForm(form) {
    form.querySelectorAll("button, input, textarea, select").forEach(field => {
      field.disabled = true;
    });
  }

  enableForm(form) {
    form.querySelectorAll("button, input, textarea, select").forEach(field => {
      field.disabled = false;
    });
  }
}

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new FormController();
  });
} else {
  new FormController();
}
