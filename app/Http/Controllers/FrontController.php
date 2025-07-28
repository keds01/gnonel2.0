<?php

namespace App\Http\Controllers;

use App\Abonnement;
use App\Categorieabonnement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Spec;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Request as FacadesRequest;

class FrontController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function viewmesref()
  {
    $verif = User::verifabonnement(Auth::user());


    $countRef = 0;
    $canPublish = true;

    $categories = DB::table('categories')->orderby('code_categorie', 'asc')->get();
    $categorieautorites = DB::table('categorieautorites')->orderby('id', 'asc')->get();
    $pays = DB::table('pays')->get();

    if ($verif == null && Auth::user()->type_user == 3) {
      $valide = DB::table('references')->where('status', '=', 1)->where('references.user_id', '=', Auth::user()->id)->count();
      $refuse = DB::table('references')->where('status', '=', 2)->where('references.user_id', '=', Auth::user()->id)->count();
      $att = DB::table('references')->where('status', '=', 0)->where('references.user_id', '=', Auth::user()->id)->count();

      $references = DB::table('references')
        ->where('references.user_id', '=', Auth::user()->id)
        ->orderby('references.created_at', 'desc')
        ->get();

      if ($references->count() >= 2) {
        $canPublish = false;
      }
    } else {
      if ($verif->date_fin == null) {
        return redirect(route('home'));
      } elseif ($verif->date_fin < date('Y-m-d')) {
        session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
        return redirect(route('pricing'));
      }


      $valide = DB::table('references')->where('status', '=', 1)->where('references.operateur', '=', Auth::user()->ratache_operateur)->count();
      $refuse = DB::table('references')->where('status', '=', 2)->where('references.operateur', '=', Auth::user()->ratache_operateur)->count();
      $att = DB::table('references')->where('status', '=', 0)->where('references.operateur', '=', Auth::user()->ratache_operateur)->count();

      $references = DB::table('references')
        ->where('references.operateur', '=', Auth::user()->ratache_operateur)
        ->orderby('references.created_at', 'desc')
        ->get();
    }


    return view('index_user_operateur', compact('references', 'valide', 'pays', 'refuse', 'att', 'categories', 'categorieautorites', 'canPublish', 'verif'));
  }

  function editmesref($id)
  {

    $categories = DB::table('categories')->orderby('code_categorie', 'asc')->get();
    $categorieautorites = DB::table('categorieautorites')->orderby('id', 'asc')->get();
    $pays = DB::table('pays')->get();
    $reference = DB::table('references')
      ->where('references.operateur', '=', Auth::user()->ratache_operateur)
      ->where('references.idreference', '=', $id)
      ->orderby('references.created_at', 'desc')
      ->first();
    return view('edit_user_operateur', compact('reference', 'pays', 'categories', 'categorieautorites'));
  }

  function updatemesref($id, Request $request)
  {
    $data = request()->validate([
      'reference' => ['required', 'max:50'],
      'sous_traitance' => ['nullable'],
      'autorite' => ['required'],
      'annee_execution' => ['required'],
      'type' => ['required', 'numeric', 'gt:0'],
      'marche' => ['required', 'string'],
      'compte' => ['required', 'string'],
      'groupement' => ['required', 'string'],
      'montant' => ['nullable', 'numeric'],
      'date' => ['nullable'],
      'show_amount' => ['nullable', 'numeric'],
    ]);



    if ($request->file('preuve') == null) {
      $add = DB::table('references')
        ->where('idreference', $id)
        ->update([
          'date_contrat' => $data['date'],
          'reference_marche' => $data['reference'],
          'libelle_marche' => $data['marche'],
          'type_marche' => $data['type'],
          'montant' => $data['montant'],
          'show_amount' => $data['show_amount'],
          'annee_execution' => $data['annee_execution'],
          'autorite_contractante' => $data['autorite'],
          'sous_traitance' => $data['sous_traitance'],
          'compte' => $data['compte'],
          'groupement' => $data['groupement'],
          'status' => 0,
          'created_at' => NOW(),
          'updated_at' => NOW(),
        ]);
    } else {
      $add = DB::table('references')
        ->where('idreference', $id)
        ->update([
          'date_contrat' => $data['date'],
          'reference_marche' => $data['reference'],
          'libelle_marche' => $data['marche'],
          'type_marche' => $data['type'],
          'montant' => $data['montant'],
          'show_amount' => $data['show_amount'],
          'annee_execution' => $data['annee_execution'],
          'preuve_execution' => User::showUploadFile($request->file('preuve')),
          'autorite_contractante' => $data['autorite'],
          'sous_traitance' => $data['sous_traitance'],
          'compte' => $data['compte'],
          'groupement' => $data['groupement'],
          'status' => 0,
          'created_at' => NOW(),
          'updated_at' => NOW(),
        ]);
    }

    return redirect(route('viewmesref'))->with('update_ok', '');
  }
  function deletemesref($id)
  {
    $delete = DB::table('references')->where('idreference', '=', $id)->delete();
    if ($delete) {
      return redirect()->route('viewmesref')->with('delete_ok', '');
    }
    return redirect()->route('viewmesref');
  }



  // tous les reference pour l'autorité
  public function viewallref($id, Request $request)
  {
    $verif = User::verifabonnement(Auth::user());

    if ($verif->date_fin == null) {
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
      return redirect(route('pricing'));
    }
    $user = $id;
    //$valide=DB::table('references') ->where('status', '=', 1)->count();
    //$refuse=DB::table('references') ->where('status', '=', 2)->count();
    $att = DB::table('references')->where('status', '=', 0)->count();
    \Log::info('Recherche de références pour opérateur - Paramètres', [
    'operateur_id' => $user,
    'terme_recherche' => $request->reference,
    'session_id' => session()->getId()
  ]);
  
  $references = DB::table('references')
    ->join('operateurs', 'operateurs.id', '=', 'references.operateur')
    ->join('pays', 'pays.id', '=', 'operateurs.id_pays')
    ->join('autoritecontractantes', 'autoritecontractantes.id', '=', 'references.autorite_contractante')
    ->join('pays as p', 'p.id', '=', 'autoritecontractantes.id_pays')
    ->join('categories', 'categories.id', '=', 'references.type_marche')
    // ->join('users', 'users.ratache_operateur', '=', 'operateurs.id')
    ->where('references.operateur', '=', $user)
    ->where('references.status', '=', 1)
    ->when($request->gnonelid, function ($query, $gnonelid) {
      return $query->where('operateurs.gnonelid', 'LIKE', '%' . $gnonelid . '%');
    })
    ->when($request->reference, function ($query, $searchTerm) {
      \Log::info('Filtrage par terme de recherche avancée: ' . $searchTerm);
      $searchTermWithWildcards = '%' . $searchTerm . '%';
      return $query->where(function($q) use ($searchTermWithWildcards) {
        $q->where('references.libelle_marche', 'like', $searchTermWithWildcards)
          ->orWhere('references.reference_marche', 'like', $searchTermWithWildcards)
          ->orWhere('operateurs.raison_social', 'like', $searchTermWithWildcards)
          ->orWhere('autoritecontractantes.raison_social', 'like', $searchTermWithWildcards);
      });
    })
    ->select('references.*', 'autoritecontractantes.raison_social as autorite_contractante', 'nom_categorie', 'pays.nom_pays as paysau', 'operateurs.raison_social')
    ->orderby('references.created_at', 'desc')
    ->get();
    // return view('index_user_autorite',compact('references','valide','refuse','att'));

    return response()->json(
      [
        "status" => "success",
        "donnes" => $references

      ]
    );
  }
  
  /**
   * Récupère les suggestions d'autocomplétion pour la recherche de références
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function autocomplete(Request $request)
  {
    \Log::info('Recherche autocompletion - Paramètres', [
      'term' => $request->term,
      'operateur_id' => $request->operateur_id,
      'session_id' => session()->getId()
    ]);
    
    if (empty($request->term) || strlen($request->term) < 2) {
      return response()->json([]);
    }
    
    try {
      $searchTerm = '%' . $request->term . '%';
      $query = DB::table('references')
        ->join('operateurs', 'operateurs.id', '=', 'references.operateur')
        ->join('autoritecontractantes', 'autoritecontractantes.id', '=', 'references.autorite_contractante')
        ->where('references.status', '=', 1);
      
      // Si un opérateur est spécifié, limiter les résultats à cet opérateur
      if (!empty($request->operateur_id)) {
        $query->where('references.operateur', '=', $request->operateur_id);
      }
      
      // Rechercher dans les champs pertinents
      $query->where(function($q) use ($searchTerm) {
        $q->where('references.libelle_marche', 'like', $searchTerm)
          ->orWhere('references.reference_marche', 'like', $searchTerm)
          ->orWhere('operateurs.raison_social', 'like', $searchTerm)
          ->orWhere('autoritecontractantes.raison_social', 'like', $searchTerm);
      });
      
      // Sélectionner uniquement les champs nécessaires pour éviter les doublons
      $suggestions = $query->select(
          'references.libelle_marche',
          'references.reference_marche',
          'operateurs.raison_social as operateur_nom',
          'autoritecontractantes.raison_social as autorite_nom'
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
        
        // Ajouter les noms d'autorités contractantes
        if (!empty($suggestion->autorite_nom) && stripos($suggestion->autorite_nom, $request->term) !== false) {
          $results[] = [
            'value' => $suggestion->autorite_nom,
            'label' => $suggestion->autorite_nom . ' (Autorité)'
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

  public function enregoperateur()
  {
    $data = request()->validate([
      'nomop' => ['required'],
      'paysop' => ['required'],
      'numop' => ['nullable'],
      'mailop' => ['nullable'],
      'secteur' => ['nullable'],
      'autreop' => ['nullable'],
    ]);
    $indicatif = DB::table('pays')->where('id', $data['paysop'])->first()->indicatif;
    $lastid = DB::table('operateurs')->orderby('id', 'desc')->first()->id + 1;
    $gnonel = $indicatif . "2" . str_pad($lastid, 7, "0", STR_PAD_LEFT);
    $adduser = DB::table('operateurs')->insert([
      'raison_social' => $data['nomop'],
      'id_pays' => $data['paysop'],
      'des_operateur' => $data['autreop'],
      'num_fiscal' => $data['numop'],
      'gnonelid' => $gnonel,
      'mail' => $data['mailop'],
      'secteuractivite_id' => $data['secteur'],
      'created_at' => NOW(),
      'updated_at' => NOW(),

    ]);
    return redirect()->back()->with('flash_message_success', 'Enregistrement effectué avec succès. Vous le trouverez dans la liste des opérateurs');
  }
  public function enregautorite()
  {
    $data = request()->validate([
      'nomaut' => ['required'],
      'paysaut' => ['required'],
      'typeaut' => ['nullable'],
      'autreaut' => ['nullable'],
    ]);
    $indicatif = DB::table('pays')->where('id', $data['paysaut'])->first()->indicatif;
    $lastid = DB::table('autoritecontractantes')->orderby('id', 'desc')->first()->id + 1;
    $gnonel = $indicatif . "2" . str_pad($lastid, 7, "0", STR_PAD_LEFT);
    $adduser = DB::table('autoritecontractantes')->insert([
      'raison_social' => $data['nomaut'],
      'id_pays' => $data['paysaut'],
      'gnonelid' => $gnonel,
      'des_autorite' => $data['autreaut'],
      'categorieautorite_id' => $data['typeaut'],
      'created_at' => NOW(),
      'updated_at' => NOW(),
    ]);
    return redirect()->back()->with('flash_message_success', 'Enregistrement effectué avec succès. Vous le trouverez dans la liste des autorités contractantes');
  }
  public function enregreference(Request $request)
  {
    $verif = User::verifabonnement(Auth::user());

    if ($verif == null && Auth::user()->type_user == 3) {
    } else {
      if ($verif->date_fin == null) {
        return redirect(route('home'));
      } elseif ($verif->date_fin < date('Y-m-d')) {
        session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
        return redirect(route('pricing'));
      }
    }

    $data = request()->validate([
      'reference' => ['required'],
      'marche' => ['required'],
      'type' => ['nullable'],
      'annee_execution' => ['nullable'],
      'autorite' => ['required'],
      'sous_traitance' => ['nullable'],
      'compte' => ['nullable'],
      'groupement' => ['nullable'],
      'date' => ['nullable'],
      'montant' => ['nullable'],
      'show_amount' => ['nullable'],
    ]);



    if ($verif == null && Auth::user()->type_user == 3) {
      $add = DB::table('references')->insert([
        'reference_marche' => $data['reference'],
        'numeroreference' => User::genereId(),
        'libelle_marche' => $data['marche'],
        'type_marche' => $data['type'],
        'annee_execution' => $data['annee_execution'],
        'autorite_contractante' => $data['autorite'],
        'sous_traitance' => $data['sous_traitance'],
        'user_id' => Auth::user()->id,
        'compte' => $data['compte'],
        'operateur' => Auth::user()->ratache_operateur,
        'groupement' => $data['groupement'],
        'date_contrat' => $data['date'],
        'show_amount' => $data['show_amount'],
        'status' => 0,
        'created_at' => NOW(),
        'updated_at' => NOW(),
        'preuve_execution' => User::showUploadFile($request->file('preuve')),
        'montant' => $data['montant'],
      ]);
    } else {
      $add = DB::table('references')->insert([
        'reference_marche' => $data['reference'],
        'numeroreference' => User::genereId(),
        'libelle_marche' => $data['marche'],
        'type_marche' => $data['type'],
        'annee_execution' => $data['annee_execution'],
        'autorite_contractante' => $data['autorite'],
        'show_amount' => $data['show_amount'],
        'sous_traitance' => $data['sous_traitance'],
        'operateur' => Auth::user()->ratache_operateur,
        'compte' => $data['compte'],
        'groupement' => $data['groupement'],
        'date_contrat' => $data['date'],
        'status' => 0,
        'created_at' => NOW(),
        'updated_at' => NOW(),
        'preuve_execution' => User::showUploadFile($request->file('preuve')),
        'montant' => $data['montant'],
      ]);
    }

    return redirect()->back();
  }

  public function infocompte()
  {
    // Récupérer les informations d'abonnement, mais ne pas bloquer l'accès
    $verif = User::verifabonnement(Auth::user());
    $abonnementActif = true;
    $messageAbonnement = null;
  
    // Vérifier le statut d'abonnement pour l'affichage, mais pas pour bloquer l'accès
    if ($verif == null || $verif->date_fin == null) {
      $abonnementActif = false;
      $messageAbonnement = "Vous n'avez pas d'abonnement actif";
    } elseif ($verif->date_fin < date('Y-m-d')) {
      $abonnementActif = false;
      $messageAbonnement = 'Votre abonnement a expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y');
    }
  
    $operateur = DB::table('operateurs')
      ->join('pays', 'pays.id', '=', 'operateurs.id_pays')
      ->leftjoin('secteuractivite', 'secteuractivite.idsecteuractivite', '=', 'operateurs.secteuractivite_id')
      ->select('operateurs.*', 'pays.nom_pays')
      ->where('operateurs.id', '=', Auth::user()->ratache_operateur)->first();
  
    return view('abonnes/infocompte', compact('operateur', 'abonnementActif', 'messageAbonnement'));
  }

  public function infocompteaut()
  {
    $verif = User::verifabonnement(Auth::user());

    if ($verif->date_fin == null) {
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
      return redirect(route('pricing'));
    }
    $autorite = DB::table('autoritecontractantes')
      ->join('pays', 'pays.id', '=', 'autoritecontractantes.id_pays')
      ->select('autoritecontractantes.*', 'pays.nom_pays')
      ->where('autoritecontractantes.id', '=', Auth::user()->ratache_autorite)->first();
    // dd(Auth::user()->ratache_autorite);
    return view('abonnes/infocompteaut', compact('autorite'));
  }

  public function detailsreference($id)
  {
    // Vérification de l'abonnement de l'utilisateur courant
    $verif = User::verifabonnement(Auth::user());
  
    // Obtenir l'ID de l'opérateur associé à cette référence
    $oper_id = DB::table('references')
      ->join('operateurs', 'operateurs.id', '=', 'references.operateur')
      ->where('references.idreference', $id)
      ->select('operateurs.id')
      ->first()->id;
  
    // Informations de l'opérateur pour le debug
    $operateur_info = DB::table('operateurs')->where('id', $oper_id)->first();
    $debug_info = [
      'operateur_id' => $oper_id, 
      'raison_social' => $operateur_info->raison_social ?? 'N/A',
      'timestamp' => date('Y-m-d H:i:s')
    ];
  
    // Vérification de l'abonnement de l'utilisateur courant
    if ($verif->date_fin == null) {
      \Log::info('Utilisateur courant sans abonnement', $debug_info);
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      \Log::info('Abonnement de l\'utilisateur courant expiré', array_merge($debug_info, [
        'date_fin_user' => $verif->date_fin,
        'aujourd\'hui' => date('Y-m-d')
      ]));
      session()->flash('message', sprintf('Veuillez vous réabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
      return redirect(route('pricing'));
    }
  
    // NOUVELLE LOGIQUE: Récupérer TOUS les utilisateurs associés à cet opérateur
    $users_ref = User::where('ratache_operateur', $oper_id)->get();
  
    \Log::info('Vérification des utilisateurs associés à l\'opérateur', array_merge($debug_info, [
      'nombre_utilisateurs_associes' => count($users_ref)
    ]));
  
    // Si aucun utilisateur n'est associé à cet opérateur
    if ($users_ref->isEmpty()) {
      \Log::info('Aucun utilisateur associé à cet opérateur', $debug_info);
      return back()->with('add_ok', '');
    }
  
    // Vérifier si au moins un utilisateur a un abonnement valide
    $abonnement_valide = false;
    $utilisateurs_verifies = [];
  
    foreach ($users_ref as $user) {
      $user_info = [
        'user_id' => $user->id,
        'name' => $user->name,
        'date_fin' => $user->date_fin ?? 'NULL'
      ];
      
      // Un abonnement est valide si date_fin existe et est dans le futur
      if ($user->date_fin !== null && $user->date_fin >= date('Y-m-d')) {
        $abonnement_valide = true;
        $user_info['abonnement_valide'] = true;
      } else {
        $user_info['abonnement_valide'] = false;
        if ($user->date_fin === null) {
          $user_info['raison'] = 'Pas de date de fin';
        } else {
          $user_info['raison'] = 'Abonnement expiré';
        }
      }
      
      $utilisateurs_verifies[] = $user_info;
    }
  
    \Log::info('Résultat de la vérification des abonnements', array_merge($debug_info, [
      'abonnement_valide_trouve' => $abonnement_valide,
      'utilisateurs' => $utilisateurs_verifies
    ]));
  
    // Si aucun abonnement valide n'est trouvé, demander une recommandation
    if (!$abonnement_valide) {
      \Log::info('Aucun abonnement valide trouvé pour cet opérateur', $debug_info);
      return back()->with('add_ok', '');
    }
  
    // Si un abonnement valide est trouvé, permettre l'accès aux détails
    $reference = DB::table('references')
      ->where('references.idreference', '=', $id)
      ->first();

    \Log::info('Accès autorisé aux détails de la référence', array_merge($debug_info, [
      'reference_id' => $id
    ]));
  
    return view('detailsreference', compact('reference'));
  }

  public function selectoperateur(Request $request)
  {
    $verif = User::verifabonnement(Auth::user());

    $abon = \app\User::verifabonnement(\Illuminate\Support\Facades\Auth::user());
    $pays = null;
    $idpays = "";

    if (Auth::user()->ratache_operateur != null) {
      $idpays = DB::table('operateurs')->where('id', Auth::user()->ratache_operateur)->first()->id_pays;
    }
    if (Auth::user()->ratache_autorite != null) {
      $idpays = DB::table('autoritecontractantes')->where('id', Auth::user()->ratache_autorite)->first()->id_pays;
    }

    if ($verif->date_fin == null) {
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      return redirect(route('pricing'));
    }

    if ($abon->oper_local == 1 && $abon->oper_international == 1) {
      $pays = DB::table('pays')->get();
    } else {
      $pays = DB::table('pays')->where('id', $idpays)->get();
      //n'afficher que le pays que la personne qui s'est connecter
    }

    $paysId = isset($request->pays_id) ? $request->pays_id : 0;
    $operateurId = isset($request->operateur_id) ? $request->operateur_id : 0;


    return view('view_select_operateur', compact('pays', 'paysId', 'operateurId'));
  }

  public function service()
  {
    return view('landing.services');
  }
  public function service_business()
  {
    return view('landing.services.business');
  }
  public function service_pro()
  {
    return view('landing.services.pro');
  }
  public function service_mix()
  {
    return view('landing.services.mix');
  }
  public function service_options()
  {
    return view('landing.services.options');
  }

  public function contact()
  {
    return view('landing.contact');
  }

  public function pricing(FacadesRequest $request)
  {
    $pays = DB::table('pays')->orderby('nom_pays')->get();

    if (request('affiliation') != null) {
      session([
        'affiliation' => request('affiliation')
      ]);
    }
    $abonnements = Abonnement::all();
    $categories = Categorieabonnement::all();

    return view('landing.pricing', compact('pays', 'categories', 'abonnements'));
  }

  public function listspec()
  {
    $specs = Spec::where('status', 1)->paginate(12);
    $pays = DB::table('pays')->orderby('nom_pays')->get();
    $categories = DB::table('categories')->orderby('code_categorie', 'asc')->get();

    return view('spec', compact('specs', 'pays', 'categories'));
  }
  public function listspecabonne()
  {
    $verif = User::verifabonnement(Auth::user());

    $specs = Spec::where('specs.status', 1);

    if ($verif == null && Auth::user()->type_user == 3) {
      $specs =  $specs->join('users', 'users.id', '=', 'specs.user_id')->where('users.role', 'admin');
    } else {
      if ($verif->date_fin == null) {
        return redirect(route('home'));
      } elseif ($verif->date_fin < date('Y-m-d')) {
        session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
        return redirect(route('pricing'));
      }
    }

    $specs = $specs->paginate(12);

    // Vérifier si des specs existent dans la base de données
    $specsCount = DB::table('specs')->where('status', 1)->count();
    
    // Ajouter un message d'information pour l'utilisateur
    $message = '';
    if($specsCount == 0) {
      $message = 'Aucune spécification technique n\'est disponible actuellement.'; 
    } else {
      $message = 'Veuillez sélectionner un pays et cliquer sur "Visualiser" pour voir les spécifications disponibles.'; 
    }

    $idpays = "";
    if (Auth::user()->ratache_operateur != null) {
      $idpays = DB::table('operateurs')->where('id', Auth::user()->ratache_operateur)->first()->id_pays;
    }
    if (Auth::user()->ratache_autorite != null) {
      $idpays = DB::table('autoritecontractantes')->where('id', Auth::user()->ratache_autorite)->first()->id_pays;
    }
    if ($verif != null) {
      if ($verif->oper_local == 1 && $verif->oper_international == 1) {
        $pays = DB::table('pays')->orderby('nom_pays')->get();
      } else {
        $pays = DB::table('pays')->where('id', $idpays)->get();
      }
    } else {
      $pays = DB::table('pays')->where('id', $idpays)->get();
    }


    $categories = DB::table('categories')->orderby('code_categorie', 'asc')->get();


    return view('spec_abonne', compact('specs', 'pays', 'categories', 'message', 'specsCount'));
  }
  public function getmodifpass()
  {
    return view('abonnes/modifpass');
  }

  public function modifpass(Request $request)
  {
    $user = User::find(Auth::user()->id);
    $validator = Validator::make(
      $request->all(),

      [
        'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
        'password_confirmation' => 'min:6'
      ]
    );



    if ($validator->fails()) {

      return redirect(route('modifpass'))->withErrors($validator->errors());
    } else {
      if (Hash::check($request->old, $user->password)) {
        $user->password = Hash::make($request->password);
        $user->save();
        session()->flash('okk', sprintf('Mot de passe modifié avec succès'));
        return redirect(route('modifpass'));
      } else {
        session()->flash('message', sprintf("une erreur s'est produite"));
        return redirect(route('modifpass'));
      }
    }
  }

  public function annuler($id)
  {
    $souscription = DB::table('souscriptions')->where('idsouscription', '=', $id)
      ->join('abonnement', 'abonnement.id', '=', 'souscriptions.idabonnement')
      ->first();
    if ($souscription->date_fin == null && $souscription->iduser == Auth::user()->id) {
      $delete = DB::table('souscriptions')->where('idsouscription', '=', $id)->delete();
      $user = User::find(Auth::user()->id);
      Auth::logout();
      $user->delete();

      return redirect(route('pricing'));
    }
    return redirect()->back();
  }



  public function viewextraitmesref()
  {
    $verif = User::verifabonnement(Auth::user());

    if ($verif->date_fin == null) {
      return redirect(route('home'));
    } elseif ($verif->date_fin < date('Y-m-d')) {
      session()->flash('message', sprintf('Veuillez vous reabonner votre abonnement etait expiré le ' . Carbon::parse($verif->date_fin)->format('d/m/Y')));
      return redirect(route('pricing'));
    }


    $references = DB::table('references')
      ->where('status', '=', 1)
      ->where('references.operateur', '=', Auth::user()->ratache_operateur)
      ->orderby('references.created_at', 'desc')
      ->get();
    return view('index_user_extrait ', compact('references'));
  }

  public function postextraitmesref(Request $request)
  {
    $refs = explode("_", $request->idref);
    $taille = sizeof($refs);
    $tab = [$taille - 2];
    for ($i = 0; $i < $taille; $i++) {
      if ($refs[$i] != '') {
        $tab[$i] = $refs[$i];
      }
    }

    $references = DB::table('references')
      ->where('status', '=', 1)
      ->where('references.operateur', '=', Auth::user()->ratache_operateur)
      ->whereIn('references.idreference', $tab)
      ->orderby('references.created_at', 'desc')
      ->get();
    $oper = DB::table('operateurs')
      ->where('operateurs.id', '=', Auth::user()->ratache_operateur)
      ->first()->raison_social;
    //dd($references);
    //$pdf = \PDF::loadView('extrait_to_pdf',compact('references'));
    //return $pdf->download('extrait_to_pdf.pdf');
    //return view('extrait_to_pdf ',compact('references'));


    //pdf
    $html = '<span style="font-size:15px; text-align: center; display: block;">EXTRAIT DE REFERENCES TECHNIQUES (' . $oper . ')</span><br><br><br>';
    $data = $references;

    // Convert data array to HTML table
    $table = '<table border="0.1" cellspacing="0" cellpadding="5"><tr style="background-color: #1b87fa;margin-right:5PX"><th  style="color: white;width:15%">Index</th><th  style="color: white;width:15%">Numéro Contrat</th><th  style="color: white;width:30%">Libellé</th><th  style="color: white;width:25%">Autorité contractante</th><th  style="color: white;width:10%">Année</th></tr>';
    foreach ($data as $row) {
      $table .= '<tr><td>' . $row->numeroreference . '</td><td>' . $row->reference_marche . '</td><td>' . $row->libelle_marche . '</td><td>' . \Illuminate\Support\Facades\DB::table('autoritecontractantes')->where('id', $row->autorite_contractante)->first()->raison_social . '</td><td>' . $row->annee_execution . '</td></tr>';
    }
    $table .= '</table>';
    User::imprimer($html, $table);

    // Write the HTML table to the PDF

  }
  
  /**
   * Filtrer les spécifications techniques
   * Utilisé par la requête AJAX dans la vue spec_abonne
   */
  public function filtrerspec(Request $request)
  {
    try {
      $pays = $request->pays;
      $categorie = $request->categorie;
      $recherche = $request->recherche;
      
      // Log pour déboguer les valeurs reçues
      \Log::info('Filtrage des specs', [
          'pays' => $pays,
          'categorie' => $categorie,
          'recherche' => $recherche
      ]);
      
      $verif = User::verifabonnement(Auth::user());

      $query = DB::table('specs')
        ->join('categories', 'categories.id', '=', 'specs.categorie_id')
        ->join('pays', 'pays.id', '=', 'specs.pays_id')
        ->join('users', 'users.id', '=', 'specs.user_id')
        ->select(
          'specs.*',
          'categories.nom_categorie',
          'pays.nom_pays'
        )
        ->where('specs.status', 1);
        
      // Condition pour utilisateurs non abonnés
      if ($verif == null && Auth::user()->type_user == 3) {
        $query = $query->where('users.role', 'admin');
      }
      
      // Filtrer par pays si sélectionné (correction de la vérification)
      if (!empty($pays)) {
        $query = $query->where('specs.pays_id', $pays);
      }
      
      // Filtrer par catégorie si sélectionnée (correction de la vérification)
      if (!empty($categorie)) {
        $query = $query->where('specs.categorie_id', $categorie);
      }
      
      // Recherche par mot-clé si spécifié
      if (!empty($recherche)) {
        $query = $query->where('specs.libelle', 'LIKE', '%' . $recherche . '%');
      }
      
      // Pagination pour les résultats
      $page = $request->input('page', 1);
      $perPage = 12;
      $offset = ($page - 1) * $perPage;
      
      // Compter le total d'abord
      $total = $query->count();
      
      // Appliquer la pagination
      $results = $query->offset($offset)->limit($perPage)->get();
      
      // Générer la pagination HTML
      $totalPages = ceil($total / $perPage);
      $paginationHtml = '';
      
      if ($totalPages > 1) {
          $paginationHtml = '<div class="d-flex justify-content-center"><nav aria-label="Page navigation">';
          $paginationHtml .= '<ul class="pagination">';
          
          // Page précédente
          if ($page > 1) {
              $paginationHtml .= '<li class="page-item"><a class="page-link pagination-link" data-page="' . ($page - 1) . '" href="#">Précédent</a></li>';
          }
          
          // Pages numérotées
          for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++) {
              $active = ($i == $page) ? 'active' : '';
              $paginationHtml .= '<li class="page-item ' . $active . '"><a class="page-link pagination-link" data-page="' . $i . '" href="#">' . $i . '</a></li>';
          }
          
          // Page suivante
          if ($page < $totalPages) {
              $paginationHtml .= '<li class="page-item"><a class="page-link pagination-link" data-page="' . ($page + 1) . '" href="#">Suivant</a></li>';
          }
          
          $paginationHtml .= '</ul></nav></div>';
      }
      
      return response()->json([
        'data' => $results,
        'pagination' => $paginationHtml,
        'total' => $total,
        'current_page' => $page,
        'total_pages' => $totalPages
      ]);
      
    } catch (\Exception $e) {
      \Log::error('Erreur lors du filtrage des specs: ' . $e->getMessage());
      return response()->json([
        'error' => 'Une erreur est survenue lors du filtrage',
        'message' => $e->getMessage(),
        'data' => []
      ], 500);
    }
  }
  
  /**
   * Fournit des suggestions pour l'autocomplétion de la barre de recherche publique des specs
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getPublicSpecSuggestions(Request $request)
  {
    try {
      $term = trim($request->input('term', ''));
      
      // Requête simple
      $results = DB::table('specs')
        ->where('libelle', 'like', '%' . $term . '%')
        ->where('status', '=', 1) // Uniquement les specs actives
        ->select('libelle')
        ->distinct()
        ->orderBy('libelle')
        ->limit(10)
        ->get();
        
      // Log des résultats
      \Log::info('Résultats publics pour "' . $term . '"', ['count' => count($results)]);
      
      // Extraire les libellés
      $suggestions = [];
      foreach ($results as $result) {
        $suggestions[] = $result->libelle;
      }
      
      return response()->json($suggestions);
    } catch (\Exception $e) {
      \Log::error('Erreur dans getPublicSpecSuggestions: ' . $e->getMessage());
      
      // En cas d'erreur, retourner un tableau vide
      return response()->json([]);
    }
  }
  
  /**
   * Fournit des suggestions pour l'autocomplétion des IDs Gnonel
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getGnonelIdSuggestions(Request $request)
  {
    try {
      $term = trim($request->input('term', ''));
      
      // Requête pour trouver les IDs Gnonel correspondants
      $results = DB::table('operateurs')
        ->where('gnonelid', 'like', '%' . $term . '%')
        ->select('gnonelid')
        ->distinct()
        ->orderBy('gnonelid')
        ->limit(10)
        ->get();
        
      // Extraire les IDs
      $suggestions = [];
      foreach ($results as $result) {
        $suggestions[] = $result->gnonelid;
      }
      
      return response()->json($suggestions);
    } catch (\Exception $e) {
      \Log::error('Erreur dans getGnonelIdSuggestions: ' . $e->getMessage());
      
      // En cas d'erreur, retourner un tableau vide
      return response()->json([]);
    }
  }
}
