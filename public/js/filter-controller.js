// Product Filter AJAX Controller
// Handles live product filtering without page reload

export default class extends window.Stimulus.Controller {
    static targets = ['productList', 'pagination', 'filterForm', 'loading'];
    static values = { baseUrl: '/api/products/filter' };

    connect() {
        this.page = 1;
        this.filters = {};
    }

    async applyFilters(event) {
        event?.preventDefault();
        
        this.page = 1;
        this.collectFilters();
        await this.loadProducts();
    }

    async goToPage(page) {
        this.page = page;
        this.collectFilters();
        await this.loadProducts();
    }

    collectFilters() {
        this.filters = {
            q: document.querySelector('[name="search"]')?.value || '',
            min: document.querySelector('[name="min"]')?.value || '',
            max: document.querySelector('[name="max"]')?.value || '',
            categories: Array.from(document.querySelectorAll('[name="categories"]:checked'))
                .map(el => el.value)
                .join(','),
            colors: Array.from(document.querySelectorAll('[name="colors"]:checked'))
                .map(el => el.value)
                .join(','),
            sizes: Array.from(document.querySelectorAll('[name="sizes"]:checked'))
                .map(el => el.value)
                .join(','),
            onSale: document.querySelector('[name="onSale"]')?.checked || false,
        };
    }

    async loadProducts() {
        this.showLoading();

        try {
            const params = new URLSearchParams({
                ...this.filters,
                page: this.page,
            });

            const response = await fetch(`${this.baseUrlValue}?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();

            if (data.success) {
                this.updateProductList(data.products);
                this.updatePagination(data.pagination);
                this.element.dispatchEvent(new CustomEvent('products-filtered', { detail: data }));
            }
        } catch (error) {
            console.error('Error loading products:', error);
            this.showError('Failed to load products');
        } finally {
            this.hideLoading();
        }
    }

    updateProductList(products) {
        if (!this.hasProductListTarget) return;

        if (products.length === 0) {
            this.productListTarget.innerHTML = '<p class="text-center text-muted">No products found</p>';
            return;
        }

        this.productListTarget.innerHTML = products.map(product => `
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="/uploads/product/${product.image}" class="card-img-top" alt="${product.name}" style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text text-muted">${product.description}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0">$${product.price.toFixed(2)}</span>
                            ${product.onSale ? '<span class="badge bg-danger">On Sale</span>' : ''}
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <button class="btn btn-primary w-100" data-action="click->cart#addToCart" data-product-id="${product.id}">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    updatePagination(pagination) {
        if (!this.hasPaginationTarget) return;

        const pages = [];
        const maxPages = 5;
        const startPage = Math.max(1, pagination.page - Math.floor(maxPages / 2));
        const endPage = Math.min(pagination.pages, startPage + maxPages - 1);

        if (startPage > 1) {
            pages.push(`<li class="page-item"><a class="page-link" href="#" data-action="click->filter#goToPage" data-page="1">1</a></li>`);
            if (startPage > 2) pages.push(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }

        for (let i = startPage; i <= endPage; i++) {
            pages.push(`
                <li class="page-item ${i === pagination.page ? 'active' : ''}">
                    <a class="page-link" href="#" data-action="click->filter#goToPage" data-page="${i}">${i}</a>
                </li>
            `);
        }

        if (endPage < pagination.pages) {
            if (endPage < pagination.pages - 1) pages.push(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            pages.push(`<li class="page-item"><a class="page-link" href="#" data-action="click->filter#goToPage" data-page="${pagination.pages}">${pagination.pages}</a></li>`);
        }

        this.paginationTarget.innerHTML = `<nav aria-label="Page navigation"><ul class="pagination">${pages.join('')}</ul></nav>`;
    }

    showLoading() {
        if (this.hasLoadingTarget) {
            this.loadingTarget.style.display = 'block';
        }
    }

    hideLoading() {
        if (this.hasLoadingTarget) {
            this.loadingTarget.style.display = 'none';
        }
    }

    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        this.element.insertAdjacentElement('afterbegin', alert);
        setTimeout(() => alert.remove(), 3000);
    }
}
