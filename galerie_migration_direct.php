<?php
/**
 * Script PHP pour migrer directement les tables de la galerie dans MySQL
 * Exécutez: php galerie_migration_direct.php
 * Ou via navigateur: http://votre-site.com/galerie_migration_direct.php
 */

// Configuration de la base de données
$host = 'localhost';
$dbname = 'gnone1681928_106xcl';
$username = 'root';
$password = '';

// Configuration pour éviter les problèmes de charset
ini_set('default_charset', 'UTF-8');

echo "=== Migration Galerie ===\n\n";

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connexion au serveur MySQL réussie\n";
    
    // Sélectionner la base de données
    $pdo->exec("USE `$dbname`");
    echo "✓ Base de données '$dbname' sélectionnée\n\n";
    
    // Désactiver les vérifications de clés étrangères
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "✓ Vérifications de clés étrangères désactivées\n";
    
    // Supprimer les tables si elles existent (dans l'ordre inverse pour les dépendances)
    $tables = ['likes', 'photos', 'events'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "✓ Table '$table' supprimée (si elle existait)\n";
    }
    
    echo "\n--- Création des tables ---\n\n";
    
    // Création de la table events
    $sql = "CREATE TABLE events (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✓ Table 'events' créée avec succès\n";
    
    // Création de la table photos
    $sql = "CREATE TABLE photos (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✓ Table 'photos' créée avec succès\n";
    
    // Création de la table likes
    $sql = "CREATE TABLE likes (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✓ Table 'likes' créée avec succès\n";
    
    // Réactiver les vérifications de clés étrangères
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "\n✓ Vérifications de clés étrangères réactivées\n";
    
    echo "\n=== Migration terminée avec succès! ===\n";
    echo "Les tables de la galerie sont maintenant prêtes.\n";
    
} catch (PDOException $e) {
    echo "\n❌ Erreur: " . $e->getMessage() . "\n";
    echo "Code d'erreur: " . $e->getCode() . "\n";
    exit(1);
}
