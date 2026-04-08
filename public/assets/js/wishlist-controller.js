// Wishlist Controller - AJAX wishlist system

class WishlistController {
  constructor() {
    this.setupEventListeners();
  }

  setupEventListeners() {
    // Add to wishlist buttons
    document.querySelectorAll("[data-action='add-wishlist']").forEach(btn => {
      btn.addEventListener("click", (e) => this.addToWishlist(e));
    });

    // Remove from wishlist buttons
    document.querySelectorAll("[data-action='remove-wishlist']").forEach(btn => {
      btn.addEventListener("click", (e) => this.removeFromWishlist(e));
    });

    // Wishlist heart toggle
    document.querySelectorAll("[data-wishlist-toggle]").forEach(btn => {
      btn.addEventListener("click", (e) => this.toggleWishlist(e));
    });
  }

  async addToWishlist(event) {
    event.preventDefault();
    const button = event.currentTarget;
    const productId = button.dataset.productId;

    this.showLoading(button);

    try {
      const response = await fetch("/api/wishlist/add", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ productId }),
      });

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error || "Failed to add to wishlist");
      }

      const data = await response.json();
      this.notify(data.message, "success");
      this.updateWishlistUI(button, true, data.count);
    } catch (error) {
      console.error("Error:", error);
      this.notify("❌ " + error.message, "error");
    } finally {
      this.hideLoading(button);
    }
  }

  async removeFromWishlist(event) {
    event.preventDefault();
    const button = event.currentTarget;
    const productId = button.dataset.productId;

    this.showLoading(button);

    try {
      const response = await fetch("/api/wishlist/remove", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ productId }),
      });

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error || "Failed to remove from wishlist");
      }

      const data = await response.json();
      this.notify(data.message, "success");
      this.updateWishlistUI(button, false, data.count);
    } catch (error) {
      console.error("Error:", error);
      this.notify("❌ " + error.message, "error");
    } finally {
      this.hideLoading(button);
    }
  }

  async toggleWishlist(event) {
    const productId = event.currentTarget.dataset.productId;

    try {
      // Check if in wishlist
      const checkResponse = await fetch(`/api/wishlist/check/${productId}`, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (!checkResponse.ok) {
        // Not logged in
        window.location.href = "/login";
        return;
      }

      const checkData = await checkResponse.json();

      if (checkData.inWishlist) {
        await this.removeFromWishlist(event);
      } else {
        await this.addToWishlist(event);
      }
    } catch (error) {
      console.error("Error:", error);
      this.notify("❌ Error updating wishlist", "error");
    }
  }

  updateWishlistUI(button, isAdded, count) {
    button.dataset.inWishlist = isAdded;

    // Update heart icon
    if (isAdded) {
      button.classList.add("in-wishlist");
      button.innerHTML = "❤️";
    } else {
      button.classList.remove("in-wishlist");
      button.innerHTML = "🤍";
    }

    // Update wishlist count if displayed
    const countElement = document.querySelector("[data-wishlist-count]");
    if (countElement) {
      countElement.textContent = count;
      countElement.style.display = count > 0 ? "inline-block" : "none";
    }
  }

  showLoading(button) {
    button.disabled = true;
    button.style.opacity = "0.6";
  }

  hideLoading(button) {
    button.disabled = false;
    button.style.opacity = "1";
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
    new WishlistController();
  });
} else {
  new WishlistController();
}
