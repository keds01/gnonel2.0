<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gnone1681928_106xcl', 'root', '');
    
    // Détails complets de la souscription ID 360
    $stmt = $pdo->prepare('SELECT s.*, a.libelle, a.prix, a.nbjours, a.monnaie FROM souscriptions s LEFT JOIN abonnement a ON s.idabonnement = a.id WHERE s.idsouscription = ?');
    $stmt->execute([360]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($subscription) {
        echo "DÉTAILS SOUSCRIPTION ID 360:\n";
        echo json_encode($subscription, JSON_PRETTY_PRINT) . "\n";
        
        echo "\nANALYSE:\n";
        echo "- Statut: {$subscription['statut']} (0=en cours, 1=approuvé, 2=annulé)\n";
        echo "- Montant final: {$subscription['montant_finale_apaye']} XOF\n";
        echo "- Frais bonus: {$subscription['frais_bonus']} XOF\n";
        echo "- Date fin: " . ($subscription['date_fin'] ?: 'NON DÉFINIE') . "\n";
        echo "- Référence paiement: " . ($subscription['referencepaiement'] ?: 'AUCUNE') . "\n";
        echo "- Méthode paiement: " . ($subscription['payment_method'] ?: 'AUCUNE') . "\n";
        echo "- Statut paiement: " . ($subscription['status_p'] ?: 'AUCUN') . "\n";
        
        // Vérifier si l'abonnement est valide
        $today = date('Y-m-d');
        $is_valid = $subscription['date_fin'] && $subscription['date_fin'] >= $today && $subscription['statut'] == 1;
        
        echo "\nÉTAT ABONNEMENT: " . ($is_valid ? 'ACTIF ✅' : 'INACTIF ❌') . "\n";
        
    } else {
        echo "SOUSCRIPTION 360 NON TROUVÉE";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
