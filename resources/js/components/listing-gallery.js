export default function listingGallery(imageUrls = [], imageAlt = '') {
    const urls = Array.isArray(imageUrls) ? imageUrls.filter(Boolean) : [];

    return {
        activeSlide: 0,
        imageAlt: String(imageAlt || 'Autoturism'),
        imageUrls: urls,
        loadedSlides: urls.length ? [0] : [],
        loadingSlides: [],
        pendingSlide: null,
        preloadObserver: null,
        touchStartX: null,

        get slides() {
            return this.imageUrls.length;
        },

        get currentImage() {
            return this.imageUrls[this.activeSlide] || '';
        },

        get currentAlt() {
            return `${this.imageAlt} - poza ${this.activeSlide + 1}`;
        },

        init() {
            this.observeForPreload();
        },

        destroy() {
            this.preloadObserver?.disconnect();
        },

        isLoaded(index) {
            return this.loadedSlides.includes(index);
        },

        nextIndex(index = this.activeSlide) {
            return this.slides < 2 ? 0 : (index === this.slides - 1 ? 0 : index + 1);
        },

        prevIndex(index = this.activeSlide) {
            return this.slides < 2 ? 0 : (index === 0 ? this.slides - 1 : index - 1);
        },

        next() {
            this.goTo(this.nextIndex());
        },

        prev() {
            this.goTo(this.prevIndex());
        },

        goTo(index) {
            if (this.slides < 2 || index === this.activeSlide) return;

            if (this.isLoaded(index)) {
                this.setActive(index);
                return;
            }

            this.preload(index, true);
        },

        setActive(index) {
            this.activeSlide = index;
            this.preload(this.nextIndex(index));
        },

        preloadAdjacent() {
            if (this.slides > 1) {
                this.preload(this.nextIndex());
            }
        },

        preload(index, activate = false) {
            if (this.slides < 1 || !this.imageUrls[index]) return;

            if (this.isLoaded(index)) {
                if (activate) this.setActive(index);
                return;
            }

            if (activate) this.pendingSlide = index;
            if (this.loadingSlides.includes(index)) return;

            this.loadingSlides.push(index);

            const image = new Image();
            image.decoding = 'async';
            image.onload = () => {
                if (!this.loadedSlides.includes(index)) {
                    this.loadedSlides.push(index);
                }

                this.loadingSlides = this.loadingSlides.filter((slide) => slide !== index);

                if (this.pendingSlide === index) {
                    this.pendingSlide = null;
                    this.setActive(index);
                }
            };
            image.onerror = () => {
                this.loadingSlides = this.loadingSlides.filter((slide) => slide !== index);
                if (this.pendingSlide === index) this.pendingSlide = null;
            };
            image.src = this.imageUrls[index];
        },

        observeForPreload() {
            if (this.slides < 2) return;

            if (!('IntersectionObserver' in window)) {
                this.preloadAdjacent();
                return;
            }

            this.preloadObserver = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    this.preloadAdjacent();
                    this.preloadObserver?.disconnect();
                }
            }, { rootMargin: '450px 0px' });
            this.preloadObserver.observe(this.$el);
        },

        onTouchStart(event) {
            this.preloadAdjacent();
            this.touchStartX = event.changedTouches?.[0]?.clientX ?? null;
        },

        onTouchEnd(event) {
            const touchEndX = event.changedTouches?.[0]?.clientX;
            if (this.touchStartX === null || touchEndX === undefined || this.slides < 2) return;

            const diff = this.touchStartX - touchEndX;
            if (Math.abs(diff) > 40) {
                diff > 0 ? this.next() : this.prev();
            }

            this.touchStartX = null;
        },
    };
}
