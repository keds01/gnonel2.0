-- Script MySQL direct pour la galerie
-- Exécutez ce script directement dans phpMyAdmin ou via ligne de commande:
-- mysql -u root -p gnone1681928_106xcl < galerie_migration_direct.sql

-- Désactiver les vérifications de clés étrangères pendant la création
SET FOREIGN_KEY_CHECKS = 0;

-- Supprimer les tables si elles existent (avec gestion d'erreur)
DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS photos;
DROP TABLE IF EXISTS events;

-- Création de la table events
CREATE TABLE events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    category VARCHAR(255) NULL,
    description TEXT NULL,
    cover_image VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    likes_count INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_event_date (event_date),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table photos
CREATE TABLE photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255) NULL,
    caption VARCHAR(255) NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_hidden TINYINT(1) DEFAULT 0,
    `order` INT DEFAULT 0,
    likes_count INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id),
    INDEX idx_is_featured (is_featured),
    INDEX idx_is_hidden (is_hidden),
    INDEX idx_order (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table likes
CREATE TABLE likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NULL,
    photo_id BIGINT UNSIGNED NULL,
    session_id VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id),
    INDEX idx_photo_id (photo_id),
    INDEX idx_session_id (session_id),
    INDEX idx_ip_address (ip_address),
    UNIQUE KEY unique_session_event_like (session_id, event_id),
    UNIQUE KEY unique_session_photo_like (session_id, photo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- Afficher un message de succès
SELECT 'Migration galerie terminée avec succès!' AS message;
