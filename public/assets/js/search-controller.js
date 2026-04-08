// Search Controller - Live search with autocomplete

class SearchController {
  constructor() {
    this.searchInput = document.querySelector("[data-search-input]");
    this.resultsContainer = document.querySelector("[data-search-results]");
    this.debounceTimer = null;
    
    if (this.searchInput) {
      this.setupEventListeners();
    }
  }

  setupEventListeners() {
    this.searchInput.addEventListener("input", (e) => {
      clearTimeout(this.debounceTimer);
      const query = e.target.value.trim();

      if (query.length < 2) {
        this.hideResults();
        return;
      }

      this.debounceTimer = setTimeout(() => {
        this.search(query);
      }, 300); // Wait 300ms after user stops typing
    });

    // Hide results when clicking outside
    document.addEventListener("click", (e) => {
      if (!e.target.closest("[data-search-container]")) {
        this.hideResults();
      }
    });

    // Handle keyboard navigation
    this.searchInput.addEventListener("keydown", (e) => {
      const items = this.resultsContainer?.querySelectorAll("[data-search-item]");
      if (!items) return;

      switch (e.key) {
        case "ArrowDown":
          e.preventDefault();
          this.navigateResults(items, 1);
          break;
        case "ArrowUp":
          e.preventDefault();
          this.navigateResults(items, -1);
          break;
        case "Enter":
          e.preventDefault();
          const active = this.resultsContainer.querySelector("[data-search-item].active");
          if (active) {
            active.click();
          }
          break;
        case "Escape":
          this.hideResults();
          break;
      }
    });
  }

  async search(query) {
    if (!this.resultsContainer) return;

    this.showLoading();

    try {
      const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (!response.ok) {
        throw new Error("Search failed");
      }

      const data = await response.json();
      this.displayResults(data.results, query);
    } catch (error) {
      console.error("Search error:", error);
      this.showError("❌ Search failed");
    }
  }

  displayResults(results, query) {
    if (results.length === 0) {
      this.resultsContainer.innerHTML = `<div class="search-no-results">No results found for "${query}"</div>`;
      this.showResults();
      return;
    }

    let html = '<ul class="search-results-list">';
    results.forEach((result, index) => {
      const highlighted = this.highlightQuery(result.name, query);
      html += `
        <li data-search-item class="search-item ${index === 0 ? 'active' : ''}">
          <a href="${result.url}" class="search-item-link">
            <div class="search-item-image" style="background-image: url('${result.image}')"></div>
            <div class="search-item-content">
              <div class="search-item-name">${highlighted}</div>
              <div class="search-item-price">$${result.price.toFixed(2)}</div>
            </div>
          </a>
        </li>
      `;
    });
    html += '</ul>';

    this.resultsContainer.innerHTML = html;
    this.showResults();

    // Add click handlers
    this.resultsContainer.querySelectorAll("[data-search-item]").forEach(item => {
      item.addEventListener("click", () => {
        const link = item.querySelector(".search-item-link");
        window.location.href = link.href;
      });

      item.addEventListener("mouseenter", () => {
        this.resultsContainer.querySelectorAll("[data-search-item]").forEach(i => {
          i.classList.remove("active");
        });
        item.classList.add("active");
      });
    });
  }

  highlightQuery(text, query) {
    const regex = new RegExp(`(${query})`, "gi");
    return text.replace(regex, "<strong>$1</strong>");
  }

  navigateResults(items, direction) {
    let currentIndex = -1;
    items.forEach((item, index) => {
      if (item.classList.contains("active")) {
        currentIndex = index;
      }
    });

    let newIndex = currentIndex + direction;
    if (newIndex < 0) newIndex = items.length - 1;
    if (newIndex >= items.length) newIndex = 0;

    items.forEach(item => item.classList.remove("active"));
    items[newIndex].classList.add("active");
  }

  showLoading() {
    this.resultsContainer.innerHTML = `
      <div class="search-loading">
        <span class="spinner"></span> Searching...
      </div>
    `;
    this.showResults();
  }

  showError(message) {
    this.resultsContainer.innerHTML = `<div class="search-error">${message}</div>`;
    this.showResults();
  }

  showResults() {
    this.resultsContainer.style.display = "block";
  }

  hideResults() {
    if (this.resultsContainer) {
      this.resultsContainer.style.display = "none";
    }
  }
}

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new SearchController();
  });
} else {
  new SearchController();
}
