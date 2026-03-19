(function () {
    if (!window._cmsSlug) return;

    var editBtn   = document.getElementById('cms-edit-btn');
    var saveBar   = document.getElementById('cms-save-bar');
    var saveBtn   = document.getElementById('cms-save-btn');
    var cancelBtn = document.getElementById('cms-cancel-btn');
    var toast     = document.getElementById('cms-toast');

    if (!editBtn) return;

    editBtn.addEventListener('click', enableEdit);
    cancelBtn.addEventListener('click', function () { location.reload(); });
    saveBtn.addEventListener('click', saveEdit);

    function enableEdit() {
        editBtn.style.display = 'none';
        saveBar.style.display = 'flex';

        document.querySelectorAll('.page-main .content-section').forEach(function (sec, idx) {
            sec.setAttribute('data-cms-idx', idx);
        });

        // ── Résultats : textarea brute ─────────────────────────────
        document.querySelectorAll('.content-section[data-cms-type="resultats"]').forEach(function (sec) {
            var overlay  = sec.querySelector('.cms-raw-overlay');
            var rendered = sec.querySelector('.cms-rendered-content');
            var h2       = sec.querySelector('.content-h2');
            if (overlay)  overlay.style.display = 'block';
            if (rendered) rendered.style.display = 'none';
            if (h2) { h2.contentEditable = 'true'; h2.classList.add('cms-editing'); }
        });

        // ── Stats : valeur + label contenteditable ─────────────────
        document.querySelectorAll('.content-section[data-cms-type="stats"]').forEach(function (sec) {
            var h2 = sec.querySelector('.content-h2');
            if (h2) { h2.contentEditable = 'true'; h2.classList.add('cms-editing'); }
            sec.querySelectorAll('[data-cms-field="valeur"], [data-cms-field="label"]').forEach(function (el) {
                el.contentEditable = 'true';
                el.classList.add('cms-editing');
            });
        });

        // ── Tout le reste : h2 + tous les enfants directs hors h2 ──
        // (couvre : content-text, info-box, div inline, content-grid-X, etc.)
        document.querySelectorAll('.content-section').forEach(function (sec) {
            var type = sec.getAttribute('data-cms-type') || '';

            // Sauter les types déjà gérés ci-dessus
            if (type === 'resultats' || type === 'stats') return;

            Array.from(sec.children).forEach(function (child) {
                if (child.contentEditable === 'true') return; // déjà fait
                if (child.classList.contains('cms-raw-overlay')) return; // réservé
                child.contentEditable = 'true';
                child.classList.add('cms-editing');
            });
        });
    }

    function saveEdit() {
        saveBtn.textContent = 'Sauvegarde\u2026';
        saveBtn.disabled = true;

        var updates = [];

        // ── Texte / fallback / grilles : h2 + premier contenu éditable
        document.querySelectorAll('.content-section[data-cms-idx]').forEach(function (sec) {
            var idx  = parseInt(sec.getAttribute('data-cms-idx'));
            var type = sec.getAttribute('data-cms-type') || '';
            if (type === 'resultats' || type === 'stats') return;

            var h2 = sec.querySelector('.content-h2[contenteditable]');
            if (h2) updates.push({ idx: idx, field: 'titre', value: h2.innerText.trim() });

            // Premier enfant direct éditable qui n'est pas h2
            var contentEl = Array.from(sec.children).find(function (el) {
                return !el.classList.contains('content-h2') && el.contentEditable === 'true';
            });
            if (contentEl) {
                updates.push({ idx: idx, field: 'contenu', value: contentEl.innerHTML.trim() });
            }
        });

        // ── Résultats : textarea ───────────────────────────────────
        document.querySelectorAll('.content-section[data-cms-type="resultats"]').forEach(function (sec) {
            var idx      = parseInt(sec.getAttribute('data-cms-idx'));
            var textarea = sec.querySelector('.cms-raw-field');
            var h2       = sec.querySelector('.content-h2[contenteditable]');
            if (h2)       updates.push({ idx: idx, field: 'titre',   value: h2.innerText.trim() });
            if (textarea) updates.push({ idx: idx, field: 'contenu', value: textarea.value.trim() });
        });

        // ── Stats : reconstruire items ─────────────────────────────
        document.querySelectorAll('.content-section[data-cms-type="stats"]').forEach(function (sec) {
            var idx = parseInt(sec.getAttribute('data-cms-idx'));
            var h2  = sec.querySelector('.content-h2[contenteditable]');
            if (h2) updates.push({ idx: idx, field: 'titre', value: h2.innerText.trim() });

            var items = [];
            sec.querySelectorAll('[data-cms-item-idx]').forEach(function (itemEl) {
                var i    = parseInt(itemEl.getAttribute('data-cms-item-idx'));
                var vEl  = itemEl.querySelector('[data-cms-field="valeur"]');
                var lEl  = itemEl.querySelector('[data-cms-field="label"]');
                var iEl  = itemEl.querySelector('[data-cms-field="icon"]');
                items[i] = {
                    valeur: vEl ? vEl.innerText.trim() : '',
                    label:  lEl ? lEl.innerText.trim() : '',
                    icon:   iEl ? iEl.innerText.trim() : ''
                };
            });
            if (items.length) updates.push({ idx: idx, field: 'items', value: JSON.stringify(items) });
        });

        var body = 'slug='     + encodeURIComponent(window._cmsSlug)
                 + '&updates=' + encodeURIComponent(JSON.stringify(updates));

        fetch(window._cmsSaveUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                showToast('Sauvegard\u00e9 !', true);
                setTimeout(function () { location.reload(); }, 900);
            } else {
                showToast('Erreur\u00a0: ' + (data.error || 'inconnue'), false);
                saveBtn.textContent = 'Sauvegarder';
                saveBtn.disabled = false;
            }
        })
        .catch(function () {
            showToast('Erreur de connexion', false);
            saveBtn.textContent = 'Sauvegarder';
            saveBtn.disabled = false;
        });
    }

    function showToast(msg, ok) {
        toast.textContent = msg;
        toast.style.background = ok ? '#2e7d32' : '#c62828';
        toast.style.opacity    = '1';
        toast.style.transform  = 'translateY(0)';
        setTimeout(function () {
            toast.style.opacity   = '0';
            toast.style.transform = 'translateY(10px)';
        }, 2200);
    }
})();
