/**
 * @property {HTMLElement} pagination
 * @property {HTMLElement} content
 * @property {HTMLElement} sorting
 * @property {HTMLFormElement} form
 */
export default class Filter {

    /**
     * @param {HTMLElement|null} element
     */
    constructor (element) {

        if (element === null) {
            return
        }
        // console.log('aaaaggggggggga')
        this.pagination = element.querySelector('.js-filter-pagination')
        this.content = element.querySelector('.js-filter-content')
        this.contentShow = element.querySelector('.js-filter-content-show')
        this.sorting = element.querySelector('.js-filter-sorting')
        this.form = element.querySelector('.js-filter-form')
        this.abortController = null
        this.requestId = 0
        // this.size = element.querySelector('.js-size')
        // this.color = element.querySelector('.js-color')
        this.bindEvents()
    }

    /**
     * Ajoute les comportements aux différents éléments
     */
    bindEvents () {
        const aClickListener = e => {
            const target = e.target instanceof Element ? e.target : e.target.parentElement
            if (target === null) {
                return
            }
            const link = target.closest('a')
            if (link !== null) {
                e.preventDefault()
                this.loadUrl(link.getAttribute('href'))
            }
        }
        this.sorting.addEventListener('click', aClickListener)
        this.pagination.addEventListener('click', aClickListener)
        // this.color.addEventListener('click', aClickListener)

        this.form.querySelectorAll('input').forEach(input => {
            input.addEventListener('change', this.loadForm.bind(this))
        })
    }

    async loadForm () {
        const data = new FormData(this.form)
        const url = new URL(this.form.getAttribute('action') || window.location.href)
        const params = new URLSearchParams()
        const currentUrlParams = new URLSearchParams(window.location.search)

        // Keep topbar state when sidebar filters change
        ;['maxItemPerPage', 'sort', 'direction'].forEach((key) => {
            const values = currentUrlParams.getAll(key)
            values.forEach((value) => params.append(key, value))
        })

        data.forEach((value, key) => {
            params.append(key, value)
        })
        return this.loadUrl(url.pathname + '?' + params.toString())
    }

    async loadUrl (url) {
        // const ajaxUrl = url + '&ajax=1'
        // this.showLoader()
        if (this.abortController) {
            this.abortController.abort()
        }
        this.abortController = new AbortController()
        const currentRequestId = ++this.requestId

        const params = new URLSearchParams(url.split('?')[1] || '')
        params.set('ajax', 1)
        try {
            const response = await fetch(url.split('?')[0] + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: this.abortController.signal
            })

            if (currentRequestId !== this.requestId) {
                return
            }

            if (response.status >= 200 && response.status < 300) {
                const data = await response.json()
                // this.flipContent(data.content, append)
                this.content.innerHTML = data.content
                this.contentShow.innerHTML = data.contentShow
                this.sorting.innerHTML = data.sorting
                this.pagination.innerHTML = data.pagination
                // this.size.innerHTML = data.size

                this.updatePrices(data)
                params.delete('ajax')
                history.replaceState({}, '', url.split('?')[0] + '?' + params.toString())
            } else {
                console.error(response)
            }
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return
            }
            console.error(error)
        }
        // this.hideLoader()
    }

    updatePrices({min, max}){
        const slider = document.getElementById('slider')
        if (slider === null){
            return
        }
        slider.noUiSlider.updateOptions({
            range: {
                min: [min],
                max: [max]
            }
        })
    }


    // showLoader () {
    //     // Code à écrire
    // }
    //
    // hideLoader () {
    //     // Code à écrire
    // }
}
