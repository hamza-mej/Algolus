// Reviews Controller - AJAX reviews system

class ReviewsController {
  constructor() {
    this.setupEventListeners();
  }

  setupEventListeners() {
    // Load reviews button
    document.querySelectorAll("[data-action='load-reviews']").forEach(btn => {
      btn.addEventListener("click", (e) => this.loadReviews(e));
    });

    // Submit review form
    const reviewForm = document.querySelector("[data-reviews-form]");
    if (reviewForm) {
      reviewForm.addEventListener("submit", (e) => this.submitReview(e));
    }

    // Helpful button
    document.querySelectorAll("[data-action='mark-helpful']").forEach(btn => {
      btn.addEventListener("click", (e) => this.markHelpful(e));
    });
  }

  async loadReviews(event) {
    event.preventDefault();
    const productId = event.currentTarget.dataset.productId;
    const container = document.querySelector("[data-reviews-container]");

    if (!container) return;

    this.showLoading(container);

    try {
      const response = await fetch(`/api/reviews/${productId}`, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (!response.ok) throw new Error("Failed to load reviews");

      const data = await response.json();
      this.displayReviews(data, container);
    } catch (error) {
      console.error("Error:", error);
      this.showError(container, "Failed to load reviews");
    } finally {
      this.hideLoading(container);
    }
  }

  displayReviews(data, container) {
    let html = `
      <div class="reviews-header">
        <h3>Product Reviews (${data.stats.count})</h3>
        <div class="review-rating">
          <div class="stars">${this.renderStars(data.stats.average)}</div>
          <span class="rating-text">${data.stats.average.toFixed(1)}/5 based on ${data.stats.count} reviews</span>
        </div>
        <div class="rating-distribution">
    `;

    // Show rating distribution
    Object.keys(data.stats.distribution)
      .reverse()
      .forEach((rating) => {
        const count = data.stats.distribution[rating];
        const percent = data.stats.count > 0 ? (count / data.stats.count) * 100 : 0;
        html += `
          <div class="rating-bar">
            <span>${rating} ⭐</span>
            <div class="bar">
              <div class="fill" style="width: ${percent}%"></div>
            </div>
            <span>(${count})</span>
          </div>
        `;
      });

    html += `
        </div>
      </div>
      <div class="reviews-list">
    `;

    // Show individual reviews
    data.reviews.forEach((review) => {
      html += `
        <div class="review-item">
          <div class="review-header">
            <div class="review-author">
              <strong>${this.escapeHtml(review.author)}</strong>
              <span class="review-date">${review.createdAt}</span>
            </div>
            <div class="review-rating">${this.renderStars(review.rating)}</div>
          </div>
          <div class="review-title">${this.escapeHtml(review.title)}</div>
          <div class="review-content">${this.escapeHtml(review.comment)}</div>
          <div class="review-footer">
            <button data-action="mark-helpful" data-review-id="${review.id}" class="helpful-btn">
              👍 Helpful (${review.helpful})
            </button>
          </div>
        </div>
      `;
    });

    html += `
      </div>
    `;

    container.innerHTML = html;
    this.setupEventListeners();
  }

  async submitReview(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const productId = form.dataset.productId;
    const rating = form.querySelector("[name='rating']").value;
    const title = form.querySelector("[name='title']").value;
    const comment = form.querySelector("[name='comment']").value;

    if (!rating || !title || !comment) {
      this.notify("Please fill all fields", "warning");
      return;
    }

    this.showFormLoading(form);

    try {
      const response = await fetch("/api/reviews/submit", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ productId, rating, title, comment }),
      });

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error || "Failed to submit review");
      }

      const data = await response.json();
      this.notify("✅ Review submitted! Pending approval.", "success");
      form.reset();
      form.style.display = "none";
    } catch (error) {
      console.error("Error:", error);
      this.notify(`❌ ${error.message}`, "error");
    } finally {
      this.hideFormLoading(form);
    }
  }

  async markHelpful(event) {
    event.preventDefault();
    const reviewId = event.currentTarget.dataset.reviewId;
    const button = event.currentTarget;

    try {
      const response = await fetch(`/api/reviews/${reviewId}/helpful`, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (!response.ok) throw new Error("Failed to mark as helpful");

      const data = await response.json();
      button.textContent = `👍 Helpful (${data.helpful})`;
      button.disabled = true;
    } catch (error) {
      console.error("Error:", error);
      this.notify("❌ Error marking helpful", "error");
    }
  }

  renderStars(rating) {
    const full = Math.floor(rating);
    const half = rating % 1 !== 0 ? 1 : 0;
    const empty = 5 - full - half;

    let stars = "⭐".repeat(full);
    if (half) stars += "⭐";
    stars += "☆".repeat(empty);

    return stars;
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  showLoading(element) {
    element.innerHTML = '<div class="loading"><span class="spinner"></span> Loading reviews...</div>';
  }

  hideLoading(element) {
    // Loading state cleared by displayReviews
  }

  showError(container, message) {
    container.innerHTML = `<div class="alert alert-danger">${message}</div>`;
  }

  showFormLoading(form) {
    const button = form.querySelector("button[type='submit']");
    button.disabled = true;
    button.innerHTML = '<span class="spinner"></span> Submitting...';
  }

  hideFormLoading(form) {
    const button = form.querySelector("button[type='submit']");
    button.disabled = false;
    button.innerHTML = "Submit Review";
  }

  notify(message, type = "info") {
    const notification = document.createElement("div");
    const bgColor = {
      success: "#28a745",
      error: "#dc3545",
      warning: "#ffc107",
      info: "#17a2b8",
    }[type] || "#17a2b8";

    notification.innerHTML = `
      <div style="
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${bgColor};
        color: ${type === 'warning' ? '#000' : '#fff'};
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideIn 0.3s ease-in;
        font-weight: 500;
      ">
        ${message}
      </div>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
  }
}

// Initialize
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new ReviewsController();
  });
} else {
  new ReviewsController();
}
