<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== VÉRIFICATION DE L'OPÉRATEUR test6@gmail.com ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;

try {
    $email = 'test6@gmail.com';
    
    echo PHP_EOL . "🔍 Recherche de l'utilisateur avec l'email: " . $email . PHP_EOL;
    
    // Rechercher l'utilisateur dans la base de données
    $user = DB::table('users')->where('email', $email)->first();
    
    if ($user) {
        echo PHP_EOL . "✅ UTILISATEUR TROUVÉ !" . PHP_EOL;
        echo "📋 Informations de l'utilisateur:" . PHP_EOL;
        echo "- ID: " . $user->id . PHP_EOL;
        echo "- Email: " . $user->email . PHP_EOL;
        echo "- Nom: " . $user->name . PHP_EOL;
        echo "- Prénom: " . $user->prenom . PHP_EOL;
        echo "- Téléphone: " . $user->telephone . PHP_EOL;
        echo "- Type utilisateur: " . $user->type_user . PHP_EOL;
        echo "- Rôle: " . $user->role . PHP_EOL;
        echo "- Status: " . $user->status . PHP_EOL;
        echo "- Date création: " . $user->created_at . PHP_EOL;
        echo "- Date mise à jour: " . $user->updated_at . PHP_EOL;
        
        // Vérifier le type d'utilisateur
        if ($user->type_user == 4) {
            echo PHP_EOL . "👤 TYPE: Opérateur" . PHP_EOL;
            echo "- Rattaché à l'opérateur ID: " . ($user->ratache_operateur ?? 'Non défini') . PHP_EOL;
        } elseif ($user->type_user == 5) {
            echo PHP_EOL . "🏛️ TYPE: Autorité" . PHP_EOL;
            echo "- Rattaché à l'autorité ID: " . ($user->ratache_autorite ?? 'Non défini') . PHP_EOL;
        } elseif ($user->type_user == 2) {
            echo PHP_EOL . "🏛️ TYPE: Autorité contractante" . PHP_EOL;
        } else {
            echo PHP_EOL . "❓ TYPE: Autre (" . $user->type_user . ")" . PHP_EOL;
        }
        
        // Vérifier le statut
        if ($user->status == 0) {
            echo "📊 STATUT: Inactif (en attente de paiement)" . PHP_EOL;
        } elseif ($user->status == 1) {
            echo "📊 STATUT: Actif" . PHP_EOL;
        } else {
            echo "📊 STATUT: " . $user->status . PHP_EOL;
        }
        
        // Vérifier les souscriptions associées
        echo PHP_EOL . "📄 VÉRIFICATION DES SOUSCRIPTIONS:" . PHP_EOL;
        $souscriptions = DB::table('souscriptions')->where('iduser', $user->id)->get();
        
        if ($souscriptions->count() > 0) {
            echo "✅ " . $souscriptions->count() . " souscription(s) trouvée(s)" . PHP_EOL;
            
            foreach ($souscriptions as $souscription) {
                echo PHP_EOL . "📋 Souscription ID: " . $souscription->idsouscription . PHP_EOL;
                echo "- ID Abonnement: " . $souscription->idabonnement . PHP_EOL;
                echo "- Montant: " . $souscription->montant_finale_apaye . " XOF" . PHP_EOL;
                echo "- Statut: " . $souscription->statut . PHP_EOL;
                echo "- Date création: " . $souscription->created_at . PHP_EOL;
                
                // Récupérer les détails de l'abonnement
                $abonnement = DB::table('abonnement')->where('id', $souscription->idabonnement)->first();
                if ($abonnement) {
                    echo "- Nom abonnement: " . $abonnement->libelle . PHP_EOL;
                    echo "- Prix: " . $abonnement->prix . " XOF" . PHP_EOL;
                }
                
                // Vérifier le statut de la souscription
                if ($souscription->statut == 0) {
                    echo "📊 STATUT SOUSCRIPTION: En attente de paiement" . PHP_EOL;
                } elseif ($souscription->statut == 1) {
                    echo "📊 STATUT SOUSCRIPTION: Payée et active" . PHP_EOL;
                } elseif ($souscription->statut == 2) {
                    echo "📊 STATUT SOUSCRIPTION: Annulée" . PHP_EOL;
                } else {
                    echo "📊 STATUT SOUSCRIPTION: " . $souscription->statut . PHP_EOL;
                }
            }
        } else {
            echo "❌ Aucune souscription trouvée pour cet utilisateur" . PHP_EOL;
        }
        
        // Vérifier l'accès au compte
        echo PHP_EOL . "🔐 TEST D'ACCÈS AU COMPTE:" . PHP_EOL;
        
        // Utiliser la méthode verifabonnement du modèle User
        try {
            $verifAbonnement = \App\User::verifabonnement($user);
            if ($verifAbonnement) {
                echo "✅ Abonnement actif détecté" . PHP_EOL;
                echo "- Détails: " . json_encode($verifAbonnement) . PHP_EOL;
            } else {
                echo "❌ Aucun abonnement actif détecté" . PHP_EOL;
                echo "- L'utilisateur verra un message pour renouveler" . PHP_EOL;
            }
        } catch (Exception $e) {
            echo "⚠️  Erreur lors de la vérification: " . $e->getMessage() . PHP_EOL;
        }
        
        // Vérifier les permissions d'accès
        echo PHP_EOL . "🔒 PERMISSIONS D'ACCÈS:" . PHP_EOL;
        if ($user->status == 0) {
            echo "✅ Peut se connecter (compte créé mais inactif)" . PHP_EOL;
            echo "⚠️  Verra un message d'avertissement pour paiement" . PHP_EOL;
        } elseif ($user->status == 1) {
            echo "✅ Accès complet autorisé" . PHP_EOL;
        }
        
        echo PHP_EOL . "🎯 CONCLUSION:" . PHP_EOL;
        echo "- L'utilisateur test6@gmail.com existe bien dans la base de données" . PHP_EOL;
        echo "- Type: " . ($user->type_user == 4 ? "Opérateur" : "Autre") . PHP_EOL;
        echo "- Statut: " . ($user->status == 0 ? "Inactif (accessible)" : "Actif") . PHP_EOL;
        echo "- Souscriptions: " . $souscriptions->count() . " trouvée(s)" . PHP_EOL;
        
    } else {
        echo PHP_EOL . "❌ UTILISATEUR NON TROUVÉ !" . PHP_EOL;
        echo "Aucun utilisateur avec l'email " . $email . " n'existe dans la base de données." . PHP_EOL;
        
        // Vérifier s'il y a des emails similaires
        echo PHP_EOL . "🔍 Recherche d'emails similaires:" . PHP_EOL;
        $similarUsers = DB::table('users')->where('email', 'like', '%test6%')->get();
        
        if ($similarUsers->count() > 0) {
            echo "✅ " . $similarUsers->count() . " utilisateur(s) avec 'test6' trouvé(s):" . PHP_EOL;
            foreach ($similarUsers as $similarUser) {
                echo "- " . $similarUser->email . " (ID: " . $similarUser->id . ")" . PHP_EOL;
            }
        } else {
            echo "❌ Aucun utilisateur avec 'test6' trouvé" . PHP_EOL;
        }
    }
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Fichier: " . $e->getFile() . PHP_EOL;
    echo "Ligne: " . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== VÉRIFICATION TERMINÉE ===" . PHP_EOL;
