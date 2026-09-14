<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\User;

class BaseController extends Controller
{
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
            
            // Log des résultats
            \Log::info('Recherche de références pour "' . $term . '"', ['count' => count($results)]);
            
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
}
