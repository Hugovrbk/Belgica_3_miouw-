<?php
/**
 * cms_render.php — Rendu universel des blocs CMS
 * Types : text · resultats · player_grid · staff_grid · image_gallery · stats · schedule_block
 */
function cms_render($contenu) {
    if (empty($contenu)) return '';

    $sections = json_decode($contenu, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($sections)) {
        return '<section class="content-section"><div class="content-text">' . $contenu . '</div></section>';
    }

    $em   = isset($_SESSION['user_admin']) && $_SESSION['user_admin'] == 1; // edit mode
    $html = '';

    foreach ($sections as $idx => $s) {
        $titre = trim($s['titre']   ?? '');
        $body  = trim($s['contenu'] ?? '');
        $type  = $s['type'] ?? 'text';
        $items = $s['items'] ?? [];

        if (!$titre && !$body && empty($items)) continue;

        $sec_attrs = $em ? ' data-cms-idx="' . $idx . '" data-cms-type="' . htmlspecialchars($type) . '"' : '';
        $html .= '<section class="content-section"' . $sec_attrs . '>';

        if ($titre) {
            $html .= '<h2 class="content-h2">' . htmlspecialchars($titre) . '</h2>';
        }

        switch ($type) {

            // ── Résultats / Timeline ────────────────────────────────
            case 'resultats':
                if ($em && $body) {
                    $rows = max(3, substr_count($body, "\n") + 2);
                    $html .= '<div class="cms-raw-overlay" style="display:none;">'
                           . '<textarea class="cms-raw-field" data-field="contenu" rows="' . $rows . '">'
                           . htmlspecialchars($body)
                           . '</textarea>'
                           . '<small style="color:#888;display:block;margin-top:5px;">Format : colonne1 | colonne2 (une entrée par ligne)</small>'
                           . '</div>';
                }
                $html .= '<div class="cms-rendered-content"><div class="content-grid-2">';
                $lines = array_filter(array_map('trim', explode("\n", $body)));
                foreach ($lines as $line) {
                    $parts = array_map('trim', explode('|', $line));
                    if (count($parts) >= 2) {
                        $cat   = htmlspecialchars($parts[0]);
                        $score = htmlspecialchars($parts[1]);
                        $desc  = htmlspecialchars($parts[2] ?? '');
                        $html .= '<div class="content-card" style="display:flex;flex-direction:row;align-items:stretch;">'
                               . '<div style="width:70px;flex-shrink:0;background:linear-gradient(135deg,#1a1f3a,#2a0810);display:flex;align-items:center;justify-content:center;font-family:\'Bebas Neue\',sans-serif;font-size:1.2rem;color:var(--rouge);">' . $cat . '</div>'
                               . '<div class="content-card-body" style="flex:1;"><h3>' . $score . '</h3>'
                               . ($desc ? '<p style="margin-top:4px;font-size:.85rem;color:#888;">' . $desc . '</p>' : '')
                               . '</div></div>';
                    } else {
                        $html .= '<p>' . htmlspecialchars($line) . '</p>';
                    }
                }
                $html .= '</div></div>';
                break;

            // ── Grille joueurs ──────────────────────────────────────
            case 'player_grid':
                if (!empty($items)) {
                    $html .= '<div class="content-grid-4">';
                    foreach ($items as $item) {
                        $img   = htmlspecialchars($item['image']  ?? '');
                        $nom   = htmlspecialchars($item['nom']    ?? '');
                        $poste = htmlspecialchars($item['poste']  ?? '');
                        $num   = htmlspecialchars($item['numero'] ?? '');
                        $html .= '<div class="player-card">';
                        $html .= '<div class="player-img">';
                        if ($img) {
                            $html .= '<img src="' . $img . '" alt="' . $nom . '" onerror="this.parentNode.innerHTML=\'<div style=\\\'width:100%;height:100%;min-height:180px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0d1022,#1a0a12);font-size:3rem;\\\'>👤</div>\'">';
                        } else {
                            $html .= '<div style="width:100%;height:100%;min-height:180px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0d1022,#1a0a12);font-size:3rem;">👤</div>';
                        }
                        $html .= '</div>';
                        $html .= '<div class="player-info">';
                        if ($num)   $html .= '<div class="player-num">' . $num . '</div>';
                        if ($nom)   $html .= '<div class="player-name">' . $nom . '</div>';
                        if ($poste) $html .= '<div class="player-pos">' . $poste . '</div>';
                        $html .= '</div></div>';
                    }
                    $html .= '</div>';
                }
                break;

            // ── Grille staff ────────────────────────────────────────
            case 'staff_grid':
                if (!empty($items)) {
                    $html .= '<div class="content-grid-3">';
                    foreach ($items as $item) {
                        $img  = htmlspecialchars($item['image'] ?? '');
                        $nom  = htmlspecialchars($item['nom']   ?? '');
                        $role = htmlspecialchars($item['role']  ?? '');
                        $desc = htmlspecialchars($item['desc']  ?? '');
                        $html .= '<div class="content-card" style="background:var(--navy,#1A1F3A);color:#fff;border:1px solid rgba(255,255,255,.08);">';
                        if ($img) {
                            $html .= '<div class="content-card-img"><img src="' . $img . '" alt="' . $nom . '"></div>';
                        } else {
                            $html .= '<div class="content-card-img" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0d1022,#1a0a12);aspect-ratio:1/1;font-size:3.5rem;">🧑‍💼</div>';
                        }
                        $html .= '<div class="content-card-body">';
                        if ($nom)  $html .= '<h3 style="color:#fff;">' . $nom . '</h3>';
                        if ($role) $html .= '<p style="color:var(--or,#F0C040);font-size:.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;">' . $role . '</p>';
                        if ($desc) $html .= '<p style="color:rgba(255,255,255,.5);font-size:.86rem;margin-top:6px;">' . $desc . '</p>';
                        $html .= '</div></div>';
                    }
                    $html .= '</div>';
                }
                break;

            // ── Galerie d'images ────────────────────────────────────
            case 'image_gallery':
                if (!empty($items)) {
                    $html .= '<div class="content-grid-3">';
                    foreach ($items as $item) {
                        $img     = htmlspecialchars($item['image']   ?? '');
                        $legende = htmlspecialchars($item['legende'] ?? '');
                        if (!$img) continue;
                        $html .= '<div class="content-card">';
                        $html .= '<div class="content-card-img"><img src="' . $img . '" alt="' . $legende . '"></div>';
                        if ($legende) $html .= '<div class="content-card-body"><p>' . $legende . '</p></div>';
                        $html .= '</div>';
                    }
                    $html .= '</div>';
                }
                break;

            // ── Statistiques / Chiffres clés ────────────────────────
            case 'stats':
                if (!empty($items)) {
                    $html .= '<div class="content-grid-4">';
                    foreach ($items as $i => $item) {
                        $val   = htmlspecialchars($item['valeur'] ?? '');
                        $label = htmlspecialchars($item['label']  ?? '');
                        $icon  = htmlspecialchars($item['icon']   ?? '');
                        $ia    = $em ? ' data-cms-item-idx="' . $i . '"' : '';
                        $html .= '<div' . $ia . ' style="background:var(--navy,#1A1F3A);color:#fff;padding:28px 16px;border-radius:14px;border-top:3px solid var(--rouge,#C8102E);text-align:center;">';
                        if ($icon) {
                            $html .= '<div' . ($em ? ' data-cms-field="icon"' : '') . ' style="font-size:2rem;margin-bottom:8px;">' . $icon . '</div>';
                        }
                        $html .= '<div' . ($em ? ' data-cms-field="valeur"' : '') . ' style="font-family:\'Bebas Neue\',sans-serif;font-size:2.8rem;line-height:1;color:var(--or,#F0C040);">' . $val . '</div>'
                               . '<div' . ($em ? ' data-cms-field="label"' : '') . ' style="font-size:.72rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-top:8px;">' . $label . '</div>'
                               . '</div>';
                    }
                    $html .= '</div>';
                }
                break;

            // ── Calendrier de matchs ────────────────────────────────
            case 'schedule_block':
                if (!empty($items)) {
                    $html .= '<div style="display:flex;flex-direction:column;gap:10px;">';
                    foreach ($items as $item) {
                        $date  = htmlspecialchars($item['date']        ?? '');
                        $heure = htmlspecialchars($item['heure']       ?? '');
                        $adv   = htmlspecialchars($item['adversaire']  ?? '');
                        $lieu  = htmlspecialchars($item['lieu']        ?? '');
                        $comp  = htmlspecialchars($item['competition'] ?? '');
                        $html .= '<div style="display:flex;align-items:center;gap:16px;background:#F8F9FC;border-radius:10px;padding:14px 18px;border-left:4px solid var(--rouge,#C8102E);">'
                               . '<div style="min-width:80px;text-align:center;"><div style="font-family:\'Bebas Neue\',sans-serif;font-size:1.6rem;color:var(--navy,#1A1F3A);line-height:1;">' . $date . '</div>'
                               . ($heure ? '<div style="font-size:.78rem;color:#888;">' . $heure . '</div>' : '') . '</div>'
                               . '<div style="flex:1;"><strong style="font-family:\'Barlow Condensed\',sans-serif;font-size:1.05rem;color:var(--navy,#1A1F3A);">' . $adv . '</strong>'
                               . ($lieu ? '<div style="font-size:.85rem;color:#666;">📍 ' . $lieu . '</div>' : '')
                               . ($comp ? '<div style="font-size:.72rem;color:var(--rouge,#C8102E);font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin-top:2px;">' . $comp . '</div>' : '')
                               . '</div></div>';
                    }
                    $html .= '</div>';
                }
                break;

            // ── Texte libre (default) ───────────────────────────────
            default:
                if ($body) {
                    $html .= '<div class="content-text">' . $body . '</div>';
                }
        }

        $html .= '</section>';
    }

    return $html;
}
