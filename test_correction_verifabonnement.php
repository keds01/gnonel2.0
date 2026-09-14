<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== TEST DE LA CORRECTION verifabonnement() ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;

try {
    $email = 'test6@gmail.com';
    
    echo PHP_EOL . "🔍 Test avec l'utilisateur: " . $email . PHP_EOL;
    
    // 1. Récupérer l'utilisateur
    $user = DB::table('users')->where('email', $email)->first();
    
    if (!$user) {
        echo "❌ Utilisateur non trouvé" . PHP_EOL;
        exit(1);
    }
    
    echo "✅ Utilisateur trouvé (ID: " . $user->id . ")" . PHP_EOL;
    
    // 2. Créer une instance du modèle User
    $userModel = \App\User::find($user->id);
    
    if (!$userModel) {
        echo "❌ Impossible de créer le modèle User" . PHP_EOL;
        exit(1);
    }
    
    echo "✅ Modèle User créé" . PHP_EOL;
    
    // 3. Tester la méthode verifabonnement corrigée
    echo PHP_EOL . "🔍 Test de la méthode verifabonnement() corrigée:" . PHP_EOL;
    
    $verifResult = \App\User::verifabonnement($userModel);
    
    if ($verifResult) {
        echo "❌ PROBLÈME : La méthode retourne encore un résultat" . PHP_EOL;
        echo "- ID Souscription: " . $verifResult->idsouscription . PHP_EOL;
        echo "- Statut: " . $verifResult->statut . PHP_EOL;
        echo "- Abonnement: " . $verifResult->libelle . PHP_EOL;
        echo "🔴 La correction n'a pas fonctionné" . PHP_EOL;
    } else {
        echo "✅ SUCCÈS : La méthode retourne null" . PHP_EOL;
        echo "🎉 La correction fonctionne ! L'utilisateur sans paiement n'a plus d'abonnement détecté" . PHP_EOL;
    }
    
    // 4. Vérifier manuellement la souscription
    echo PHP_EOL . "🔍 Vérification manuelle de la souscription:" . PHP_EOL;
    
    $souscription = DB::table('souscriptions')
        ->where('iduser', $user->id)
        ->where('statut', 1) // Uniquement les actifs
        ->join('abonnement', 'abonnement.id', '=', 'souscriptions.idabonnement')
        ->first();
    
    if ($souscription) {
        echo "❌ Souscription active trouvée manuellement" . PHP_EOL;
    } else {
        echo "✅ Aucune souscription active trouvée manuellement" . PHP_EOL;
    }
    
    // 5. Vérifier toutes les souscriptions de l'utilisateur
    echo PHP_EOL . "📊 Toutes les souscriptions de l'utilisateur:" . PHP_EOL;
    
    $allSouscriptions = DB::table('souscriptions')
        ->where('iduser', $user->id)
        ->join('abonnement', 'abonnement.id', '=', 'souscriptions.idabonnement')
        ->select('souscriptions.idsouscription', 'souscriptions.statut', 'abonnement.libelle')
        ->get();
    
    foreach ($allSouscriptions as $sub) {
        echo "- ID: " . $sub->idsouscription . ", Statut: " . $sub->statut . ", Abonnement: " . $sub->libelle . PHP_EOL;
    }
    
    // 6. Tester avec un utilisateur qui a un abonnement actif
    echo PHP_EOL . "🔍 Test avec un utilisateur ayant un abonnement actif:" . PHP_EOL;
    
    $userActif = DB::table('souscriptions')
        ->where('souscriptions.statut', 1)
        ->join('users', 'users.id', '=', 'souscriptions.iduser')
        ->select('users.id', 'users.email')
        ->first();
    
    if ($userActif) {
        echo "✅ Utilisateur actif trouvé: " . $userActif->email . PHP_EOL;
        
        $userActifModel = \App\User::find($userActif->id);
        $verifActif = \App\User::verifabonnement($userActifModel);
        
        if ($verifActif) {
            echo "✅ L'utilisateur actif a bien un abonnement détecté" . PHP_EOL;
            echo "- ID Souscription: " . $verifActif->idsouscription . PHP_EOL;
            echo "- Statut: " . $verifActif->statut . PHP_EOL;
        } else {
            echo "❌ L'utilisateur actif n'a pas d'abonnement détecté (problème)" . PHP_EOL;
        }
    } else {
        echo "⚠️  Aucun utilisateur avec abonnement actif trouvé pour le test" . PHP_EOL;
    }
    
    echo PHP_EOL . "🎯 CONCLUSION:" . PHP_EOL;
    
    if (!$verifResult) {
        echo "✅ CORRECTION RÉUSSIE !" . PHP_EOL;
        echo "- test6@gmail.com n'a plus d'accès complet" . PHP_EOL;
        echo "- Il verra un message d'avertissement pour paiement" . PHP_EOL;
        echo "- Les utilisateurs payés conservent leur accès" . PHP_EOL;
    } else {
        echo "❌ CORRECTION ÉCHOUÉE" . PHP_EOL;
        echo "- La méthode verifabonnement() retourne encore un résultat" . PHP_EOL;
        echo "- Il faut vérifier la syntaxe ou rafraîchir le cache" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Fichier: " . $e->getFile() . PHP_EOL;
    echo "Ligne: " . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== TEST TERMINÉ ===" . PHP_EOL;
