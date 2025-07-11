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
            \Log::info('Tentative de téléchargement: ' . $path);
            \Log::info('Existence du fichier: ' . (file_exists($path) ? 'Oui' : 'Non'));
            
            // Vérification d'existence du fichier
            if (!file_exists($path)) {
                return response()->json(['error' => 'Fichier introuvable: ' . $path], 404);
            }
            
            // Méthode simple et directe - envoyer le fichier en tant que réponse
            return response()->file($path);
            
        } catch (\Exception $e) {
            // Journaliser l'exception
            \Log::error('Erreur lors du téléchargement: ' . $e->getMessage());
            
            // Retourner une réponse d'erreur explicative
            return response()->json([
                'error' => 'Erreur lors du téléchargement',
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
        
        // 6. Configurer les en-têtes pour le téléchargement
        $headers = [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        // 7. Renvoyer le fichier avec les en-têtes corrects
        return response()->file($path, $headers);
    }
}
