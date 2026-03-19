<?php
// Requires from the calling page: $page_slug, $root
// Optionally: $cms_r (CMS row)
?>
<style>
.cms-editing {
    outline: 2px dashed rgba(200,16,46,.5);
    outline-offset: 4px;
    border-radius: 4px;
    cursor: text;
    min-height: 1em;
    transition: outline-color .15s;
}
.cms-editing:focus {
    outline-color: #C8102E;
    background: rgba(200,16,46,.04);
}
#cms-save-bar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 10000;
    background: #1a1f3a;
    color: #fff;
    padding: 10px 20px;
    display: none;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,.5);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: .9rem;
    letter-spacing: .04em;
}
#cms-save-bar span { flex: 1; opacity: .55; }
#cms-save-btn {
    background: #2e7d32;
    color: #fff;
    border: none;
    padding: 8px 22px;
    border-radius: 6px;
    cursor: pointer;
    font-family: inherit;
    font-weight: 700;
    font-size: .85rem;
    letter-spacing: .1em;
    text-transform: uppercase;
}
#cms-save-btn:disabled { opacity: .6; cursor: default; }
#cms-cancel-btn {
    background: transparent;
    color: rgba(255,255,255,.6);
    border: 1px solid rgba(255,255,255,.2);
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-family: inherit;
    font-size: .85rem;
}
#cms-edit-btn {
    position: fixed;
    bottom: 22px; right: 22px;
    z-index: 9000;
    background: #C8102E;
    color: #fff;
    padding: 10px 20px;
    border-radius: 30px;
    font-family: 'Barlow Condensed', sans-serif;
    font-weight: 700;
    font-size: .85rem;
    letter-spacing: .1em;
    box-shadow: 0 4px 20px rgba(200,16,46,.5);
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    transition: transform .2s;
}
#cms-edit-btn:hover { transform: translateY(-2px); }
.cms-raw-field {
    width: 100%;
    background: #f8f9fc;
    border: 2px solid rgba(200,16,46,.3);
    border-radius: 8px;
    padding: 12px 14px;
    font-family: monospace;
    font-size: .88rem;
    color: #333;
    line-height: 1.6;
    resize: vertical;
    box-sizing: border-box;
}
.cms-raw-field:focus { outline: none; border-color: #C8102E; }
#cms-toast {
    position: fixed;
    bottom: 80px; right: 22px;
    z-index: 10001;
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    font-family: 'Barlow Condensed', sans-serif;
    font-weight: 700;
    font-size: .9rem;
    opacity: 0;
    transform: translateY(10px);
    transition: opacity .3s, transform .3s;
    pointer-events: none;
}
</style>

<div id="cms-save-bar">
    <span>Mode édition — cliquez sur un titre ou un texte pour le modifier</span>
    <button id="cms-cancel-btn">Annuler</button>
    <button id="cms-save-btn">Sauvegarder</button>
</div>

<button id="cms-edit-btn">✏️ Éditer cette page</button>
<div id="cms-toast"></div>

<script>
window._cmsSlug    = <?= json_encode($page_slug ?? '') ?>;
window._cmsSaveUrl = <?= json_encode($root . 'includes/inline_save.php') ?>;
</script>
<script src="<?= htmlspecialchars($root) ?>js/inline_edit.js"></script>
