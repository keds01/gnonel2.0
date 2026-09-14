<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== DIAGNOSTIC DE CONNEXION ===" . PHP_EOL;

// Demander les informations de l'utilisateur
echo PHP_EOL . "Veuillez entrer les informations de connexion :" . PHP_EOL;
echo "Email: ";
$email = trim(fgets(STDIN));
echo "Mot de passe: ";
$password = trim(fgets(STDIN));

if (empty($email) || empty($password)) {
    echo "ERREUR: Email et mot de passe requis" . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== VÉRIFICATION DU COMPTE ===" . PHP_EOL;

try {
    // 1. Vérifier si l'utilisateur existe
    $user = DB::table('users')->where('email', $email)->first();
    
    if (!$user) {
        echo "❌ COMPTE NON TROUVÉ" . PHP_EOL;
        echo "Aucun utilisateur trouvé avec l'email: " . $email . PHP_EOL;
        echo PHP_EOL . "Comptes existants dans la base:" . PHP_EOL;
        $users = DB::table('users')->select('email', 'name', 'created_at', 'status', 'type_user')->limit(10)->get();
        foreach ($users as $u) {
            echo "- " . $u->email . " (" . $u->name . ") - Status: " . $u->status . " - Type: " . $u->type_user . PHP_EOL;
        }
        exit(1);
    }
    
    echo "✅ COMPTE TROUVÉ" . PHP_EOL;
    echo "ID: " . $user->id . PHP_EOL;
    echo "Nom: " . $user->name . " " . ($user->prenom ?? '') . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;
    echo "Status: " . $user->status . " (0=inactif, 1=actif)" . PHP_EOL;
    echo "Type utilisateur: " . $user->type_user . PHP_EOL;
    echo "Rôle: " . $user->role . PHP_EOL;
    echo "Date création: " . $user->created_at . PHP_EOL;
    echo "Date mise à jour: " . $user->updated_at . PHP_EOL;
    
    // 2. Vérifier le mot de passe
    echo PHP_EOL . "=== VÉRIFICATION DU MOT DE PASSE ===" . PHP_EOL;
    
    if (Hash::check($password, $user->password)) {
        echo "✅ MOT DE PASSE CORRECT" . PHP_EOL;
    } else {
        echo "❌ MOT DE PASSE INCORRECT" . PHP_EOL;
        echo "Le mot de passe fourni ne correspond pas au hash en base de données." . PHP_EOL;
        
        // Proposer de réinitialiser le mot de passe
        echo PHP_EOL . "Souhaitez-vous réinitialiser le mot de passe ? (o/n): ";
        $reset = trim(fgets(STDIN));
        if (strtolower($reset) === 'o') {
            $newPassword = 'password123'; // Mot de passe temporaire
            $hashedPassword = Hash::make($newPassword);
            
            DB::table('users')->where('id', $user->id)->update([
                'password' => $hashedPassword,
                'updated_at' => now()
            ]);
            
            echo "✅ Mot de passe réinitialisé à: " . $newPassword . PHP_EOL;
            echo "Veuillez changer ce mot de passe après connexion." . PHP_EOL;
        }
    }
    
    // 3. Vérifier les souscriptions
    echo PHP_EOL . "=== VÉRIFICATION DES ABONNEMENTS ===" . PHP_EOL;
    
    $subscriptions = DB::table('souscriptions')
        ->where('iduser', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    if ($subscriptions->count() > 0) {
        echo "Nombre d'abonnements: " . $subscriptions->count() . PHP_EOL;
        foreach ($subscriptions as $sub) {
            echo "- ID: " . $sub->idsouscription . PHP_EOL;
            echo "  Statut: " . $sub->statut . " (0=en attente, 1=payé)" . PHP_EOL;
            echo "  Montant: " . ($sub->montant_finale_apaye ?? 'N/A') . " XOF" . PHP_EOL;
            echo "  Date création: " . $sub->created_at . PHP_EOL;
            echo "  Date fin: " . ($sub->date_fin ?? 'N/A') . PHP_EOL;
            echo PHP_EOL;
        }
    } else {
        echo "❌ Aucun abonnement trouvé" . PHP_EOL;
    }
    
    // 4. Test d'authentification Laravel
    echo PHP_EOL . "=== TEST D'AUTHENTIFICATION LARAVEL ===" . PHP_EOL;
    
    // Simuler une tentative de connexion
    $credentials = [
        'email' => $email,
        'password' => $password
    ];
    
    // Créer un guard pour tester
    $auth = app('auth');
    
    if ($auth->attempt($credentials)) {
        echo "✅ AUTHENTIFICATION LARAVEL RÉUSSIE" . PHP_EOL;
        $authUser = $auth->user();
        echo "Utilisateur connecté: " . $authUser->email . PHP_EOL;
        $auth->logout();
    } else {
        echo "❌ AUTHENTIFICATION LARAVEL ÉCHOUÉE" . PHP_EOL;
        echo "L'authentification Laravel a échoué." . PHP_EOL;
    }
    
    // 5. Vérifier les logs récents
    echo PHP_EOL . "=== VÉRIFICATION DES LOGS RÉCENTS ===" . PHP_EOL;
    
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        echo "Fichier de log trouvé: " . $logFile . PHP_EOL;
        echo "Taille: " . filesize($logFile) . " octets" . PHP_EOL;
        
        // Lire les dernières lignes du log
        $lines = file($logFile);
        $recentLines = array_slice($lines, -20); // 20 dernières lignes
        
        echo PHP_EOL . "Dernières entrées de log:" . PHP_EOL;
        echo str_repeat("-", 50) . PHP_EOL;
        foreach ($recentLines as $line) {
            if (stripos($line, $email) !== false || stripos($line, 'auth') !== false || stripos($line, 'login') !== false) {
                echo $line;
            }
        }
    } else {
        echo "❌ Fichier de log non trouvé" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== FIN DU DIAGNOSTIC ===" . PHP_EOL;
