<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\User;



class RechercheController extends Controller

{


public function rechercheoffre()
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            session()->flash('message', sprintf('Veuillez vous connecter pour accéder aux appels d\'offres.'));
            return redirect(route('login'));
        }

        // Vérifier si l'utilisateur a un abonnement actif
        $verif = User::verifabonnement(Auth::user());
        if ($verif == null) {
            session()->flash('message', 'Veuillez souscrire à un abonnement pour accéder aux appels d\'offres.');
            return redirect(route('pricing'));
        }

        $paysId = request('pays');

        $pays = DB::table('pays')->orderby('nom_pays')->get();

        $offresQuery = DB::table('appeloffres')
            ->join('autoritecontractantes', 'autoritecontractantes.id', '=', 'appeloffres.id_autorite')
            ->join('categories', 'categories.id', '=', 'appeloffres.id_categorie')
            ->join('pays', 'pays.id', '=', 'autoritecontractantes.id_pays')
            ->where('appeloffres.status', '=', 1)
            ->where('appeloffres.date_cloture', '>', date('Y-m-d H:i:s'));

        if ($paysId) {
            $offresQuery->where('autoritecontractantes.id_pays', '=', $paysId);
        }

        $offres = $offresQuery->select(
            'appeloffres.*',
            'autoritecontractantes.raison_social',
            'categories.nom_categorie',
            'pays.nom_pays'
        )
            ->orderby('appeloffres.created_at', 'desc')
            ->get();

        $data = ['pays' => $paysId];

        return view('appels_offres.index', compact('pays', 'offres', 'data'));



        

    }
    public function recherche()
    {
        $paysId = request('pays');

        $pays = DB::table('pays')->orderby('nom_pays')->get();

        $offresQuery = DB::table('appeloffres')
            ->join('autoritecontractantes','autoritecontractantes.id','=','appeloffres.id_autorite')
            ->join('categories','categories.id','=','appeloffres.id_categorie')
            ->where('appeloffres.status','=',1)
            ->where('appeloffres.date_cloture','>',date('Y-m-d H:i:s'));

        if ($paysId) {
            $offresQuery->where('autoritecontractantes.id_pays','=',$paysId);
        }

        $offres = $offresQuery->select('appeloffres.*', 'autoritecontractantes.raison_social','categories.nom_categorie')
            ->orderby('appeloffres.created_at','desc')
            ->get();

        $data = ['pays' => $paysId];

        return view('resultat',compact('pays','offres','data'));



        

    }


    public function rechercheajax($id = null)
    {
        $query = DB::table('appeloffres')
            ->join('autoritecontractantes', 'autoritecontractantes.id', '=', 'appeloffres.id_autorite')
            ->join('categories', 'categories.id', '=', 'appeloffres.id_categorie')
            ->join('pays', 'pays.id', '=', 'autoritecontractantes.id_pays')
            ->where('appeloffres.status', '=', 1)
            ->where('appeloffres.date_cloture', '>', date('Y-m-d H:i:s'));

        if ($id && $id != '') {
            $query->where('autoritecontractantes.id_pays', '=', $id);
        }

        $offres = $query->select(
            'appeloffres.*',
            'autoritecontractantes.raison_social',
            'categories.nom_categorie',
            'pays.nom_pays'
        )
            ->orderby('appeloffres.created_at', 'desc')
            ->get();

        return response()->json([
            "status" => "success",
            "donnes" => $offres
        ]);



        

    }



    public function details_offre($id)

    {
       if (!Auth()->check()) {
         session()->flash('message', sprintf('Abonnez-vous pour poursuivre la recherche. '));
    return redirect(url('offre-abonnements'));
        }



        $offres = DB::table('appeloffres')

            ->join('autoritecontractantes','autoritecontractantes.id','=','appeloffres.id_autorite')

            ->join('categories','categories.id','=','appeloffres.id_categorie')

            ->join('pays','pays.id','=','autoritecontractantes.id_pays')

            ->where('appeloffres.id','=',$id)

            ->where('appeloffres.status','=',1)

            ->select('appeloffres.*','pays.nom_pays','autoritecontractantes.raison_social','categories.nom_categorie')

            ->get();



        return view('detailsoffre',compact('offres'));



        

    }

    // Autocomplétion pour la recherche de références techniques
    public function autocompleteReference(Request $request)
    {
        $term = $request->get('term');
        $results = DB::table('references')
            ->where('libelle_marche', 'LIKE', '%' . $term . '%')
            ->limit(10)
            ->pluck('libelle_marche');
        return response()->json($results);
    }

    // Autocomplétion pour la recherche de mots-clés de spécifications
    public function autocompleteSpec(Request $request)
    {
        $term = $request->get('term');
        $results = DB::table('specs')
            ->where('libelle', 'LIKE', '%' . $term . '%')
            ->limit(10)
            ->pluck('libelle');
        return response()->json($results);
    }

    // ============== MÉTHODES POUR LES AUTORITÉS CONTRACTANTES ==============

    /**
     * Affiche le formulaire de création d'appel d'offres pour les autorités contractantes
     */
    public function createOffreForm()
    {
        // Vérifier que l'utilisateur est une autorité contractante ou admin
        if (Auth::user()->type_user != 5 && Auth::user()->type_user != 0) {
            return redirect()->route('rechercheoffre')->with('message', 'Accès réservé aux autorités contractantes');
        }

        // Vérifier l'abonnement (sauf pour les admins)
        if (Auth::user()->type_user != 0) {
            $verif = User::verifabonnement(Auth::user());
            if ($verif == null || $verif->date_fin == null || $verif->date_fin < date('Y-m-d')) {
                return redirect()->route('pricing')->with('message', 'Veuillez souscrire à un abonnement pour publier des appels d\'offres');
            }
        }

        // Récupérer l'autorité contractante de l'utilisateur (ou toutes pour l'admin)
        if (Auth::user()->type_user == 0) {
            // Admin : récupérer la première autorité pour le formulaire
            $autorite = DB::table('autoritecontractantes')->first();
        } else {
            $autorite = DB::table('autoritecontractantes')
                ->where('id', Auth::user()->ratache_autorite)
                ->first();
        }

        if (!$autorite) {
            return redirect()->route('rechercheoffre')->with('message', 'Aucune autorité contractante disponible');
        }

        $categories = DB::table('categories')->orderby('nom_categorie')->get();
        $modes = DB::table('modes')->orderby('libelle')->get();
        $secteur_activites = DB::table('secteuractivite')
            ->select('secteuractivite.*')
            ->orderby('libellesecteuractivite')
            ->get();

        return view('appels_offres.create_autorite', compact('autorite', 'categories', 'modes', 'secteur_activites'));
    }

    /**
     * Enregistre un appel d'offres créé par une autorité contractante
     */
    public function storeOffre(Request $request)
    {
        // Vérifier que l'utilisateur est une autorité contractante ou admin
        if (Auth::user()->type_user != 5 && Auth::user()->type_user != 0) {
            return redirect()->route('rechercheoffre')->with('message', 'Accès réservé aux autorités contractantes');
        }

        // Vérifier l'abonnement (sauf pour les admins)
        if (Auth::user()->type_user != 0) {
            $verif = User::verifabonnement(Auth::user());
            if ($verif == null || $verif->date_fin == null || $verif->date_fin < date('Y-m-d')) {
                return redirect()->route('pricing')->with('message', 'Veuillez souscrire à un abonnement pour publier des appels d\'offres');
            }
        }

        // Récupérer l'autorité contractante
        if (Auth::user()->type_user == 0 && $request->has('autorite_id')) {
            // Admin peut choisir l'autorité
            $autorite = DB::table('autoritecontractantes')
                ->where('id', $request->autorite_id)
                ->first();
        } elseif (Auth::user()->type_user == 0) {
            $autorite = DB::table('autoritecontractantes')->first();
        } else {
            $autorite = DB::table('autoritecontractantes')
                ->where('id', Auth::user()->ratache_autorite)
                ->first();
        }

        if (!$autorite) {
            return redirect()->route('rechercheoffre')->with('message', 'Aucune autorité contractante associée à votre compte');
        }

        $data = $request->validate([
            'categorie' => ['required', 'numeric', 'gt:0'],
            'secteur' => ['required'],
            'reference' => ['required', 'max:100'],
            'libelle' => ['required', 'max:254'],
            'source' => ['required', 'max:50'],
            'date_publication' => ['required', 'date'],
            'date_cloture' => ['required', 'date', 'after:date_publication'],
            'description' => ['required'],
            'mode' => ['required'],
            'contact' => ['required'],
        ]);

        $add = DB::table('appeloffres')->insert([
            'id_autorite' => $autorite->id,
            'id_categorie' => $data['categorie'],
            'idsecteuractivite' => $data['secteur'],
            'reference' => $data['reference'],
            'libelle_appel' => $data['libelle'],
            'date_publication' => $data['date_publication'],
            'date_cloture' => $data['date_cloture'],
            'source' => $data['source'],
            'description' => $data['description'],
            'created_by' => Auth::id(),
            'created_at' => NOW(),
            'updated_at' => NOW(),
            'mode_id' => $data['mode'],
            'contact' => $data['contact'],
            'status' => 1, // Actif par défaut pour les autorités
        ]);

        if ($add) {
            return redirect()->route('rechercheoffre')->with('flash_message_success', 'Appel d\'offres publié avec succès');
        } else {
            return redirect()->back()->with('flash_message_error', 'Erreur lors de la publication de l\'appel d\'offres');
        }
    }

    /**
     * Liste les appels d'offres publiés par l'autorité contractante connectée
     */
    public function mesOffres()
    {
        // Vérifier que l'utilisateur est une autorité contractante
        if (Auth::user()->type_user != 5) {
            return redirect()->route('rechercheoffre')->with('message', 'Accès réservé aux autorités contractantes');
        }

        // Récupérer l'autorité contractante de l'utilisateur
        $autorite = DB::table('autoritecontractantes')
            ->where('id', Auth::user()->ratache_autorite)
            ->first();

        if (!$autorite) {
            return redirect()->route('rechercheoffre')->with('message', 'Aucune autorité contractante associée à votre compte');
        }

        $offres = DB::table('appeloffres')
            ->join('autoritecontractantes', 'autoritecontractantes.id', '=', 'appeloffres.id_autorite')
            ->join('categories', 'categories.id', '=', 'appeloffres.id_categorie')
            ->leftjoin('modes', 'modes.id', '=', 'appeloffres.mode_id')
            ->where('appeloffres.id_autorite', $autorite->id)
            ->select('appeloffres.*', 'autoritecontractantes.raison_social', 'categories.nom_categorie', 'modes.libelle')
            ->orderby('appeloffres.created_at', 'desc')
            ->get();

        return view('appels_offres.mes_offres', compact('offres'));
    }

    /**
     * Affiche le formulaire d'édition d'un appel d'offres
     */
    public function editOffre($id)
    {
        // Vérifier que l'utilisateur est une autorité contractante ou admin
        if (Auth::user()->type_user != 5 && Auth::user()->type_user != 0) {
            return redirect()->route('rechercheoffre')->with('message', 'Accès réservé aux autorités contractantes');
        }

        // Récupérer l'offre
        $offre = DB::table('appeloffres')->where('id', $id)->first();
        if (!$offre) {
            return redirect()->route('autorite.mes.offres')->with('flash_message_error', 'Appel d\'offres non trouvé');
        }

        // Vérifier que l'offre appartient à l'autorité de l'utilisateur (sauf admin)
        if (Auth::user()->type_user == 5) {
            $autorite = DB::table('autoritecontractantes')
                ->where('id', Auth::user()->ratache_autorite)
                ->first();
            if (!$autorite || $offre->id_autorite != $autorite->id) {
                return redirect()->route('autorite.mes.offres')->with('flash_message_error', 'Vous n\'avez pas le droit de modifier cet appel d\'offres');
            }
        }

        $categories = DB::table('categories')->orderby('nom_categorie')->get();
        $modes = DB::table('modes')->orderby('libelle')->get();
        $secteur_activites = DB::table('secteuractivite')
            ->select('secteuractivite.*')
            ->orderby('libellesecteuractivite')
            ->get();

        return view('appels_offres.edit_autorite', compact('offre', 'categories', 'modes', 'secteur_activites'));
    }

    /**
     * Met à jour un appel d'offres
     */
    public function updateOffre(Request $request, $id)
    {
        // Vérifier que l'utilisateur est une autorité contractante ou admin
        if (Auth::user()->type_user != 5 && Auth::user()->type_user != 0) {
            return redirect()->route('rechercheoffre')->with('message', 'Accès réservé aux autorités contractantes');
        }

        // Récupérer l'offre
        $offre = DB::table('appeloffres')->where('id', $id)->first();
        if (!$offre) {
            return redirect()->route('autorite.mes.offres')->with('flash_message_error', 'Appel d\'offres non trouvé');
        }

        // Vérifier que l'offre appartient à l'autorité de l'utilisateur (sauf admin)
        if (Auth::user()->type_user == 5) {
            $autorite = DB::table('autoritecontractantes')
                ->where('id', Auth::user()->ratache_autorite)
                ->first();
            if (!$autorite || $offre->id_autorite != $autorite->id) {
                return redirect()->route('autorite.mes.offres')->with('flash_message_error', 'Vous n\'avez pas le droit de modifier cet appel d\'offres');
            }
        }

        $data = $request->validate([
            'categorie' => ['required', 'numeric', 'gt:0'],
            'secteur' => ['required'],
            'reference' => ['required', 'max:100'],
            'libelle' => ['required', 'max:254'],
            'source' => ['required', 'max:50'],
            'date_publication' => ['required', 'date'],
            'date_cloture' => ['required', 'date', 'after:date_publication'],
            'description' => ['required'],
            'mode' => ['required'],
            'contact' => ['required'],
        ]);

        $update = DB::table('appeloffres')
            ->where('id', $id)
            ->update([
                'id_categorie' => $data['categorie'],
                'idsecteuractivite' => $data['secteur'],
                'reference' => $data['reference'],
                'libelle_appel' => $data['libelle'],
                'date_publication' => $data['date_publication'],
                'date_cloture' => $data['date_cloture'],
                'source' => $data['source'],
                'description' => $data['description'],
                'updated_by' => Auth::id(),
                'updated_at' => NOW(),
                'mode_id' => $data['mode'],
                'contact' => $data['contact'],
            ]);

        if ($update) {
            return redirect()->route('autorite.mes.offres')->with('flash_message_success', 'Appel d\'offres modifié avec succès');
        } else {
            return redirect()->back()->with('flash_message_error', 'Erreur lors de la modification de l\'appel d\'offres');
        }
    }

    /**
     * Supprime un appel d'offres
     */
    public function deleteOffre($id)
    {
        // Vérifier que l'utilisateur est une autorité contractante ou admin
        if (Auth::user()->type_user != 5 && Auth::user()->type_user != 0) {
            return redirect()->route('rechercheoffre')->with('message', 'Accès réservé aux autorités contractantes');
        }

        // Récupérer l'offre
        $offre = DB::table('appeloffres')->where('id', $id)->first();
        if (!$offre) {
            return redirect()->route('autorite.mes.offres')->with('flash_message_error', 'Appel d\'offres non trouvé');
        }

        // Vérifier que l'offre appartient à l'autorité de l'utilisateur (sauf admin)
        if (Auth::user()->type_user == 5) {
            $autorite = DB::table('autoritecontractantes')
                ->where('id', Auth::user()->ratache_autorite)
                ->first();
            if (!$autorite || $offre->id_autorite != $autorite->id) {
                return redirect()->route('autorite.mes.offres')->with('flash_message_error', 'Vous n\'avez pas le droit de supprimer cet appel d\'offres');
            }
        }

        $delete = DB::table('appeloffres')->where('id', $id)->delete();

        if ($delete) {
            return redirect()->route('autorite.mes.offres')->with('flash_message_success', 'Appel d\'offres supprimé avec succès');
        } else {
            return redirect()->route('autorite.mes.offres')->with('flash_message_error', 'Erreur lors de la suppression de l\'appel d\'offres');
        }
    }
}

