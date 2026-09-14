<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gnone1681928_106xcl', 'root', '');
    
    // Rechercher l'utilisateur par email
    $stmt = $pdo->prepare('SELECT id, name, email, telephone, type_user, status, created_at FROM users WHERE email = ?');
    $stmt->execute(['ekoue.mailpro@gmail.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "UTILISATEUR TROUVÉ:\n";
        echo json_encode($user, JSON_PRETTY_PRINT) . "\n";
        
        // Rechercher ses souscriptions
        $stmt2 = $pdo->prepare('SELECT s.*, a.libelle, a.prix FROM souscriptions s LEFT JOIN abonnement a ON s.idabonnement = a.id WHERE s.iduser = ? ORDER BY s.created_at DESC');
        $stmt2->execute([$user['id']]);
        $souscriptions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nSOUSCRIPTIONS (" . count($souscriptions) . "):\n";
        foreach ($souscriptions as $sub) {
            echo "- ID: {$sub['idsouscription']}, Statut: {$sub['statut']}, Montant: {$sub['montant_finale_apaye']}, Date fin: " . ($sub['date_fin'] ?: 'NON DÉFINIE') . "\n";
        }
        
        // Vérifier l'état de l'abonnement
        $stmt3 = $pdo->prepare('SELECT COUNT(*) as count FROM souscriptions WHERE iduser = ? AND statut = 1 AND date_fin >= CURDATE()');
        $stmt3->execute([$user['id']]);
        $activeSub = $stmt3->fetch(PDO::FETCH_ASSOC);
        
        echo "\nÉTAT ABONNEMENT: " . ($activeSub['count'] > 0 ? 'ACTIF ✅' : 'INACTIF ❌') . "\n";
        
        // Vérifier les logs récents pour cet utilisateur
        echo "\nRECHERCHE LOGS RÉCENTS...\n";
        $logFile = 'storage/logs/laravel.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lines = array_slice(array_reverse(explode("\n", $content)), 0, 50);
            
            $userLogs = [];
            foreach ($lines as $line) {
                if (strpos($line, $user['email']) !== false || strpos($line, 'ID: ' . $user['id']) !== false) {
                    $userLogs[] = $line;
                }
            }
            
            if (!empty($userLogs)) {
                echo "LOGS RÉCENTS POUR CET UTILISATEUR:\n";
                foreach (array_slice($userLogs, 0, 5) as $log) {
                    echo "- " . trim($log) . "\n";
                }
            } else {
                echo "AUCUN LOG RÉCENT TROUVÉ\n";
            }
        }
        
    } else {
        echo "UTILISATEUR ekoue.mailpro@gmail.com NON TROUVÉ\n";
    }
    
} catch (Exception $e) {
    echo 'ERREUR: ' . $e->getMessage();
}
