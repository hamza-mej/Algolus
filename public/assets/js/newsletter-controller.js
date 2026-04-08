// Newsletter Controller - Email subscription management

class NewsletterController {
  constructor() {
    this.setupForms();
  }

  setupForms() {
    // Newsletter subscription form
    const subscribeForm = document.querySelector("[data-newsletter-form]");
    if (subscribeForm) {
      subscribeForm.addEventListener("submit", (e) => this.handleSubscribe(e));
    }

    // Preferences form
    const prefsForm = document.querySelector("[data-preferences-form]");
    if (prefsForm) {
      prefsForm.addEventListener("submit", (e) => this.handlePreferences(e));
    }

    // Load preferences if authenticated
    this.loadPreferences();
  }

  async handleSubscribe(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const email = form.querySelector("[name='email']").value;
    const submitBtn = form.querySelector("button[type='submit']");

    submitBtn.disabled = true;
    submitBtn.textContent = "Subscribing...";

    try {
      const response = await fetch("/api/newsletter/subscribe", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ email }),
      });

      const data = await response.json();

      if (response.ok) {
        this.showNotification("Success! Check your email for confirmation", "success");
        form.reset();
      } else {
        this.showNotification(data.error || "Subscription failed", "error");
      }
    } catch (error) {
      this.showNotification("Error subscribing", "error");
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Subscribe";
    }
  }

  async loadPreferences() {
    try {
      const response = await fetch("/api/newsletter/preferences", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (response.ok) {
        const data = await response.json();
        this.displayPreferences(data);
      }
    } catch (error) {
      // Not authenticated, skip
    }
  }

  async handlePreferences(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const checkboxes = form.querySelectorAll("[name^='preference_']:checked");

    const preferences = Array.from(checkboxes).reduce((acc, cb) => {
      acc[cb.value] = true;
      return acc;
    }, {});

    try {
      const response = await fetch("/api/newsletter/preferences", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ preferences }),
      });

      if (response.ok) {
        this.showNotification("Preferences updated", "success");
      }
    } catch (error) {
      this.showNotification("Error updating preferences", "error");
    }
  }

  displayPreferences(data) {
    const container = document.querySelector("[data-preferences-list]");
    if (!container) return;

    let html = `<div class="preferences-section">
      <h3>Email Preferences</h3>
      <p>${data.email}</p>
      <p class="status">Status: <strong>${data.status}</strong></p>
    </div>`;

    container.innerHTML = html;
  }

  showNotification(message, type) {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => notification.remove(), 3000);
  }
}

// Initialize
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new NewsletterController();
  });
} else {
  new NewsletterController();
}
