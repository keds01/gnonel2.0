<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

echo "=== ANALYSE DONNÉES CLIENT TRANSMISES ===\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gnone1681928_106xcl', 'root', '');
    
    // 1. Récupérer l'utilisateur réel
    $stmt = $pdo->prepare('SELECT id, name, email, telephone, prenom FROM users WHERE email = ?');
    $stmt->execute(['ekoue.mailpro@gmail.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "UTILISATEUR RÉEL:\n";
        echo "- ID: {$user['id']}\n";
        echo "- Nom: {$user['name']}\n";
        echo "- Prénom: " . ($user['prenom'] ?: 'NON DÉFINI') . "\n";
        echo "- Email: {$user['email']}\n";
        echo "- Téléphone: {$user['telephone']}\n\n";
        
        // 2. Récupérer les données FedaPay envoyées (logs)
        echo "DONNÉES ENVOYÉES À FEDAPAY:\n";
        $logFile = 'storage/logs/laravel.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);
            
            $customerPayloads = [];
            foreach ($lines as $line) {
                if (strpos($line, 'Creating customer with payload') !== false) {
                    $customerPayloads[] = $line;
                }
            }
            
            if (!empty($customerPayloads)) {
                foreach (array_slice($customerPayloads, -3) as $payload) {
                    echo "- " . trim($payload) . "\n";
                }
            } else {
                echo "- Aucun payload client trouvé dans les logs\n";
            }
        }
        
        // 3. Vérifier le client FedaPay existant
        echo "\nCLIENT FEDAPAY EXISTANT (ID: 7222480):\n";
        echo "D'après le webhook:\n";
        echo "- Nom: RERE RERER\n";
        echo "- Email: email@email.com\n";
        echo "- ID: 7222480\n";
        
        // 4. Comparaison
        echo "\n=== COMPARAISON ===\n";
        echo "DONNÉES UTILISATEUR:\n";
        echo "- Nom: {$user['name']} " . ($user['prenom'] ? $user['prenom'] : '') . "\n";
        echo "- Email: {$user['email']}\n";
        echo "- Téléphone: {$user['telephone']}\n\n";
        
        echo "DONNÉES WEBHOOK:\n";
        echo "- Nom: RERE RERER\n";
        echo "- Email: email@email.com\n";
        echo "- Customer ID: 7222480\n\n";
        
        echo "PROBLÈME IDENTIFIÉ:\n";
        echo "- ❌ Les données ne correspondent PAS\n";
        echo "- ❌ Le client FedaPay 7222480 n'est pas l'utilisateur ekoue.mailpro@gmail.com\n";
        echo "- ❌ Le système utilise un ancien client générique\n\n";
        
        // 5. Vérifier si l'utilisateur a un client FedaPay
        echo "=== VÉRIFICATION CLIENT FEDAPAY ===\n";
        $stmt2 = $pdo->prepare('SELECT * FROM fedapay_customers WHERE user_id = ?');
        $stmt2->execute([$user['id']]);
        $customer = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($customer) {
            echo "- Client FedaPay local trouvé: {$customer['customer_id']}\n";
        } else {
            echo "- Aucun client FedaPay local pour cet utilisateur\n";
        }
        
    } else {
        echo "UTILISATEUR ekoue.mailpro@gmail.com NON TROUVÉ\n";
    }
    
} catch (Exception $e) {
    echo 'ERREUR: ' . $e->getMessage();
}
