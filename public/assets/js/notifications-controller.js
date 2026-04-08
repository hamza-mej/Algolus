// Notifications Controller - Real-time notifications

class NotificationsController {
  constructor() {
    this.pollInterval = null;
    this.setupUI();
    this.startPolling();
  }

  setupUI() {
    // Notification bell icon
    const bell = document.querySelector("[data-notifications-bell]");
    if (bell) {
      bell.addEventListener("click", () => this.toggleNotifications());
    }

    // Clear all button
    const clearBtn = document.querySelector("[data-action='clear-notifications']");
    if (clearBtn) {
      clearBtn.addEventListener("click", () => this.clearAllNotifications());
    }

    // Mark as read buttons
    document.querySelectorAll("[data-action='mark-read']").forEach((btn) => {
      btn.addEventListener("click", (e) => this.markAsRead(e));
    });
  }

  startPolling() {
    // Poll every 30 seconds
    this.pollInterval = setInterval(() => {
      this.loadNotifications();
    }, 30000);

    // Initial load
    this.loadNotifications();
  }

  async loadNotifications() {
    try {
      const response = await fetch("/api/notifications", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) return;

      const data = await response.json();
      this.updateUI(data);
    } catch (error) {
      console.error("Error loading notifications:", error);
    }
  }

  updateUI(data) {
    // Update badge count
    const badge = document.querySelector("[data-unread-count]");
    if (badge) {
      badge.textContent = data.unreadCount;
      badge.style.display = data.unreadCount > 0 ? "block" : "none";
    }

    // Update notification list
    const list = document.querySelector("[data-notifications-list]");
    if (!list) return;

    if (data.notifications.length === 0) {
      list.innerHTML = "<p>No notifications</p>";
      return;
    }

    let html = "";
    data.notifications.forEach((notif) => {
      const bgColor = {
        info: "#e3f2fd",
        success: "#e8f5e9",
        warning: "#fff3e0",
        error: "#ffebee",
      }[notif.severity] || "#f5f5f5";

      html += `
        <div class="notification-item ${notif.isRead ? "read" : "unread"}" style="background: ${bgColor}">
          <div class="notification-content">
            <strong>${this.escapeHtml(notif.title)}</strong>
            <p>${this.escapeHtml(notif.message)}</p>
            <small>${notif.createdAt}</small>
          </div>
          <div class="notification-actions">
            ${
              notif.actionUrl
                ? `<a href="${notif.actionUrl}" class="btn-small">View</a>`
                : ""
            }
            ${
              !notif.isRead
                ? `<button data-action="mark-read" data-id="${notif.id}" class="btn-small">Mark Read</button>`
                : ""
            }
          </div>
        </div>
      `;
    });

    list.innerHTML = html;
    this.setupUI(); // Re-attach listeners
  }

  async markAsRead(event) {
    event.preventDefault();
    const notifId = event.currentTarget.dataset.id;

    try {
      const response = await fetch(`/api/notifications/${notifId}/read`, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (response.ok) {
        this.loadNotifications();
      }
    } catch (error) {
      console.error("Error marking as read:", error);
    }
  }

  async clearAllNotifications() {
    if (!confirm("Delete all notifications?")) return;

    try {
      const response = await fetch("/api/notifications/clear-all", {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (response.ok) {
        this.loadNotifications();
      }
    } catch (error) {
      console.error("Error clearing notifications:", error);
    }
  }

  toggleNotifications() {
    const panel = document.querySelector("[data-notifications-panel]");
    if (panel) {
      panel.classList.toggle("show");
    }
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  destroy() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }
  }
}

// Initialize
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    window.notificationsController = new NotificationsController();
  });
} else {
  window.notificationsController = new NotificationsController();
}
