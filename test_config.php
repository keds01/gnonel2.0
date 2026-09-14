<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

echo "=== TEST DE CONFIGURATION FEDAPAY ===\n\n";

try {
    // Test 1: Vérifier si les variables sont chargées
    echo "1. VARIABLES D'ENVIRONNEMENT:\n";
    echo "   FEDAPAY_ENVIRONMENT: " . env('FEDAPAY_ENVIRONMENT', 'NON DÉFINI') . "\n";
    echo "   FEDAPAY_SECRET_KEY: " . substr(env('FEDAPAY_SECRET_KEY', ''), 0, 15) . "...\n";
    echo "   FEDAPAY_WEBHOOK_SECRET: " . substr(env('FEDAPAY_WEBHOOK_SECRET', ''), 0, 15) . "...\n";
    
    // Test 2: Vérifier le helper FedaPay
    echo "\n2. TEST HELPER FEDAPAY:\n";
    $isConfigured = \App\FedaPayHelper::isConfigured();
    echo "   Helper configuré: " . ($isConfigured ? 'OUI' : 'NON') . "\n";
    
    if ($isConfigured) {
        $baseUrl = \App\FedaPayHelper::getBaseUrl();
        echo "   URL API: " . $baseUrl . "\n";
    }
    
    // Test 3: Vérifier la cohérence
    echo "\n3. VÉRIFICATION COHÉRENCE:\n";
    $env = env('FEDAPAY_ENVIRONMENT');
    $secretKey = env('FEDAPAY_SECRET_KEY');
    $webhookSecret = env('FEDAPAY_WEBHOOK_SECRET');
    
    echo "   Environnement: " . $env . "\n";
    echo "   Type clé API: " . (strpos($secretKey, 'sk_test_') === 0 ? 'TEST' : 'LIVE') . "\n";
    echo "   Type webhook: " . (strpos($webhookSecret, 'wh_test_') === 0 ? 'TEST' : 'LIVE') . "\n";
    
    if ($env === 'sandbox' && strpos($secretKey, 'sk_live_') === 0) {
        echo "   ⚠️ ERREUR: Clé LIVE en mode SANDBOX!\n";
    }
    
    if ($env === 'sandbox' && strpos($webhookSecret, 'wh_live_') === 0) {
        echo "   ⚠️ ERREUR: Secret LIVE en mode SANDBOX!\n";
    }
    
    echo "\n=== ÉTAT GLOBAL ===\n";
    echo "Statut: " . ($isConfigured ? 'PRÊT' : 'NON CONFIGURÉ') . "\n";
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}
