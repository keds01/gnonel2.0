<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== VÉRIFICATION DES COMPTES UTILISATEURS ===" . PHP_EOL;

try {
    // 1. Vérifier les comptes créés récemment
    echo PHP_EOL . "1. COMPTES CRÉÉS RÉCEMMENT (derniers 7 jours):" . PHP_EOL;
    echo "--------------------------------------------" . PHP_EOL;
    
    $recentUsers = DB::table('users')
        ->where('created_at', '>=', now()->subDays(7))
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Nombre de comptes créés: " . $recentUsers->count() . PHP_EOL . PHP_EOL;
    
    if ($recentUsers->count() > 0) {
        foreach ($recentUsers as $user) {
            echo "ID: " . $user->id . PHP_EOL;
            echo "Nom: " . $user->name . " " . ($user->prenom ?? '') . PHP_EOL;
            echo "Email: " . $user->email . PHP_EOL;
            echo "Status: " . $user->status . " (" . ($user->status == 1 ? 'Actif' : 'Inactif') . ")" . PHP_EOL;
            echo "Type: " . $user->type_user . PHP_EOL;
            echo "Rôle: " . $user->role . PHP_EOL;
            echo "Date création: " . $user->created_at . PHP_EOL;
            echo "-------------------" . PHP_EOL;
        }
    }
    
    // 2. Vérifier les comptes avec problèmes potentiels
    echo PHP_EOL . "2. COMPTES AVEC PROBLÈMES POTENTIELS:" . PHP_EOL;
    echo "----------------------------------------" . PHP_EOL;
    
    // Comptes sans mot de passe hashé
    $noPassword = DB::table('users')
        ->whereNull('password')
        ->orWhere('password', '')
        ->count();
    
    if ($noPassword > 0) {
        echo "⚠️  " . $noPassword . " comptes sans mot de passe valide" . PHP_EOL;
    }
    
    // Comptes avec email dupliqué
    $duplicateEmails = DB::table('users')
        ->select('email', DB::raw('count(*) as count'))
        ->groupBy('email')
        ->having('count', '>', 1)
        ->get();
    
    if ($duplicateEmails->count() > 0) {
        echo "⚠️  Emails dupliqués:" . PHP_EOL;
        foreach ($duplicateEmails as $dup) {
            echo "- " . $dup->email . " (" . $dup->count . " comptes)" . PHP_EOL;
        }
    }
    
    // 3. Vérifier les tentatives de connexion récentes
    echo PHP_EOL . "3. TEST DE CONNEXION AVEC DIFFÉRENTS SCÉNARIOS:" . PHP_EOL;
    echo "------------------------------------------------" . PHP_EOL;
    
    // Prendre le premier utilisateur récent pour tester
    if ($recentUsers->count() > 0) {
        $testUser = $recentUsers->first();
        echo "Test avec l'utilisateur: " . $testUser->email . PHP_EOL;
        
        // Vérifier si le mot de passe est bien hashé
        $passwordInfo = password_get_info($testUser->password);
        echo "Type de hash: " . ($passwordInfo['algoName'] ?? 'Inconnu') . PHP_EOL;
        echo "Hash valide: " . ($passwordInfo['algo'] != null ? 'Oui' : 'Non') . PHP_EOL;
        
        // Tester avec un mot de passe incorrect
        $wrongPasswordTest = Hash::check('wrongpassword', $testUser->password);
        echo "Test mot de passe incorrect: " . ($wrongPasswordTest ? '❌ Problème' : '✅ Normal') . PHP_EOL;
    }
    
    // 4. Vérifier la configuration d'authentification
    echo PHP_EOL . "4. CONFIGURATION D'AUTHENTIFICATION:" . PHP_EOL;
    echo "------------------------------------" . PHP_EOL;
    
    echo "Guard par défaut: " . config('auth.defaults.guard') . PHP_EOL;
    echo "Provider par défaut: " . config('auth.defaults.provider') . PHP_EOL;
    echo "Modèle User: " . config('auth.providers.users.model') . PHP_EOL;
    
    // 5. Vérifier les souscriptions en attente
    echo PHP_EOL . "5. SOUSCRIPTIONS EN ATTENTE DE PAIEMENT:" . PHP_EOL;
    echo "----------------------------------------" . PHP_EOL;
    
    $pendingSubscriptions = DB::table('souscriptions')
        ->where('statut', 0)
        ->join('users', 'users.id', '=', 'souscriptions.iduser')
        ->select('souscriptions.*', 'users.email', 'users.name')
        ->orderBy('souscriptions.created_at', 'desc')
        ->get();
    
    echo "Nombre de souscriptions en attente: " . $pendingSubscriptions->count() . PHP_EOL . PHP_EOL;
    
    if ($pendingSubscriptions->count() > 0) {
        foreach ($pendingSubscriptions as $sub) {
            echo "Souscription ID: " . $sub->idsouscription . PHP_EOL;
            echo "Utilisateur: " . $sub->email . " (" . $sub->name . ")" . PHP_EOL;
            echo "Montant: " . ($sub->montant_finale_apaye ?? 'N/A') . " XOF" . PHP_EOL;
            echo "Date création: " . $sub->created_at . PHP_EOL;
            echo "-------------------" . PHP_EOL;
        }
    }
    
    // 6. Vérifier les logs d'erreurs récents
    echo PHP_EOL . "6. LOGS D'ERREURS RÉCENTS:" . PHP_EOL;
    echo "----------------------------" . PHP_EOL;
    
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        $recentLines = array_slice($lines, -50); // 50 dernières lignes
        
        $errorCount = 0;
        foreach ($recentLines as $line) {
            if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
                if ($errorCount < 10) { // Limiter l'affichage
                    echo $line . PHP_EOL;
                    $errorCount++;
                }
            }
        }
        
        if ($errorCount == 0) {
            echo "Aucune erreur récente trouvée dans les logs" . PHP_EOL;
        }
    } else {
        echo "Fichier de log non trouvé" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== RECOMMANDATIONS ===" . PHP_EOL;
echo "1. Si vous ne pouvez pas vous connecter:" . PHP_EOL;
echo "   - Vérifiez que votre compte existe bien" . PHP_EOL;
echo "   - Vérifiez que votre mot de passe est correct" . PHP_EOL;
echo "   - Vérifiez que votre compte est actif (status = 1)" . PHP_EOL;
echo "2. Si le compte est inactif (status = 0):" . PHP_EOL;
echo "   - Le paiement doit être validé pour activer le compte" . PHP_EOL;
echo "3. Pour réinitialiser un mot de passe:" . PHP_EOL;
echo "   - Utilisez la page de récupération de mot de passe" . PHP_EOL;
echo "   - Ou contactez l'administrateur" . PHP_EOL;

echo PHP_EOL . "=== FIN DE LA VÉRIFICATION ===" . PHP_EOL;
