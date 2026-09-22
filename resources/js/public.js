function initializePublicPropertyFilters() {
    document.querySelectorAll('[data-public-property-filters]').forEach((form) => {
        const status = form.querySelector('[data-public-filter-status]');
        const sidebar = form.closest('[data-public-filter-sidebar]');
        const toggle = sidebar?.querySelector('[data-public-filter-toggle]');
        const toggleLabel = toggle?.querySelector('[data-public-filter-toggle-label]');
        const toggleIcon = toggle?.querySelector('[data-public-filter-toggle-icon]');
        const panel = sidebar?.querySelector('[data-public-filter-panel]');
        const results = document.querySelector('[data-public-property-results]');
        const mobileViewport = window.matchMedia('(max-width: 1023px)');
        const storageKey = `pandagestion:property-filters:${window.location.pathname}`;
        let isSubmitting = false;

        const setPanelExpanded = (expanded, moveToResults = false) => {
            if (!toggle || !panel) {
                return;
            }

            toggle.setAttribute('aria-expanded', String(expanded));
            panel.classList.toggle('hidden', !expanded);
            toggleIcon?.classList.toggle('rotate-180', expanded);

            if (toggleLabel) {
                toggleLabel.textContent = expanded ? 'Ocultar filtros' : 'Mostrar filtros';
            }

            if (!expanded && moveToResults && results) {
                const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                window.requestAnimationFrame(() => {
                    results.focus({ preventScroll: true });
                    results.scrollIntoView({
                        behavior: reduceMotion ? 'auto' : 'smooth',
                        block: 'start',
                    });
                });
            }
        };

        const saveFilterState = () => {
            const groups = {};

            form.querySelectorAll('[data-filter-options-scroll]').forEach((group, index) => {
                const key = group.dataset.filterOptionsScroll || String(index);
                groups[key] = group.scrollTop;
            });

            try {
                window.sessionStorage.setItem(storageKey, JSON.stringify({
                    savedAt: Date.now(),
                    panelExpanded: toggle?.getAttribute('aria-expanded') === 'true',
                    pageScrollY: window.scrollY,
                    groups,
                }));
            } catch {
                // The filters still work when session storage is unavailable.
            }
        };

        const restoreFilterState = () => {
            let state = null;

            try {
                const storedState = window.sessionStorage.getItem(storageKey);
                window.sessionStorage.removeItem(storageKey);
                state = storedState ? JSON.parse(storedState) : null;
            } catch {
                return;
            }

            if (!state || Date.now() - state.savedAt > 120000) {
                return;
            }

            if (state.panelExpanded && mobileViewport.matches) {
                setPanelExpanded(true);
            }

            const restorePositions = () => {
                form.querySelectorAll('[data-filter-options-scroll]').forEach((group, index) => {
                    const key = group.dataset.filterOptionsScroll || String(index);
                    const scrollTop = Number(state.groups?.[key]);

                    if (Number.isFinite(scrollTop)) {
                        group.scrollTop = scrollTop;
                    }
                });

                const pageScrollY = Number(state.pageScrollY);

                if (Number.isFinite(pageScrollY)) {
                    window.scrollTo(0, pageScrollY);
                }
            };

            window.requestAnimationFrame(() => {
                restorePositions();
                window.requestAnimationFrame(restorePositions);
            });
        };

        toggle?.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            setPanelExpanded(!expanded, expanded);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape'
                && mobileViewport.matches
                && toggle?.getAttribute('aria-expanded') === 'true'
            ) {
                setPanelExpanded(false, true);
                toggle.focus({ preventScroll: true });
            }
        });

        form.addEventListener('submit', () => {
            saveFilterState();
            isSubmitting = true;
            form.setAttribute('aria-busy', 'true');

            if (status) {
                status.textContent = 'Actualizando resultados…';
            }
        });

        form.addEventListener('change', (event) => {
            if (isSubmitting
                || !(event.target instanceof HTMLElement)
                || !event.target.matches('[data-auto-submit-filter]')
            ) {
                return;
            }

            isSubmitting = true;
            form.requestSubmit();
        });

        restoreFilterState();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePublicPropertyFilters);
} else {
    initializePublicPropertyFilters();
}
