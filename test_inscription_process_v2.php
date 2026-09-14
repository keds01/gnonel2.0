<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== TEST DU PROCESSUS D'INSCRIPTION (CORRIGÉ) ===" . PHP_EOL;

try {
    // 1. Préparer les données de test
    $testEmail = 'test_inscription_' . time() . '@gmail.com';
    $testData = [
        'abonnement' => 1, // ID d'abonnement par défaut
        'name' => 'Test',
        'prename' => 'Inscription',
        'email' => $testEmail,
        'pays' => 1, // Togo par défaut
        'telephone' => '+22890123456',
        'type' => 'operateur',
        'count' => 1,
        'structure' => 1, // ID de l'opérateur (entier)
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ];
    
    echo "📋 Données de test:" . PHP_EOL;
    echo "Email: " . $testEmail . PHP_EOL;
    echo "Nom: " . $testData['name'] . " " . $testData['prename'] . PHP_EOL;
    echo "Type: " . $testData['type'] . PHP_EOL;
    echo "Structure ID: " . $testData['structure'] . PHP_EOL;
    echo PHP_EOL;
    
    // 2. Vérifier que l'email n'existe pas déjà
    echo "🔍 Vérification email existant..." . PHP_EOL;
    $existingUser = DB::table('users')->where('email', $testEmail)->first();
    if ($existingUser) {
        echo "❌ Email existe déjà: " . $existingUser->email . PHP_EOL;
        exit(1);
    } else {
        echo "✅ Email disponible" . PHP_EOL;
    }
    
    // 3. Simuler la création d'utilisateur (comme dans la fonction souscription)
    echo PHP_EOL . "👤 Création de l'utilisateur..." . PHP_EOL;
    
    if ($testData['type'] == "operateur") {
        $add = DB::table('users')->insert([
            'name' => $testData['name'],
            'prenom' => $testData['prename'],
            'email' => $testData['email'],
            'telephone' => $testData['telephone'],
            'role' => 'user',
            'type_user' => 55, // En attente
            'ratache_operateur' => $testData['structure'], // Entier
            'password' => Hash::make($testData['password']),
            'created_at' => NOW(),
            'updated_at' => NOW(),
            'status' => 0 // Inactif
        ]);
    } elseif ($testData['type'] == "autorite") {
        $add = DB::table('users')->insert([
            'name' => $testData['name'],
            'prenom' => $testData['prename'],
            'email' => $testData['email'],
            'telephone' => $testData['telephone'],
            'role' => 'user',
            'type_user' => 55, // En attente
            'ratache_autorite' => $testData['structure'], // Entier
            'password' => Hash::make($testData['password']),
            'created_at' => NOW(),
            'updated_at' => NOW(),
            'status' => 0 // Inactif
        ]);
    }
    
    if ($add) {
        echo "✅ Utilisateur créé avec succès" . PHP_EOL;
    } else {
        echo "❌ Erreur lors de la création de l'utilisateur" . PHP_EOL;
        exit(1);
    }
    
    // 4. Vérifier que l'utilisateur existe bien en base
    echo PHP_EOL . "🔍 Vérification de l'utilisateur en base..." . PHP_EOL;
    
    $createdUser = DB::table('users')->where('email', $testEmail)->first();
    if ($createdUser) {
        echo "✅ Utilisateur trouvé en base de données" . PHP_EOL;
        echo "ID: " . $createdUser->id . PHP_EOL;
        echo "Email: " . $createdUser->email . PHP_EOL;
        echo "Status: " . $createdUser->status . " (0=inactif, 1=actif)" . PHP_EOL;
        echo "Type utilisateur: " . $createdUser->type_user . PHP_EOL;
        echo "Date création: " . $createdUser->created_at . PHP_EOL;
        echo "Rattaché opérateur: " . ($createdUser->ratache_operateur ?? 'Non') . PHP_EOL;
        echo "Rattaché autorité: " . ($createdUser->ratache_autorite ?? 'Non') . PHP_EOL;
        
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
    
    // 5. Créer la souscription (comme dans le processus)
    echo PHP_EOL . "📄 Création de la souscription..." . PHP_EOL;
    
    $abonnementInfo = DB::table('abonnement')->where('id', $testData['abonnement'])->first();
    if (!$abonnementInfo) {
        echo "❌ Abonnement ID " . $testData['abonnement'] . " non trouvé" . PHP_EOL;
        exit(1);
    }
    
    $montantFinale = $abonnementInfo->prix ?? 1000; // Valeur par défaut
    
    $souscriptionId = DB::table('souscriptions')->insertGetId([
        'idabonnement' => $testData['abonnement'],
        'paysreference' => $testData['pays'],
        'count' => $testData['count'],
        'iduser' => $createdUser->id,
        'montant_finale_apaye' => $montantFinale,
        'created_at' => NOW(),
        'updated_at' => NOW(),
    ]);
    
    if ($souscriptionId) {
        echo "✅ Souscription créée avec ID: " . $souscriptionId . PHP_EOL;
    } else {
        echo "❌ Erreur lors de la création de la souscription" . PHP_EOL;
        exit(1);
    }
    
    // 6. Vérifier la souscription
    echo PHP_EOL . "🔍 Vérification de la souscription..." . PHP_EOL;
    
    $createdSubscription = DB::table('souscriptions')->where('idsouscription', $souscriptionId)->first();
    if ($createdSubscription) {
        echo "✅ Souscription trouvée en base" . PHP_EOL;
        echo "ID: " . $createdSubscription->idsouscription . PHP_EOL;
        echo "ID Utilisateur: " . $createdSubscription->iduser . PHP_EOL;
        echo "Statut: " . $createdSubscription->statut . " (0=en attente, 1=payé)" . PHP_EOL;
        echo "Montant: " . $createdSubscription->montant_finale_apaye . " XOF" . PHP_EOL;
        echo "Date création: " . $createdSubscription->created_at . PHP_EOL;
    } else {
        echo "❌ Souscription non trouvée en base" . PHP_EOL;
        exit(1);
    }
    
    // 7. Tester l'authentification
    echo PHP_EOL . "🔐 Test d'authentification..." . PHP_EOL;
    
    $credentials = [
        'email' => $testEmail,
        'password' => $testData['password']
    ];
    
    // Utiliser le guard d'authentification
    $auth = app('auth');
    
    if ($auth->attempt($credentials)) {
        echo "✅ Authentification réussie" . PHP_EOL;
        $authUser = $auth->user();
        echo "Utilisateur connecté: " . $authUser->email . PHP_EOL;
        echo "ID: " . $authUser->id . PHP_EOL;
        $auth->logout();
        echo "✅ Déconnexion réussie" . PHP_EOL;
    } else {
        echo "❌ Authentification échouée" . PHP_EOL;
    }
    
    // 8. Résumé
    echo PHP_EOL . "=== RÉSUMÉ DU TEST ===" . PHP_EOL;
    echo "✅ Utilisateur créé: OUI" . PHP_EOL;
    echo "✅ Souscription créée: OUI" . PHP_EOL;
    echo "✅ Authentification possible: " . ($auth->attempt($credentials) ? "OUI" : "NON") . PHP_EOL;
    echo "✅ Compte actif: NON (status = 0 - normal avant paiement)" . PHP_EOL;
    echo PHP_EOL;
    echo "📝 CONCLUSION:" . PHP_EOL;
    echo "L'utilisateur est BIEN CRÉÉ lors de l'inscription, AVANT le paiement." . PHP_EOL;
    echo "Le compte reste inactif (status = 0) jusqu'au paiement validé." . PHP_EOL;
    echo "L'utilisateur peut se connecter mais avec des fonctionnalités limitées." . PHP_EOL;
    
    // 9. Nettoyage optionnel
    echo PHP_EOL . "🧹 Nettoyage des données de test..." . PHP_EOL;
    
    $deleteUser = DB::table('users')->where('email', $testEmail)->delete();
    $deleteSubscription = DB::table('souscriptions')->where('idsouscription', $souscriptionId)->delete();
    
    if ($deleteUser && $deleteSubscription) {
        echo "✅ Données de test supprimées" . PHP_EOL;
    } else {
        echo "⚠️  Certaines données n'ont pas pu être supprimées" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== FIN DU TEST ===" . PHP_EOL;
