// Product Filter Controller - Live product filtering without page reload

class FilterController {
  constructor() {
    this.filters = {};
    this.page = 1;
    this.setupEventListeners();
  }

  setupEventListeners() {
    // Category filters
    document.querySelectorAll("[data-filter='category']").forEach(checkbox => {
      checkbox.addEventListener("change", () => this.applyFilters());
    });

    // Color filters
    document.querySelectorAll("[data-filter='color']").forEach(checkbox => {
      checkbox.addEventListener("change", () => this.applyFilters());
    });

    // Size filters
    document.querySelectorAll("[data-filter='size']").forEach(checkbox => {
      checkbox.addEventListener("change", () => this.applyFilters());
    });

    // Price range slider
    const priceMin = document.querySelector("[data-price='min']");
    const priceMax = document.querySelector("[data-price='max']");
    if (priceMin && priceMax) {
      priceMin.addEventListener("change", () => this.applyFilters());
      priceMax.addEventListener("change", () => this.applyFilters());
    }

    // Sort dropdown
    const sortSelect = document.querySelector("[data-sort]");
    if (sortSelect) {
      sortSelect.addEventListener("change", () => this.applyFilters());
    }

    // Pagination
    document.querySelectorAll("[data-page]").forEach(link => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        this.page = parseInt(link.dataset.page);
        this.applyFilters();
      });
    });
  }

  getFilters() {
    const filters = {
      categories: [],
      colors: [],
      sizes: [],
      minPrice: 0,
      maxPrice: 10000,
      sort: "newest",
      page: this.page,
    };

    // Get selected categories
    document.querySelectorAll("[data-filter='category']:checked").forEach(cb => {
      filters.categories.push(cb.value);
    });

    // Get selected colors
    document.querySelectorAll("[data-filter='color']:checked").forEach(cb => {
      filters.colors.push(cb.value);
    });

    // Get selected sizes
    document.querySelectorAll("[data-filter='size']:checked").forEach(cb => {
      filters.sizes.push(cb.value);
    });

    // Get price range
    const priceMin = document.querySelector("[data-price='min']");
    const priceMax = document.querySelector("[data-price='max']");
    if (priceMin) filters.minPrice = parseInt(priceMin.value);
    if (priceMax) filters.maxPrice = parseInt(priceMax.value);

    // Get sort
    const sortSelect = document.querySelector("[data-sort]");
    if (sortSelect) filters.sort = sortSelect.value;

    return filters;
  }

  async applyFilters() {
    const filters = this.getFilters();
    const container = document.querySelector("[data-products-container]");

    if (!container) return;

    this.showLoading(container);

    try {
      const queryString = new URLSearchParams();
      
      if (filters.categories.length > 0) {
        queryString.append("categories", filters.categories.join(","));
      }
      if (filters.colors.length > 0) {
        queryString.append("colors", filters.colors.join(","));
      }
      if (filters.sizes.length > 0) {
        queryString.append("sizes", filters.sizes.join(","));
      }
      if (filters.minPrice > 0) {
        queryString.append("minPrice", filters.minPrice);
      }
      if (filters.maxPrice < 10000) {
        queryString.append("maxPrice", filters.maxPrice);
      }
      if (filters.sort !== "newest") {
        queryString.append("sort", filters.sort);
      }
      if (filters.page > 1) {
        queryString.append("page", filters.page);
      }

      const response = await fetch(`/api/products/filter?${queryString}`, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (!response.ok) {
        throw new Error("Failed to filter products");
      }

      const data = await response.json();
      
      // Update products
      if (data.productsHtml) {
        container.innerHTML = data.productsHtml;
      }

      // Update pagination
      const pagination = document.querySelector("[data-pagination]");
      if (pagination && data.paginationHtml) {
        pagination.innerHTML = data.paginationHtml;
      }

      // Update active filters display
      this.updateActiveFilters();

      // Re-attach event listeners
      this.setupEventListeners();
    } catch (error) {
      console.error("Error:", error);
      this.showError(container, "❌ Error applying filters");
    } finally {
      this.hideLoading(container);
    }
  }

  updateActiveFilters() {
    const filters = this.getFilters();
    const activeFiltersContainer = document.querySelector("[data-active-filters]");

    if (!activeFiltersContainer) return;

    let filterHTML = "";

    // Category filters
    document.querySelectorAll("[data-filter='category']:checked").forEach(cb => {
      filterHTML += `<span class="badge badge-primary">${cb.dataset.label} <button data-remove-filter="category" data-value="${cb.value}" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:5px;">×</button></span>`;
    });

    // Color filters
    document.querySelectorAll("[data-filter='color']:checked").forEach(cb => {
      filterHTML += `<span class="badge badge-primary">${cb.dataset.label} <button data-remove-filter="color" data-value="${cb.value}" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:5px;">×</button></span>`;
    });

    // Size filters
    document.querySelectorAll("[data-filter='size']:checked").forEach(cb => {
      filterHTML += `<span class="badge badge-primary">${cb.dataset.label} <button data-remove-filter="size" data-value="${cb.value}" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:5px;">×</button></span>`;
    });

    activeFiltersContainer.innerHTML = filterHTML;

    // Attach remove filter listeners
    document.querySelectorAll("[data-remove-filter]").forEach(btn => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        const filterType = btn.dataset.removeFilter;
        const value = btn.dataset.value;
        document.querySelector(`[data-filter='${filterType}'][value='${value}']`)?.click();
      });
    });
  }

  showLoading(container) {
    container.style.opacity = "0.5";
    container.style.pointerEvents = "none";
  }

  hideLoading(container) {
    container.style.opacity = "1";
    container.style.pointerEvents = "auto";
  }

  showError(container, message) {
    const errorDiv = document.createElement("div");
    errorDiv.className = "alert alert-danger";
    errorDiv.textContent = message;
    container.prepend(errorDiv);
    setTimeout(() => errorDiv.remove(), 5000);
  }
}

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new FilterController();
  });
} else {
  new FilterController();
}
