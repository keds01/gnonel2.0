<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mode;
use App\Spec;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;
use Carbon\Carbon;

class SpecController extends Controller
{
  public function indexAdmin()
  {
    User::admin(Auth::user());
    $specs = Spec::all();
    return view('specs/index_admin', compact('specs'));
  }


  public function index()
  {
    $verif = User::verifabonnement(Auth::user());


    if ($verif->date_fin == null) {
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
      return redirect(route('pricing'));
    }



    $specs = Spec::where('user_id', Auth::user()->id)->get();
    return view('specs/index', compact('specs'));
  }

  public function create()
  {
    if (Auth::user()->role == "user") {

      $verif = User::verifabonnement(Auth::user());


      if ($verif->date_fin == null) {
        return redirect(route('home'));
      } elseif ($verif->date_fin < date('Y-m-d')) {
        session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
        return redirect(route('pricing'));
      }
    }


    $pays = DB::table('pays')->orderby('nom_pays')->get();
    $categories = DB::table('categories')->orderby('code_categorie', 'asc')->get();


    return view('specs/create', compact('categories', 'pays'));
  }

  public function store(Request $request)
  {
    $pays = null;
    if (Auth::user()->role == "user") {
      $pays = User::retrunpays(Auth::user());
    }


    $validator = Validator::make(
      $request->all(),
      [
        'categorie' =>  'required',
        'libelle' =>  'required',
        'fichier' =>  'required',
      ]
    );

    if ($validator->fails()) {
      if (Auth::user()->role == "user") {
        return redirect(route('specifications.create'))->withErrors($validator->errors());
      } else {
        return redirect(route('specifications.createAdmin'))->withErrors($validator->errors());
      }
    } else {
      $spec = new Spec;
      $spec->categorie_id = $request->categorie;
      $spec->user_id = Auth::user()->id;
      $spec->lien = User::showUploadFile($request->file('fichier'));
      $spec->pays_id = $pays;
      $spec->libelle = $request->libelle;
      $spec->contexte = $request->contexte;
      $spec->save();

      if (Auth::user()->role == "user") {
        return redirect()->route('specifications.index')->with('add_ok', 'Spécification ajoutée.');
      } else {
        return redirect()->route('specifications.indexAdmin')->with('add_ok', 'Spécification ajoutée.');
      }
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

    $spec = Spec::find($id);
    $pays = DB::table('pays')->orderby('nom_pays')->get();
    $categories = DB::table('categories')->orderby('code_categorie', 'asc')->get();
    return view('specs/create', compact('spec', 'categories', 'pays'));
  }

  public function update($id, Request $request)
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
        'categorie' =>  'required',
        'libelle' =>  'required',
      ]
    );


    if ($validator->fails()) {
      return redirect(route('specifications.index'))->withErrors($validator->errors());
    } else {
      $spec = Spec::find($id);
      $spec->categorie_id = $request->categorie;
      $spec->libelle = $request->libelle;
      $spec->contexte = $request->contexte;
      if ($request->file('fichier')) {
        Spec::deletefile('/images/uploads/' . $spec->lien);
        $spec->lien = User::showUploadFile($request->file('fichier'));
      }
      $spec->save();
      return redirect()->route('specifications.index')->with('update_ok', '');
    }
  }

  public function valider($id)
  {
    User::admin(Auth::user());
    $spec = Spec::find($id);
    $spec->status = 1;
    $spec->save();
    return redirect()->route('specifications.indexAdmin')->with('valider_ok', '');
  }

  public function rejeter($id)
  {
    User::admin(Auth::user());
    $spec = Spec::find($id);
    $spec->status = 2;
    $spec->save();
    return redirect()->route('specifications.indexAdmin')->with('rejeter_ok', '');
  }


  public function delete($id)
  {
    $spec = Spec::find($id);
    $a = $spec->lien;
    $spec->delete();
    Spec::deletefile('/images/uploads/' . $a);
    return redirect()->route('specifications.index')->with('delete_ok', '');
  }

  public function deleteAdmin($id)
  {
    $spec = Spec::find($id);
    $a = $spec->lien;
    $spec->delete();
    Spec::deletefile('/images/uploads/' . $a);
    return redirect()->route('specifications.indexAdmin')->with('delete_ok', '');
  }

  public function filtrerspec(Request $request)
  {
    $pays = '';
    $categorie = '';
    $recherche = '';
    $pays = $request->pays;
    $categorie = $request->categorie;
    $recherche = $request->recherche;

    $resultat = [];
    if ($pays != '' && $categorie == '' && $recherche == '') {
      $resultat = DB::table('specs')
        ->join('pays', 'pays.id', '=', 'specs.pays_id')
        ->join('categories', 'categories.id', '=', 'specs.categorie_id')
        ->where('specs.pays_id', '=', $pays)
        ->select('specs.*', 'categories.nom_categorie', 'pays.nom_pays')->orderby('specs.libelle', 'asc')->get();
    } elseif ($pays == '' && $categorie != '' && $recherche == '') {
      $resultat = DB::table('specs')
        ->join('pays', 'pays.id', '=', 'specs.pays_id')
        ->join('categories', 'categories.id', '=', 'specs.categorie_id')
        ->where('specs.categorie_id', '=', $categorie)
        ->select('specs.*', 'categories.nom_categorie', 'pays.nom_pays')->orderby('specs.libelle', 'asc')->get();
    } elseif ($pays == '' && $categorie == '' && $recherche != '') {
      $resultat = DB::table('specs')
        ->join('pays', 'pays.id', '=', 'specs.pays_id')
        ->join('categories', 'categories.id', '=', 'specs.categorie_id')
        ->where('specs.libelle', 'like', '%' . $recherche . '%')
        ->select('specs.*', 'categories.nom_categorie', 'pays.nom_pays')->orderby('specs.libelle', 'asc')->get();
    } elseif ($pays != '' && $categorie != '' && $recherche == '') {
      $resultat = DB::table('specs')
        ->join('pays', 'pays.id', '=', 'specs.pays_id')
        ->join('categories', 'categories.id', '=', 'specs.categorie_id')
        ->where('specs.categorie_id', '=', $categorie)
        ->where('specs.pays_id', '=', $pays)
        ->select('specs.*', 'categories.nom_categorie', 'pays.nom_pays')->orderby('specs.libelle', 'asc')->get();
    } elseif ($pays != '' && $categorie != '' && $recherche != '') {
      $resultat = DB::table('specs')
        ->join('pays', 'pays.id', '=', 'specs.pays_id')
        ->join('categories', 'categories.id', '=', 'specs.categorie_id')
        ->where('specs.categorie_id', '=', $categorie)
        ->where('specs.pays_id', '=', $pays)
        ->where('specs.libelle', 'like', '%' . $recherche . '%')
        ->select('specs.*', 'categories.nom_categorie', 'pays.nom_pays')->orderby('specs.libelle', 'asc')->get();
    } else {
    }

    return response()->json(
      [
        "status" => "success",
        "donnes" => $resultat

      ]
    );
  }
  
  /**
   * Fournit des suggestions pour l'autocomplétion de la barre de recherche
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getSearchSuggestions(Request $request)
  {
    try {
      // Récupérer les paramètres de la requête et les assainir
      $term = trim($request->input('term', ''));
      
      // Garantir des résultats même si term est vide
      if (empty($term)) {
        // Retourner quelques spécifications par défaut
        $defaultSpecs = DB::table('specs')->select('libelle')->distinct()->limit(5)->get();
        $suggestions = [];
        foreach ($defaultSpecs as $spec) {
          $suggestions[] = $spec->libelle;
        }
        
        if (!empty($suggestions)) {
          return response()->json($suggestions);
        } else {
          return response()->json(['Essayez de taper un mot clé']);
        }
      }
      
      // Récupérer des données d'exemple pour déboguer
      $sampleSpecs = DB::table('specs')->select('id', 'libelle')->limit(3)->get();
      \Log::info('Exemples de spécifications:', ['samples' => $sampleSpecs]);
      
      // Requête simple
      $results = DB::table('specs')
        ->where('libelle', 'like', '%' . $term . '%')
        ->select('libelle')
        ->distinct()
        ->orderBy('libelle')
        ->limit(10)
        ->get();
        
      // Log des résultats
      \Log::info('Résultats pour "' . $term . '"', ['count' => count($results)]);
      
      if (count($results) == 0) {
        // Si aucun résultat, renvoyer des suggestions de secours
        $backupResults = DB::table('specs')
          ->select('libelle')
          ->distinct()
          ->orderBy('libelle')
          ->limit(5)
          ->get();
          
        if (count($backupResults) > 0) {
          $backupSuggestions = [];
          foreach ($backupResults as $result) {
            $backupSuggestions[] = $result->libelle;
          }
          return response()->json($backupSuggestions);
        } else {
          return response()->json(['Aucun résultat disponible']);
        }
      }
      
      // Extraire les libellés
      $suggestions = [];
      foreach ($results as $result) {
        $suggestions[] = $result->libelle;
      }
      
      return response()->json($suggestions);
    } catch (\Exception $e) {
      \Log::error('Erreur dans getSearchSuggestions: ' . $e->getMessage());
      
      // En cas d'erreur, retourner des données factices pour tester l'interface
      return response()->json([
        'Spécification 1', 
        'Spécification 2', 
        'Spécification 3'
      ]);
    }
  }
}
