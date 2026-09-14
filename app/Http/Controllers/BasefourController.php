<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Zone;
use App\Basefour;
use Carbon\Carbon;

class BasefourController extends Controller
{
  public function index()
  {
    $verif = User::verifabonnement(Auth::user());
    $pays = null;
    $idpays = "";
    $bases = Basefour::where('user_id', '=', Auth::user()->id)->get();
    
    if (Auth::user()->ratache_operateur != null) {
      $idpays = DB::table('operateurs')->where('id', Auth::user()->ratache_operateur)->first()->id_pays;
    }
    if (Auth::user()->ratache_autorite != null) {
      $idpays = DB::table('autoritecontractantes')->where('id', Auth::user()->ratache_autorite)->first()->id_pays;
    }

    if ($verif->date_fin == null) {
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
      return redirect(route('pricing'));
    }

    if ($verif->oper_local == 1 && $verif->oper_international == 1) {
      $pays = DB::table('pays')->orderby('nom_pays')->get();
    } else {
      $pays = DB::table('pays')->where('id', $idpays)->get();
      //n'afficher que le pays que la personne qui s'est connecter
    }
    
    // Récupération des catégories pour le filtre
    $categories = DB::table('categories')->orderby('nom_categorie', 'asc')->get();

    $bases = Basefour::where('user_id', '=', Auth::user()->id)->get();
    return view('bases/index', compact('bases', 'pays', 'categories'));
  }
  public function create()
  {
    $verif = User::verifabonnement(Auth::user());
    $abon = \app\User::verifabonnement(\Illuminate\Support\Facades\Auth::user());
    $pays = null;
    $idpays = "";
    $bases = Basefour::where('user_id', '=', Auth::user()->id)->get();
    if (Auth::user()->ratache_operateur != null) {
      $idpays = DB::table('operateurs')->where('id', Auth::user()->ratache_operateur)->first()->id_pays;
    }
    if (Auth::user()->ratache_autorite != null) {
      $idpays = DB::table('autoritecontractantes')->where('id', Auth::user()->ratache_autorite)->first()->id_pays;
    }

    if ($verif->date_fin == null) {
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
      return redirect(route('pricing'));
    }

    if ($abon->oper_local == 1 && $abon->oper_international == 1) {
      $pays = DB::table('pays')->orderby('nom_pays')->get();
    } else {
      $pays = DB::table('pays')->where('id', $idpays)->get();
      //n'afficher que le pays que la personne qui s'est connecter
    }

    //$pays = DB::table('pays')->orderby('nom_pays')->get();
    $zones = Zone::all();

    return view('bases/create', compact('zones', 'pays', 'bases'));
  }

  public function store(Request $request)
  {
    $verif = User::verifabonnement(Auth::user());
    if ($verif->date_fin == null) {
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
      return redirect(route('pricing'));
    }

    $validator = Validator::make(
      $request->all(),

      [
        'structure' =>  'required',
      ]
    );



    if ($validator->fails()) {

      return redirect(route('basefournisseurs.create'))->withErrors($validator->errors());
    } else {

      $basefour = new Basefour;
      $basefour->user_id = Auth::user()->id;
      $basefour->operateur_id = $request->structure;
      // $basefour->numerorccm = $request->numerorccm;
      $basefour->save();
      return redirect()->route('basefournisseurs.create')->with('add_ok', '');
    }
  }

  public function edit($id)
  {
    $verif = User::verifabonnement(Auth::user());

    if ($verif->date_fin == null) {
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
      return redirect(route('pricing'));
    }

    $base = Basefour::find($id);
    $pays = DB::table('pays')->orderby('nom_pays')->get();
    $zones = Zone::all();
    $bases = Basefour::where('user_id', '=', Auth::user()->id)->get();
    return view('bases/create', compact('base', 'bases', 'pays', 'zones'));
  }

  public function update($id, Request $request)
  {
    $basefour = Basefour::find($id);
    $validator = Validator::make(
      $request->all(),

      [
        'structure' =>  'required',
      ]
    );



    if ($validator->fails()) {

      return redirect(route('basefournisseurs.edit', 'base'))->withErrors($validator->errors());
    } else {

      $basefour->operateur_id = $request->structure;
      // $basefour->numerorccm = $request->numerorccm;
      $basefour->save();
      return redirect()->route('basefournisseurs.create')->with('update_ok', '');
    }
  }

  public function delete($id)
  {
    $base = Basefour::find($id);
    $base->delete();
    return redirect()->route('basefournisseurs.create')->with('delete_ok', '');
  }

  public function searchreference(Request $request)
  {
    \Log::info('Recherche de références - Paramètres', [
      'pays_id' => $request->pays,
      'categorie_id' => $request->categorie,
      'terme_recherche' => $request->reference,
      'session_id' => session()->getId()
    ]);
    
    if (empty($request->pays)) {
      \Log::warning('Recherche de références: Pays non spécifié');
      return response()->json(['data' => [], 'pagination' => '']);
    }
    try {
      $query = DB::table('references')
        ->join('operateurs', 'operateurs.id', '=', 'references.operateur')
        ->join('pays', 'pays.id', '=', 'operateurs.id_pays')
        ->join('categories', 'categories.id', '=', 'references.type_marche');
      $query->where('references.status', '=', 1)
            ->where('pays.id', '=', $request->pays);
      if (!empty($request->categorie)) {
        $query->where('references.type_marche', '=', $request->categorie);
        \Log::info('Filtrage par catégorie: ' . $request->categorie);
      }
      if (!empty($request->reference)) {
        $searchTerm = '%' . $request->reference . '%';
        $query->where(function($q) use ($searchTerm) {
          $q->where('references.libelle_marche', 'like', $searchTerm)
            ->orWhere('references.reference_marche', 'like', $searchTerm)
            ->orWhere('operateurs.raison_social', 'like', $searchTerm);
        });
        \Log::info('Filtrage par terme de recherche: ' . $request->reference);
      }
      $references = $query->select(
          'references.idreference',
          'references.libelle_marche',
          'references.reference_marche',
          'references.annee_execution',
          'operateurs.raison_social as operateur', 
          'operateurs.gnonelid as gnonelid', 
          'pays.id as pays_id', 
          'operateurs.id as operateur_id',
          'categories.nom_categorie as categorie'
      )
      ->orderBy('references.created_at', 'desc')
      ->paginate(10);
      // Limiter la longueur des textes
      foreach ($references as $reference) {
        if (strlen($reference->libelle_marche) > 200) {
          $reference->libelle_marche = substr($reference->libelle_marche, 0, 200) . '...';
        }
      }
      // Si AJAX, retourner JSON paginé
      if ($request->ajax()) {
        return response()->json([
          'data' => $references->items(),
          'pagination' => $references->links()->render()
        ]);
      }
      // Sinon, comportement existant (à adapter si besoin)
      return response()->json($references);
    } catch (\Exception $e) {
      \Log::error('Erreur dans searchreference: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
      ]);
      return response()->json([
        'error' => true,
        'message' => $e->getMessage()
      ], 500);
    }
  }
  
  /**
   * Fournit des suggestions pour l'autocomplétion des références
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getReferenceSuggestions(Request $request)
  {
    try {
      $term = trim($request->input('term', ''));
      
      // Requête pour trouver les références correspondantes
      $results = DB::table('references')
        ->where('libelle_marche', 'like', '%' . $term . '%')
        ->orWhere('reference_marche', 'like', '%' . $term . '%')
        ->select('libelle_marche', 'reference_marche')
        ->distinct()
        ->limit(10)
        ->get();
      
      // Extraire les suggestions
      $suggestions = [];
      foreach ($results as $result) {
        // Ajouter le libellé avec la référence entre parenthèses
        $suggestions[] = $result->libelle_marche . ' (' . $result->reference_marche . ')';
      }
      
      return response()->json($suggestions);
    } catch (\Exception $e) {
      \Log::error('Erreur dans getReferenceSuggestions: ' . $e->getMessage());
      return response()->json([]);
    }
  }
  
  /**
   * Fournit des suggestions pour l'autocomplétion des numéros RCCM
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getRccmSuggestions(Request $request)
  {
    try {
      $term = trim($request->input('term', ''));
      
      // Requête pour trouver les numéros RCCM correspondants
      $results = DB::table('operateurs')
        ->where('numerorccm', 'like', '%' . $term . '%')
        ->select('numerorccm', 'raison_social')
        ->distinct()
        ->limit(10)
        ->get();
      
      // Extraire les suggestions
      $suggestions = [];
      foreach ($results as $result) {
        // Ajouter le numéro RCCM avec la raison sociale entre parenthèses
        if (!empty($result->numerorccm)) {
          $suggestions[] = $result->numerorccm . ' (' . $result->raison_social . ')';
        }
      }
      
      return response()->json($suggestions);
    } catch (\Exception $e) {
      \Log::error('Erreur dans getRccmSuggestions: ' . $e->getMessage());
      return response()->json([]);
    }
  }
  
  /**
   * Récupère les suggestions d'autocomplétion pour la recherche de références
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function autocomplete(Request $request)
  {
    \Log::info('Recherche autocompletion basefournisseurs - Paramètres', [
      'term' => $request->term,
      'pays_id' => $request->pays_id,
      'categorie_id' => $request->categorie_id,
      'session_id' => session()->getId()
    ]);
    
    if (empty($request->term) || strlen($request->term) < 2) {
      return response()->json([]);
    }
    
    try {
      $searchTerm = '%' . $request->term . '%';
      $query = DB::table('references')
        ->join('operateurs', 'operateurs.id', '=', 'references.operateur')
        ->join('pays', 'pays.id', '=', 'operateurs.id_pays')
        ->join('categories', 'categories.id', '=', 'references.type_marche')
        ->where('references.status', '=', 1);
      
      // Filtrer par pays si spécifié
      if (!empty($request->pays_id)) {
        $query->where('pays.id', '=', $request->pays_id);
      }
      
      // Filtrer par catégorie si spécifié
      if (!empty($request->categorie_id)) {
        $query->where('references.type_marche', '=', $request->categorie_id);
      }
      
      // Rechercher dans les champs pertinents
      $query->where(function($q) use ($searchTerm) {
        $q->where('references.libelle_marche', 'like', $searchTerm)
          ->orWhere('references.reference_marche', 'like', $searchTerm)
          ->orWhere('operateurs.raison_social', 'like', $searchTerm);
      });
      
      // Sélectionner uniquement les champs nécessaires pour éviter les doublons
      $suggestions = $query->select(
          'references.libelle_marche',
          'references.reference_marche',
          'operateurs.raison_social as operateur_nom'
      )
      ->distinct()
      ->limit(10)
      ->get();
      
      // Formater les résultats pour l'autocomplétion
      $results = [];
      foreach ($suggestions as $suggestion) {
        // Ajouter les libellés de marché
        if (!empty($suggestion->libelle_marche) && stripos($suggestion->libelle_marche, $request->term) !== false) {
          $results[] = [
            'value' => $suggestion->libelle_marche,
            'label' => $suggestion->libelle_marche . ' (Libellé)'
          ];
        }
        
        // Ajouter les références de marché
        if (!empty($suggestion->reference_marche) && stripos($suggestion->reference_marche, $request->term) !== false) {
          $results[] = [
            'value' => $suggestion->reference_marche,
            'label' => $suggestion->reference_marche . ' (Référence)'
          ];
        }
        
        // Ajouter les noms d'opérateurs
        if (!empty($suggestion->operateur_nom) && stripos($suggestion->operateur_nom, $request->term) !== false) {
          $results[] = [
            'value' => $suggestion->operateur_nom,
            'label' => $suggestion->operateur_nom . ' (Opérateur)'
          ];
        }
        

      }
      
      // Supprimer les doublons et limiter à 10 résultats
      $uniqueResults = [];
      $seenValues = [];
      
      foreach ($results as $result) {
        if (!in_array($result['value'], $seenValues)) {
          $seenValues[] = $result['value'];
          $uniqueResults[] = $result;
          
          if (count($uniqueResults) >= 10) {
            break;
          }
        }
      }
      
      return response()->json($uniqueResults);
      
    } catch (\Exception $e) {
      \Log::error('Erreur dans autocomplete: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
      ]);
      return response()->json([]);
    }
  }
}
