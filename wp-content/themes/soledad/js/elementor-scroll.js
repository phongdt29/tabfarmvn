(function () {
    const STORAGE_KEY = 'penci_pagination_scroll';

    function getHeaderOffset() {
        const header = document.querySelector('.penci-header-wrap');
        return header ? header.offsetHeight : 0;
    }

    // Capture pagination click
    document.addEventListener('click', function (e) {
        const link = e.target.closest('.penci-pagination a.page-numbers');
        if (!link) return;

        const elementorEl = link.closest('.elementor-element');
        if (!elementorEl) return;

        const elIdClass = [...elementorEl.classList].find(cls =>
            cls.indexOf('elementor-element-') === 0
        );

        const rect = elementorEl.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        sessionStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({
                id: elIdClass || null,
                top: rect.top + scrollTop
            })
        );
    });

    // Restore scroll position
    window.addEventListener('load', function () {
        const raw = sessionStorage.getItem(STORAGE_KEY);
        if (!raw) return;

        try {
            const data = JSON.parse(raw);
            let targetTop = data.top;

            if (data.id) {
                const targetEl = document.querySelector('.' + data.id);
                if (targetEl) {
                    const rect = targetEl.getBoundingClientRect();
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    targetTop = rect.top + scrollTop;
                }
            }

            // Apply header spacing
            targetTop -= getHeaderOffset();

            window.scrollTo({
                top: Math.max(targetTop, 0),
                behavior: 'smooth'
            });
        } catch (e) {}

        sessionStorage.removeItem(STORAGE_KEY);
    });
})();