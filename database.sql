-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 18 mars 2026 à 11:09
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `verbeek-hugo`
--
CREATE DATABASE IF NOT EXISTS `verbeek-hugo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `verbeek-hugo`;

-- --------------------------------------------------------

--
-- Structure de la table `equipes`
--

DROP TABLE IF EXISTS `equipes`;
CREATE TABLE IF NOT EXISTS `equipes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `equipes` (`id`, `nom`, `created_at`) VALUES
(1, 'RFC Liège', '2026-03-10 07:21:29'),
(2, 'RWDM Brussels FC', '2026-03-10 07:21:29'),
(3, 'Zulte Waregem', '2026-03-10 07:21:29'),
(4, 'OH Leuven', '2026-03-10 07:21:29'),
(5, 'Lommel SK', '2026-03-10 07:21:29'),
(6, 'SK Deinze', '2026-03-10 07:21:29'),
(7, 'FC Virton', '2026-03-10 07:21:29'),
(8, 'SK Beveren', '2026-03-10 07:21:29'),
(9, 'Beerschot VA', '2026-03-10 07:21:29'),
(10, 'Lierse SK', '2026-03-10 07:21:29');

-- --------------------------------------------------------

--
-- Structure de la table `matches`
--

DROP TABLE IF EXISTS `matches`;
CREATE TABLE IF NOT EXISTS `matches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `equipe_adversaire` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stade` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_match` datetime NOT NULL,
  `competition` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `nav_pages`
--

DROP TABLE IF EXISTS `nav_pages`;
CREATE TABLE IF NOT EXISTS `nav_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categorie` varchar(50) NOT NULL COMMENT 'Ex: ACTU, CLUB, D1B...',
  `label` varchar(100) NOT NULL COMMENT 'Texte affiché dans le menu',
  `folder` varchar(50) NOT NULL COMMENT 'Dossier PHP ex: actu, club...',
  `slug` varchar(100) NOT NULL COMMENT 'Nom du fichier sans .php',
  `position` tinyint NOT NULL DEFAULT '0' COMMENT 'Ordre dans le dropdown',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=visible, 0=masqué',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `nav_pages` (`id`, `categorie`, `label`, `folder`, `slug`, `position`, `active`) VALUES
(1, 'ACTU', 'Dames', 'actu', 'dames', 1, 1),
(2, 'ACTU', 'Évènements', 'actu', 'evenements', 2, 1),
(3, 'ACTU', 'Jeunes', 'actu', 'jeunes', 3, 1),
(4, 'ACTU', 'Sportive', 'actu', 'sportive', 4, 1),
(5, 'ACTU', 'Supporters', 'actu', 'supporters', 5, 1),
(6, 'ACTU', 'Newsletter', 'actu', 'newsletter', 6, 1),
(7, 'CLUB', 'Comité de vigilance', 'club', 'comite', 1, 1),
(8, 'CLUB', 'Histoire', 'club', 'histoire', 2, 1),
(9, 'CLUB', 'Organigramme', 'club', 'organigramme', 3, 1),
(10, 'CLUB', 'Sang & Marine', 'club', 'sangmarine', 4, 1),
(11, 'CLUB', 'Stade de Rocourt', 'club', 'stade', 5, 1),
(12, 'D1B', 'Calendrier', 'd1b', 'calendrier', 1, 1),
(13, 'D1B', 'Classement', 'd1b', 'classement', 2, 1),
(14, 'D1B', 'Joueurs', 'd1b', 'joueurs', 3, 1),
(15, 'D1B', 'Staff', 'd1b', 'staff', 4, 1),
(16, 'DAMES', 'Équipe D1', 'dames', 'equipe-d1', 1, 1),
(17, 'DAMES', 'Équipe IP', 'dames', 'equipe-ip', 2, 1),
(18, 'DAMES', 'Ladies Younited', 'dames', 'ladies-younited', 3, 1),
(19, 'EDJ', 'Cotisations', 'edj', 'cotisations', 1, 1),
(20, 'EDJ', 'Entraînements', 'edj', 'entrainements', 2, 1),
(21, 'EDJ', 'Équipe U21', 'edj', 'equipe-u21', 3, 1),
(22, 'EDJ', 'Parents Fair-play', 'edj', 'parents-fairplay', 4, 1),
(23, 'EDJ', 'Recrutement', 'edj', 'recrutement', 5, 1),
(24, 'EDJ', 'Règlement', 'edj', 'reglement', 6, 1),
(25, 'EDJ', 'Secrétariat', 'edj', 'secretariat', 7, 1),
(26, 'FANS', 'Abos 25/26', 'fans', 'abos', 1, 1),
(27, 'FANS', 'Billetterie 25/26', 'fans', 'billetterie', 2, 1),
(28, 'FANS', 'Cashless au stade', 'fans', 'cashless', 3, 1),
(29, 'FANS', 'Clubs de supporters', 'fans', 'clubs-supporters', 4, 1),
(30, 'FANS', 'Fanshop', 'fans', 'fanshop', 5, 1),
(31, 'FANS', 'PMR au stade', 'fans', 'pmr', 6, 1),
(32, 'FANS', 'Règlement OI', 'fans', 'reglement-oi', 7, 1),
(33, 'MÉDIAS', 'Accréditation', 'medias', 'accreditation', 1, 1),
(34, 'MÉDIAS', 'Newsletter', 'medias', 'newsletter', 2, 1),
(35, 'MÉDIAS', 'Magazine', 'medias', 'magazine', 3, 1),
(36, 'MÉDIAS', 'Réseaux sociaux', 'medias', 'reseaux', 4, 1),
(37, 'BUSINESS', 'Business Club 1892', 'business', 'business-club', 1, 1),
(38, 'BUSINESS', 'Partenaires', 'business', 'partenaires', 2, 1),
(39, 'BUSINESS', 'Repas VIP', 'business', 'repas-vip', 3, 1),
(40, 'TICKETS', 'Billetterie', 'tickets', 'tickets', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `resultats`
--

DROP TABLE IF EXISTS `resultats`;
CREATE TABLE IF NOT EXISTS `resultats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `journee` int NOT NULL,
  `equipe_domicile` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buts_domicile` int NOT NULL DEFAULT '0',
  `equipe_exterieur` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buts_exterieur` int NOT NULL DEFAULT '0',
  `date_match` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `resultats` (`id`, `journee`, `equipe_domicile`, `buts_domicile`, `equipe_exterieur`, `buts_exterieur`, `date_match`, `created_at`) VALUES
(1, 1, 'RFC Liège', 2, 'RWDM Brussels FC', 0, '2025-08-10', '2026-03-10 07:21:29'),
(2, 1, 'Zulte Waregem', 1, 'OH Leuven', 1, '2025-08-10', '2026-03-10 07:21:29'),
(3, 1, 'SK Beveren', 0, 'Lommel SK', 2, '2025-08-10', '2026-03-10 07:21:29'),
(4, 2, 'RFC Liège', 1, 'Zulte Waregem', 1, '2025-08-17', '2026-03-10 07:21:29'),
(5, 2, 'RWDM Brussels FC', 2, 'SK Deinze', 0, '2025-08-17', '2026-03-10 07:21:29'),
(6, 3, 'OH Leuven', 0, 'RFC Liège', 3, '2025-08-24', '2026-03-10 07:21:29'),
(7, 3, 'Lommel SK', 1, 'RWDM Brussels FC', 1, '2025-08-24', '2026-03-10 07:21:29'),
(8, 4, 'RFC Liège', 2, 'SK Beveren', 0, '2025-08-31', '2026-03-10 07:21:29'),
(9, 4, 'Beerschot VA', 1, 'Zulte Waregem', 2, '2025-08-31', '2026-03-10 07:21:29'),
(10, 5, 'FC Virton', 0, 'RFC Liège', 2, '2025-09-07', '2026-03-10 07:21:29'),
(11, 5, 'SK Deinze', 1, 'OH Leuven', 0, '2025-09-07', '2026-03-10 07:21:29');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isadmin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
