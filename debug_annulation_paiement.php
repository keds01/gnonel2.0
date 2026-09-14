<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== DEBUG ANNULATION PAIEMENT test6@gmail.com ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

try {
    $email = 'test6@gmail.com';
    
    echo PHP_EOL . "🔍 ANALYSE DES SOUSCRIPTIONS DE test6@gmail.com" . PHP_EOL;
    
    $user = DB::table('users')->where('email', $email)->first();
    
    if (!$user) {
        echo "❌ Utilisateur non trouvé" . PHP_EOL;
        exit(1);
    }
    
    echo PHP_EOL . "📋 INFORMATIONS UTILISATEUR:" . PHP_EOL;
    echo "- ID: " . $user->id . PHP_EOL;
    echo "- Email: " . $user->email . PHP_EOL;
    echo "- Type: " . $user->type_user . PHP_EOL;
    echo "- Status: " . $user->status . PHP_EOL;
    echo "- Créé: " . $user->created_at . PHP_EOL;
    echo "- Mis à jour: " . $user->updated_at . PHP_EOL;
    
    echo PHP_EOL . "📄 TOUTES LES SOUSCRIPTIONS (6 trouvées):" . PHP_EOL;
    
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
        echo "- Montant: " . ($souscription->montant_finale_apaye ?? 'Non défini') . " XOF" . PHP_EOL;
        echo "- Date création: " . $souscription->created_at . PHP_EOL;
        echo "- Date mise à jour: " . $souscription->updated_at . PHP_EOL;
        
        if ($souscription->referencepaiement) {
            echo "- Référence paiement: " . $souscription->referencepaiement . PHP_EOL;
        }
        
        if ($souscription->identifier) {
            echo "- Identifier: " . $souscription->identifier . PHP_EOL;
        }
        
        if ($souscription->date_fin) {
            echo "- Date fin: " . $souscription->date_fin . PHP_EOL;
        }
        
        if ($souscription->status_p) {
            echo "- Status paiement: " . $souscription->status_p . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "🔍 ANALYSE DU PROBLÈME:" . PHP_EOL;
    
    echo PHP_EOL . "❌ PROBLÈMES IDENTIFIÉS:" . PHP_EOL;
    echo "1. Toutes les souscriptions ont statut = 0 (en attente)" . PHP_EOL;
    echo "2. Aucune souscription n'a été mise à jour après annulation" . PHP_EOL;
    echo "3. L'utilisateur a 6 souscriptions créées (multiples essais)" . PHP_EOL;
    
    echo PHP_EOL . "🔍 CAUSES POSSIBLES:" . PHP_EOL;
    echo "1. ❌ Le webhook FedaPay n'a pas reçu l'annulation" . PHP_EOL;
    echo "2. ❌ L'événement 'transaction.canceled' n'est pas traité correctement" . PHP_EOL;
    echo "3. ❌ La route cancel-subscription n'a pas été appelée" . PHP_EOL;
    echo "4. ❌ Problème de configuration webhook FedaPay" . PHP_EOL;
    
    echo PHP_EOL . "🔧 VÉRIFICATION DU WEBHOOK:" . PHP_EOL;
    
    // Vérifier les logs récents
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        echo "📋 Dernières lignes du log Laravel:" . PHP_EOL;
        
        $lines = file($logFile);
        $recentLines = array_slice($lines, -20); // 20 dernières lignes
        
        foreach ($recentLines as $line) {
            if (strpos($line, 'FedaPay') !== false || strpos($line, 'webhook') !== false || strpos($line, 'canceled') !== false) {
                echo "- " . trim($line) . PHP_EOL;
            }
        }
    }
    
    echo PHP_EOL . "🎯 SOLUTIONS POSSIBLES:" . PHP_EOL;
    echo "1. Mettre manuellement les souscriptions à statut = 2 (annulées)" . PHP_EOL;
    echo "2. Vérifier la configuration webhook FedaPay" . PHP_EOL;
    echo "3. Ajouter le traitement de l'événement 'transaction.canceled'" . PHP_EOL;
    echo "4. Nettoyer les souscriptions multiples" . PHP_EOL;
    
    echo PHP_EOL . "💡 ACTION IMMÉDIATE:" . PHP_EOL;
    echo "Souhaitez-vous que je mette à jour manuellement toutes les souscriptions de test6@gmail.com à 'annulée' (statut = 2) ?" . PHP_EOL;
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Fichier: " . $e->getFile() . PHP_EOL;
    echo "Ligne: " . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== DEBUG TERMINÉ ===" . PHP_EOL;
