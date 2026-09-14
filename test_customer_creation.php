<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

echo "=== TEST CRÉATION CLIENT FEDAPAY AVEC DONNÉES RÉELLES ===\n\n";

try {
    // Simuler l'utilisateur ekoue.mailpro@gmail.com
    $testUser = new \App\User([
        'name' => 'ekoue',
        'prenom' => 'ekoue', 
        'email' => 'ekoue.mailpro@gmail.com',
        'telephone' => '123456987'
    ]);
    
    echo "UTILISATEUR TEST:\n";
    echo "- Nom: " . $testUser->name . "\n";
    echo "- Prénom: " . $testUser->prenom . "\n";
    echo "- Email: " . $testUser->email . "\n";
    echo "- Téléphone: " . $testUser->telephone . "\n\n";
    
    // Tester la création de client
    echo "TEST CRÉATION CLIENT FEDAPAY:\n";
    $result = \App\FedaPayHelper::createCustomer($testUser);
    
    if ($result['success']) {
        echo "✅ SUCCÈS:\n";
        echo "- Customer ID: " . $result['customer_id'] . "\n";
        echo "- Existing: " . ($result['existing'] ? 'OUI' : 'NON') . "\n";
        
        if (!$result['existing']) {
            echo "- NOUVEAU CLIENT CRÉÉ avec les vraies données!\n";
        } else {
            echo "- Client existant réutilisé (données identiques)\n";
        }
    } else {
        echo "❌ ERREUR: " . ($result['error'] ?? 'Inconnue') . "\n";
    }
    
    echo "\n=== VÉRIFICATION PAYLOAD ENVOYÉ ===\n";
    echo "Le payload envoyé à FedaPay contiendra:\n";
    echo "- firstname: " . ($testUser->prenom ?: $testUser->name) . "\n";
    echo "- lastname: " . $testUser->name . "\n";
    echo "- email: " . $testUser->email . "\n";
    
} catch (Exception $e) {
    echo 'ERREUR: ' . $e->getMessage();
}
