/**
 * DocScolaire SaaS — Moteur d'Interactivité & UI Micro-animations
 * Palette de commande Cmd+K, Toasts dynamiques, Copie dans le presse-papier & Filtres en direct
 */

document.addEventListener('DOMContentLoaded', () => {
    initCmdK();
    initCopyButtons();
    initPasswordToggles();
    initLiveTableFilters();
});

/* ── 1. Palette de Commande Universelle Cmd+K / Ctrl+K ── */
function initCmdK() {
    const backdrop = document.getElementById('cmdk-backdrop');
    if (!backdrop) return;

    const input = document.getElementById('cmdk-input');
    const items = backdrop.querySelectorAll('.cmdk-item');

    function openCmdK() {
        backdrop.classList.add('active');
        if (input) {
            input.value = '';
            input.focus();
            filterItems('');
        }
    }

    function closeCmdK() {
        backdrop.classList.remove('active');
    }

    function filterItems(query) {
        const q = query.toLowerCase().trim();
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (!q || text.includes(q)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Raccourci clavier global Cmd+K / Ctrl+K
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (backdrop.classList.contains('active')) {
                closeCmdK();
            } else {
                openCmdK();
            }
        } else if (e.key === 'Escape' && backdrop.classList.contains('active')) {
            closeCmdK();
        }
    });

    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) closeCmdK();
    });

    if (input) {
        input.addEventListener('input', (e) => filterItems(e.target.value));
    }

    // Triggers dans l'interface
    document.querySelectorAll('[data-cmdk-trigger]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openCmdK();
        });
    });
}

/* ── 2. Boutons de Copie Rapide avec Animation Check ── */
function initCopyButtons() {
    document.querySelectorAll('[data-copy]').forEach(el => {
        el.style.cursor = 'pointer';
        el.addEventListener('click', async () => {
            const textToCopy = el.getAttribute('data-copy') || el.innerText;
            try {
                await navigator.clipboard.writeText(textToCopy.trim());
                const originalHtml = el.innerHTML;
                el.innerHTML = `<span style="color:#10b981;font-weight:700;">✓ Copié !</span>`;
                setTimeout(() => {
                    el.innerHTML = originalHtml;
                }, 1600);
                showModernToast(`Copié dans le presse-papier : ${textToCopy}`, 'info');
            } catch (err) {
                console.error('Erreur copie:', err);
            }
        });
    });
}

/* ── 3. Basculeur de Mot de Passe Sécurisé ── */
function initPasswordToggles() {
    document.querySelectorAll('[data-toggle-password]').forEach(toggleBtn => {
        toggleBtn.addEventListener('click', () => {
            const targetId = toggleBtn.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                toggleBtn.setAttribute('title', 'Masquer le mot de passe');
                toggleBtn.innerHTML = `
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                `;
            } else {
                input.type = 'password';
                toggleBtn.setAttribute('title', 'Afficher le mot de passe');
                toggleBtn.innerHTML = `
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                `;
            }
        });
    });
}

/* ── 4. Filtre en direct sur les Tableaux ── */
function initLiveTableFilters() {
    document.querySelectorAll('[data-table-filter]').forEach(searchInput => {
        const tableId = searchInput.getAttribute('data-table-filter');
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (!query || text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
}

/* ── 5. Système de Toasts Flottants ── */
function showModernToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `modern-toast toast-${type}`;
    
    let icon = 'ℹ️';
    if (type === 'success') icon = '✅';
    if (type === 'error') icon = '⚠️';

    toast.innerHTML = `
        <span style="font-size:16px;">${icon}</span>
        <span style="flex:1;">${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px) scale(0.95)';
        setTimeout(() => toast.remove(), 250);
    }, 3800);
}

// Exposer globalement
window.showModernToast = showModernToast;
