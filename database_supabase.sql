-- ============================================================
-- Schéma PostgreSQL pour Supabase — Belgica 3 RFC Liège
-- Importer via : Supabase Dashboard → SQL Editor
-- Converti depuis database.sql (MySQL 9.1.0 → PostgreSQL)
-- ============================================================

-- Table : equipes
DROP TABLE IF EXISTS equipes CASCADE;
CREATE TABLE equipes (
    id         SERIAL PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (nom)
);

INSERT INTO equipes (id, nom, created_at) VALUES
(1,  'RFC Liège',        '2026-03-10 07:21:29'),
(2,  'RWDM Brussels FC', '2026-03-10 07:21:29'),
(3,  'Zulte Waregem',    '2026-03-10 07:21:29'),
(4,  'OH Leuven',        '2026-03-10 07:21:29'),
(5,  'Lommel SK',        '2026-03-10 07:21:29'),
(6,  'SK Deinze',        '2026-03-10 07:21:29'),
(7,  'FC Virton',        '2026-03-10 07:21:29'),
(8,  'SK Beveren',       '2026-03-10 07:21:29'),
(9,  'Beerschot VA',     '2026-03-10 07:21:29'),
(10, 'Lierse SK',        '2026-03-10 07:21:29');

-- Resynchroniser la séquence après INSERT avec IDs explicites
SELECT setval('equipes_id_seq', (SELECT MAX(id) FROM equipes));

-- --------------------------------------------------------

-- Table : matches
DROP TABLE IF EXISTS matches CASCADE;
CREATE TABLE matches (
    id                SERIAL PRIMARY KEY,
    equipe_adversaire VARCHAR(100) NOT NULL,
    stade             VARCHAR(150) NOT NULL,
    date_match        TIMESTAMP NOT NULL,
    competition       VARCHAR(100) DEFAULT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------

-- Table : nav_pages
DROP TABLE IF EXISTS nav_pages CASCADE;
CREATE TABLE nav_pages (
    id        SERIAL PRIMARY KEY,
    categorie VARCHAR(50)  NOT NULL,
    label     VARCHAR(100) NOT NULL,
    folder    VARCHAR(50)  NOT NULL,
    slug      VARCHAR(100) NOT NULL,
    position  SMALLINT     NOT NULL DEFAULT 0,
    active    BOOLEAN      NOT NULL DEFAULT TRUE
);

INSERT INTO nav_pages (id, categorie, label, folder, slug, position, active) VALUES
(1,  'ACTU',     'Dames',                'actu',     'dames',           1, TRUE),
(2,  'ACTU',     'Évènements',           'actu',     'evenements',      2, TRUE),
(3,  'ACTU',     'Jeunes',               'actu',     'jeunes',          3, TRUE),
(4,  'ACTU',     'Sportive',             'actu',     'sportive',        4, TRUE),
(5,  'ACTU',     'Supporters',           'actu',     'supporters',      5, TRUE),
(6,  'ACTU',     'Newsletter',           'actu',     'newsletter',      6, TRUE),
(7,  'CLUB',     'Comité de vigilance',  'club',     'comite',          1, TRUE),
(8,  'CLUB',     'Histoire',             'club',     'histoire',        2, TRUE),
(9,  'CLUB',     'Organigramme',         'club',     'organigramme',    3, TRUE),
(10, 'CLUB',     'Sang & Marine',        'club',     'sangmarine',      4, TRUE),
(11, 'CLUB',     'Stade de Rocourt',     'club',     'stade',           5, TRUE),
(12, 'D1B',      'Calendrier',           'd1b',      'calendrier',      1, TRUE),
(13, 'D1B',      'Classement',           'd1b',      'classement',      2, TRUE),
(14, 'D1B',      'Joueurs',              'd1b',      'joueurs',         3, TRUE),
(15, 'D1B',      'Staff',               'd1b',      'staff',           4, TRUE),
(16, 'DAMES',    'Équipe D1',            'dames',    'equipe-d1',       1, TRUE),
(17, 'DAMES',    'Équipe IP',            'dames',    'equipe-ip',       2, TRUE),
(18, 'DAMES',    'Ladies Younited',      'dames',    'ladies-younited', 3, TRUE),
(19, 'EDJ',      'Cotisations',          'edj',      'cotisations',     1, TRUE),
(20, 'EDJ',      'Entraînements',        'edj',      'entrainements',   2, TRUE),
(21, 'EDJ',      'Équipe U21',           'edj',      'equipe-u21',      3, TRUE),
(22, 'EDJ',      'Parents Fair-play',    'edj',      'parents-fairplay',4, TRUE),
(23, 'EDJ',      'Recrutement',          'edj',      'recrutement',     5, TRUE),
(24, 'EDJ',      'Règlement',            'edj',      'reglement',       6, TRUE),
(25, 'EDJ',      'Secrétariat',          'edj',      'secretariat',     7, TRUE),
(26, 'FANS',     'Abos 25/26',           'fans',     'abos',            1, TRUE),
(27, 'FANS',     'Billetterie 25/26',    'fans',     'billetterie',     2, TRUE),
(28, 'FANS',     'Cashless au stade',    'fans',     'cashless',        3, TRUE),
(29, 'FANS',     'Clubs de supporters',  'fans',     'clubs-supporters',4, TRUE),
(30, 'FANS',     'Fanshop',              'fans',     'fanshop',         5, TRUE),
(31, 'FANS',     'PMR au stade',         'fans',     'pmr',             6, TRUE),
(32, 'FANS',     'Règlement OI',         'fans',     'reglement-oi',    7, TRUE),
(33, 'MÉDIAS',   'Accréditation',        'medias',   'accreditation',   1, TRUE),
(34, 'MÉDIAS',   'Newsletter',           'medias',   'newsletter',      2, TRUE),
(35, 'MÉDIAS',   'Magazine',             'medias',   'magazine',        3, TRUE),
(36, 'MÉDIAS',   'Réseaux sociaux',      'medias',   'reseaux',         4, TRUE),
(37, 'BUSINESS', 'Business Club 1892',   'business', 'business-club',   1, TRUE),
(38, 'BUSINESS', 'Partenaires',          'business', 'partenaires',     2, TRUE),
(39, 'BUSINESS', 'Repas VIP',            'business', 'repas-vip',       3, TRUE),
(40, 'TICKETS',  'Billetterie',          'tickets',  'tickets',         1, TRUE);

SELECT setval('nav_pages_id_seq', (SELECT MAX(id) FROM nav_pages));

-- --------------------------------------------------------

-- Table : resultats
DROP TABLE IF EXISTS resultats CASCADE;
CREATE TABLE resultats (
    id               SERIAL PRIMARY KEY,
    journee          INT          NOT NULL,
    equipe_domicile  VARCHAR(100) NOT NULL,
    buts_domicile    INT          NOT NULL DEFAULT 0,
    equipe_exterieur VARCHAR(100) NOT NULL,
    buts_exterieur   INT          NOT NULL DEFAULT 0,
    date_match       DATE         DEFAULT NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO resultats (id, journee, equipe_domicile, buts_domicile, equipe_exterieur, buts_exterieur, date_match, created_at) VALUES
(1,  1, 'RFC Liège',        2, 'RWDM Brussels FC', 0, '2025-08-10', '2026-03-10 07:21:29'),
(2,  1, 'Zulte Waregem',    1, 'OH Leuven',        1, '2025-08-10', '2026-03-10 07:21:29'),
(3,  1, 'SK Beveren',       0, 'Lommel SK',         2, '2025-08-10', '2026-03-10 07:21:29'),
(4,  2, 'RFC Liège',        1, 'Zulte Waregem',    1, '2025-08-17', '2026-03-10 07:21:29'),
(5,  2, 'RWDM Brussels FC', 2, 'SK Deinze',        0, '2025-08-17', '2026-03-10 07:21:29'),
(6,  3, 'OH Leuven',        0, 'RFC Liège',        3, '2025-08-24', '2026-03-10 07:21:29'),
(7,  3, 'Lommel SK',        1, 'RWDM Brussels FC', 1, '2025-08-24', '2026-03-10 07:21:29'),
(8,  4, 'RFC Liège',        2, 'SK Beveren',       0, '2025-08-31', '2026-03-10 07:21:29'),
(9,  4, 'Beerschot VA',     1, 'Zulte Waregem',    2, '2025-08-31', '2026-03-10 07:21:29'),
(10, 5, 'FC Virton',        0, 'RFC Liège',        2, '2025-09-07', '2026-03-10 07:21:29'),
(11, 5, 'SK Deinze',        1, 'OH Leuven',        0, '2025-09-07', '2026-03-10 07:21:29');

SELECT setval('resultats_id_seq', (SELECT MAX(id) FROM resultats));

-- --------------------------------------------------------

-- Table : user
DROP TABLE IF EXISTS "user" CASCADE;
CREATE TABLE "user" (
    id         SERIAL PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL,
    email      VARCHAR(100) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    isadmin    BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (username),
    UNIQUE (email)
);
