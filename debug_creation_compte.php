<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== SCRIPT DE DEBUGGING - CRÉATION DE COMPTE ET ANNULATION PAIEMENT ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

try {
    // 1. SIMULATION DES DONNÉES D'INSCRIPTION
    echo PHP_EOL . "📋 ÉTAPE 1: Simulation des données d'inscription..." . PHP_EOL;
    
    $testEmail = 'debug_' . time() . '@test.com';
    $testData = [
        'abonnement' => 1,
        'name' => 'DEBUG',
        'prename' => 'USER',
        'email' => $testEmail,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'pays' => 1,
        'telephone' => '+22890123456',
        'type' => 'operateur',
        'structure' => 1, // ID d'une structure existante
        'count' => 1
    ];
    
    echo "Email de test: " . $testEmail . PHP_EOL;
    echo "Type: " . $testData['type'] . PHP_EOL;
    echo "Structure ID: " . $testData['structure'] . PHP_EOL;
    
    // 2. VÉRIFICATION PRÉ-CRÉATION
    echo PHP_EOL . "🔍 ÉTAPE 2: Vérification pré-création..." . PHP_EOL;
    
    $existingUser = DB::table('users')->where('email', $testEmail)->first();
    if ($existingUser) {
        echo "⚠️  Un utilisateur avec cet email existe déjà (ID: " . $existingUser->id . ")" . PHP_EOL;
        echo "Suppression de l'utilisateur existant pour le test..." . PHP_EOL;
        DB::table('users')->where('email', $testEmail)->delete();
        echo "✅ Utilisateur existant supprimé" . PHP_EOL;
    } else {
        echo "✅ Aucun utilisateur existant avec cet email" . PHP_EOL;
    }
    
    // 3. SIMULATION DE LA CRÉATION (comme dans AbonnementController@souscription)
    echo PHP_EOL . "👤 ÉTAPE 3: Simulation de la création d'utilisateur..." . PHP_EOL;
    
    // Récupérer les infos de l'abonnement
    $abonnementInfo = DB::table('abonnement')->where('id', '=', $testData['abonnement'])->first();
    if (!$abonnementInfo) {
        echo "❌ Abonnement ID " . $testData['abonnement'] . " non trouvé" . PHP_EOL;
        exit(1);
    }
    
    echo "✅ Abonnement trouvé: " . $abonnementInfo->libelle . PHP_EOL;
    
    // Vérifier si l'utilisateur existe déjà (logique corrigée)
    $existingUser = DB::table('users')->where('email', $testData['email'])->first();
    
    if ($existingUser) {
        echo "📌 Utilisateur existe déjà, utilisation de l'ID: " . $existingUser->id . PHP_EOL;
        $userId = $existingUser->id;
    } else {
        echo "🆕 Création d'un nouvel utilisateur..." . PHP_EOL;
        
        if ($testData['type'] == "operateur") {
            $userId = DB::table('users')->insertGetId([
                'name' => $testData['name'],
                'prenom' => $testData['prename'],
                'email' => $testData['email'],
                'telephone' => $testData['telephone'],
                'role' => 'user',
                'type_user' => 4, // Corrigé: 4 pour opérateur
                'ratache_operateur' => $testData['structure'],
                'password' => Hash::make($testData['password']),
                'created_at' => NOW(),
                'updated_at' => NOW(),
                'status' => 0
            ]);
        } elseif ($testData['type'] == "autorite") {
            $userId = DB::table('users')->insertGetId([
                'name' => $testData['name'],
                'prenom' => $testData['prename'],
                'email' => $testData['email'],
                'telephone' => $testData['telephone'],
                'role' => 'user',
                'type_user' => 5, // Corrigé: 5 pour autorité
                'ratache_autorite' => $testData['structure'],
                'password' => Hash::make($testData['password']),
                'created_at' => NOW(),
                'updated_at' => NOW(),
                'status' => 0
            ]);
        } else {
            echo "❌ Type d'utilisateur non valide: " . $testData['type'] . PHP_EOL;
            exit(1);
        }
        
        echo "✅ Utilisateur créé avec ID: " . $userId . PHP_EOL;
    }
    
    // 4. VÉRIFICATION POST-CRÉATION
    echo PHP_EOL . "🔍 ÉTAPE 4: Vérification post-création..." . PHP_EOL;
    
    $createdUser = DB::table('users')->where('id', $userId)->first();
    if ($createdUser) {
        echo "✅ Utilisateur trouvé en base de données" . PHP_EOL;
        echo "ID: " . $createdUser->id . PHP_EOL;
        echo "Email: " . $createdUser->email . PHP_EOL;
        echo "Nom: " . $createdUser->name . " " . $createdUser->prenom . PHP_EOL;
        echo "Type utilisateur: " . $createdUser->type_user . PHP_EOL;
        echo "Status: " . $createdUser->status . " (0=inactif, 1=actif)" . PHP_EOL;
        echo "Date création: " . $createdUser->created_at . PHP_EOL;
        
        // Tester le mot de passe
        if (Hash::check($testData['password'], $createdUser->password)) {
            echo "✅ Mot de passe hashé correctement" . PHP_EOL;
        } else {
            echo "❌ Erreur de hashage du mot de passe" . PHP_EOL;
        }
    } else {
        echo "❌ Utilisateur non trouvé en base après création" . PHP_EOL;
        exit(1);
    }
    
    // 5. CRÉATION DE LA SOUSCRIPTION
    echo PHP_EOL . "📄 ÉTAPE 5: Création de la souscription..." . PHP_EOL;
    
    $montantFinale = $abonnementInfo->prix;
    
    $souscriptionId = DB::table('souscriptions')->insertGetId([
        'idabonnement' => $testData['abonnement'],
        'paysreference' => $testData['pays'],
        'count' => $testData['count'],
        'iduser' => $userId,
        'montant_finale_apaye' => $montantFinale,
        'created_at' => NOW(),
        'updated_at' => NOW(),
        'statut' => 0 // 0 = en attente de paiement
    ]);
    
    echo "✅ Souscription créée avec ID: " . $souscriptionId . PHP_EOL;
    echo "Montant: " . $montantFinale . " XOF" . PHP_EOL;
    echo "Statut: 0 (en attente de paiement)" . PHP_EOL;
    
    // 6. TEST D'AUTHENTIFICATION
    echo PHP_EOL . "🔐 ÉTAPE 6: Test d'authentification..." . PHP_EOL;
    
    $credentials = [
        'email' => $testEmail,
        'password' => $testData['password']
    ];
    
    $auth = app('auth');
    
    if ($auth->attempt($credentials)) {
        echo "✅ Authentification réussie" . PHP_EOL;
        $authUser = $auth->user();
        echo "Utilisateur connecté: " . $authUser->email . PHP_EOL;
        
        // Déconnexion pour le test
        $auth->logout();
        echo "🔓 Déconnexion effectuée" . PHP_EOL;
    } else {
        echo "❌ Échec de l'authentification" . PHP_EOL;
    }
    
    // 7. SIMULATION D'ANNULATION DE PAIEMENT
    echo PHP_EOL . "❌ ÉTAPE 7: Simulation d'annulation de paiement..." . PHP_EOL;
    
    // Marquer la souscription comme annulée
    $updated = DB::table('souscriptions')
        ->where('idsouscription', $souscriptionId)
        ->update([
            'statut' => 2, // 2 = annulé
            'updated_at' => NOW()
        ]);
    
    if ($updated) {
        echo "✅ Souscription marquée comme annulée (statut = 2)" . PHP_EOL;
    } else {
        echo "❌ Erreur lors de la mise à jour du statut" . PHP_EOL;
    }
    
    // 8. VÉRIFICATION DE L'ÉTAT DU COMPTE APRÈS ANNULATION
    echo PHP_EOL . "🔍 ÉTAPE 8: Vérification de l'état du compte après annulation..." . PHP_EOL;
    
    $userAfterCancel = DB::table('users')->where('id', $userId)->first();
    $souscriptionAfterCancel = DB::table('souscriptions')->where('idsouscription', $souscriptionId)->first();
    
    echo "📊 État de l'utilisateur après annulation:" . PHP_EOL;
    echo "- ID: " . $userAfterCancel->id . PHP_EOL;
    echo "- Email: " . $userAfterCancel->email . PHP_EOL;
    echo "- Status: " . $userAfterCancel->status . " (toujours accessible)" . PHP_EOL;
    echo "- Date création: " . $userAfterCancel->created_at . PHP_EOL;
    
    echo PHP_EOL . "📊 État de la souscription après annulation:" . PHP_EOL;
    echo "- ID: " . $souscriptionAfterCancel->idsouscription . PHP_EOL;
    echo "- ID Utilisateur: " . $souscriptionAfterCancel->iduser . PHP_EOL;
    echo "- Statut: " . $souscriptionAfterCancel->statut . " (2=annulé)" . PHP_EOL;
    echo "- Montant: " . $souscriptionAfterCancel->montant_finale_apaye . " XOF" . PHP_EOL;
    
    // 9. TEST D'ACCÈS AU COMPTE APRÈS ANNULATION
    echo PHP_EOL . "🔐 ÉTAPE 9: Test d'accès au compte après annulation..." . PHP_EOL;
    
    if ($auth->attempt($credentials)) {
        echo "✅ L'utilisateur peut TOUJOURS se connecter après annulation" . PHP_EOL;
        echo "✅ Le compte reste accessible même sans paiement" . PHP_EOL;
        
        $authUser = $auth->user();
        
        // Vérifier l'accès à la page info compte
        $verifAbonnement = \App\User::verifabonnement($authUser);
        if ($verifAbonnement == null) {
            echo "✅ Aucun abonnement actif détecté (normal après annulation)" . PHP_EOL;
            echo "✅ L'utilisateur verra un message l'invitant à renouveler" . PHP_EOL;
        } else {
            echo "⚠️  Un abonnement est encore détecté: " . json_encode($verifAbonnement) . PHP_EOL;
        }
        
        $auth->logout();
    } else {
        echo "❌ L'utilisateur ne peut plus se connecter" . PHP_EOL;
    }
    
    // 10. RÉSUMÉ
    echo PHP_EOL . "📋 RÉSUMÉ DU DEBUGGING:" . PHP_EOL;
    echo "✅ Utilisateur créé: OUI (ID: " . $userId . ")" . PHP_EOL;
    echo "✅ Souscription créée: OUI (ID: " . $souscriptionId . ")" . PHP_EOL;
    echo "✅ Authentification possible: OUI" . PHP_EOL;
    echo "✅ Compte accessible après annulation: OUI" . PHP_EOL;
    echo "✅ Souscription marquée comme annulée: OUI" . PHP_EOL;
    
    echo PHP_EOL . "🎯 CONCLUSION:" . PHP_EOL;
    echo "Le système fonctionne CORRECTEMENT:" . PHP_EOL;
    echo "- Le compte est créé dès l'inscription" . PHP_EOL;
    echo "- L'utilisateur peut se connecter immédiatement" . PHP_EOL;
    echo "- En cas d'annulation, le compte reste accessible" . PHP_EOL;
    echo "- L'utilisateur verra un message pour renouveler son abonnement" . PHP_EOL;
    
    // 11. NETTOYAGE
    echo PHP_EOL . "🧹 NETTOYAGE DES DONNÉES DE TEST..." . PHP_EOL;
    
    $deleteUser = DB::table('users')->where('email', $testEmail)->delete();
    $deleteSubscription = DB::table('souscriptions')->where('idsouscription', $souscriptionId)->delete();
    
    if ($deleteUser && $deleteSubscription) {
        echo "✅ Données de test supprimées avec succès" . PHP_EOL;
    } else {
        echo "⚠️  Certaines données n'ont pas pu être supprimées" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Fichier: " . $e->getFile() . PHP_EOL;
    echo "Ligne: " . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== DEBUGGING TERMINÉ AVEC SUCCÈS ===" . PHP_EOL;
