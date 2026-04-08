// Subscription Manager - Handle subscriptions and loyalty

class SubscriptionManager {
  constructor() {
    this.loadPlans();
    this.loadCurrentSubscription();
    this.setupLoyalty();
  }

  async loadPlans() {
    const container = document.querySelector("[data-subscription-plans]");
    if (!container) return;

    try {
      const response = await fetch("/api/subscriptions/plans", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      const data = await response.json();
      this.displayPlans(data.plans, container);
    } catch (error) {
      console.error("Error loading plans:", error);
    }
  }

  displayPlans(plans, container) {
    let html = '<div class="plans-grid">';

    plans.forEach((plan) => {
      html += `
        <div class="plan-card">
          <h3>${plan.name}</h3>
          <p>${plan.description}</p>
          <div class="plan-price">$${plan.price}/${plan.billingCycle}</div>
          ${plan.setupFee ? `<p class="setup-fee">Setup: $${plan.setupFee}</p>` : ""}
          ${plan.trialDays ? `<p class="trial-badge">Free ${plan.trialDays} day trial</p>` : ""}
          <ul class="features">
            ${plan.features.map((f) => `<li>✓ ${f}</li>`).join("")}
          </ul>
          <button onclick="subscriptionManager.subscribe(${plan.id})" class="btn-subscribe">
            Subscribe Now
          </button>
        </div>
      `;
    });

    html += "</div>";
    container.innerHTML = html;
  }

  async subscribe(planId) {
    try {
      const response = await fetch("/api/subscriptions/subscribe", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ planId }),
      });

      const data = await response.json();
      if (data.success) {
        alert("Subscription successful!");
        this.loadCurrentSubscription();
      }
    } catch (error) {
      alert("Subscription failed");
    }
  }

  async loadCurrentSubscription() {
    const container = document.querySelector("[data-current-subscription]");
    if (!container) return;

    try {
      const response = await fetch("/api/subscriptions/current", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      const data = await response.json();
      if (data.subscription) {
        this.displayCurrentSubscription(data.subscription, container);
      }
    } catch (error) {
      // Not authenticated
    }
  }

  displayCurrentSubscription(sub, container) {
    let html = `
      <div class="current-subscription">
        <h3>${sub.plan}</h3>
        <p>Status: <strong>${sub.status}</strong></p>
        <p>Started: ${new Date(sub.startDate).toLocaleDateString()}</p>
        <p>Next Billing: ${new Date(sub.nextBillingDate).toLocaleDateString()}</p>
        <p>Renewals: ${sub.renewalCount}</p>
        <button onclick="subscriptionManager.cancel()" class="btn-danger">Cancel Subscription</button>
      </div>
    `;
    container.innerHTML = html;
  }

  async cancel() {
    if (!confirm("Are you sure you want to cancel your subscription?")) return;

    try {
      const response = await fetch("/api/subscriptions/cancel", {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      const data = await response.json();
      if (data.success) {
        alert("Subscription cancelled");
        this.loadCurrentSubscription();
      }
    } catch (error) {
      alert("Cancellation failed");
    }
  }

  setupLoyalty() {
    this.loadLoyaltyAccount();
    const redeemForm = document.querySelector("[data-redeem-form]");
    if (redeemForm) {
      redeemForm.addEventListener("submit", (e) => this.handleRedeem(e));
    }
  }

  async loadLoyaltyAccount() {
    const container = document.querySelector("[data-loyalty-account]");
    if (!container) return;

    try {
      const response = await fetch("/api/loyalty/account", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      const data = await response.json();
      this.displayLoyaltyAccount(data, container);
    } catch (error) {
      // Not authenticated
    }
  }

  displayLoyaltyAccount(account, container) {
    const progress = (account.tierPoints / account.nextTierThreshold) * 100;

    let html = `
      <div class="loyalty-card">
        <h3>Loyalty Account</h3>
        <div class="points-display">
          <div class="current-points">
            <span class="label">Current Points</span>
            <span class="value">${account.currentPoints}</span>
          </div>
          <div class="tier-badge">${account.tier.toUpperCase()}</div>
        </div>
        <p>Earn ${(account.multiplier * 100).toFixed(0)}% more with ${account.tier} tier</p>
        <div class="tier-progress">
          <div class="progress-bar" style="width: ${progress}%"></div>
          <p>${account.pointsToNextTier} points to next tier</p>
        </div>
        <div class="loyalty-stats">
          <p>Total Earned: ${account.totalEarned}</p>
          <p>Total Redeemed: ${account.totalRedeemed}</p>
        </div>
      </div>
    `;

    container.innerHTML = html;
  }

  async handleRedeem(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const points = form.querySelector("[name='points']").value;

    try {
      const response = await fetch("/api/loyalty/redeem", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ points: parseInt(points) }),
      });

      const data = await response.json();
      if (data.success) {
        alert(`Redeemed! ${data.discount.toFixed(2)} discount applied`);
        this.loadLoyaltyAccount();
        form.reset();
      } else {
        alert(data.error);
      }
    } catch (error) {
      alert("Redemption failed");
    }
  }
}

// Initialize
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    window.subscriptionManager = new SubscriptionManager();
  });
} else {
  window.subscriptionManager = new SubscriptionManager();
}
