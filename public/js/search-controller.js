// Search Autocomplete Controller
// Provides live search suggestions without page reload

export default class extends window.Stimulus.Controller {
    static targets = ['input', 'suggestions', 'suggestionsContainer'];
    static values = { baseUrl: '/api/products/search', debounceDelay: 300 };

    connect() {
        this.debounceTimer = null;
    }

    async search(event) {
        const query = this.inputTarget.value.trim();

        // Clear previous timer
        clearTimeout(this.debounceTimer);

        // Hide suggestions if query too short
        if (query.length < 2) {
            this.hideSuggestions();
            return;
        }

        // Debounce API calls
        this.debounceTimer = setTimeout(() => this.fetchSuggestions(query), this.debounceDelayValue);
    }

    async fetchSuggestions(query) {
        try {
            const response = await fetch(`${this.baseUrlValue}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();
            this.displaySuggestions(data.results);
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }

    displaySuggestions(results) {
        if (results.length === 0) {
            this.suggestionsTarget.innerHTML = '<div class="dropdown-item text-muted">No results found</div>';
            this.showSuggestions();
            return;
        }

        this.suggestionsTarget.innerHTML = results.map(product => `
            <a href="/product/${product.id}" class="dropdown-item d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <strong>${this.escapeHtml(product.name)}</strong>
                    <br>
                    <small class="text-muted">$${product.price.toFixed(2)}</small>
                </div>
                ${product.image ? `<img src="/uploads/product/${product.image}" alt="${product.name}" style="width: 40px; height: 40px; object-fit: cover; margin-left: 10px;">` : ''}
            </a>
        `).join('');

        this.showSuggestions();
    }

    showSuggestions() {
        this.suggestionsContainerTarget.style.display = 'block';
    }

    hideSuggestions() {
        this.suggestionsContainerTarget.style.display = 'none';
    }

    selectSuggestion(event) {
        event.preventDefault();
        // Navigation happens automatically via href
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Hide suggestions when clicking outside
    disconnect() {
        document.removeEventListener('click', this.handleClickOutside);
    }
}
