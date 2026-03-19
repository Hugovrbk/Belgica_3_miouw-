# CLAUDE.md — RFC Liège Website

Projet scolaire (secondaire) : site web officiel du RFC Liège, club de football belge évoluant en D1B (saison 2025-2026). Développement en PHP vanilla + JS vanilla, sans framework.

---

## Stack technique

| Couche | Techno |
|---|---|
| Backend | PHP 8.3 (vanilla, PDO) |
| Frontend | JS vanilla, CSS custom (variables CSS) |
| BDD locale | MySQL 9.1 via WAMP |
| BDD prod | PostgreSQL (Supabase, SSL) |
| Déploiement | Vercel avec `vercel-php@0.7.2` |
| Fonts | Bebas Neue, Barlow Condensed (Google Fonts) |

**Règle absolue : rester en vanilla PHP/JS/CSS. Pas de framework (pas de Laravel, React, Vue, Tailwind...).**

---

## Architecture du projet

```
Belgica_3_miouw-/
├── index.html / index.php        # Entry point public
├── db.php                        # Connexion PDO (MySQL local / PostgreSQL Supabase)
├── setup.php                     # Init BDD
├── vercel.json                   # Config déploiement Vercel
├── database.sql                  # Dump MySQL complet
│
├── css/
│   ├── style.css                 # Styles globaux + variables CSS theming
│   ├── animations.css            # Animations CSS
│   ├── admin_styles.css
│   └── admin_dashboard.css
│
├── js/
│   ├── main.js                   # JS principal (nav, interactions)
│   ├── animations.js             # IntersectionObserver pour animations au scroll
│   └── admin.js                  # JS panel admin
│
├── includes/
│   ├── cms_render.php            # Rendu universel des blocs CMS (JSON → HTML)
│   └── nav_init.sql              # SQL d'init de la navigation
│
├── pages/
│   ├── includes/
│   │   ├── header.php            # Header/nav commun (dropdown depuis nav_pages)
│   │   └── classement.php        # Widget classement D1B
│   │
│   ├── compte/                   # Auth utilisateur
│   │   ├── php/
│   │   │   ├── db.php            # db.php alternatif (connexion directe WAMP)
│   │   │   ├── login.php         # Traitement login (POST → session)
│   │   │   ├── logout.php
│   │   │   ├── register.php
│   │   │   └── dashboard.php
│   │   └── html/
│   │       ├── login.php
│   │       └── register.html
│   │
│   ├── admin/                    # Panel admin principal
│   │   ├── html/
│   │   │   ├── admin.php         # Dashboard admin (matchs, résultats, nav, CMS, users)
│   │   │   └── check_admin.php   # Guard : redirige si pas admin
│   │   └── php/                  # Actions AJAX/POST admin
│   │       ├── save_match.php / delete_match.php
│   │       ├── save_result.php / delete_result.php
│   │       ├── save_actualite.php / delete_actualite.php
│   │       ├── save_joueur.php / delete_joueur.php
│   │       ├── save_historique.php / delete_historique.php
│   │       ├── save_content.php  # Sauvegarde blocs CMS (JSON)
│   │       ├── nav_actions.php   # CRUD nav_pages
│   │       ├── manage_teams.php
│   │       ├── toggle_admin.php
│   │       └── upload_image.php
│   │
│   ├── admin-club/               # Admin secondaire (vue club)
│   │
│   └── club/                     # Pages publiques par catégorie
│       ├── actu/                 # Dames, Événements, Jeunes, Sportive, Supporters, Newsletter
│       ├── club/                 # Comité, Histoire, Organigramme, Sang&Marine, Stade
│       ├── d1b/                  # Calendrier, Classement, Joueurs, Staff
│       ├── dames/                # Équipe D1, IP, Ladies Younited
│       ├── edj/                  # École des jeunes
│       ├── fans/                 # Abos, Billetterie, Cashless, Fanshop...
│       ├── medias/               # Accréditation, Newsletter, Magazine, Réseaux
│       ├── business/             # Business Club, Partenaires, Repas VIP
│       └── tickets/
│
├── multimedia/
│   └── img/                      # hero/, logo/, logo, news/, sponsor/, sponsors/, resaux/
```

---

## Base de données

### db.php (racine) — connexion intelligente
```php
// Si DB_HOST présent (Vercel/Supabase) → PostgreSQL avec SSL
// Sinon → MySQL local (WAMP fallback)
```

### Tables principales

| Table | Description |
|---|---|
| `user` | id, username, email, password (hash), isadmin (0/1), created_at |
| `equipes` | id, nom (unique), created_at |
| `matches` | id, equipe_adversaire, stade, date_match, competition, ... |
| `resultats` | id, journee, equipe_domicile, buts_domicile, equipe_exterieur, buts_exterieur, date_match |
| `nav_pages` | id, categorie, label, folder, slug, position, active |
| `page_content` | id, slug (unique), contenu (JSON), updated_at |

### Catégories nav_pages
`ACTU · CLUB · D1B · DAMES · EDJ · FANS · MÉDIAS · BUSINESS · TICKETS`

---

## CMS Custom (`includes/cms_render.php`)

Le contenu des pages est stocké en JSON dans `page_content.contenu` et rendu via `cms_render($contenu)`.

### Types de blocs disponibles

| Type | Description |
|---|---|
| `text` | Texte libre HTML |
| `resultats` | Timeline résultats (format : `colonne1 \| colonne2` par ligne) |
| `player_grid` | Grille joueurs (items: image, nom, poste, numero) |
| `staff_grid` | Grille staff (items: image, nom, role, desc) |
| `image_gallery` | Galerie images (items: image, legende) |
| `stats` | Stats/chiffres clés (items: valeur, label, icon) |
| `schedule_block` | Calendrier matchs (items: date, heure, adversaire, lieu, competition) |

Structure JSON :
```json
[
  { "type": "text", "titre": "Titre", "contenu": "<p>...</p>" },
  { "type": "player_grid", "titre": "Joueurs", "items": [...] }
]
```

---

## Conventions de code

### PHP
- PDO avec prepared statements (jamais d'interpolation directe dans les requêtes SQL)
- `session_start()` en haut des pages qui utilisent les sessions
- Auth : `$_SESSION['user_id']`, `$_SESSION['user_admin']` (1 = admin)
- Pas de namespace/composer — includes manuels avec `require_once`

### CSS
- Variables CSS pour le theming :
  - `--rouge: #C8102E` (rouge RFC Liège)
  - `--navy: #1A1F3A`
  - `--or: #F0C040`
- Dark/light mode via variables CSS
- Pas de framework CSS

### JS
- Vanilla uniquement
- `IntersectionObserver` pour les animations au scroll (`animations.js`)
- Pas de bundler — fichiers JS séparés inclus via `<script src>`

---

## Environnements

### Local (développement)
- WAMP sur Windows, accès via `http://localhost/Belgica_3_miouw-`
- MySQL : host `localhost`, db `verbeek-hugo`, user `root`
- `db.php` à la racine : détecte automatiquement local vs prod

### Production (Vercel + Supabase)
- Vercel avec runtime `vercel-php@0.7.2`
- PostgreSQL Supabase avec `sslmode=require`
- Variables d'environnement Vercel : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_PORT`

---

## Commandes utiles

```bash
# Lancer le projet en local
# Ouvrir WAMP → démarrer Apache + MySQL → naviguer vers :
# http://localhost/Belgica_3_miouw-

# Accès admin
# http://localhost/Belgica_3_miouw-/pages/admin/html/admin.php
# (nécessite un compte avec isadmin=1 dans la table user)

# Réinitialiser la BDD
# Importer database.sql dans phpMyAdmin

# Déployer sur Vercel
# vercel deploy (depuis la racine du projet)
```

---

## Priorités actuelles

1. **Design/UX** : amélioration de l'interface publique, responsive, animations
2. **Refactoring** : cohérence de la connexion BDD (unifier les `db.php`), nettoyage des pages obsolètes
3. **CMS** : enrichissement des types de blocs, amélioration de l'éditeur admin
