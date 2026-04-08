// Cart AJAX Controller
// Handles all shopping cart operations via AJAX (add, remove, update, clear)

export default class extends window.Stimulus.Controller {
    static targets = ['cart', 'cartCount', 'cartTotal', 'addButton', 'removeButton'];
    static values = { baseUrl: '/api/cart' };

    connect() {
        this.loadCart();
    }

    async addToCart(event) {
        event.preventDefault();
        const productId = event.currentTarget.dataset.productId;
        
        if (!productId) return;

        this.showLoading(event.currentTarget);

        try {
            const response = await fetch(`${this.baseUrlValue}/add`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ productId: parseInt(productId) }),
            });

            const data = await response.json();

            if (data.success) {
                this.updateCartUI(data.cart);
                this.showNotification('Product added to cart', 'success');
            } else {
                this.showNotification(data.error || 'Failed to add product', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred', 'error');
        } finally {
            this.hideLoading(event.currentTarget);
        }
    }

    async removeFromCart(event, productId) {
        event.preventDefault();
        
        if (!productId) return;

        this.showLoading(event.currentTarget);

        try {
            const response = await fetch(`${this.baseUrlValue}/remove`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ productId: parseInt(productId) }),
            });

            const data = await response.json();

            if (data.success) {
                this.updateCartUI(data.cart);
                this.showNotification('Product removed from cart', 'success');
            } else {
                this.showNotification(data.error || 'Failed to remove product', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred', 'error');
        } finally {
            this.hideLoading(event.currentTarget);
        }
    }

    async clearCart(event) {
        event.preventDefault();

        if (!confirm('Are you sure you want to clear your cart?')) return;

        this.showLoading(event.currentTarget);

        try {
            const response = await fetch(`${this.baseUrlValue}/clear`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();

            if (data.success) {
                this.updateCartUI(data.cart);
                this.showNotification('Cart cleared', 'success');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred', 'error');
        } finally {
            this.hideLoading(event.currentTarget);
        }
    }

    async loadCart() {
        try {
            const response = await fetch(`${this.baseUrlValue}/data`);
            const data = await response.json();
            this.updateCartUI(data);
        } catch (error) {
            console.error('Error loading cart:', error);
        }
    }

    updateCartUI(cart) {
        if (this.hasCartCountTarget) {
            this.cartCountTarget.textContent = cart.count;
            this.cartCountTarget.style.display = cart.count > 0 ? 'inline' : 'none';
        }

        if (this.hasCartTotalTarget) {
            this.cartTotalTarget.textContent = `$${cart.total.toFixed(2)}`;
        }

        if (this.hasCartTarget) {
            this.updateCartContent(cart);
        }

        // Dispatch custom event for other controllers
        this.element.dispatchEvent(new CustomEvent('cart-updated', { detail: cart }));
    }

    updateCartContent(cart) {
        const html = cart.items.length === 0 
            ? '<p class="text-center text-muted">Your cart is empty</p>'
            : `<ul class="list-group">
                ${cart.items.map(item => `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small>$${item.price.toFixed(2)} x ${item.quantity}</small>
                        </div>
                        <div>
                            <strong>$${item.subtotal.toFixed(2)}</strong>
                            <button class="btn btn-sm btn-danger ms-2" onclick="this.getRootNode().host.cart.removeFromCart(event, ${item.id})">Remove</button>
                        </div>
                    </li>
                `).join('')}
                </ul>`;
        this.cartTarget.innerHTML = html;
    }

    showLoading(element) {
        element.disabled = true;
        const originalHTML = element.innerHTML;
        element.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        element.dataset.originalHTML = originalHTML;
    }

    hideLoading(element) {
        element.disabled = false;
        if (element.dataset.originalHTML) {
            element.innerHTML = element.dataset.originalHTML;
        }
    }

    showNotification(message, type = 'info') {
        // You can integrate with a toast/notification library here
        // For now, using browser alert - can be improved
        const bgClass = type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info';
        const notification = document.createElement('div');
        notification.className = `alert alert-${bgClass} alert-dismissible fade show`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.insertAdjacentElement('afterbegin', notification);
        setTimeout(() => notification.remove(), 3000);
    }
}
