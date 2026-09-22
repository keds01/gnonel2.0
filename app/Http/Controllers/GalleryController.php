<?php

namespace App\Http\Controllers;

use App\Event;
use App\Photo;
use App\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    // ==================== BACK-OFFICE ====================
    
    /**
     * Afficher la liste des événements (Admin)
     */
    public function index()
    {
        $events = Event::orderBy('event_date', 'desc')->get();
        return view('gallery.admin.index', compact('events'));
    }
    
    /**
     * Afficher le formulaire de création d'événement
     */
    public function create()
    {
        return view('gallery.admin.create');
    }
    
    /**
     * Enregistrer un nouvel événement
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);
        
        $event = new Event();
        $event->title = $request->title;
        $event->event_date = $request->event_date;
        $event->category = $request->category;
        $event->description = $request->description;
        $event->is_active = true;
        $event->likes_count = 0;
        
        // Gérer l'image de couverture
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageName = time() . '_' . Str::slug($request->title) . '.webp';
            
            // Créer le répertoire s'il n'existe pas
            $path = public_path('storage/events');
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
            
            // Sauvegarder l'image avec conversion WebP
            $imageInfo = getimagesize($image->getRealPath());
            $sourceImage = null;
            
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($image->getRealPath());
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($image->getRealPath());
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($image->getRealPath());
                    break;
            }
            
            if ($sourceImage) {
                // Redimensionner si nécessaire
                $width = imagesx($sourceImage);
                $height = imagesy($sourceImage);
                $newWidth = 1200;
                $newHeight = ($height * $newWidth) / $width;
                
                if ($width > $newWidth) {
                    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagewebp($resizedImage, $path . '/' . $imageName, 80);
                    imagedestroy($resizedImage);
                } else {
                    imagewebp($sourceImage, $path . '/' . $imageName, 80);
                }
                
                imagedestroy($sourceImage);
                $event->cover_image = $imageName;
            }
        }
        
        $event->save();
        
        // Gérer les photos supplémentaires si présentes
        if ($request->hasFile('photos')) {
            $this->processMultiplePhotos($request->file('photos'), $event->id);
        }
        
        return redirect()->route('gallery.admin.index')
            ->with('flash_message_success', 'Événement créé avec succès');
    }
    
    /**
     * Afficher les détails d'un événement (Admin)
     */
    public function show($id)
    {
        $event = Event::with('photos')->findOrFail($id);
        return view('gallery.admin.show', compact('event'));
    }
    
    /**
     * Afficher le formulaire d'édition d'événement
     */
    public function edit($id)
    {
        $event = Event::with('photos')->findOrFail($id);
        return view('gallery.admin.edit', compact('event'));
    }
    
    /**
     * Mettre à jour un événement
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);
        
        $event = Event::findOrFail($id);
        $event->title = $request->title;
        $event->event_date = $request->event_date;
        $event->category = $request->category;
        $event->description = $request->description;
        
        // Gérer l'image de couverture
        if ($request->hasFile('cover_image')) {
            // Supprimer l'ancienne image
            if ($event->cover_image && File::exists(public_path('storage/events/' . $event->cover_image))) {
                File::delete(public_path('storage/events/' . $event->cover_image));
            }
            
            $image = $request->file('cover_image');
            $imageName = time() . '_' . Str::slug($request->title) . '.webp';
            
            $path = public_path('storage/events');
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
            
            // Sauvegarder l'image avec conversion WebP
            $imageInfo = getimagesize($image->getRealPath());
            $sourceImage = null;
            
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($image->getRealPath());
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($image->getRealPath());
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($image->getRealPath());
                    break;
            }
            
            if ($sourceImage) {
                // Redimensionner si nécessaire
                $width = imagesx($sourceImage);
                $height = imagesy($sourceImage);
                $newWidth = 1200;
                $newHeight = ($height * $newWidth) / $width;
                
                if ($width > $newWidth) {
                    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagewebp($resizedImage, $path . '/' . $imageName, 80);
                    imagedestroy($resizedImage);
                } else {
                    imagewebp($sourceImage, $path . '/' . $imageName, 80);
                }
                
                imagedestroy($sourceImage);
                $event->cover_image = $imageName;
            }
        }
        
        $event->save();
        
        return redirect()->route('gallery.admin.index')
            ->with('flash_message_success', 'Événement mis à jour avec succès');
    }
    
    /**
     * Supprimer un événement
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        
        // Supprimer les photos associées
        foreach ($event->photos as $photo) {
            $this->deletePhotoFiles($photo);
            $photo->delete();
        }
        
        // Supprimer l'image de couverture
        if ($event->cover_image && File::exists(public_path('storage/events/' . $event->cover_image))) {
            File::delete(public_path('storage/events/' . $event->cover_image));
        }
        
        $event->delete();
        
        return redirect()->route('gallery.admin.index')
            ->with('flash_message_success', 'Événement supprimé avec succès');
    }
    
    /**
     * Process multiple photos helper method
     */
    private function processMultiplePhotos($photos, $eventId)
    {
        $uploadedCount = 0;
        
        foreach ($photos as $index => $image) {
            try {
                $photo = new Photo();
                $photo->event_id = $eventId;
                $photo->is_hidden = false;
                $photo->is_featured = false;
                $photo->order = Photo::where('event_id', $eventId)->count() + $index + 1;
                $photo->likes_count = 0;
                
                // Générer le nom de fichier
                $imageName = time() . '_' . $index . '_' . Str::random(8) . '.webp';
                
                // Créer les répertoires
                $mainPath = public_path('storage/events');
                $thumbPath = public_path('storage/events/thumbnails');
                
                if (!File::exists($mainPath)) {
                    File::makeDirectory($mainPath, 0755, true);
                }
                if (!File::exists($thumbPath)) {
                    File::makeDirectory($thumbPath, 0755, true);
                }
                
                // Traiter l'image principale avec GD
                $imageInfo = getimagesize($image->getRealPath());
                $sourceImage = null;
                
                switch ($imageInfo[2]) {
                    case IMAGETYPE_JPEG:
                        $sourceImage = imagecreatefromjpeg($image->getRealPath());
                        break;
                    case IMAGETYPE_PNG:
                        $sourceImage = imagecreatefrompng($image->getRealPath());
                        break;
                    case IMAGETYPE_GIF:
                        $sourceImage = imagecreatefromgif($image->getRealPath());
                        break;
                }
                
                if ($sourceImage) {
                    // Redimensionner l'image principale
                    $width = imagesx($sourceImage);
                    $height = imagesy($sourceImage);
                    $newWidth = 1920;
                    $newHeight = ($height * $newWidth) / $width;
                    
                    if ($width > $newWidth) {
                        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagewebp($resizedImage, $mainPath . '/' . $imageName, 85);
                        imagedestroy($resizedImage);
                    } else {
                        imagewebp($sourceImage, $mainPath . '/' . $imageName, 85);
                    }
                    
                    $photo->image_path = $imageName;
                    
                    // Créer la miniature
                    $thumbWidth = 400;
                    $thumbHeight = ($height * $thumbWidth) / $width;
                    
                    $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
                    imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                    imagewebp($thumbImage, $thumbPath . '/' . $imageName, 70);
                    imagedestroy($thumbImage);
                    
                    $photo->thumbnail_path = $imageName;
                    imagedestroy($sourceImage);
                }
                
                $photo->save();
                $uploadedCount++;
            } catch (\Exception $e) {
                // Continuer avec les autres photos en cas d'erreur
                continue;
            }
        }
        
        return $uploadedCount;
    }
    
    /**
     * Upload multiple photos (Bulk upload)
     */
    public function bulkUpload(Request $request, $eventId)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);
        
        $event = Event::findOrFail($eventId);
        $uploadedCount = 0;
        
        foreach ($request->file('photos') as $index => $image) {
            try {
                $photo = new Photo();
                $photo->event_id = $event->id;
                $photo->is_hidden = false;
                $photo->is_featured = false;
                $photo->order = $event->photos()->count() + $index + 1;
                $photo->likes_count = 0;
                
                // Générer le nom de fichier
                $imageName = time() . '_' . $index . '_' . Str::random(8) . '.webp';
                
                // Créer les répertoires
                $mainPath = public_path('storage/events');
                $thumbPath = public_path('storage/events/thumbnails');
                
                if (!File::exists($mainPath)) {
                    File::makeDirectory($mainPath, 0755, true);
                }
                if (!File::exists($thumbPath)) {
                    File::makeDirectory($thumbPath, 0755, true);
                }
                
                // Traiter l'image principale avec GD
                $imageInfo = getimagesize($image->getRealPath());
                $sourceImage = null;
                
                switch ($imageInfo[2]) {
                    case IMAGETYPE_JPEG:
                        $sourceImage = imagecreatefromjpeg($image->getRealPath());
                        break;
                    case IMAGETYPE_PNG:
                        $sourceImage = imagecreatefrompng($image->getRealPath());
                        break;
                    case IMAGETYPE_GIF:
                        $sourceImage = imagecreatefromgif($image->getRealPath());
                        break;
                }
                
                if ($sourceImage) {
                    // Redimensionner l'image principale
                    $width = imagesx($sourceImage);
                    $height = imagesy($sourceImage);
                    $newWidth = 1920;
                    $newHeight = ($height * $newWidth) / $width;
                    
                    if ($width > $newWidth) {
                        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagewebp($resizedImage, $mainPath . '/' . $imageName, 85);
                        imagedestroy($resizedImage);
                    } else {
                        imagewebp($sourceImage, $mainPath . '/' . $imageName, 85);
                    }
                    
                    $photo->image_path = $imageName;
                    
                    // Créer la miniature
                    $thumbWidth = 400;
                    $thumbHeight = ($height * $thumbWidth) / $width;
                    
                    $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
                    imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                    imagewebp($thumbImage, $thumbPath . '/' . $imageName, 70);
                    imagedestroy($thumbImage);
                    
                    $photo->thumbnail_path = $imageName;
                    imagedestroy($sourceImage);
                }
                
                $photo->save();
                $uploadedCount++;
                
            } catch (\Exception $e) {
                \Log::error('Error uploading photo: ' . $e->getMessage());
            }
        }
        
        // Mettre à jour l'image de couverture si l'événement n'en a pas
        if (!$event->cover_image && $event->photos()->exists()) {
            $firstPhoto = $event->photos()->orderBy('order')->first();
            $event->cover_image = $firstPhoto->image_path;
            $event->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => "$uploadedCount photo(s) téléchargée(s) avec succès",
            'uploaded_count' => $uploadedCount
        ]);
    }
    
    /**
     * Mettre à jour les informations d'une photo
     */
    public function updatePhoto(Request $request, $photoId)
    {
        $request->validate([
            'caption' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
            'is_hidden' => 'boolean',
            'order' => 'integer'
        ]);
        
        $photo = Photo::findOrFail($photoId);
        $photo->caption = $request->caption;
        $photo->is_featured = $request->is_featured ?? $photo->is_featured;
        $photo->is_hidden = $request->is_hidden ?? $photo->is_hidden;
        $photo->order = $request->order ?? $photo->order;
        $photo->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Photo mise à jour avec succès'
        ]);
    }
    
    /**
     * Supprimer une photo
     */
    public function deletePhoto($photoId)
    {
        $photo = Photo::findOrFail($photoId);
        $eventId = $photo->event_id;
        
        $this->deletePhotoFiles($photo);
        $photo->delete();
        
        // Mettre à jour l'image de couverture si nécessaire
        $event = Event::find($eventId);
        if ($event && $event->cover_image === $photo->image_path) {
            $newCover = $event->photos()->where('id', '!=', $photoId)->first();
            if ($newCover) {
                $event->cover_image = $newCover->image_path;
            } else {
                $event->cover_image = null;
            }
            $event->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Photo supprimée avec succès'
        ]);
    }
    
    /**
     * Définir une photo comme couverture
     */
    public function setAsCover($photoId)
    {
        $photo = Photo::findOrFail($photoId);
        $event = $photo->event;
        
        $event->cover_image = $photo->image_path;
        $event->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Photo définie comme couverture'
        ]);
    }
    
    // ==================== FRONT-OFFICE ====================
    
    /**
     * Afficher la galerie publique
     */
    public function publicIndex(Request $request)
    {
        $query = Event::where('is_active', true)->withCount('photos');
        
        // Filtres
        if ($request->has('year')) {
            $query->whereYear('event_date', $request->year);
        }
        
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }
        
        $events = $query->orderBy('event_date', 'desc')->paginate(12);
        
        // Récupérer les années disponibles pour le filtre
        $years = Event::where('is_active', true)
            ->selectRaw('YEAR(event_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Récupérer les catégories disponibles
        $categories = Event::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');
        
        return view('gallery.public.index', compact('events', 'years', 'categories'));
    }
    
    /**
     * Afficher les détails d'un événement (Public)
     */
    public function publicShow($id)
    {
        $event = Event::with(['photos' => function($query) {
            $query->where('is_hidden', false)->orderBy('order');
        }])->where('is_active', true)->findOrFail($id);
        
        return view('gallery.public.show', compact('event'));
    }
    
    // ==================== LIKES ====================
    
    /**
     * Ajouter un like à un événement
     */
    public function likeEvent(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $sessionId = session()->getId();
        $ipAddress = $request->ip();
        
        // Vérifier si l'utilisateur a déjà liké
        $existingLike = Like::where('session_id', $sessionId)
            ->where('event_id', $eventId)
            ->first();
        
        if ($existingLike) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà liké cet événement'
            ]);
        }
        
        // Créer le like
        $like = new Like();
        $like->event_id = $eventId;
        $like->session_id = $sessionId;
        $like->ip_address = $ipAddress;
        $like->user_agent = $request->userAgent();
        $like->save();
        
        // Incrémenter le compteur
        $event->likes_count++;
        $event->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Like ajouté',
            'likes_count' => $event->likes_count
        ]);
    }
    
    /**
     * Ajouter un like à une photo
     */
    public function likePhoto(Request $request, $photoId)
    {
        $photo = Photo::findOrFail($photoId);
        $sessionId = session()->getId();
        $ipAddress = $request->ip();
        
        // Vérifier si l'utilisateur a déjà liké
        $existingLike = Like::where('session_id', $sessionId)
            ->where('photo_id', $photoId)
            ->first();
        
        if ($existingLike) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà liké cette photo'
            ]);
        }
        
        // Créer le like
        $like = new Like();
        $like->photo_id = $photoId;
        $like->session_id = $sessionId;
        $like->ip_address = $ipAddress;
        $like->user_agent = $request->userAgent();
        $like->save();
        
        // Incrémenter le compteur
        $photo->likes_count++;
        $photo->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Like ajouté',
            'likes_count' => $photo->likes_count
        ]);
    }
    
    // ==================== HELPER METHODS ====================
    
    private function deletePhotoFiles($photo)
    {
        // Supprimer l'image principale
        if ($photo->image_path && File::exists(public_path('storage/events/' . $photo->image_path))) {
            File::delete(public_path('storage/events/' . $photo->image_path));
        }
        
        // Supprimer la miniature
        if ($photo->thumbnail_path && File::exists(public_path('storage/events/thumbnails/' . $photo->thumbnail_path))) {
            File::delete(public_path('storage/events/thumbnails/' . $photo->thumbnail_path));
        }
    }
}