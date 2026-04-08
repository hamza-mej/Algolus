// Analytics Dashboard Controller - Advanced reporting

class AnalyticsDashboardController {
  constructor() {
    this.initDashboard();
  }

  initDashboard() {
    const dashboard = document.querySelector("[data-analytics-dashboard]");
    if (!dashboard) return;

    // Load all analytics
    this.loadSalesAnalytics();
    this.loadCustomerLTV();
    this.loadProductPerformance();
    this.loadConversionFunnel();
    this.loadEngagementMetrics();

    // Setup export button
    const exportBtn = document.querySelector("[data-export-analytics]");
    if (exportBtn) {
      exportBtn.addEventListener("click", () => this.exportAnalytics());
    }

    // Setup date filter
    const filterForm = document.querySelector("[data-analytics-filter]");
    if (filterForm) {
      filterForm.addEventListener("submit", (e) => {
        e.preventDefault();
        this.loadSalesAnalytics();
      });
    }
  }

  async loadSalesAnalytics() {
    const container = document.querySelector("[data-sales-analytics]");
    if (!container) return;

    const fromInput = document.querySelector('[name="analytics_from"]');
    const toInput = document.querySelector('[name="analytics_to"]');

    const params = new URLSearchParams();
    if (fromInput?.value) params.append("from", fromInput.value);
    if (toInput?.value) params.append("to", toInput.value);

    try {
      const response = await fetch(`/api/analytics/sales?${params}`, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) throw new Error("Failed to load sales analytics");

      const data = await response.json();
      this.renderSalesAnalytics(data.data, container);
    } catch (error) {
      console.error("Error:", error);
      container.innerHTML = '<div class="error">Failed to load analytics</div>';
    }
  }

  renderSalesAnalytics(data, container) {
    let html = `
      <div class="analytics-card">
        <h3>Sales Analytics</h3>
        <div class="stats-grid">
          <div class="stat">
            <span class="label">Total Orders</span>
            <span class="value">${data.totalOrders}</span>
          </div>
          <div class="stat">
            <span class="label">Total Revenue</span>
            <span class="value">$${data.totalRevenue?.toFixed(2)}</span>
          </div>
          <div class="stat">
            <span class="label">Average Order Value</span>
            <span class="value">$${data.averageOrderValue?.toFixed(2)}</span>
          </div>
        </div>

        <h4>By Status</h4>
        <div class="status-breakdown">
          ${Object.entries(data.byStatus || {})
            .map(
              ([status, stats]) => `
            <div class="status-item">
              <span>${status}</span>
              <span>${stats.count} orders - $${stats.revenue?.toFixed(2)}</span>
            </div>
          `
            )
            .join("")}
        </div>
      </div>
    `;

    container.innerHTML = html;
  }

  async loadCustomerLTV() {
    const container = document.querySelector("[data-customer-ltv]");
    if (!container) return;

    try {
      const response = await fetch("/api/analytics/customer-ltv", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) throw new Error("Failed to load customer LTV");

      const data = await response.json();
      this.renderCustomerLTV(data.data, container);
    } catch (error) {
      console.error("Error:", error);
    }
  }

  renderCustomerLTV(data, container) {
    let html = `
      <div class="analytics-card">
        <h3>Customer Lifetime Value</h3>
        <div class="stats-grid">
          <div class="stat">
            <span class="label">Total Customers</span>
            <span class="value">${data.totalCustomers}</span>
          </div>
          <div class="stat">
            <span class="label">Avg Customer Value</span>
            <span class="value">$${data.averageCustomerValue?.toFixed(2)}</span>
          </div>
          <div class="stat">
            <span class="label">Total Customer Value</span>
            <span class="value">$${data.totalCustomerValue?.toFixed(2)}</span>
          </div>
        </div>

        <h4>Top 10 Customers</h4>
        <table class="analytics-table">
          <thead>
            <tr>
              <th>Email</th>
              <th>Orders</th>
              <th>LTV</th>
              <th>Avg Order</th>
            </tr>
          </thead>
          <tbody>
            ${data.topCustomers
              ?.map(
                (c) => `
              <tr>
                <td>${c.email}</td>
                <td>${c.orders}</td>
                <td>$${c.ltv?.toFixed(2)}</td>
                <td>$${c.averageOrderValue?.toFixed(2)}</td>
              </tr>
            `
              )
              .join("")}
          </tbody>
        </table>
      </div>
    `;

    container.innerHTML = html;
  }

  async loadProductPerformance() {
    const container = document.querySelector("[data-product-performance]");
    if (!container) return;

    try {
      const response = await fetch("/api/analytics/product-performance", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) throw new Error("Failed to load product performance");

      const data = await response.json();
      this.renderProductPerformance(data.data, container);
    } catch (error) {
      console.error("Error:", error);
    }
  }

  renderProductPerformance(data, container) {
    let html = `
      <div class="analytics-card">
        <h3>Top 20 Products</h3>
        <table class="analytics-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Sales</th>
              <th>Revenue</th>
              <th>Avg Rating</th>
              <th>Reviews</th>
              <th>Wishlist</th>
            </tr>
          </thead>
          <tbody>
            ${data.topProducts
              ?.map(
                (p) => `
              <tr>
                <td>${p.name}</td>
                <td>${p.sales}</td>
                <td>$${p.revenue?.toFixed(2)}</td>
                <td>${p.avgRating} ⭐</td>
                <td>${p.reviews}</td>
                <td>${p.wishlistCount}</td>
              </tr>
            `
              )
              .join("")}
          </tbody>
        </table>
      </div>
    `;

    container.innerHTML = html;
  }

  async loadConversionFunnel() {
    const container = document.querySelector("[data-conversion-funnel]");
    if (!container) return;

    try {
      const response = await fetch("/api/analytics/funnel", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) throw new Error("Failed to load funnel");

      const data = await response.json();
      this.renderConversionFunnel(data.data, container);
    } catch (error) {
      console.error("Error:", error);
    }
  }

  renderConversionFunnel(data, container) {
    const maxValue = Math.max(data.visitors, data.addedToCart, data.purchased);

    let html = `
      <div class="analytics-card">
        <h3>Conversion Funnel</h3>
        <div class="funnel-steps">
          <div class="funnel-step" style="width: ${(data.visitors / maxValue) * 100}%">
            <span>Visitors: ${data.visitors}</span>
          </div>
          <div class="funnel-step" style="width: ${(data.addedToCart / maxValue) * 100}%">
            <span>Added to Cart: ${data.addedToCart} (${data.cartConversionRate}%)</span>
          </div>
          <div class="funnel-step" style="width: ${(data.purchased / maxValue) * 100}%">
            <span>Purchased: ${data.purchased} (${data.purchaseConversionRate}%)</span>
          </div>
        </div>
        <p class="funnel-overall">Overall Conversion Rate: <strong>${data.overallConversionRate}%</strong></p>
      </div>
    `;

    container.innerHTML = html;
  }

  async loadEngagementMetrics() {
    const container = document.querySelector("[data-engagement-metrics]");
    if (!container) return;

    try {
      const response = await fetch("/api/analytics/engagement", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) throw new Error("Failed to load engagement");

      const data = await response.json();
      this.renderEngagementMetrics(data.data, container);
    } catch (error) {
      console.error("Error:", error);
    }
  }

  renderEngagementMetrics(data, container) {
    let html = `
      <div class="analytics-card">
        <h3>Engagement Metrics</h3>
        <div class="engagement-grid">
          <div class="engagement-item">
            <h4>Reviews</h4>
            <span class="value">${data.totalReviews}</span>
          </div>
          <div class="engagement-item">
            <h4>Wishlist Items</h4>
            <span class="value">${data.totalWishlistItems}</span>
          </div>
          <div class="engagement-item">
            <h4>Active Users (30d)</h4>
            <span class="value">${data.activeUsers30Days}</span>
          </div>
          <div class="engagement-item">
            <h4>Newsletter Subscribers</h4>
            <span class="value">${data.newsletterSubscribers}</span>
          </div>
        </div>
      </div>
    `;

    container.innerHTML = html;
  }

  async exportAnalytics() {
    try {
      const response = await fetch("/api/analytics/export/csv", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) throw new Error("Export failed");

      const data = await response.json();
      this.downloadCSV(data.data, data.filename);
    } catch (error) {
      console.error("Error:", error);
      alert("Failed to export analytics");
    }
  }

  downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    a.click();
  }
}

// Initialize
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new AnalyticsDashboardController();
  });
} else {
  new AnalyticsDashboardController();
}
