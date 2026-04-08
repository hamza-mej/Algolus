/**
 * Price Slider Controller
 * Implements noUiSlider for price range filtering
 */
class PriceSliderController {
    constructor() {
        this.init();
    }

    init() {
        const slider = document.getElementById('slider');
        if (!slider) {
            console.warn('Price slider element not found');
            return;
        }

        const minInput = document.querySelector('input[name*="min"]');
        const maxInput = document.querySelector('input[name*="max"]');
        
        if (!minInput || !maxInput) {
            console.warn('Price input fields not found');
            return;
        }

        const min = parseInt(slider.dataset.min) || 0;
        const max = parseInt(slider.dataset.max) || 1000;
        
        console.log('Initializing price slider:', { min, max });

        // Check if noUiSlider is available
        if (typeof noUiSlider === 'undefined') {
            console.error('noUiSlider library not loaded');
            return;
        }

        // Initialize noUiSlider
        try {
            noUiSlider.create(slider, {
                start: [min, max],
                connect: true,
                range: {
                    'min': min,
                    'max': max
                },
                step: 1,
                format: {
                    to: (value) => Math.round(value),
                    from: (str) => parseInt(str)
                }
            });
            console.log('Price slider initialized successfully');
        } catch (e) {
            console.error('Error initializing slider:', e);
            return;
        }

        // Update input fields when slider changes
        slider.noUiSlider.on('update', (values) => {
            minInput.value = values[0];
            maxInput.value = values[1];
            this.updatePriceDisplay(values[0], values[1]);
        });

        // Update slider when input fields change
        minInput.addEventListener('change', () => {
            const minVal = parseInt(minInput.value) || min;
            const maxVal = parseInt(maxInput.value) || max;
            slider.noUiSlider.set([minVal, maxVal]);
        });

        maxInput.addEventListener('change', () => {
            const minVal = parseInt(minInput.value) || min;
            const maxVal = parseInt(maxInput.value) || max;
            slider.noUiSlider.set([minVal, maxVal]);
        });
    }

    updatePriceDisplay(min, max) {
        // Update display span if it exists
        const displaySpan = document.querySelector('.price-filter > span:first-of-type');
        if (displaySpan) {
            displaySpan.textContent = `Range: $${Math.round(min)}.00 - $${Math.round(max)}.00`;
        }
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing price slider controller');
    new PriceSliderController();
});

