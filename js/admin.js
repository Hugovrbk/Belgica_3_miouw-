/* ═══════════════════════════════════════════════════════════════
   RFC LIÈGE — admin.js
   Animations interface d'administration
   ═══════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

    /* ── 1. SIDEBAR TOGGLE (mobile) ─────────────────────────── */
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openSb  = () => { sidebar.classList.add('open'); if(overlay)overlay.classList.add('active'); document.body.style.overflow='hidden'; toggle.textContent='✕'; };
    const closeSb = () => { sidebar.classList.remove('open'); if(overlay)overlay.classList.remove('active'); document.body.style.overflow=''; toggle.textContent='☰'; };
    if (toggle) toggle.addEventListener('click', () => sidebar.classList.contains('open') ? closeSb() : openSb());
    if (overlay) overlay.addEventListener('click', closeSb);

    /* ── 2. SMOOTH SCROLL SIDEBAR LINKS ─────────────────────── */
    document.querySelectorAll('.sidebar-link[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const t = document.querySelector(this.getAttribute('href'));
            if (t) window.scrollTo({ top: t.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
            document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            if (window.innerWidth <= 960) closeSb();
        });
    });

    /* ── 3. SECTION BLOCKS REVEAL ────────────────────────────── */
    const blocks = document.querySelectorAll('.section-block, .stats-row');
    blocks.forEach((b, i) => { b.style.opacity='0'; b.style.transform='translateY(22px)'; b.style.transition=`opacity 0.5s ease ${i*0.06}s, transform 0.5s ease ${i*0.06}s`; });
    const bObs = new IntersectionObserver((entries) => {
        entries.forEach(e => { if(e.isIntersecting){e.target.style.opacity='1';e.target.style.transform='';bObs.unobserve(e.target);} });
    }, { threshold: 0.04 });
    blocks.forEach(b => bObs.observe(b));

    /* ── 4. STAT CARDS COUNT UP ──────────────────────────────── */
    function animCount(el, target, dur=1400) {
        const s = performance.now();
        const tick = now => { const p=Math.min((now-s)/dur,1); const e=1-Math.pow(1-p,3); el.textContent=Math.round(e*target); if(p<1)requestAnimationFrame(tick); };
        requestAnimationFrame(tick);
    }
    const statVals = document.querySelectorAll('.stat-value[data-count]');
    const svObs = new IntersectionObserver(entries => {
        entries.forEach(e => { if(e.isIntersecting){animCount(e.target,parseInt(e.target.dataset.count));svObs.unobserve(e.target);} });
    }, { threshold: 0.5 });
    statVals.forEach(el => svObs.observe(el));

    /* ── 5. FLASH MSG AUTO-DISMISS ───────────────────────────── */
    const flash = document.querySelector('.flash-msg');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s, transform 0.5s, padding 0.4s, margin 0.4s';
            flash.style.opacity='0'; flash.style.transform='translateY(-8px)';
            flash.style.padding='0'; flash.style.margin='0'; flash.style.overflow='hidden';
            setTimeout(() => flash.remove(), 500);
        }, 4500);
    }

    /* ── 6. RESULT ITEMS STAGGER ─────────────────────────────── */
    const resItems = document.querySelectorAll('.result-item');
    if (resItems.length) {
        resItems.forEach(r => { r.style.opacity='0'; r.style.transform='translateX(-12px)'; r.style.transition='opacity 0.4s ease, transform 0.4s ease, background 0.25s ease'; });
        const rObs = new IntersectionObserver(entries => {
            if(entries[0].isIntersecting){resItems.forEach((r,i)=>setTimeout(()=>{r.style.opacity='1';r.style.transform='';},i*35));rObs.disconnect();}
        }, { threshold: 0.04 });
        rObs.observe(resItems[0]);
    }

    /* ── 7. TABLE STAGGER ────────────────────────────────────── */
    const rows = document.querySelectorAll('.classement-table tbody tr');
    if (rows.length) {
        rows.forEach(r => { r.style.opacity='0'; r.style.transform='translateX(-14px)'; r.style.transition='opacity 0.4s ease, transform 0.4s ease, background 0.2s'; });
        const trObs = new IntersectionObserver(entries => {
            if(entries[0].isIntersecting){rows.forEach((r,i)=>setTimeout(()=>{r.style.opacity='1';r.style.transform='';},i*55));trObs.disconnect();}
        }, { threshold: 0.04 });
        const t = document.querySelector('.classement-table');
        if (t) trObs.observe(t);
    }

    /* ── 8. MATCH ITEMS HOVER ────────────────────────────────── */
    document.querySelectorAll('.match-item').forEach(item => {
        item.addEventListener('mouseenter', () => item.style.transform='translateX(5px)');
        item.addEventListener('mouseleave', () => item.style.transform='');
    });

    /* ── 9. TEAM CHIPS HOVER ─────────────────────────────────── */
    document.querySelectorAll('.team-chip').forEach(c => {
        c.addEventListener('mouseenter', () => c.style.transform='translateY(-2px) scale(1.04)');
        c.addEventListener('mouseleave', () => c.style.transform='');
    });

    /* ── 10. FORM INPUT FOCUS SCALE ──────────────────────────── */
    document.querySelectorAll('.admin-form .form-group input, .admin-form .form-group select').forEach(inp => {
        inp.addEventListener('focus', () => inp.closest('.form-group').style.transform='scale(1.015)');
        inp.addEventListener('blur',  () => inp.closest('.form-group').style.transform='');
    });

    /* ── 11. SCROLL PROGRESS ─────────────────────────────────── */
    const bar = document.getElementById('scroll-progress');
    if (bar) {
        window.addEventListener('scroll', () => {
            const p = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            bar.style.width = p + '%';
        }, { passive: true });
    }

});
