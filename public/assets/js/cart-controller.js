// Cart AJAX Controller - Handles all shopping cart operations without page reload

class CartController {
  constructor() {
    this.setupEventListeners();
  }

  setupEventListeners() {
    // Add to cart buttons
    document.querySelectorAll("[data-action='add-to-cart']").forEach(btn => {
      btn.addEventListener("click", (e) => this.addToCart(e));
    });

    // Remove from cart buttons
    document.querySelectorAll("[data-action='remove-from-cart']").forEach(btn => {
      btn.addEventListener("click", (e) => this.removeFromCart(e));
    });

    // Update quantity inputs
    document.querySelectorAll("[data-action='update-quantity']").forEach(input => {
      input.addEventListener("change", (e) => this.updateQuantity(e));
    });

    // Clear cart button
    const clearBtn = document.querySelector("[data-action='clear-cart']");
    if (clearBtn) {
      clearBtn.addEventListener("click", (e) => this.clearCart(e));
    }
  }

  async addToCart(event) {
    event.preventDefault();
    const button = event.currentTarget;
    const productId = button.dataset.productId;
    const quantityInput = document.querySelector(`[data-quantity-input='${productId}']`);
    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

    this.showLoading(button);

    try {
      const response = await fetch("/api/cart/add", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ productId, quantity }),
      });

      if (!response.ok) {
        throw new Error("Failed to add to cart");
      }

      const data = await response.json();
      this.updateCartUI(data);
      this.notify("✅ Product added to cart!", "success");
    } catch (error) {
      console.error("Error:", error);
      this.notify("❌ Error adding to cart", "error");
    } finally {
      this.hideLoading(button);
    }
  }

  async removeFromCart(event) {
    event.preventDefault();
    const button = event.currentTarget;
    const productId = button.dataset.productId;

    if (!confirm("Remove this item from cart?")) return;

    this.showLoading(button);

    try {
      const response = await fetch("/api/cart/remove", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ productId }),
      });

      if (!response.ok) {
        throw new Error("Failed to remove from cart");
      }

      const data = await response.json();
      this.updateCartUI(data);
      this.notify("✅ Item removed from cart!", "success");
    } catch (error) {
      console.error("Error:", error);
      this.notify("❌ Error removing item", "error");
    } finally {
      this.hideLoading(button);
    }
  }

  async updateQuantity(event) {
    const input = event.currentTarget;
    const productId = input.dataset.productId;
    const quantity = parseInt(input.value);

    if (quantity <= 0) {
      input.value = 1;
      this.notify("⚠️ Quantity must be at least 1", "warning");
      return;
    }

    try {
      const response = await fetch("/api/cart/update", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ productId, quantity }),
      });

      if (!response.ok) {
        throw new Error("Failed to update quantity");
      }

      const data = await response.json();
      this.updateCartUI(data);
    } catch (error) {
      console.error("Error:", error);
      this.notify("❌ Error updating quantity", "error");
    }
  }

  async clearCart(event) {
    event.preventDefault();

    if (!confirm("Clear entire cart?")) return;

    try {
      const response = await fetch("/api/cart/clear", {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (!response.ok) {
        throw new Error("Failed to clear cart");
      }

      const data = await response.json();
      this.updateCartUI(data);
      this.notify("✅ Cart cleared!", "success");
    } catch (error) {
      console.error("Error:", error);
      this.notify("❌ Error clearing cart", "error");
    }
  }

  updateCartUI(data) {
    // Update cart count
    const cartCount = document.querySelector("[data-cart-count]");
    if (cartCount) {
      cartCount.textContent = data.itemCount;
      cartCount.style.display = data.itemCount > 0 ? "block" : "none";
    }

    // Update cart total
    const cartTotal = document.querySelector("[data-cart-total]");
    if (cartTotal) {
      cartTotal.textContent = `$${data.total.toFixed(2)}`;
    }

    // Update mini cart if exists
    const miniCart = document.querySelector("[data-mini-cart]");
    if (miniCart && data.miniCartHtml) {
      miniCart.innerHTML = data.miniCartHtml;
      // Re-attach event listeners
      this.setupEventListeners();
    }

    // Update cart page if exists
    const cartContainer = document.querySelector("[data-cart-container]");
    if (cartContainer && data.cartHtml) {
      cartContainer.innerHTML = data.cartHtml;
      // Re-attach event listeners
      this.setupEventListeners();
    }
  }

  showLoading(element) {
    element.disabled = true;
    element.style.opacity = "0.6";
    element.innerHTML = '<span class="spinner"></span> Loading...';
  }

  hideLoading(element) {
    element.disabled = false;
    element.style.opacity = "1";
    element.innerHTML = element.dataset.originalText || "Add to Cart";
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

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new CartController();
  });
} else {
  new CartController();
}
