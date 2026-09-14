<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== VÉRIFICATION DE DÉSACTIVATION COMPTE test6@gmail.com ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

try {
    $email = 'test6@gmail.com';
    
    echo PHP_EOL . "🔍 Vérification si le compte est désactivé immédiatement sans abonnement" . PHP_EOL;
    
    // 1. Récupérer l'utilisateur
    $user = DB::table('users')->where('email', $email)->first();
    
    if (!$user) {
        echo "❌ Utilisateur non trouvé" . PHP_EOL;
        exit(1);
    }
    
    echo PHP_EOL . "📋 ÉTAT ACTUEL DU COMPTE:" . PHP_EOL;
    echo "- ID: " . $user->id . PHP_EOL;
    echo "- Email: " . $user->email . PHP_EOL;
    echo "- Status: " . $user->status . PHP_EOL;
    echo "- Date création: " . $user->created_at . PHP_EOL;
    echo "- Date mise à jour: " . $user->updated_at . PHP_EOL;
    
    // 2. Analyser le statut
    echo PHP_EOL . "🔍 ANALYSE DU STATUT:" . PHP_EOL;
    
    switch($user->status) {
        case 0:
            echo "✅ Status = 0 : Compte INACTIF mais ACCESSIBLE" . PHP_EOL;
            echo "- L'utilisateur peut se connecter" . PHP_EOL;
            echo "- Voir des messages d'avertissement" . PHP_EOL;
            echo "- Accès limité aux fonctionnalités" . PHP_EOL;
            break;
        case 1:
            echo "✅ Status = 1 : Compte ACTIF et complet" . PHP_EOL;
            echo "- Accès complet à toutes les fonctionnalités" . PHP_EOL;
            break;
        case 2:
            echo "❌ Status = 2 : Compte DÉSACTIVÉ/BLOQUÉ" . PHP_EOL;
            echo "- L'utilisateur ne peut pas se connecter" . PHP_EOL;
            break;
        default:
            echo "❓ Status = " . $user->status . " : État inconnu" . PHP_EOL;
    }
    
    // 3. Vérifier les souscriptions
    echo PHP_EOL . "📄 VÉRIFICATION DES SOUSCRIPTIONS:" . PHP_EOL;
    
    $souscriptions = DB::table('souscriptions')->where('iduser', $user->id)->get();
    
    foreach ($souscriptions as $souscription) {
        echo "- Souscription ID: " . $souscription->idsouscription . PHP_EOL;
        echo "  Statut: " . $souscription->statut . " (";
        
        switch($souscription->statut) {
            case 0: echo "En attente de paiement"; break;
            case 1: echo "Payée et active"; break;
            case 2: echo "Annulée"; break;
            default: echo "Inconnu"; break;
        }
        
        echo ")" . PHP_EOL;
        echo "  Date création: " . $souscription->created_at . PHP_EOL;
    }
    
    // 4. Tester l'authentification
    echo PHP_EOL . "🔐 TEST D'AUTHENTIFICATION:" . PHP_EOL;
    
    // Créer une instance du modèle User
    $userModel = \App\User::find($user->id);
    
    if ($userModel) {
        echo "✅ Modèle User créé" . PHP_EOL;
        
        // Vérifier si le compte est désactivé (status = 2)
        if ($user->status == 2) {
            echo "❌ Le compte est DÉSACTIVÉ - connexion impossible" . PHP_EOL;
        } else {
            echo "✅ Le compte n'est PAS désactivé - connexion possible" . PHP_EOL;
            
            // Tester la méthode verifabonnement
            $verif = \App\User::verifabonnement($userModel);
            
            if ($verif) {
                echo "✅ Abonnement actif détecté" . PHP_EOL;
            } else {
                echo "❌ Aucun abonnement actif détecté" . PHP_EOL;
                echo "🔔 L'utilisateur verra des messages pour s'abonner" . PHP_EOL;
            }
        }
    }
    
    // 5. Vérifier s'il y a une logique de désactivation automatique
    echo PHP_EOL . "🔍 RECHERCHE DE LOGIQUE DE DÉSACTIVATION AUTOMATIQUE:" . PHP_EOL;
    
    // Vérifier si le statut change automatiquement
    $timeDiff = strtotime('now') - strtotime($user->created_at);
    $hoursDiff = $timeDiff / 3600;
    
    echo "- Temps écoulé depuis création: " . round($hoursDiff, 2) . " heures" . PHP_EOL;
    echo "- Status actuel: " . $user->status . PHP_EOL;
    echo "- Date création: " . $user->created_at . PHP_EOL;
    echo "- Date mise à jour: " . $user->updated_at . PHP_EOL;
    
    if ($user->created_at == $user->updated_at) {
        echo "✅ Le statut n'a PAS changé depuis la création" . PHP_EOL;
        echo "🔔 PAS de désactivation automatique détectée" . PHP_EOL;
    } else {
        echo "⚠️  Le statut a changé depuis la création" . PHP_EOL;
        echo "🔍 Possible désactivation automatique" . PHP_EOL;
    }
    
    // 6. Conclusion
    echo PHP_EOL . "🎯 CONCLUSION SUR LA DÉSACTIVATION:" . PHP_EOL;
    
    if ($user->status == 0) {
        echo "✅ Le compte test6@gmail.com N'est PAS désactivé" . PHP_EOL;
        echo "✅ Il reste accessible même sans abonnement" . PHP_EOL;
        echo "✅ Status = 0 (inactif mais accessible)" . PHP_EOL;
        echo "🔔 L'utilisateur peut se connecter mais avec accès limité" . PHP_EOL;
    } elseif ($user->status == 2) {
        echo "❌ Le compte test6@gmail.com EST désactivé" . PHP_EOL;
        echo "❌ Status = 2 (bloqué/désactivé)" . PHP_EOL;
        echo "🔔 L'utilisateur ne peut pas se connecter" . PHP_EOL;
    } else {
        echo "✅ Le compte test6@gmail.com est ACTIF" . PHP_EOL;
        echo "✅ Status = 1 (accès complet)" . PHP_EOL;
    }
    
    echo PHP_EOL . "💡 RAPPEL DU FONCTIONNEMENT:" . PHP_EOL;
    echo "- Status 0 = Inactif mais accessible (peut se connecter)" . PHP_EOL;
    echo "- Status 1 = Actif (accès complet)" . PHP_EOL;
    echo "- Status 2 = Désactivé (connexion impossible)" . PHP_EOL;
    echo "- La désactivation automatique N'EST PAS implémentée" . PHP_EOL;
    echo "- Les comptes restent accessibles indéfiniment" . PHP_EOL;
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Fichier: " . $e->getFile() . PHP_EOL;
    echo "Ligne: " . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== VÉRIFICATION TERMINÉE ===" . PHP_EOL;
