<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

echo "=== VÉRIFICATION ÉTAT SYSTÈME ===\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gnone1681928_106xcl', 'root', '');
    
    // Vérifier l'utilisateur ekoue.mailpro@gmail.com
    $stmt = $pdo->prepare('SELECT id, name, email, status, type_user FROM users WHERE email = ?');
    $stmt->execute(['ekoue.mailpro@gmail.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "UTILISATEUR:\n";
        echo "- ID: {$user['id']}\n";
        echo "- Nom: {$user['name']}\n";
        echo "- Email: {$user['email']}\n";
        echo "- Status: {$user['status']} (" . ($user['status'] == 1 ? 'ACTIF' : 'INACTIF') . ")\n";
        echo "- Type: {$user['type_user']}\n\n";
        
        // Vérifier ses souscriptions
        $stmt2 = $pdo->prepare('SELECT s.*, a.libelle, a.prix FROM souscriptions s LEFT JOIN abonnement a ON s.idabonnement = a.id WHERE s.iduser = ? ORDER BY s.created_at DESC LIMIT 3');
        $stmt2->execute([$user['id']]);
        $souscriptions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "SOUSCRIPTIONS RÉCENTES:\n";
        foreach ($souscriptions as $sub) {
            $statutLabel = $sub['statut'] == 1 ? 'ACTIF' : ($sub['statut'] == 2 ? 'ANNULÉ' : 'EN ATTENTE');
            echo "- ID: {$sub['idsouscription']}, Formule: {$sub['libelle']}, Statut: {$statutLabel}, Montant: {$sub['montant_finale_apaye']} XOF\n";
        }
        
        // Vérifier s'il y a une souscription active
        $stmt3 = $pdo->prepare('SELECT COUNT(*) as count FROM souscriptions WHERE iduser = ? AND statut = 1 AND date_fin >= CURDATE()');
        $stmt3->execute([$user['id']]);
        $activeCount = $stmt3->fetch(PDO::FETCH_ASSOC);
        
        echo "\nRÉSUMÉ:\n";
        echo "- Souscriptions actives: " . ($activeCount['count'] > 0 ? 'OUI ✅' : 'NON ❌') . "\n";
        echo "- Dernière mise à jour: " . date('Y-m-d H:i:s') . "\n";
        
    } else {
        echo "UTILISATEUR ekoue.mailpro@gmail.com NON TROUVÉ\n";
    }
    
    echo "\n=== LOGS RÉCENTS (FedaPay) ===\n";
    $logFile = 'storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $lines = array_slice(array_reverse(explode("\n", $content)), 0, 20);
        
        $fedapayLogs = [];
        foreach ($lines as $line) {
            if (strpos($line, 'FedaPay') !== false || strpos($line, 'fedapay_webhook') !== false) {
                $fedapayLogs[] = $line;
            }
        }
        
        if (!empty($fedapayLogs)) {
            echo "\nDERNIERS LOGS FEDAPAY:\n";
            foreach (array_slice($fedapayLogs, 0, 5) as $log) {
                echo "- " . trim($log) . "\n";
            }
        } else {
            echo "Aucun log FedaPay récent\n";
        }
    }
    
} catch (Exception $e) {
    echo 'ERREUR: ' . $e->getMessage();
}
