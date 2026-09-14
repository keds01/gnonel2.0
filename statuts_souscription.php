<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== TOUS LES STATUTS DE SOUSCRIPTION POSSIBLES ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;

try {
    echo PHP_EOL . "📋 STATUTS DE SOUSCRIPTION IDENTIFIÉS DANS LE CODE:" . PHP_EOL;
    
    echo PHP_EOL . "🔢 STATUTS NUMÉRIQUES (base de données):" . PHP_EOL;
    echo "0 - En attente de paiement" . PHP_EOL;
    echo "1 - Payée et active" . PHP_EOL;
    echo "2 - Annulée" . PHP_EOL;
    
    echo PHP_EOL . "📝 STATUTS TEXTES (FedaPay/API externes):" . PHP_EOL;
    echo "pending - En attente" . PHP_EOL;
    echo "approved - Approuvée/Payée" . PHP_EOL;
    echo "canceled - Annulée" . PHP_EOL;
    echo "failed - Échouée" . PHP_EOL;
    echo "success - Succès" . PHP_EOL;
    
    echo PHP_EOL . "🔄 CORRESPONDANCE STATUTS:" . PHP_EOL;
    echo "FedaPay 'pending' → DB '0' (En attente de paiement)" . PHP_EOL;
    echo "FedaPay 'approved' → DB '1' (Payée et active)" . PHP_EOL;
    echo "FedaPay 'canceled' → DB '2' (Annulée)" . PHP_EOL;
    echo "FedaPay 'failed' → DB '2' (Annulée)" . PHP_EOL;
    
    echo PHP_EOL . "📊 STATISTIQUES ACTUELLES DES SOUSCRIPTIONS:" . PHP_EOL;
    
    // Compter les souscriptions par statut
    $stats = DB::table('souscriptions')
        ->select('statut', DB::raw('count(*) as count'))
        ->groupBy('statut')
        ->orderBy('statut')
        ->get();
    
    $total = DB::table('souscriptions')->count();
    
    echo "Total des souscriptions: " . $total . PHP_EOL . PHP_EOL;
    
    foreach ($stats as $stat) {
        $percentage = $total > 0 ? round(($stat->count / $total) * 100, 2) : 0;
        
        switch($stat->statut) {
            case 0:
                $label = "En attente de paiement";
                break;
            case 1:
                $label = "Payée et active";
                break;
            case 2:
                $label = "Annulée";
                break;
            default:
                $label = "Autre (" . $stat->statut . ")";
                break;
        }
        
        echo "Statut " . $stat->statut . " - " . $label . ": " . $stat->count . " (" . $percentage . "%)" . PHP_EOL;
    }
    
    echo PHP_EOL . "🔍 EXEMPLES DE SOUSCRIPTIONS PAR STATUT:" . PHP_EOL;
    
    // Exemples pour chaque statut
    $statuts = [0, 1, 2];
    
    foreach ($statuts as $statut) {
        echo PHP_EOL . "📋 Statut " . $statut . ":" . PHP_EOL;
        
        $examples = DB::table('souscriptions')
            ->where('statut', $statut)
            ->join('users', 'users.id', '=', 'souscriptions.iduser')
            ->join('abonnement', 'abonnement.id', '=', 'souscriptions.idabonnement')
            ->select(
                'souscriptions.idsouscription',
                'souscriptions.statut',
                'souscriptions.montant_finale_apaye',
                'souscriptions.created_at',
                'users.email',
                'abonnement.libelle'
            )
            ->limit(3)
            ->get();
        
        if ($examples->count() > 0) {
            foreach ($examples as $example) {
                echo "- ID: " . $example->idsouscription . PHP_EOL;
                echo "  Email: " . $example->email . PHP_EOL;
                echo "  Abonnement: " . $example->libelle . PHP_EOL;
                echo "  Montant: " . $example->montant_finale_apaye . " XOF" . PHP_EOL;
                echo "  Date: " . $example->created_at . PHP_EOL;
                echo PHP_EOL;
            }
        } else {
            echo "Aucune souscription avec ce statut" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "🔧 GESTION DES STATUTS DANS LE CODE:" . PHP_EOL;
    echo "routes/web.php - Ligne 95: statut = 2 (annulation)" . PHP_EOL;
    echo "debug scripts: statut = 0 (création), statut = 2 (annulation)" . PHP_EOL;
    echo "Filtres: where('souscriptions.statut', 1) (souscriptions actives)" . PHP_EOL;
    
    echo PHP_EOL . "🎯 RÉSUMÉ DES STATUTS:" . PHP_EOL;
    echo "┌─────────┬─────────────────────┬─────────────────┐" . PHP_EOL;
    echo "│ Statut  │ Description        │ État utilisateur │" . PHP_EOL;
    echo "├─────────┼─────────────────────┼─────────────────┤" . PHP_EOL;
    echo "│    0    │ En attente paiement │ Peut se connecter│" . PHP_EOL;
    echo "│    1    │ Payée et active     │ Accès complet   │" . PHP_EOL;
    echo "│    2    │ Annulée             │ Peut se connecter│" . PHP_EOL;
    echo "└─────────┴─────────────────────┴─────────────────┘" . PHP_EOL;
    
    echo PHP_EOL . "💡 NOTES IMPORTANTES:" . PHP_EOL;
    echo "- Tous les utilisateurs peuvent se connecter quel que soit le statut" . PHP_EOL;
    echo "- Le statut 0 et 2 montrent un message d'avertissement pour paiement" . PHP_EOL;
    echo "- Le statut 1 donne un accès complet sans avertissement" . PHP_EOL;
    echo "- Les statuts sont mis à jour via les webhooks FedaPay" . PHP_EOL;
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Fichier: " . $e->getFile() . PHP_EOL;
    echo "Ligne: " . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== ANALYSE DES STATUTS TERMINÉE ===" . PHP_EOL;
