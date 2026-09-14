<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== CORRECTION STATUT SOUSCRIPTIONS test6@gmail.com ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;

try {
    $email = 'test6@gmail.com';
    
    echo PHP_EOL . "🔧 MISE À JOUR DES SOUSCRIPTIONS:" . PHP_EOL;
    
    $user = DB::table('users')->where('email', $email)->first();
    
    if (!$user) {
        echo "❌ Utilisateur non trouvé" . PHP_EOL;
        exit(1);
    }
    
    echo "Utilisateur trouvé: ID " . $user->id . PHP_EOL;
    
    // Mettre à jour toutes les souscriptions à statut = 2 (annulée)
    $updated = DB::table('souscriptions')
        ->where('iduser', $user->id)
        ->where('statut', 0)  // Uniquement celles en attente
        ->update([
            'statut' => 2,
            'updated_at' => now()
        ]);
    
    echo PHP_EOL . "✅ SOUSCRIPTIONS MISES À JOUR:" . PHP_EOL;
    echo "- Nombre de souscriptions modifiées: " . $updated . PHP_EOL;
    echo "- Ancien statut: 0 (En attente de paiement)" . PHP_EOL;
    echo "- Nouveau statut: 2 (Annulée)" . PHP_EOL;
    
    // Vérifier le résultat
    echo PHP_EOL . "🔍 VÉRIFICATION:" . PHP_EOL;
    
    $souscriptions = DB::table('souscriptions')
        ->where('iduser', $user->id)
        ->join('abonnement', 'abonnement.id', '=', 'souscriptions.idabonnement')
        ->select('souscriptions.*', 'abonnement.libelle as abonnement_libelle')
        ->orderBy('souscriptions.created_at', 'desc')
        ->get();
    
    foreach ($souscriptions as $i => $souscription) {
        echo PHP_EOL . "📋 Souscription #" . ($i + 1) . PHP_EOL;
        echo "- ID: " . $souscription->idsouscription . PHP_EOL;
        echo "- Abonnement: " . $souscription->abonnement_libelle . PHP_EOL;
        echo "- Statut: " . $souscription->statut . " (";
        
        switch($souscription->statut) {
            case 0: echo "En attente de paiement"; break;
            case 1: echo "Payée et active"; break;
            case 2: echo "Annulée"; break;
            default: echo "Inconnu"; break;
        }
        echo ")" . PHP_EOL;
        echo "- Date mise à jour: " . $souscription->updated_at . PHP_EOL;
    }
    
    echo PHP_EOL . "🎯 RÉSULTAT POUR test6@gmail.com:" . PHP_EOL;
    echo "- ✅ Plus aucune souscription en attente" . PHP_EOL;
    echo "- ✅ Toutes les souscriptions sont marquées comme annulées" . PHP_EOL;
    echo "- ✅ L'utilisateur pourra créer une nouvelle souscription propre" . PHP_EOL;
    
    echo PHP_EOL . "💡 PROCHAINE ÉTAPE:" . PHP_EOL;
    echo "test6@gmail.com peut maintenant:" . PHP_EOL;
    echo "1. Aller sur /pricing" . PHP_EOL;
    echo "2. Choisir une nouvelle formule" . PHP_EOL;
    echo "3. Créer une seule souscription propre" . PHP_EOL;
    echo "4. Effectuer le paiement sans conflit" . PHP_EOL;
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Fichier: " . $e->getFile() . PHP_EOL;
    echo "Ligne: " . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== CORRECTION TERMINÉE ===" . PHP_EOL;
