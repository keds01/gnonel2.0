<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== TEST DE CONFIGURATION FEDAPAY SANDBOX ===" . PHP_EOL;

try {
    // 1. Vérifier la configuration
    echo "1. VÉRIFICATION DE LA CONFIGURATION :" . PHP_EOL;
    echo "-----------------------------------" . PHP_EOL;
    
    $environment = env('FEDAPAY_ENVIRONMENT', 'sandbox');
    $secretKey = env('FEDAPAY_SECRET_KEY', '');
    $publicKey = env('FEDAPAY_PUBLIC_KEY', '');
    $webhookSecret = env('FEDAPAY_WEBHOOK_SECRET', '');
    $baseUrl = App\FedaPayHelper::getBaseUrl();
    
    echo "Environnement: " . $environment . PHP_EOL;
    echo "Clé secrète: " . (empty($secretKey) ? 'NON CONFIGURÉE' : substr($secretKey, 0, 15) . '...') . PHP_EOL;
    echo "Clé publique: " . (empty($publicKey) ? 'NON CONFIGURÉE' : substr($publicKey, 0, 15) . '...') . PHP_EOL;
    echo "Secret webhook: " . (empty($webhookSecret) ? 'NON CONFIGURÉ' : substr($webhookSecret, 0, 15) . '...') . PHP_EOL;
    echo "URL de base API: " . $baseUrl . PHP_EOL;
    
    $isConfigured = App\FedaPayHelper::isConfigured();
    echo "Configuration FedaPay: " . ($isConfigured ? '✓ OK' : '✗ ERREUR') . PHP_EOL . PHP_EOL;
    
    if (!$isConfigured) {
        echo "ERREUR: La configuration FedaPay est incomplète!" . PHP_EOL;
        exit(1);
    }
    
    // 2. Test de connexion API
    echo "2. TEST DE CONNEXION À L'API FEDAPAY :" . PHP_EOL;
    echo "----------------------------------------" . PHP_EOL;
    
    $client = new GuzzleHttp\Client(['timeout' => 30, 'verify' => false]);
    
    try {
        $response = $client->get($baseUrl . '/customers', [
            'headers' => [
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
            ]
        ]);
        
        echo "Connexion API: ✓ SUCCÈS" . PHP_EOL;
        echo "Code HTTP: " . $response->getStatusCode() . PHP_EOL;
        
        $data = json_decode($response->getBody(), true);
        $customerCount = isset($data['v1/customers']) ? count($data['v1/customers']) : 0;
        echo "Nombre de clients existants: " . $customerCount . PHP_EOL . PHP_EOL;
        
    } catch (GuzzleHttp\Exception\ClientException $e) {
        echo "Connexion API: ✗ ERREUR" . PHP_EOL;
        echo "Code HTTP: " . $e->getCode() . PHP_EOL;
        echo "Message: " . $e->getMessage() . PHP_EOL;
        
        $response = $e->getResponse();
        if ($response) {
            echo "Détail: " . $response->getBody()->getContents() . PHP_EOL;
        }
        echo PHP_EOL;
    }
    
    // 3. Test de création de client
    echo "3. TEST DE CRÉATION DE CLIENT :" . PHP_EOL;
    echo "------------------------------" . PHP_EOL;
    
    $testEmail = 'test_' . time() . '@sandbox.fedapay';
    $testUser = new App\User([
        'name' => 'Test Sandbox',
        'prenom' => 'FedaPay',
        'email' => $testEmail,
        'telephone' => '+22890123456'
    ]);
    
    echo "Email de test: " . $testEmail . PHP_EOL;
    
    $customerResult = App\FedaPayHelper::createCustomer($testUser);
    
    if ($customerResult['success']) {
        echo "Création client: ✓ SUCCÈS" . PHP_EOL;
        echo "ID Client: " . $customerResult['customer_id'] . PHP_EOL;
        echo "Client existant: " . ($customerResult['existing'] ?? false ? 'Oui' : 'Non') . PHP_EOL;
    } else {
        echo "Création client: ✗ ERREUR" . PHP_EOL;
        echo "Erreur: " . ($customerResult['error'] ?? 'Inconnue') . PHP_EOL;
    }
    echo PHP_EOL;
    
    // 4. Test de création de transaction (si client créé)
    if (isset($customerResult['success']) && $customerResult['success'] && isset($customerResult['customer_id'])) {
        echo "4. TEST DE CRÉATION DE TRANSACTION :" . PHP_EOL;
        echo "----------------------------------" . PHP_EOL;
        
        $transactionId = 'TEST_' . time();
        $amount = 100; // 100 XOF pour le test
        
        echo "ID Transaction test: " . $transactionId . PHP_EOL;
        echo "Montant test: " . $amount . " XOF" . PHP_EOL;
        
        $paymentResult = App\FedaPayHelper::generatePaymentLink(
            $transactionId,
            $amount,
            'Test transaction sandbox',
            url('/return-subscription'),
            url('/cancel-subscription'),
            $testUser
        );
        
        if (isset($paymentResult['data']['payment_url'])) {
            echo "Création transaction: ✓ SUCCÈS" . PHP_EOL;
            echo "URL de paiement: " . $paymentResult['data']['payment_url'] . PHP_EOL;
            echo "Référence: " . ($paymentResult['data']['tx_reference'] ?? 'N/A') . PHP_EOL;
            echo "ID Transaction FedaPay: " . ($paymentResult['data']['fedapay_transaction_id'] ?? 'N/A') . PHP_EOL;
        } else {
            echo "Création transaction: ✗ ERREUR" . PHP_EOL;
            echo "Erreur: " . ($paymentResult['error'] ?? 'Inconnue') . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== RÉSUMÉ DU TEST ===" . PHP_EOL;
    echo "Configuration: " . ($isConfigured ? '✓ OK' : '✗ ERREUR') . PHP_EOL;
    echo "Mode: " . $environment . PHP_EOL;
    echo "API: " . $baseUrl . PHP_EOL;
    
    if ($isConfigured) {
        echo "Votre configuration FedaPay Sandbox est prête à être utilisée!" . PHP_EOL;
    } else {
        echo "Veuillez vérifier votre configuration FedaPay dans le fichier .env" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "ERREUR CRITIQUE: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== FIN DU TEST ===" . PHP_EOL;
