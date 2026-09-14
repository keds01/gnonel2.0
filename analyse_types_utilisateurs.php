<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== ANALYSE DES TYPES D'UTILISATEURS type_user = 4 et 5 ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;

try {
    echo PHP_EOL . "📋 TYPES D'UTILISATEURS DANS LE SYSTÈME:" . PHP_EOL;
    
    // Analyser tous les types d'utilisateurs présents
    $types = DB::table('users')
        ->select('type_user', DB::raw('count(*) as count'))
        ->groupBy('type_user')
        ->orderBy('type_user')
        ->get();
    
    echo PHP_EOL . "📊 STATISTIQUES DES TYPES D'UTILISATEURS:" . PHP_EOL;
    foreach ($types as $type) {
        $label = getTypeLabel($type->type_user);
        echo "- Type " . $type->type_user . " : " . $type->count . " utilisateurs (" . $label . ")" . PHP_EOL;
    }
    
    echo PHP_EOL . "🎯 FOCUS SUR type_user = 4 et 5:" . PHP_EOL;
    
    // Type 4 - Opérateurs
    echo PHP_EOL . "👤 TYPE_USER = 4 : OPÉRATEUR ÉCONOMIQUE" . PHP_EOL;
    $operateurs = DB::table('users')->where('type_user', 4)->limit(3)->get();
    
    echo "Description :" . PHP_EOL;
    echo "- Opérateur économique / Entreprise" . PHP_EOL;
    echo "- Peut publier des spécifications techniques" . PHP_EOL;
    echo "- Peut gérer une base de fournisseurs" . PHP_EOL;
    echo "- Peut gérer des références techniques" . PHP_EOL;
    echo "- Doit avoir un abonnement pour fonctionnalités premium" . PHP_EOL;
    echo "- Rattaché à un opérateur (ratache_operateur)" . PHP_EOL;
    
    echo PHP_EOL . "Exemples d'opérateurs :" . PHP_EOL;
    foreach ($operateurs as $op) {
        echo "- " . $op->email . " (ID: " . $op->id . ", Rattaché: " . ($op->ratache_operateur ?? 'N/A') . ")" . PHP_EOL;
    }
    
    // Type 5 - Autorités
    echo PHP_EOL . "🏛️ TYPE_USER = 5 : AUTORITÉ CONTRACTANTE" . PHP_EOL;
    $autorites = DB::table('users')->where('type_user', 5)->limit(3)->get();
    
    echo "Description :" . PHP_EOL;
    echo "- Autorité contractante / Administration" . PHP_EOL;
    echo "- Peut publier des appels d'offres" . PHP_EOL;
    echo "- Peut consulter les offres des opérateurs" . PHP_EOL;
    echo "- Doit avoir un abonnement pour fonctionnalités premium" . PHP_EOL;
    echo "- Rattaché à une autorité (ratache_autorite)" . PHP_EOL;
    
    echo PHP_EOL . "Exemples d'autorités :" . PHP_EOL;
    foreach ($autorites as $aut) {
        echo "- " . $aut->email . " (ID: " . $aut->id . ", Rattaché: " . ($aut->ratache_autorite ?? 'N/A') . ")" . PHP_EOL;
    }
    
    // Différences entre type 4 et 5
    echo PHP_EOL . "🔄 DIFFÉRENCES ENTRE TYPE 4 ET TYPE 5 :" . PHP_EOL;
    
    echo PHP_EOL . "📋 TABLEAU COMPARATIF :" . PHP_EOL;
    echo "┌─────────────┬─────────────────┬─────────────────┐" . PHP_EOL;
    echo "│ Caractéristique │ Type 4 (Opérateur) │ Type 5 (Autorité) │" . PHP_EOL;
    echo "├─────────────┼─────────────────┼─────────────────┤" . PHP_EOL;
    echo "│ Rôle principal │ Entreprise │ Administration │" . PHP_EOL;
    echo "│ Publications │ Spécifications techniques │ Appels d'offres │" . PHP_EOL;
    echo "│ Gestion │ Base fournisseurs │ Consultation offres │" . PHP_EOL;
    echo "│ Références │ Oui │ Non │" . PHP_EOL;
    echo "│ Profil route │ info_compte │ infocompteaut │" . PHP_EOL;
    echo "│ Rattachement │ ratache_operateur │ ratache_autorite │" . PHP_EOL;
    echo "└─────────────┴─────────────────┴─────────────────┘" . PHP_EOL;
    
    // Routes spécifiques
    echo PHP_EOL . "🛣️ ROUTES SPÉCIFIQUES :" . PHP_EOL;
    
    echo PHP_EOL . "Routes pour type_user = 4 (Opérateurs) :" . PHP_EOL;
    echo "- /acceuil/membre : Tableau de bord avec compteurs" . PHP_EOL;
    echo "- listspecabonne : Liste des spécifications" . PHP_EOL;
    echo "- specifications.create : Publier une spécification" . PHP_EOL;
    echo "- basefournisseurs.* : Gestion base fournisseurs" . PHP_EOL;
    echo "- viewmesref : Mes références techniques" . PHP_EOL;
    echo "- info_compte : Profil opérateur" . PHP_EOL;
    
    echo PHP_EOL . "Routes pour type_user = 5 (Autorités) :" . PHP_EOL;
    echo "- /acceuil/membre : Tableau de bord partagé" . PHP_EOL;
    echo "- autorite.offre.* : Gestion appels d'offres" . PHP_EOL;
    echo "- rechercheoffre : Consulter les offres" . PHP_EOL;
    echo "- autorite.mes.offres : Mes publications" . PHP_EOL;
    echo "- infocompteaut : Profil autorité" . PHP_EOL;
    
    // test6@gmail.com exemple
    echo PHP_EOL . "🔍 CAS CONCRET : test6@gmail.com" . PHP_EOL;
    $test6 = DB::table('users')->where('email', 'test6@gmail.com')->first();
    
    if ($test6) {
        echo "- Type : " . $test6->type_user . " (" . getTypeLabel($test6->type_user) . ")" . PHP_EOL;
        echo "- Rattaché à : " . ($test6->ratache_operateur ?? 'Non défini') . PHP_EOL;
        echo "- Accès tableau de bord : ✅ /acceuil/membre" . PHP_EOL;
        echo "- Fonctionnalités premium : ❌ (nécessite abonnement)" . PHP_EOL;
        echo "- Profil : info_compte" . PHP_EOL;
    }
    
    echo PHP_EOL . "💡 RÉSUMÉ :" . PHP_EOL;
    echo "Type 4 = Opérateurs économiques (entreprises qui publient des spécifications)" . PHP_EOL;
    echo "Type 5 = Autorités contractantes (administrations qui publient des appels d'offres)" . PHP_EOL;
    echo "Les deux types partagent le même tableau de bord /acceuil/membre" . PHP_EOL;
    echo "Les deux types nécessitent un abonnement pour les fonctionnalités premium" . PHP_EOL;
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Fichier: " . $e->getFile() . PHP_EOL;
    echo "Ligne: " . $e->getLine() . PHP_EOL;
    exit(1);
}

function getTypeLabel($type) {
    switch($type) {
        case 0: return "Administrateur";
        case 2: return "Autorité contractante";
        case 3: return "Utilisateur standard";
        case 4: return "Opérateur économique";
        case 5: return "Autorité contractante";
        case 55: return "En attente";
        default: return "Type inconnu";
    }
}

echo PHP_EOL . "=== ANALYSE TERMINÉE ===" . PHP_EOL;
