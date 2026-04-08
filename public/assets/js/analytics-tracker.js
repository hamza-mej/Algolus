// Analytics Tracker - Track user interactions

class AnalyticsTracker {
  constructor() {
    this.pageStartTime = Date.now();
    this.setupTracking();
  }

  setupTracking() {
    // Track page view when leaving
    window.addEventListener("beforeunload", () => {
      this.trackProductView();
    });

    // Track search
    document.querySelectorAll("[data-search-input]").forEach(input => {
      input.addEventListener("change", (e) => this.trackSearch(e));
    });

    // Track form submissions
    document.querySelectorAll("[data-ajax-form]").forEach(form => {
      form.addEventListener("submit", (e) => this.trackFormSubmit(e));
    });

    // Track links
    document.querySelectorAll("a[data-track]").forEach(link => {
      link.addEventListener("click", (e) => this.trackClick(e));
    });
  }

  trackProductView() {
    const productId = document.querySelector("[data-product-id]")?.dataset.productId;
    if (!productId) return;

    const duration = Math.round((Date.now() - this.pageStartTime) / 1000);

    // Send async without blocking page unload
    navigator.sendBeacon("/api/analytics/track-view", JSON.stringify({
      productId,
      duration,
    }));
  }

  trackSearch(event) {
    const query = event.currentTarget.value.trim();
    if (query.length < 2) return;

    // Debounce search tracking
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
      fetch("/api/analytics/track-event", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({
          type: "search",
          data: { query },
        }),
        keepalive: true,
      }).catch(() => {}); // Silently fail
    }, 1000);
  }

  trackFormSubmit(event) {
    const form = event.target;
    const formType = form.dataset.ajaxForm || "form";

    fetch("/api/analytics/track-event", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({
        type: "form_submit",
        data: { formType },
      }),
      keepalive: true,
    }).catch(() => {}); // Silently fail
  }

  trackClick(event) {
    const element = event.currentTarget;
    const trackName = element.dataset.track;

    fetch("/api/analytics/track-event", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({
        type: "click",
        data: { element: trackName },
      }),
      keepalive: true,
    }).catch(() => {}); // Silently fail
  }

  /**
   * Track custom event
   */
  static trackEvent(type, data = {}) {
    fetch("/api/analytics/track-event", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({ type, data }),
      keepalive: true,
    }).catch(() => {}); // Silently fail
  }
}

// Initialize analytics
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new AnalyticsTracker();
  });
} else {
  new AnalyticsTracker();
}

// Make tracker available globally
window.AnalyticsTracker = AnalyticsTracker;
