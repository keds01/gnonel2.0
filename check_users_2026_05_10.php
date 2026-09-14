<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== ANALYSE DES COMPTES CRÉÉS LE 10/05/2026 ===" . PHP_EOL;

try {
    // Récupérer les utilisateurs créés le 10/05/2026
    $users = DB::table('users')
        ->whereDate('created_at', '2026-05-10')
        ->orderBy('created_at', 'desc')
        ->get();

    echo "Nombre total de comptes créés le 10/05/2026 : " . $users->count() . PHP_EOL . PHP_EOL;

    if ($users->count() > 0) {
        echo "Détail des comptes :" . PHP_EOL;
        echo "-------------------" . PHP_EOL;
        
        foreach ($users as $user) {
            echo "ID: " . $user->id . PHP_EOL;
            echo "Nom: " . $user->name . PHP_EOL;
            echo "Prénom: " . ($user->prenom ?? 'N/A') . PHP_EOL;
            echo "Email: " . $user->email . PHP_EOL;
            echo "Téléphone: " . ($user->telephone ?? 'N/A') . PHP_EOL;
            echo "Type utilisateur: " . $user->type_user . PHP_EOL;
            echo "Rôle: " . $user->role . PHP_EOL;
            echo "Statut: " . $user->status . PHP_EOL;
            echo "Date de création: " . $user->created_at . PHP_EOL;
            echo "-------------------" . PHP_EOL;
        }
    } else {
        echo "Aucun compte trouvé pour cette date." . PHP_EOL;
    }

    // Vérifier aussi les souscriptions créées ce jour-là
    echo PHP_EOL . "=== SOUSCRIPTIONS CRÉÉES LE 10/05/2026 ===" . PHP_EOL;
    
    $subscriptions = DB::table('souscriptions')
        ->whereDate('created_at', '2026-05-10')
        ->orderBy('created_at', 'desc')
        ->get();

    echo "Nombre total de souscriptions créées le 10/05/2026 : " . $subscriptions->count() . PHP_EOL . PHP_EOL;

    if ($subscriptions->count() > 0) {
        foreach ($subscriptions as $sub) {
            echo "ID Souscription: " . $sub->idsouscription . PHP_EOL;
            echo "ID Utilisateur: " . $sub->iduser . PHP_EOL;
            echo "ID Abonnement: " . $sub->idabonnement . PHP_EOL;
            echo "Montant: " . ($sub->montant_finale_apaye ?? 'N/A') . PHP_EOL;
            echo "Statut: " . $sub->statut . PHP_EOL;
            echo "Date de création: " . $sub->created_at . PHP_EOL;
            echo "-------------------" . PHP_EOL;
        }
    }

} catch (Exception $e) {
    echo "Erreur lors de l'analyse : " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== FIN DE L'ANALYSE ===" . PHP_EOL;
