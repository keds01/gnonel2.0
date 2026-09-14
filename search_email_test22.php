<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== RECHERCHE DE L'EMAIL test22@gmail.com DANS TOUTES LES TABLES ===" . PHP_EOL;

$emailToSearch = 'test22@gmail.com';
echo "Email recherché: " . $emailToSearch . PHP_EOL . PHP_EOL;

try {
    // Liste des tables principales à vérifier
    $tables = [
        'users',
        'souscriptions', 
        'references',
        'basefours',
        'specs',
        'recommander',
        'configurations',
        'categories',
        'pays',
        'secteuractivite',
        'abonnement',
        'categorieabonnement',
        'appels_offres',
        'candidatures'
    ];
    
    $foundInTables = [];
    
    foreach ($tables as $table) {
        echo "🔍 Recherche dans la table: " . $table . PHP_EOL;
        
        try {
            // Vérifier si la table existe
            $tableExists = DB::select("SHOW TABLES LIKE '" . $table . "'");
            
            if (empty($tableExists)) {
                echo "   ❌ Table n'existe pas" . PHP_EOL;
                continue;
            }
            
            // Obtenir les colonnes de la table
            $columns = DB::select("SHOW COLUMNS FROM " . $table);
            $columnNames = array_map(function($col) { return $col->Field; }, $columns);
            
            // Rechercher dans les colonnes de type texte/email
            $textColumns = array_filter($columnNames, function($col) {
                return strpos(strtolower($col), 'email') !== false || 
                       strpos(strtolower($col), 'mail') !== false ||
                       strpos(strtolower($col), 'contact') !== false ||
                       strpos(strtolower($col), 'user') !== false ||
                       strpos(strtolower($col), 'operateur') !== false ||
                       strpos(strtolower($col), 'autorite') !== false;
            });
            
            $found = false;
            $results = [];
            
            foreach ($textColumns as $column) {
                try {
                    $matches = DB::table($table)->where($column, 'LIKE', '%' . $emailToSearch . '%')->get();
                    
                    if ($matches->count() > 0) {
                        $found = true;
                        $results[$column] = $matches;
                        echo "   ✅ Trouvé dans la colonne '" . $column . "' (" . $matches->count() . " résultat(s))" . PHP_EOL;
                        
                        foreach ($matches as $match) {
                            echo "      ID: " . (isset($match->id) ? $match->id : 'N/A') . PHP_EOL;
                            echo "      " . $column . ": " . $match->$column . PHP_EOL;
                            if (isset($match->created_at)) {
                                echo "      Créé le: " . $match->created_at . PHP_EOL;
                            }
                            echo "      ---" . PHP_EOL;
                        }
                    }
                } catch (Exception $e) {
                    echo "   ⚠️  Erreur recherche colonne " . $column . ": " . $e->getMessage() . PHP_EOL;
                }
            }
            
            if (!$found) {
                echo "   ❌ Non trouvé dans cette table" . PHP_EOL;
            } else {
                $foundInTables[] = $table;
            }
            
        } catch (Exception $e) {
            echo "   ❌ Erreur table " . $table . ": " . $e->getMessage() . PHP_EOL;
        }
        
        echo PHP_EOL;
    }
    
    // Résumé
    echo "=== RÉSUMÉ DE LA RECHERCHE ===" . PHP_EOL;
    if (empty($foundInTables)) {
        echo "❌ L'email '" . $emailToSearch . "' n'a été trouvé dans aucune table." . PHP_EOL;
    } else {
        echo "✅ L'email '" . $emailToSearch . "' a été trouvé dans " . count($foundInTables) . " table(s):" . PHP_EOL;
        foreach ($foundInTables as $table) {
            echo "- " . $table . PHP_EOL;
        }
    }
    
    // Recherche spécifique dans la table users avec plus de détails
    echo PHP_EOL . "=== DÉTAILS DE L'UTILISATEUR SI EXISTE ===" . PHP_EOL;
    
    $user = DB::table('users')->where('email', $emailToSearch)->first();
    if ($user) {
        echo "✅ Utilisateur trouvé dans la table users:" . PHP_EOL;
        echo "ID: " . $user->id . PHP_EOL;
        echo "Nom: " . $user->name . PHP_EOL;
        echo "Prénom: " . ($user->prenom ?? 'N/A') . PHP_EOL;
        echo "Email: " . $user->email . PHP_EOL;
        echo "Téléphone: " . ($user->telephone ?? 'N/A') . PHP_EOL;
        echo "Rôle: " . $user->role . PHP_EOL;
        echo "Type utilisateur: " . $user->type_user . PHP_EOL;
        echo "Statut: " . $user->status . " (0=inactif, 1=actif)" . PHP_EOL;
        echo "Date création: " . $user->created_at . PHP_EOL;
        echo "Date mise à jour: " . $user->updated_at . PHP_EOL;
        echo "Rattaché autorité: " . ($user->ratache_autorite ?? 'Non') . PHP_EOL;
        echo "Rattaché opérateur: " . ($user->ratache_operateur ?? 'Non') . PHP_EOL;
        
        // Vérifier les souscriptions de cet utilisateur
        echo PHP_EOL . "Souscriptions de cet utilisateur:" . PHP_EOL;
        $subscriptions = DB::table('souscriptions')->where('iduser', $user->id)->get();
        if ($subscriptions->count() > 0) {
            foreach ($subscriptions as $sub) {
                echo "- ID: " . $sub->idsouscription . ", Statut: " . $sub->statut . ", Montant: " . ($sub->montant_finale_apaye ?? 'N/A') . " XOF" . PHP_EOL;
            }
        } else {
            echo "Aucune souscription trouvée" . PHP_EOL;
        }
    } else {
        echo "❌ Aucun utilisateur trouvé avec cet email dans la table users" . PHP_EOL;
    }
    
    // Recherche approchée (au cas où)
    echo PHP_EOL . "=== RECHERCHE APPROCHÉE ===" . PHP_EOL;
    
    $similarEmails = DB::table('users')
        ->where('email', 'LIKE', '%test22%')
        ->orWhere('email', 'LIKE', '%@gmail.com')
        ->where('email', 'LIKE', '%test%')
        ->limit(10)
        ->get();
    
    if ($similarEmails->count() > 0) {
        echo "Emails similaires trouvés:" . PHP_EOL;
        foreach ($similarEmails as $similar) {
            echo "- " . $similar->email . " (ID: " . $similar->id . ")" . PHP_EOL;
        }
    } else {
        echo "Aucun email similaire trouvé" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== FIN DE LA RECHERCHE ===" . PHP_EOL;
