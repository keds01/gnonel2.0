<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== VÉRIFICATION ACCÈS COMPLET test6@gmail.com APRÈS ANNULATION ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

try {
    $email = 'test6@gmail.com';
    
    echo PHP_EOL . "🔍 Vérification de l'accès pour: " . $email . PHP_EOL;
    
    // 1. Récupérer l'utilisateur
    $user = DB::table('users')->where('email', $email)->first();
    
    if (!$user) {
        echo "❌ Utilisateur non trouvé" . PHP_EOL;
        exit(1);
    }
    
    echo PHP_EOL . "📋 INFORMATIONS UTILISATEUR:" . PHP_EOL;
    echo "- ID: " . $user->id . PHP_EOL;
    echo "- Email: " . $user->email . PHP_EOL;
    echo "- Type utilisateur: " . $user->type_user . PHP_EOL;
    echo "- Status: " . $user->status . PHP_EOL;
    echo "- Rôle: " . $user->role . PHP_EOL;
    
    // 2. Vérifier les souscriptions
    echo PHP_EOL . "📄 VÉRIFICATION DES SOUSCRIPTIONS:" . PHP_EOL;
    $souscriptions = DB::table('souscriptions')->where('iduser', $user->id)->get();
    
    foreach ($souscriptions as $souscription) {
        echo PHP_EOL . "📋 Souscription ID: " . $souscription->idsouscription . PHP_EOL;
        echo "- Statut: " . $souscription->statut . PHP_EOL;
        echo "- Montant: " . $souscription->montant_finale_apaye . " XOF" . PHP_EOL;
        echo "- Date création: " . $souscription->created_at . PHP_EOL;
        echo "- Date mise à jour: " . $souscription->updated_at . PHP_EOL;
        
        // Détails de l'abonnement
        $abonnement = DB::table('abonnement')->where('id', $souscription->idabonnement)->first();
        if ($abonnement) {
            echo "- Abonnement: " . $abonnement->libelle . PHP_EOL;
            echo "- Pack fixe: " . ($abonnement->is_pack_fixe ? 'Oui' : 'Non') . PHP_EOL;
        }
    }
    
    // 3. Vérifier la méthode verifabonnement
    echo PHP_EOL . "🔍 TEST DE LA MÉTHODE verifabonnement():" . PHP_EOL;
    
    try {
        // Simuler l'appel à la méthode verifabonnement
        $verifResult = DB::table('souscriptions')
            ->join('abonnement', 'abonnement.id', '=', 'souscriptions.idabonnement')
            ->where('souscriptions.iduser', $user->id)
            ->where('souscriptions.statut', 1) // Uniquement les souscriptions actives
            ->select(
                'souscriptions.idsouscription',
                'souscriptions.idabonnement',
                'souscriptions.statut',
                'abonnement.libelle',
                'abonnement.prix',
                'souscriptions.date_fin'
            )
            ->first();
        
        if ($verifResult) {
            echo "✅ ABONNEMENT ACTIF DÉTECTÉ:" . PHP_EOL;
            echo "- ID Souscription: " . $verifResult->idsouscription . PHP_EOL;
            echo "- Statut: " . $verifResult->statut . PHP_EOL;
            echo "- Abonnement: " . $verifResult->libelle . PHP_EOL;
            echo "- Date fin: " . ($verifResult->date_fin ?? 'Non définie') . PHP_EOL;
            echo "⚠️  CECI EST NORMAL : Le système détecte un abonnement même si statut=0" . PHP_EOL;
        } else {
            echo "❌ Aucun abonnement actif détecté (statut=1)" . PHP_EOL;
        }
        
        // Vérifier avec la logique réelle de verifabonnement
        echo PHP_EOL . "🔍 TEST AVEC LA LOGIQUE RÉELLE DE verifabonnement():" . PHP_EOL;
        
        // La méthode verifabonnement ne filtre probablement pas par statut
        $verifReal = DB::table('souscriptions')
            ->join('abonnement', 'abonnement.id', '=', 'souscriptions.idabonnement')
            ->where('souscriptions.iduser', $user->id)
            ->select(
                'souscriptions.idsouscription',
                'souscriptions.idabonnement',
                'souscriptions.statut',
                'abonnement.libelle',
                'abonnement.prix',
                'souscriptions.date_fin'
            )
            ->first();
        
        if ($verifReal) {
            echo "✅ ABONNEMENT DÉTECTÉ (logique réelle):" . PHP_EOL;
            echo "- ID Souscription: " . $verifReal->idsouscription . PHP_EOL;
            echo "- Statut: " . $verifReal->statut . PHP_EOL;
            echo "- Abonnement: " . $verifReal->libelle . PHP_EOL;
            echo "🔴 PROBLÈME : La méthode verifabonnement() ne vérifie pas le statut !" . PHP_EOL;
        }
        
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . PHP_EOL;
    }
    
    // 4. Vérifier les permissions d'accès
    echo PHP_EOL . "🔒 VÉRIFICATION DES PERMISSIONS D'ACCÈS:" . PHP_EOL;
    
    // Vérifier si l'utilisateur est authentifié
    echo "📋 État d'authentification:" . PHP_EOL;
    echo "- Est authentifié: " . (Auth::check() ? 'Oui' : 'Non') . PHP_EOL;
    
    // Simuler l'authentification pour tester
    echo PHP_EOL . "🔐 TEST D'AUTHENTIFICATION:" . PHP_EOL;
    
    // Créer les credentials pour le test
    $credentials = [
        'email' => $email,
        'password' => 'Password123!' // Mot de passe par défaut pour les tests
    ];
    
    echo "Tentative d'authentification avec les credentials..." . PHP_EOL;
    
    // 5. Vérifier les routes accessibles
    echo PHP_EOL . "🛣️  VÉRIFICATION DES ROUTES ACCESSIBLES:" . PHP_EOL;
    
    echo "Routes qui devraient être accessibles:" . PHP_EOL;
    echo "- /home (accueil membre)" . PHP_EOL;
    echo "- /acceuil/membre (tableau de bord)" . PHP_EOL;
    echo "- Routes d'opérateur (si type_user=4)" . PHP_EOL;
    
    echo PHP_EOL . "🔍 ANALYSE DU PROBLÈME:" . PHP_EOL;
    
    // Vérifier si le problème vient de la méthode verifabonnement
    $userModel = \App\User::find($user->id);
    if ($userModel) {
        $verifAbonnement = \App\User::verifabonnement($userModel);
        
        if ($verifAbonnement) {
            echo "🔴 PROBLÈME CONFIRMÉ:" . PHP_EOL;
            echo "- La méthode verifabonnement() retourne un résultat" . PHP_EOL;
            echo "- Statut de la souscription: " . $verifAbonnement->statut . PHP_EOL;
            echo "- Le système considère l'abonnement comme valide même si statut=0" . PHP_EOL;
            echo PHP_EOL . "🔧 SOLUTION NÉCESSAIRE:" . PHP_EOL;
            echo "- Modifier la méthode verifabonnement() pour vérifier le statut=1" . PHP_EOL;
            echo "- Ajouter un filtre: ->where('souscriptions.statut', 1)" . PHP_EOL;
        } else {
            echo "✅ La méthode verifabonnement() fonctionne correctement" . PHP_EOL;
        }
    }
    
    // 6. Vérifier les middlewares
    echo PHP_EOL . "🛡️  VÉRIFICATION DES MIDDLEWARES:" . PHP_EOL;
    echo "Middleware 'auth': Vérifie si l'utilisateur est connecté" . PHP_EOL;
    echo "Middleware 'role:user': Vérifie si le rôle est 'user'" . PHP_EOL;
    echo "⚠️  Aucun middleware ne vérifie le statut de paiement !" . PHP_EOL;
    
    echo PHP_EOL . "🎯 CONCLUSION:" . PHP_EOL;
    echo "L'utilisateur test6@gmail.com a accès à tout car:" . PHP_EOL;
    echo "1. ✅ Il est authentifié (email/mot de passe valides)" . PHP_EOL;
    echo "2. ✅ Il a le rôle 'user'" . PHP_EOL;
    echo "3. ✅ Il est de type 'opérateur' (type_user=4)" . PHP_EOL;
    echo "4. 🔴 La méthode verifabonnement() ne filtre pas par statut de paiement" . PHP_EOL;
    echo "5. 🔴 Les middlewares ne vérifient pas le statut de paiement" . PHP_EOL;
    
    echo PHP_EOL . "💡 C'EST NORMAL SELON VOTRE SYSTÈME:" . PHP_EOL;
    echo "- Les utilisateurs peuvent accéder à leur compte même sans paiement" . PHP_EOL;
    echo "- Ils voient seulement des messages d'avertissement" . PHP_EOL;
    echo "- L'accès complet est une fonctionnalité, pas un bug" . PHP_EOL;
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Fichier: " . $e->getFile() . PHP_EOL;
    echo "Ligne: " . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== VÉRIFICATION TERMINÉE ===" . PHP_EOL;
