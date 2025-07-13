<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DownloadController extends Controller
{
    /**
     * Gérer la visualisation des spécifications techniques (sans téléchargement)
     * Ouvre directement le document dans le navigateur
     */
    public function viewSpec($filename)
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'auth_required' => true,
                    'message' => 'Veuillez vous connecter pour visualiser des spécifications.'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
        $verif = User::verifabonnement($user);
        $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';
        
        // Sécuriser le nom de fichier
        $filename = basename($filename);
        
        // Chemin physique du fichier
        $path = public_path('images/uploads/' . $filename);
        
        // Vérification d'existence du fichier
        if (!File::exists($path)) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé'
                ], 404);
            }
            abort(404, 'Fichier non trouvé');
        }
        
        // Pour les non-abonnés, limiter à 3 visualisations
        if ($verif == null || $verif->date_fin < date('Y-m-d')) {
            $viewCount = Session::get('view_count', 0);
            
            if ($viewCount >= 3) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'limit_reached' => true,
                        'message' => 'Limite de visualisations atteinte. Veuillez vous abonner pour visualiser plus de spécifications techniques.'
                    ]);
                }
                
                Session::flash('message', 'Limite de visualisations atteinte. Veuillez vous abonner pour visualiser plus de spécifications techniques.');
                return redirect()->route('pricing');
            }
            
            // Incrémenter le compteur de visualisations
            Session::put('view_count', $viewCount + 1);
        }
        
        // Obtenir le type MIME du fichier
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        
        // Configurer les en-têtes pour la visualisation (inline au lieu de attachment)
        $headers = [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ];
        
        // Renvoyer le fichier avec les en-têtes corrects pour la visualisation
        return response()->file($path, $headers);
    }
    
    /**
     * Gérer le téléchargement des spécifications techniques
     * avec limitation pour les utilisateurs non abonnés
     */
    public function downloadSpec($filename)
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'auth_required' => true,
                    'message' => 'Veuillez vous connecter pour télécharger des spécifications.'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
        $verif = User::verifabonnement($user);
        $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';
        
        // Vérifier si le fichier existe avant tout
        $path = public_path('images/uploads/' . $filename);
        if (!File::exists($path)) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé'
                ], 404);
            }
            abort(404, 'Fichier non trouvé');
        }
        
        // Si l'utilisateur est abonné, permettre le téléchargement sans restriction
        if ($verif != null && $verif->date_fin >= date('Y-m-d')) {
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'limit_reached' => false,
                    'download_url' => route('download.spec.direct', $filename),
                    'message' => 'Téléchargement autorisé'
                ]);
            }
            
            return $this->processDownload($filename);
        }
        
        // Pour les non-abonnés, limiter à 3 téléchargements
        $downloadCount = Session::get('download_count', 0);
        
        if ($downloadCount >= 3) {
            return response()->json([
                'success' => false,
                'limit_reached' => true,
                'message' => 'Limite de téléchargements atteinte. Veuillez vous abonner pour télécharger plus de spécifications techniques.'
            ]);
        }
        
        // Incrémenter le compteur de téléchargements
        Session::put('download_count', $downloadCount + 1);
        
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'limit_reached' => false,
                'download_url' => route('download.spec.direct', $filename),
                'message' => 'Téléchargement autorisé',
                'download_count' => $downloadCount + 1
            ]);
        }
        
        return $this->processDownload($filename);
    }
    
    /**
     * Téléchargement direct sans vérifications (utilisé après validation par downloadSpec)
     */
    public function directDownload($filename)
    {
        try {
            // Sécuriser le nom de fichier
            $filename = basename($filename);
            
            // Chemin physique du fichier
            $path = public_path('images/uploads/' . $filename);
            
            // Débogage : journaliser les informations du fichier
            \Log::info('Tentative de visualisation: ' . $path);
            \Log::info('Existence du fichier: ' . (file_exists($path) ? 'Oui' : 'Non'));
            
            // Vérification d'existence du fichier
            if (!file_exists($path)) {
                return response()->json(['error' => 'Fichier introuvable: ' . $path], 404);
            }
            
            // Obtenir le type MIME du fichier
            $mime = mime_content_type($path) ?: 'application/octet-stream';
            
            // Configurer les en-têtes pour la visualisation (inline au lieu de attachment)
            $headers = [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ];
            
            // Méthode simple et directe - envoyer le fichier en tant que réponse pour visualisation
            return response()->file($path, $headers);
            
        } catch (\Exception $e) {
            // Journaliser l'exception
            \Log::error('Erreur lors de la visualisation: ' . $e->getMessage());
            
            // Retourner une réponse d'erreur explicative
            return response()->json([
                'error' => 'Erreur lors de la visualisation',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    /**
     * Traiter le téléchargement du fichier
     */
    private function processDownload($filename)
    {
        // 1. Nettoyer le nom du fichier pour éviter toute manipulation de chemin
        $filename = basename($filename);
        
        // 2. Construire le chemin correct vers le fichier
        $path = public_path('images/uploads/' . $filename);
        
        // 3. Vérification explicite de l'existence du fichier
        if (!file_exists($path)) {
            // Journaliser l'erreur pour faciliter le débogage
            \Log::error('Fichier non trouvé : ' . $path);
            abort(404, 'Fichier non trouvé : ' . $filename);
        }
        
        // 4. Vérifier si le fichier est lisible
        if (!is_readable($path)) {
            \Log::error('Fichier non lisible : ' . $path);
            abort(403, 'Impossible de lire le fichier : ' . $filename);
        }
        
        // 5. Obtenir le type MIME du fichier
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        
        // 6. Configurer les en-têtes pour la visualisation (inline au lieu de attachment)
        $headers = [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ];
        
        // 7. Renvoyer le fichier avec les en-têtes corrects pour la visualisation
        return response()->file($path, $headers);
    }
}
