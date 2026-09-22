import './bootstrap';
import Swal from 'sweetalert2';

function initializeAddressSearch() {
    document.querySelectorAll('[data-address-search]').forEach((container) => {
        const input = container.querySelector('[data-address-search-input]');
        const button = container.querySelector('[data-address-search-button]');
        const buttonLabel = container.querySelector('[data-address-search-button-label]');
        const status = container.querySelector('[data-address-search-status]');
        const results = container.querySelector('[data-address-search-results]');
        const searchUrl = container.dataset.searchUrl;
        const latitudeField = document.getElementById('latitud');
        const longitudeField = document.getElementById('longitud');
        const map = document.querySelector('[data-address-map]');
        const mapFrame = map?.querySelector('[data-address-map-frame]');
        const mapLink = map?.querySelector('[data-address-map-link]');

        if (!input || !button || !buttonLabel || !status || !results || !searchUrl) {
            return;
        }

        let activeRequest = null;
        let mapUpdateTimer = null;

        const hideMap = () => {
            if (!map || !mapFrame || !mapLink) {
                return;
            }

            map.classList.add('hidden');
            mapFrame.removeAttribute('src');
            mapLink.setAttribute('href', '#');
        };

        const updateMap = () => {
            if (!map || !mapFrame || !mapLink || !latitudeField || !longitudeField) {
                return;
            }

            const latitude = Number.parseFloat(latitudeField.value);
            const longitude = Number.parseFloat(longitudeField.value);

            if (!Number.isFinite(latitude)
                || !Number.isFinite(longitude)
                || latitude < -90
                || latitude > 90
                || longitude < -180
                || longitude > 180
            ) {
                hideMap();

                return;
            }

            const latitudeDelta = 0.004;
            const longitudeDelta = 0.008;
            const boundingBox = [
                Math.max(-180, longitude - longitudeDelta),
                Math.max(-90, latitude - latitudeDelta),
                Math.min(180, longitude + longitudeDelta),
                Math.min(90, latitude + latitudeDelta),
            ].join(',');
            const embedParameters = new URLSearchParams({
                bbox: boundingBox,
                layer: 'mapnik',
                marker: `${latitude},${longitude}`,
            });
            const mapParameters = new URLSearchParams({
                mlat: String(latitude),
                mlon: String(longitude),
            });

            mapFrame.src = `https://www.openstreetmap.org/export/embed.html?${embedParameters}`;
            mapLink.href = `https://www.openstreetmap.org/?${mapParameters}#map=17/${latitude}/${longitude}`;
            map.classList.remove('hidden');
        };

        const setStatus = (message, variant = 'info') => {
            const colorClasses = {
                error: 'text-red-700',
                info: 'text-slate-600',
                success: 'text-emerald-700',
            };

            status.textContent = message;
            status.classList.remove('hidden', 'text-red-700', 'text-slate-600', 'text-emerald-700');
            status.classList.add(colorClasses[variant] || colorClasses.info);
        };

        const hideResults = () => {
            results.replaceChildren();
            results.classList.add('hidden');
        };

        const selectResult = (result) => {
            const fields = {
                calle: 'calle',
                numero: 'numero',
                domicilio: 'domicilio',
                codigo_postal: 'codigo_postal',
                provincia: 'provincia',
                partido: 'partido',
                localidad: 'localidad',
                barrio: 'barrio',
                latitud: 'latitud',
                longitud: 'longitud',
            };

            Object.entries(fields).forEach(([resultKey, fieldId]) => {
                const value = result[resultKey];
                const field = document.getElementById(fieldId);

                if (field && typeof value === 'string' && value.trim() !== '') {
                    field.value = value;
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            input.value = result.label;
            hideResults();
            updateMap();
            setStatus('Dirección seleccionada. Revisá los datos antes de guardar.', 'success');
            (document.getElementById('domicilio') || document.getElementById('calle'))?.focus();
        };

        const renderResults = (items) => {
            hideResults();

            if (items.length === 0) {
                setStatus('No encontramos resultados. Probá agregando la localidad o la provincia.', 'info');

                return;
            }

            items.forEach((result, index) => {
                if (!result || typeof result.label !== 'string') {
                    return;
                }

                const resultButton = document.createElement('button');
                const label = document.createElement('span');

                resultButton.type = 'button';
                resultButton.className = 'block w-full border-b border-slate-200 px-4 py-3 text-left text-sm leading-5 text-slate-700 transition hover:bg-emerald-50 focus:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-600 last:border-b-0';
                resultButton.dataset.addressResult = String(index);
                label.className = 'block font-medium';
                label.textContent = result.label;
                resultButton.append(label);
                resultButton.addEventListener('click', () => selectResult(result));
                results.append(resultButton);
            });

            if (results.children.length === 0) {
                setStatus('No encontramos resultados válidos para esa búsqueda.', 'info');

                return;
            }

            results.classList.remove('hidden');
            setStatus(`Encontramos ${results.children.length} ${results.children.length === 1 ? 'resultado' : 'resultados'}. Elegí una dirección.`, 'info');
            results.querySelector('button')?.focus();
        };

        const search = async () => {
            const query = input.value.trim().replace(/\s+/g, ' ');

            if (query.length < 5) {
                hideResults();
                setStatus('Ingresá al menos 5 caracteres para buscar.', 'error');
                input.focus();

                return;
            }

            activeRequest?.abort();
            const request = new AbortController();
            activeRequest = request;
            button.disabled = true;
            buttonLabel.textContent = 'Buscando…';
            hideResults();
            setStatus('Buscando direcciones…', 'info');

            try {
                const url = new URL(searchUrl, window.location.origin);
                url.searchParams.set('query', query);

                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                    },
                    signal: request.signal,
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'No se pudo realizar la búsqueda.');
                }

                renderResults(Array.isArray(payload.data) ? payload.data : []);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    hideResults();
                    setStatus(error.message || 'No se pudo realizar la búsqueda.', 'error');
                }
            } finally {
                if (activeRequest === request) {
                    activeRequest = null;
                    button.disabled = false;
                    buttonLabel.textContent = 'Buscar';
                }
            }
        };

        button.addEventListener('click', search);

        [latitudeField, longitudeField].forEach((field) => {
            field?.addEventListener('input', () => {
                window.clearTimeout(mapUpdateTimer);
                mapUpdateTimer = window.setTimeout(updateMap, 400);
            });
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                search();
            }

            if (event.key === 'Escape') {
                hideResults();
            }
        });

        updateMap();
    });
}

function initializeImageSorters() {
    document.querySelectorAll('[data-image-sorter]').forEach((container) => {
        const section = container.closest('section');
        const form = document.getElementById(container.dataset.imageOrderForm);
        const submitButton = section?.querySelector('[data-image-order-submit]');
        const status = section?.querySelector('[data-image-sort-status]');

        if (!form || !submitButton || !status) {
            return;
        }

        let draggedItem = null;
        let hasChanges = false;

        const items = () => Array.from(container.querySelectorAll('[data-image-sort-item]'));

        const refreshControls = () => {
            const currentItems = items();

            currentItems.forEach((item, index) => {
                const position = index + 1;
                const positionLabel = item.querySelector('[data-image-position]');
                const previousButton = item.querySelector('[data-image-move="previous"]');
                const nextButton = item.querySelector('[data-image-move="next"]');
                const dragHandle = item.querySelector('[data-image-drag-handle]');

                if (positionLabel) {
                    positionLabel.textContent = `Posición ${position}`;
                }

                if (previousButton) {
                    previousButton.disabled = index === 0;
                    previousButton.setAttribute('aria-label', `Mover la imagen de la posición ${position} una posición antes`);
                }

                if (nextButton) {
                    nextButton.disabled = index === currentItems.length - 1;
                    nextButton.setAttribute('aria-label', `Mover la imagen de la posición ${position} una posición después`);
                }

                dragHandle?.setAttribute('aria-label', `Arrastrar la imagen de la posición ${position} para reordenarla`);
            });

            submitButton.disabled = !hasChanges;
            status.textContent = hasChanges ? 'Hay cambios de orden sin guardar.' : 'El orden no tiene cambios.';
            status.classList.toggle('text-amber-700', hasChanges);
            status.classList.toggle('text-slate-500', !hasChanges);
        };

        const markAsChanged = () => {
            hasChanges = true;
            refreshControls();
        };

        container.querySelectorAll('[data-image-move]').forEach((button) => {
            button.addEventListener('click', () => {
                const item = button.closest('[data-image-sort-item]');

                if (!item) {
                    return;
                }

                if (button.dataset.imageMove === 'previous') {
                    const previousItem = item.previousElementSibling;

                    if (previousItem) {
                        container.insertBefore(item, previousItem);
                        markAsChanged();
                    }
                } else {
                    const nextItem = item.nextElementSibling;

                    if (nextItem) {
                        nextItem.after(item);
                        markAsChanged();
                    }
                }
            });
        });

        container.querySelectorAll('[data-image-drag-handle]').forEach((handle) => {
            handle.addEventListener('dragstart', (event) => {
                draggedItem = handle.closest('[data-image-sort-item]');

                if (!draggedItem || !event.dataTransfer) {
                    event.preventDefault();

                    return;
                }

                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', draggedItem.dataset.imageId || '');
                event.dataTransfer.setDragImage(draggedItem, 24, 24);
                draggedItem.classList.add('opacity-60', 'ring-2', 'ring-emerald-500');
            });

            handle.addEventListener('dragend', () => {
                draggedItem?.classList.remove('opacity-60', 'ring-2', 'ring-emerald-500');
                draggedItem = null;
            });
        });

        container.addEventListener('dragover', (event) => {
            if (!draggedItem) {
                return;
            }

            const target = event.target instanceof Element
                ? event.target.closest('[data-image-sort-item]')
                : null;

            if (!target || target === draggedItem || !container.contains(target)) {
                return;
            }

            event.preventDefault();

            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'move';
            }

            const previousIndex = items().indexOf(draggedItem);
            const bounds = target.getBoundingClientRect();
            const verticalOffset = event.clientY - (bounds.top + bounds.height / 2);
            const horizontalOffset = event.clientX - (bounds.left + bounds.width / 2);
            const insertAfter = Math.abs(verticalOffset) > bounds.height * 0.25
                ? verticalOffset > 0
                : horizontalOffset > 0;

            container.insertBefore(draggedItem, insertAfter ? target.nextElementSibling : target);

            if (items().indexOf(draggedItem) !== previousIndex) {
                markAsChanged();
            }
        });

        container.addEventListener('drop', (event) => {
            if (draggedItem) {
                event.preventDefault();
            }
        });

        refreshControls();
    });
}

function initializeAdminShell() {
    const sidebar = document.querySelector('#admin-sidebar');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    const toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
    const closeButtons = document.querySelectorAll('[data-sidebar-close]');

    if (sidebar && overlay) {
        const setSidebarOpen = (isOpen) => {
            sidebar.classList.toggle('-translate-x-full', !isOpen);
            overlay.classList.toggle('hidden', !isOpen);
            document.body.classList.toggle('overflow-hidden', isOpen);

            toggleButtons.forEach((button) => {
                button.setAttribute('aria-expanded', String(isOpen));
            });

            if (isOpen) {
                sidebar.querySelector('[data-sidebar-close]')?.focus();
            }
        };

        toggleButtons.forEach((button) => {
            button.addEventListener('click', () => setSidebarOpen(true));
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', () => setSidebarOpen(false));
        });

        overlay.addEventListener('click', () => setSidebarOpen(false));

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !overlay.classList.contains('hidden')) {
                setSidebarOpen(false);
                toggleButtons.item(0)?.focus();
            }
        });
    }

    document.querySelectorAll('[data-alert-dismiss]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('[data-dismissible-alert]')?.remove();
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            if (form.dataset.confirmed === 'true') {
                return;
            }

            const conditionalField = form.dataset.confirmWhen
                ? form.querySelector(form.dataset.confirmWhen)
                : null;

            if (form.dataset.confirmWhen && !conditionalField?.checked) {
                return;
            }

            event.preventDefault();

            const confirmVariant = form.dataset.confirmVariant === 'success' ? 'success' : 'danger';
            const confirmButtonClasses = confirmVariant === 'success'
                ? 'inline-flex min-w-28 justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2'
                : 'inline-flex min-w-28 justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2';

            const result = await Swal.fire({
                title: form.dataset.confirmTitle || '¿Confirmar acción?',
                text: form.dataset.confirmText || 'Esta acción requiere confirmación.',
                icon: form.dataset.confirmIcon || 'warning',
                showCancelButton: true,
                confirmButtonText: form.dataset.confirmButton || 'Confirmar',
                cancelButtonText: form.dataset.cancelButton || 'Cancelar',
                reverseButtons: true,
                focusCancel: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-2xl',
                    actions: 'gap-3',
                    confirmButton: confirmButtonClasses,
                    cancelButton: 'inline-flex min-w-28 justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2',
                },
            });

            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';

                if (event.submitter) {
                    form.requestSubmit(event.submitter);
                } else {
                    form.requestSubmit();
                }
            }
        });
    });

    initializeAddressSearch();
    initializeImageSorters();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAdminShell);
} else {
    initializeAdminShell();
}
