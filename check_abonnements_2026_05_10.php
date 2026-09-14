<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== ANALYSE DES ABONNEMENTS/PAIEMENTS DU 10/05/2026 ===" . PHP_EOL;

try {
    // 1. Vérifier les souscriptions créées le 10/05/2026
    echo "1. SOUSCRIPTIONS CRÉÉES LE 10/05/2026 :" . PHP_EOL;
    echo "----------------------------------------" . PHP_EOL;
    
    $subscriptions = DB::table('souscriptions')
        ->whereDate('created_at', '2026-05-10')
        ->orderBy('created_at', 'desc')
        ->get();

    echo "Nombre total de souscriptions créées : " . $subscriptions->count() . PHP_EOL . PHP_EOL;

    if ($subscriptions->count() > 0) {
        foreach ($subscriptions as $sub) {
            echo "ID Souscription: " . $sub->idsouscription . PHP_EOL;
            echo "ID Utilisateur: " . $sub->iduser . PHP_EOL;
            echo "ID Abonnement: " . $sub->idabonnement . PHP_EOL;
            echo "Montant final: " . ($sub->montant_finale_apaye ?? 'N/A') . " XOF" . PHP_EOL;
            echo "Statut: " . $sub->statut . " (0=en attente, 1=payé)" . PHP_EOL;
            echo "Référence paiement: " . ($sub->referencepaiement ?? 'N/A') . PHP_EOL;
            echo "Méthode paiement: " . ($sub->payment_method ?? 'N/A') . PHP_EOL;
            echo "Date création: " . $sub->created_at . PHP_EOL;
            echo "Date fin: " . ($sub->date_fin ?? 'N/A') . PHP_EOL;
            echo "-------------------" . PHP_EOL;
        }
    } else {
        echo "Aucune souscription créée cette date." . PHP_EOL;
    }

    // 2. Vérifier les souscriptions mises à jour/payées le 10/05/2026
    echo PHP_EOL . "2. SOUSCRIPTIONS PAYÉES/MISES À JOUR LE 10/05/2026 :" . PHP_EOL;
    echo "-------------------------------------------------------" . PHP_EOL;
    
    $paidSubscriptions = DB::table('souscriptions')
        ->whereDate('updated_at', '2026-05-10')
        ->where('statut', 1) // Payées
        ->orderBy('updated_at', 'desc')
        ->get();

    echo "Nombre total de souscriptions payées ce jour : " . $paidSubscriptions->count() . PHP_EOL . PHP_EOL;

    if ($paidSubscriptions->count() > 0) {
        foreach ($paidSubscriptions as $sub) {
            echo "ID Souscription: " . $sub->idsouscription . PHP_EOL;
            echo "ID Utilisateur: " . $sub->iduser . PHP_EOL;
            echo "Montant payé: " . ($sub->montant_finale_apaye ?? 'N/A') . " XOF" . PHP_EOL;
            echo "Référence paiement: " . ($sub->referencepaiement ?? 'N/A') . PHP_EOL;
            echo "Méthode paiement: " . ($sub->payment_method ?? 'N/A') . PHP_EOL;
            echo "Date création: " . $sub->created_at . PHP_EOL;
            echo "Date mise à jour: " . $sub->updated_at . PHP_EOL;
            echo "Date fin abonnement: " . ($sub->date_fin ?? 'N/A') . PHP_EOL;
            echo "-------------------" . PHP_EOL;
        }
    } else {
        echo "Aucune souscription payée cette date." . PHP_EOL;
    }

    // 3. Vérifier les transactions avec référence de paiement le 10/05/2026
    echo PHP_EOL . "3. TRANSACTIONS AVEC RÉFÉRENCE DE PAIEMENT LE 10/05/2026 :" . PHP_EOL;
    echo "-----------------------------------------------------------" . PHP_EOL;
    
    $transactions = DB::table('souscriptions')
        ->whereDate('created_at', '2026-05-10')
        ->orWhereDate('updated_at', '2026-05-10')
        ->whereNotNull('referencepaiement')
        ->where('referencepaiement', '!=', '')
        ->orderBy('updated_at', 'desc')
        ->get();

    echo "Nombre total de transactions avec référence : " . $transactions->count() . PHP_EOL . PHP_EOL;

    if ($transactions->count() > 0) {
        foreach ($transactions as $trans) {
            echo "ID Souscription: " . $trans->idsouscription . PHP_EOL;
            echo "Référence paiement: " . $trans->referencepaiement . PHP_EOL;
            echo "Statut: " . $trans->statut . PHP_EOL;
            echo "Montant: " . ($trans->montant_finale_apaye ?? 'N/A') . " XOF" . PHP_EOL;
            echo "Date création: " . $trans->created_at . PHP_EOL;
            echo "Date mise à jour: " . $trans->updated_at . PHP_EOL;
            echo "-------------------" . PHP_EOL;
        }
    } else {
        echo "Aucune transaction avec référence trouvée cette date." . PHP_EOL;
    }

    // 4. Statistiques globales
    echo PHP_EOL . "4. STATISTIQUES GLOBALES DU 10/05/2026 :" . PHP_EOL;
    echo "------------------------------------------" . PHP_EOL;
    
    $totalCreated = DB::table('souscriptions')
        ->whereDate('created_at', '2026-05-10')
        ->count();
    
    $totalPaid = DB::table('souscriptions')
        ->whereDate('updated_at', '2026-05-10')
        ->where('statut', 1)
        ->count();
    
    $totalAmount = DB::table('souscriptions')
        ->whereDate('updated_at', '2026-05-10')
        ->where('statut', 1)
        ->sum('montant_finale_apaye');

    echo "Souscriptions créées : " . $totalCreated . PHP_EOL;
    echo "Souscriptions payées : " . $totalPaid . PHP_EOL;
    echo "Montant total collecté : " . ($totalAmount ?? 0) . " XOF" . PHP_EOL;

} catch (Exception $e) {
    echo "Erreur lors de l'analyse : " . $e->getMessage() . PHP_EOL;
    echo "Stack trace : " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== FIN DE L'ANALYSE ===" . PHP_EOL;
