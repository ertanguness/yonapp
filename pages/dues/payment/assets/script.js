(function() {
        const listEl = document.getElementById('ydPersonnelList');
        if (!listEl) return;

        const sortLabel = document.getElementById('ydSortLabel');
        const searchEl = document.getElementById('ydSearch');
    const searchClearBtn = document.getElementById('ydSearchClear');
        const sortButtons = document.querySelectorAll('[data-yd-sort]');
        const filterButtons = document.querySelectorAll('[data-yd-filter]');

        const updateSearchClearVisibility = () => {
            if (!searchClearBtn || !searchEl) return;
            const hasValue = (searchEl.value || '').toString().trim().length > 0;
            searchClearBtn.classList.toggle('is-visible', hasValue);
        };

        // Default: daire koduna göre (A1D2 < A1D10 gibi natural)
        let currentSort = 'unit_asc';
        let currentFilter = 'all';

        const labels = {
            amount_asc: 'Artan',
            amount_desc: 'Azalan',
            unit_asc: 'Daire A→Z',
            unit_desc: 'Daire Z→A',
            name_asc: "A→Z",
            name_desc: "Z→A",
        };

        const getItems = () => Array.from(listEl.querySelectorAll('.yd-item'));

        const normalizeText = (s) => (s || '').toString().toLocaleLowerCase('tr-TR').trim();

        const parseNet = (el) => {
            const raw = (el.getAttribute('data-yd-net') || '').toString();
            const n = Number(raw);
            return Number.isFinite(n) ? n : 0;
        };

        const naturalCompare = (a, b) => {
            // 1) Prefer Intl.Collator numeric sort when available
            try {
                if (typeof Intl !== 'undefined' && Intl.Collator) {
                    const collator = new Intl.Collator('tr-TR', {
                        numeric: true,
                        sensitivity: 'base'
                    });
                    return collator.compare(a, b);
                }
            } catch (e) {
                // ignore and fall back
            }
            // 2) Fallback: basic chunked numeric compare
            const ax = (a || '').toString().toLocaleLowerCase('tr-TR').match(/(\d+|\D+)/g) || [];
            const bx = (b || '').toString().toLocaleLowerCase('tr-TR').match(/(\d+|\D+)/g) || [];
            const len = Math.min(ax.length, bx.length);
            for (let i = 0; i < len; i++) {
                const ac = ax[i];
                const bc = bx[i];
                if (ac === bc) continue;
                const an = Number(ac);
                const bn = Number(bc);
                const aIsNum = Number.isFinite(an) && /^\d+$/.test(ac);
                const bIsNum = Number.isFinite(bn) && /^\d+$/.test(bc);
                if (aIsNum && bIsNum) return an - bn;
                return ac.localeCompare(bc, 'tr-TR', { sensitivity: 'base' });
            }
            return ax.length - bx.length;
        };

        const matchesSearch = (el, q) => {
            if (!q) return true;
            const name = normalizeText(el.getAttribute('data-yd-name'));
            const unit = normalizeText(el.getAttribute('data-yd-unit'));
            const phone = normalizeText(el.getAttribute('data-yd-phone'));
            return name.includes(q) || unit.includes(q) || phone.includes(q);
        };

        const matchesFilter = (el, filter) => {
            if (!filter || filter === 'all') return true;
            const net = parseNet(el);
            if (filter === 'has_debt') return net < 0;
            if (filter === 'paid') return net >= 0;
            return true;
        };

        const compareItems = (a, b) => {
            if (currentSort === 'amount_asc') return parseNet(a) - parseNet(b);
            if (currentSort === 'amount_desc') return parseNet(b) - parseNet(a);
            if (currentSort === 'unit_asc') {
                return naturalCompare(a.getAttribute('data-yd-unit'), b.getAttribute('data-yd-unit'));
            }
            if (currentSort === 'unit_desc') {
                return naturalCompare(b.getAttribute('data-yd-unit'), a.getAttribute('data-yd-unit'));
            }
            if (currentSort === 'name_asc') {
                return normalizeText(a.getAttribute('data-yd-name')).localeCompare(
                    normalizeText(b.getAttribute('data-yd-name')),
                    'tr-TR',
                    { sensitivity: 'base' }
                );
            }
            if (currentSort === 'name_desc') {
                return normalizeText(b.getAttribute('data-yd-name')).localeCompare(
                    normalizeText(a.getAttribute('data-yd-name')),
                    'tr-TR',
                    { sensitivity: 'base' }
                );
            }
            return 0;
        };

        const apply = () => {
            const q = normalizeText(searchEl ? searchEl.value : '');

            // Search clear button visibility
            updateSearchClearVisibility();

            const items = getItems();
            for (const el of items) {
                const visible = matchesSearch(el, q) && matchesFilter(el, currentFilter);
                el.style.display = visible ? '' : 'none';
            }

            const visibleItems = items.filter((el) => el.style.display !== 'none');
            visibleItems.sort(compareItems);
            for (const el of visibleItems) listEl.appendChild(el);

            if (sortLabel) sortLabel.textContent = labels[currentSort] || 'Sırala';
        };

        // Default label
        if (sortLabel) sortLabel.textContent = labels[currentSort] || 'Sırala';

        // Sort controls
        sortButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                currentSort = btn.getAttribute('data-yd-sort') || currentSort;
                apply();
            });
        });

        // Filter chips: keep existing styles but make them act like toggles
        filterButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                currentFilter = btn.getAttribute('data-yd-filter') || 'all';
                // active visual
                filterButtons.forEach((b) => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                apply();
            });
        });

        // Search input
        if (searchEl) {
            searchEl.addEventListener('input', apply);
            searchEl.addEventListener('change', () => {
                updateSearchClearVisibility();
                apply();
            });
            searchEl.addEventListener('focus', updateSearchClearVisibility);
            // ESC ile temizle
            searchEl.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    searchEl.value = '';
                    apply();
                    searchEl.blur();
                }
            });
        }

        // Clear button
        if (searchClearBtn && searchEl) {
            searchClearBtn.addEventListener('click', () => {
                searchEl.value = '';
                apply();
                searchEl.focus();
            });
        }

        // Initial active filter chip (default: all)
        const defaultFilterBtn = document.querySelector('[data-yd-filter="' + currentFilter + '"]');
        if (defaultFilterBtn) {
            filterButtons.forEach((b) => b.classList.remove('is-active'));
            defaultFilterBtn.classList.add('is-active');
        }

        // Initial apply
        apply();

        // Bazı tarayıcılarda (özellikle Chrome) autofill, DOMContentLoaded sonrasında input'a yazabilir.
        // Temizle butonunun doğru görünmesi için 1-2 kez daha kontrol et.
        try { Promise.resolve().then(apply); } catch (e) {}
        setTimeout(apply, 0);
        setTimeout(apply, 150);

        // bfcache (geri/ileri) veya sayfa yeniden gösterimi
        window.addEventListener('pageshow', () => {
            updateSearchClearVisibility();
            apply();
        });

        // Chrome autofill value change detection (best-effort):
        // https://developers.google.com/web/updates/2018/03/cssom
        document.addEventListener('animationstart', (e) => {
            if (e && e.target === searchEl) {
                updateSearchClearVisibility();
            }
        }, true);
    })();