// Recommendations Controller - Product recommendations

class RecommendationsController {
  constructor() {
    this.setupRecommendations();
  }

  setupRecommendations() {
    // Load personalized recommendations
    const personalContainer = document.querySelector("[data-personal-recommendations]");
    if (personalContainer) {
      this.loadPersonalRecommendations(personalContainer);
    }

    // Load similar products
    document.querySelectorAll("[data-similar-products]").forEach((container) => {
      const productId = container.dataset.productId;
      if (productId) {
        this.loadSimilarProducts(productId, container);
      }
    });

    // Load also viewed
    document.querySelectorAll("[data-also-viewed]").forEach((container) => {
      const productId = container.dataset.productId;
      if (productId) {
        this.loadAlsoViewed(productId, container);
      }
    });

    // Load related products
    document.querySelectorAll("[data-related-products]").forEach((container) => {
      const productId = container.dataset.productId;
      if (productId) {
        this.loadRelatedProducts(productId, container);
      }
    });
  }

  async loadPersonalRecommendations(container) {
    const limit = parseInt(container.dataset.limit) || 6;

    this.showLoading(container);

    try {
      const response = await fetch(`/api/recommendations?limit=${limit}`, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) throw new Error("Failed to load recommendations");

      const data = await response.json();
      this.displayRecommendations(data.recommendations, container);
    } catch (error) {
      console.error("Error:", error);
      this.hideLoading(container);
    }
  }

  async loadSimilarProducts(productId, container) {
    const limit = parseInt(container.dataset.limit) || 6;

    this.showLoading(container);

    try {
      const response = await fetch(
        `/api/recommendations/similar/${productId}?limit=${limit}`,
        { headers: { "X-Requested-With": "XMLHttpRequest" } }
      );

      if (!response.ok) throw new Error("Failed to load similar products");

      const data = await response.json();
      this.displayRecommendations(data.similar, container);
    } catch (error) {
      console.error("Error:", error);
      this.hideLoading(container);
    }
  }

  async loadAlsoViewed(productId, container) {
    const limit = parseInt(container.dataset.limit) || 4;

    this.showLoading(container);

    try {
      const response = await fetch(
        `/api/recommendations/also-viewed/${productId}?limit=${limit}`,
        { headers: { "X-Requested-With": "XMLHttpRequest" } }
      );

      if (!response.ok) throw new Error("Failed to load also viewed");

      const data = await response.json();
      this.displayRecommendations(data.also_viewed, container);
    } catch (error) {
      console.error("Error:", error);
      this.hideLoading(container);
    }
  }

  async loadRelatedProducts(productId, container) {
    const limit = parseInt(container.dataset.limit) || 4;

    this.showLoading(container);

    try {
      const response = await fetch(
        `/api/recommendations/related/${productId}?limit=${limit}`,
        { headers: { "X-Requested-With": "XMLHttpRequest" } }
      );

      if (!response.ok) throw new Error("Failed to load related products");

      const data = await response.json();
      this.displayRecommendations(data.related, container);
    } catch (error) {
      console.error("Error:", error);
      this.hideLoading(container);
    }
  }

  displayRecommendations(products, container) {
    if (products.length === 0) {
      container.innerHTML = "<p>No recommendations available</p>";
      return;
    }

    let html = '<div class="recommendations-grid">';

    products.forEach((product) => {
      html += `
        <div class="recommendation-card">
          <div class="recommendation-image">
            <img src="${product.image}" alt="${this.escapeHtml(product.name)}" loading="lazy">
            ${product.onSale ? '<span class="sale-badge">On Sale</span>' : ""}
          </div>
          <div class="recommendation-content">
            <h4><a href="${product.url}">${this.escapeHtml(product.name)}</a></h4>
            <p class="price">$${product.price.toFixed(2)}</p>
            ${
              product.category
                ? `<p class="category">${this.escapeHtml(product.category)}</p>`
                : ""
            }
            <a href="${product.url}" class="btn-small">View Product</a>
          </div>
        </div>
      `;
    });

    html += "</div>";
    container.innerHTML = html;
  }

  showLoading(container) {
    container.innerHTML =
      '<div class="loading"><span class="spinner"></span> Loading recommendations...</div>';
  }

  hideLoading(container) {
    container.innerHTML = "";
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new RecommendationsController();
  });
} else {
  new RecommendationsController();
}
