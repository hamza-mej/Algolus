// Admin Dashboard Controller - Analytics visualization

class AdminDashboardController {
  constructor() {
    this.charts = {};
    this.initDashboard();
  }

  initDashboard() {
    const dashboardContainer = document.querySelector("[data-admin-dashboard]");
    if (!dashboardContainer) return;

    this.loadDashboard();

    // Setup date range filter
    const filterForm = document.querySelector("[data-date-filter]");
    if (filterForm) {
      filterForm.addEventListener("submit", (e) => {
        e.preventDefault();
        this.loadSalesReport();
      });
    }
  }

  async loadDashboard() {
    try {
      this.showLoadingState();

      const response = await fetch("/api/admin/dashboard", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) throw new Error("Failed to load dashboard");

      const data = await response.json();
      this.renderDashboard(data.data);
    } catch (error) {
      console.error("Error loading dashboard:", error);
      document.querySelector("[data-admin-dashboard]").innerHTML =
        '<div class="error">Failed to load dashboard</div>';
    }
  }

  async loadSalesReport() {
    const fromInput = document.querySelector('[name="date_from"]');
    const toInput = document.querySelector('[name="date_to"]');

    const params = new URLSearchParams();
    if (fromInput?.value) params.append("from", fromInput.value);
    if (toInput?.value) params.append("to", toInput.value);

    try {
      const response = await fetch(`/api/admin/sales?${params}`, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) throw new Error("Failed to load sales report");

      const data = await response.json();
      this.renderSalesReport(data.data);
    } catch (error) {
      console.error("Error loading sales report:", error);
    }
  }

  renderDashboard(data) {
    const container = document.querySelector("[data-admin-dashboard]");

    let html = `
      <div class="dashboard-grid">
        <div class="stat-card">
          <h3>Total Revenue</h3>
          <p class="stat-value">$${data.totalRevenue?.toFixed(2) || "0.00"}</p>
          <span class="stat-period">All time</span>
        </div>
        
        <div class="stat-card">
          <h3>Total Orders</h3>
          <p class="stat-value">${data.totalOrders || 0}</p>
          <span class="stat-period">All time</span>
        </div>
        
        <div class="stat-card">
          <h3>Total Users</h3>
          <p class="stat-value">${data.totalUsers || 0}</p>
          <span class="stat-period">All time</span>
        </div>
        
        <div class="stat-card">
          <h3>Active Products</h3>
          <p class="stat-value">${data.totalProducts || 0}</p>
          <span class="stat-period">In stock</span>
        </div>
      </div>

      <div class="dashboard-row">
        <div class="chart-container" style="flex: 1">
          <h4>Orders by Status</h4>
          <div id="orders-by-status"></div>
        </div>
        
        <div class="chart-container" style="flex: 1">
          <h4>Payment Methods</h4>
          <div id="payment-methods"></div>
        </div>
      </div>

      <div class="dashboard-row">
        <div class="chart-container">
          <h4>Recent Orders</h4>
          <table class="stats-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              ${
                data.recentOrders
                  ?.slice(0, 5)
                  .map(
                    (order) => `
                <tr>
                  <td>#${order.id}</td>
                  <td>$${order.amount?.toFixed(2)}</td>
                  <td><span class="badge badge-${order.status}">${order.status}</span></td>
                  <td>${order.createdAt}</td>
                </tr>
              `
                  )
                  .join("")
                : ""
              }
            </tbody>
          </table>
        </div>
      </div>

      <div class="dashboard-row">
        <div class="chart-container">
          <h4>Top Products</h4>
          <table class="stats-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Sales</th>
                <th>Revenue</th>
              </tr>
            </thead>
            <tbody>
              ${
                data.topProducts
                  ?.slice(0, 5)
                  .map(
                    (product) => `
                <tr>
                  <td>${this.escapeHtml(product.name)}</td>
                  <td>${product.salesCount}</td>
                  <td>$${product.totalRevenue?.toFixed(2)}</td>
                </tr>
              `
                  )
                  .join("")
                : ""
              }
            </tbody>
          </table>
        </div>
      </div>
    `;

    container.innerHTML = html;
  }

  renderSalesReport(data) {
    const container = document.querySelector("[data-sales-report]");
    if (!container) return;

    let html = `
      <div class="report-header">
        <h3>Sales Report</h3>
        <p>Period: ${data.periodStart} to ${data.periodEnd}</p>
      </div>
      
      <div class="report-stats">
        <div class="report-stat">
          <span>Total Orders:</span>
          <strong>${data.totalOrders}</strong>
        </div>
        <div class="report-stat">
          <span>Total Revenue:</span>
          <strong>$${data.totalRevenue?.toFixed(2)}</strong>
        </div>
        <div class="report-stat">
          <span>Average Order Value:</span>
          <strong>$${data.averageOrderValue?.toFixed(2)}</strong>
        </div>
      </div>

      <div class="report-table">
        <table class="stats-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Orders</th>
              <th>Revenue</th>
              <th>Avg Value</th>
            </tr>
          </thead>
          <tbody>
            ${
              data.byDay
                ?.map(
                  (day) => `
              <tr>
                <td>${day.date}</td>
                <td>${day.orders}</td>
                <td>$${day.revenue?.toFixed(2)}</td>
                <td>$${(day.revenue / day.orders)?.toFixed(2)}</td>
              </tr>
            `
                )
                .join("")
              : ""
            }
          </tbody>
        </table>
      </div>
    `;

    container.innerHTML = html;
  }

  showLoadingState() {
    document.querySelector("[data-admin-dashboard]").innerHTML =
      '<div class="loading"><span class="spinner"></span> Loading dashboard...</div>';
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
    window.adminDashboard = new AdminDashboardController();
  });
} else {
  window.adminDashboard = new AdminDashboardController();
}
