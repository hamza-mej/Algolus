// Support & A/B Testing Controller

class SupportTicketManager {
  constructor() {
    this.setupForms();
    this.loadTickets();
  }

  setupForms() {
    const form = document.querySelector("[data-ticket-form]");
    if (form) {
      form.addEventListener("submit", (e) => this.handleCreateTicket(e));
    }
  }

  async handleCreateTicket(event) {
    event.preventDefault();
    const form = event.currentTarget;

    const data = {
      subject: form.querySelector("[name='subject']").value,
      message: form.querySelector("[name='message']").value,
      category: form.querySelector("[name='category']").value,
      priority: form.querySelector("[name='priority']").value,
    };

    try {
      const response = await fetch("/api/support/tickets", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(data),
      });

      const result = await response.json();
      if (result.success) {
        alert(`Ticket #${result.ticketId} created!`);
        form.reset();
        this.loadTickets();
      }
    } catch (error) {
      alert("Failed to create ticket");
    }
  }

  async loadTickets() {
    const container = document.querySelector("[data-tickets-list]");
    if (!container) return;

    try {
      const response = await fetch("/api/support/tickets", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      const data = await response.json();
      this.displayTickets(data.tickets, container);
    } catch (error) {
      // Not authenticated
    }
  }

  displayTickets(tickets, container) {
    if (tickets.length === 0) {
      container.innerHTML = "<p>No support tickets</p>";
      return;
    }

    let html = '<div class="tickets-list">';

    tickets.forEach((ticket) => {
      const statusColor = {
        open: "orange",
        in_progress: "blue",
        resolved: "green",
      }[ticket.status] || "gray";

      html += `
        <div class="ticket-item">
          <div class="ticket-header">
            <span class="ticket-id">#${ticket.id}</span>
            <span class="priority priority-${ticket.priority}">${ticket.priority}</span>
            <span class="status" style="color: ${statusColor}">${ticket.status}</span>
          </div>
          <h4>${this.escapeHtml(ticket.subject)}</h4>
          <p class="category">${ticket.category}</p>
          <p class="date">${new Date(ticket.createdAt).toLocaleDateString()}</p>
          ${ticket.resolvedAt ? `<p class="resolved">Resolved: ${new Date(ticket.resolvedAt).toLocaleDateString()}</p>` : ""}
        </div>
      `;
    });

    html += "</div>";
    container.innerHTML = html;
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
}

class ABTestManager {
  constructor() {
    this.loadTests();
  }

  async loadTests() {
    const container = document.querySelector("[data-ab-tests]");
    if (!container) return;

    try {
      const response = await fetch("/api/admin/ab-tests", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      const data = await response.json();
      this.displayTests(data.tests, container);
    } catch (error) {
      console.error("Error loading tests:", error);
    }
  }

  displayTests(tests, container) {
    let html = '<div class="ab-tests-container">';

    tests.forEach((test) => {
      html += `
        <div class="test-card">
          <h3>${test.name}</h3>
          <p class="type">${test.type}</p>
          <span class="status status-${test.status}">${test.status}</span>
          <div class="metrics">
            <div class="metric">
              <label>Variant A</label>
              <div class="bar">
                <div class="fill" style="width: ${test.variantARate}%"></div>
              </div>
              <p>${test.variantARate}%</p>
            </div>
            <div class="metric">
              <label>Variant B</label>
              <div class="bar">
                <div class="fill" style="width: ${test.variantBRate}%"></div>
              </div>
              <p>${test.variantBRate}%</p>
            </div>
          </div>
          <p class="improvement">Improvement: <strong>${test.improvement > 0 ? "+" : ""}${test.improvement}%</strong></p>
          ${test.winner ? `<p class="winner">Winner: Variant ${test.winner}</p>` : ""}
        </div>
      `;
    });

    html += "</div>";
    container.innerHTML = html;
  }
}

// Initialize
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    window.supportManager = new SupportTicketManager();
    window.abTestManager = new ABTestManager();
  });
} else {
  window.supportManager = new SupportTicketManager();
  window.abTestManager = new ABTestManager();
}
