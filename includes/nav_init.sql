-- ============================================================
--  RFC Liège — Table nav_pages
--  À exécuter UNE SEULE FOIS sur la base de données
-- ============================================================

CREATE TABLE IF NOT EXISTS `nav_pages` (
    `id`        INT          AUTO_INCREMENT PRIMARY KEY,
    `categorie` VARCHAR(50)  NOT NULL COMMENT 'Ex: ACTU, CLUB, D1B...',
    `label`     VARCHAR(100) NOT NULL COMMENT 'Texte affiché dans le menu',
    `folder`    VARCHAR(50)  NOT NULL COMMENT 'Dossier PHP ex: actu, club...',
    `slug`      VARCHAR(100) NOT NULL COMMENT 'Nom du fichier sans .php',
    `position`  TINYINT      NOT NULL DEFAULT 0 COMMENT 'Ordre dans le dropdown',
    `active`    TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1=visible, 0=masqué'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── ACTU ──────────────────────────────────────────────────────
INSERT INTO `nav_pages` (`categorie`,`label`,`folder`,`slug`,`position`) VALUES
('ACTU','Dames',       'actu','dames',       1),
('ACTU','Évènements',  'actu','evenements',  2),
('ACTU','Jeunes',      'actu','jeunes',      3),
('ACTU','Sportive',    'actu','sportive',    4),
('ACTU','Supporters',  'actu','supporters',  5),
('ACTU','Newsletter',  'actu','newsletter',  6);

-- ── CLUB ──────────────────────────────────────────────────────
INSERT INTO `nav_pages` (`categorie`,`label`,`folder`,`slug`,`position`) VALUES
('CLUB','Comité de vigilance','club','comite',       1),
('CLUB','Histoire',           'club','histoire',     2),
('CLUB','Organigramme',       'club','organigramme', 3),
('CLUB','Sang & Marine',      'club','sangmarine',   4),
('CLUB','Stade de Rocourt',   'club','stade',        5);

-- ── D1B ───────────────────────────────────────────────────────
INSERT INTO `nav_pages` (`categorie`,`label`,`folder`,`slug`,`position`) VALUES
('D1B','Calendrier',  'd1b','calendrier', 1),
('D1B','Classement',  'd1b','classement', 2),
('D1B','Joueurs',     'd1b','joueurs',    3),
('D1B','Staff',       'd1b','staff',      4);

-- ── DAMES ─────────────────────────────────────────────────────
INSERT INTO `nav_pages` (`categorie`,`label`,`folder`,`slug`,`position`) VALUES
('DAMES','Équipe D1',       'dames','equipe-d1',       1),
('DAMES','Équipe IP',       'dames','equipe-ip',       2),
('DAMES','Ladies Younited', 'dames','ladies-younited', 3);

-- ── EDJ ───────────────────────────────────────────────────────
INSERT INTO `nav_pages` (`categorie`,`label`,`folder`,`slug`,`position`) VALUES
('EDJ','Cotisations',    'edj','cotisations',    1),
('EDJ','Entraînements',  'edj','entrainements',  2),
('EDJ','Équipe U21',     'edj','equipe-u21',     3),
('EDJ','Parents Fair-play','edj','parents-fairplay',4),
('EDJ','Recrutement',    'edj','recrutement',    5),
('EDJ','Règlement',      'edj','reglement',      6),
('EDJ','Secrétariat',    'edj','secretariat',    7);

-- ── FANS ──────────────────────────────────────────────────────
INSERT INTO `nav_pages` (`categorie`,`label`,`folder`,`slug`,`position`) VALUES
('FANS','Abos 25/26',          'fans','abos',            1),
('FANS','Billetterie 25/26',   'fans','billetterie',     2),
('FANS','Cashless au stade',   'fans','cashless',        3),
('FANS','Clubs de supporters', 'fans','clubs-supporters',4),
('FANS','Fanshop',             'fans','fanshop',         5),
('FANS','PMR au stade',        'fans','pmr',             6),
('FANS','Règlement OI',        'fans','reglement-oi',    7);

-- ── MÉDIAS ────────────────────────────────────────────────────
INSERT INTO `nav_pages` (`categorie`,`label`,`folder`,`slug`,`position`) VALUES
('MÉDIAS','Accréditation',  'medias','accreditation', 1),
('MÉDIAS','Newsletter',     'medias','newsletter',    2),
('MÉDIAS','Magazine',       'medias','magazine',      3),
('MÉDIAS','Réseaux sociaux','medias','reseaux',       4);

-- ── BUSINESS ──────────────────────────────────────────────────
INSERT INTO `nav_pages` (`categorie`,`label`,`folder`,`slug`,`position`) VALUES
('BUSINESS','Business Club 1892','business','business-club', 1),
('BUSINESS','Partenaires',       'business','partenaires',   2),
('BUSINESS','Repas VIP',         'business','repas-vip',     3);

-- ── TICKETS ───────────────────────────────────────────────────
INSERT INTO `nav_pages` (`categorie`,`label`,`folder`,`slug`,`position`) VALUES
('TICKETS','Billetterie','tickets','tickets', 1);
