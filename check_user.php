<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gnone1681928_106xcl', 'root', '');
    
    // Rechercher l'utilisateur par email
    $stmt = $pdo->prepare('SELECT id, name, email, telephone, type_user, status, created_at FROM users WHERE email = ?');
    $stmt->execute(['test7@gmail.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "UTILISATEUR TROUVÉ:\n";
        echo json_encode($user, JSON_PRETTY_PRINT) . "\n";
        
        // Rechercher ses souscriptions
        $stmt2 = $pdo->prepare('SELECT s.*, a.libelle, a.prix, a.nbjours FROM souscriptions s LEFT JOIN abonnement a ON s.idabonnement = a.id WHERE s.iduser = ? ORDER BY s.created_at DESC');
        $stmt2->execute([$user['id']]);
        $souscriptions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nSOUSCRIPTIONS (" . count($souscriptions) . "):\n";
        foreach ($souscriptions as $sub) {
            echo "- ID: {$sub['idsouscription']}, Statut: {$sub['statut']}, Montant: {$sub['montant_finale_apaye']}, Date fin: {$sub['date_fin']}\n";
        }
    } else {
        echo "UTILISATEUR NON TROUVÉ";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
