# Documentation Module Galerie d'Événements - Gnonel

## Aperçu

Ce module ajoute une galerie d'événements complète à l'application Gnonel, permettant la gestion et l'affichage de photos d'événements avec des fonctionnalités avancées d'upload, de likes et de filtrage.

## Fonctionnalités Implémentées

### 1. Back-Office (Administration)

#### Gestion des Événements
- **Création d'événements** avec :
  - Titre (obligatoire)
  - Date de l'événement (obligatoire)
  - Catégorie/Type d'événement (optionnel)
  - Description globale
  - Image de couverture

- **Modification d'événements** : Tous les champs modifiables
- **Suppression d'événements** : Avec confirmation de sécurité
- **Liste des événements** : Vue tableau avec aperçu, statistiques

#### Gestion des Photos
- **Upload en lot (Bulk Upload)** :
  - Zone de glisser-déposer (Drag & Drop)
  - Sélection multiple de fichiers
  - Barre de progression visuelle
  - Validation des formats (JPEG, PNG, WebP)
  - Limite de taille (5 Mo par fichier)
  - Conversion automatique en WebP
  - Génération automatique de miniatures

- **Gestion unitaire des photos** :
  - Ajout de légendes/descriptions
  - Option pour mettre en avant (featured)
  - Option pour masquer des photos
  - Réordonnancement des photos
  - Suppression individuelle
  - Définition comme image de couverture

### 2. Front-Office (Public)

#### Page Principale de la Galerie
- **Affichage en grille responsive** : Cartes d'événements avec vignettes
- **Filtres avancés** :
  - Par année
  - Par catégorie
  - Par mot-clé (recherche)
- **Informations affichées** :
  - Image de couverture
  - Titre et date
  - Nombre de photos
  - Compteur de likes
- **Pagination** : Navigation paginée pour les grands volumes

#### Vue Détaillée d'un Événement
- **Description de l'événement** en haut de page
- **Grille de photos** responsive
- **Système de likes** :
  - Bouton like sous chaque photo et événement
  - Incrémentation dynamique sans rechargement (AJAX)
  - Animation visuelle lors du like
  - Système anti-multi-vote (par session)
- **Visionneuse (Lightbox)** :
  - Agrandissement plein écran
  - Navigation clavier (flèches)
  - Navigation au clic (précédent/suivant)
  - Affichage des légendes
  - Bouton like intégré
- **Lazy loading** : Chargement progressif des images

## Structure Technique

### Base de Données

#### Table `events`
```php
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->date('event_date');
    $table->string('category')->nullable();
    $table->text('description')->nullable();
    $table->string('cover_image')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('likes_count')->default(0);
    $table->timestamps();
});
```

#### Table `photos`
```php
Schema::create('photos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->constrained()->onDelete('cascade');
    $table->string('image_path');
    $table->string('thumbnail_path')->nullable();
    $table->string('caption')->nullable();
    $table->boolean('is_featured')->default(false);
    $table->boolean('is_hidden')->default(false);
    $table->integer('order')->default(0);
    $table->integer('likes_count')->default(0);
    $table->timestamps();
});
```

#### Table `likes`
```php
Schema::create('likes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('photo_id')->nullable()->constrained()->onDelete('cascade');
    $table->string('session_id')->nullable();
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();
    
    // Indexes et contraintes d'unicité
    $table->unique(['session_id', 'event_id'], 'unique_session_event_like');
    $table->unique(['session_id', 'photo_id'], 'unique_session_photo_like');
});
```

### Modèles

#### Event
- Relations : `hasMany(Photo)`, `hasMany(Like)`
- Accesseurs : `cover_image_url`
- Casts : `event_date` (date), `is_active` (boolean), `likes_count` (integer)

#### Photo
- Relations : `belongsTo(Event)`, `hasMany(Like)`
- Accesseurs : `image_url`, `thumbnail_url`
- Casts : `is_featured` (boolean), `is_hidden` (boolean), `order` (integer), `likes_count` (integer)

#### Like
- Relations : `belongsTo(Event)`, `belongsTo(Photo)`
- Scopes : `forEvent()`, `forPhoto()`, `bySession()`, `byIp()`

### Contrôleur (GalleryController)

#### Méthodes Back-Office
- `index()` : Liste des événements
- `create()` : Formulaire de création
- `store()` : Enregistrement d'événement
- `show($id)` : Détails avec gestion photos
- `edit($id)` : Formulaire d'édition
- `update($id)` : Mise à jour d'événement
- `destroy($id)` : Suppression d'événement
- `bulkUpload($eventId)` : Upload multiple
- `updatePhoto($photoId)` : Modification photo
- `deletePhoto($photoId)` : Suppression photo
- `setAsCover($photoId)` : Définir couverture

#### Méthodes Front-Office
- `publicIndex()` : Galerie publique avec filtres
- `publicShow($id)` : Détails événement public

#### Méthodes Likes (AJAX)
- `likeEvent($eventId)` : Like événement
- `likePhoto($photoId)` : Like photo

### Routes

#### Routes Publiques
```php
Route::get('/galerie', 'GalleryController@publicIndex')->name('gallery.public.index');
Route::get('/galerie/{id}', 'GalleryController@publicShow')->name('gallery.public.show');
```

#### Routes Likes (AJAX)
```php
Route::post('/gallery/like/event/{id}', 'GalleryController@likeEvent')->name('gallery.like.event');
Route::post('/gallery/like/photo/{id}', 'GalleryController@likePhoto')->name('gallery.like.photo');
```

#### Routes Administration (protégées)
```php
Route::get('/admin/galerie', 'GalleryController@index')->name('gallery.admin.index');
Route::get('/admin/galerie/create', 'GalleryController@create')->name('gallery.admin.create');
Route::post('/admin/galerie', 'GalleryController@store')->name('gallery.admin.store');
Route::get('/admin/galerie/{id}', 'GalleryController@show')->name('gallery.admin.show');
Route::get('/admin/galerie/{id}/edit', 'GalleryController@edit')->name('gallery.admin.edit');
Route::post('/admin/galerie/{id}', 'GalleryController@update')->name('gallery.admin.update');
Route::delete('/admin/galerie/{id}', 'GalleryController@destroy')->name('gallery.admin.destroy');
Route::post('/admin/galerie/{eventId}/bulk-upload', 'GalleryController@bulkUpload')->name('gallery.admin.bulk-upload');
Route::post('/admin/galerie/photo/{photoId}', 'GalleryController@updatePhoto')->name('gallery.admin.update-photo');
Route::delete('/admin/galerie/photo/{photoId}', 'GalleryController@deletePhoto')->name('gallery.admin.delete-photo');
Route::post('/admin/galerie/photo/{photoId}/set-cover', 'GalleryController@setAsCover')->name('gallery.admin.set-cover');
```

### Vues

#### Back-Office
- `gallery/admin/index.blade.php` : Liste des événements
- `gallery/admin/create.blade.php` : Formulaire création
- `gallery/admin/edit.blade.php` : Formulaire édition
- `gallery/admin/show.blade.php` : Détails avec gestion photos

#### Front-Office
- `gallery/public/index.blade.php` : Galerie publique
- `gallery/public/show.blade.php` : Détails événement public

## Installation et Configuration

### 1. Exécuter les migrations
```bash
php artisan migrate
```

### 2. Créer les répertoires de stockage
Les répertoires suivants doivent exister avec les permissions appropriées :
- `public/storage/events/`
- `public/storage/events/thumbnails/`

### 3. Permissions
Assurez-vous que les répertoires de stockage sont accessibles en écriture :
```bash
chmod -R 775 public/storage/
```

## Fonctionnalités Techniques

### Traitement d'Images
- **Conversion WebP** : Toutes les images sont converties au format WebP pour optimiser l'espace
- **Redimensionnement** : 
  - Images principales : max 1920px de largeur
  - Images de couverture : max 1200px de largeur
  - Miniatures : 400px de largeur
- **Qualité** : 70-85% selon le type d'image
- **Library** : Utilisation de GD Library (PHP natif) pour compatibilité

### Performance
- **Lazy loading** : Les images sont chargées progressivement au défilement
- **Pagination** : Les événements sont paginés (12 par page)
- **Miniatures** : Utilisation de thumbnails pour l'affichage en grille
- **Compression WebP** : Réduction de la taille des fichiers

### Sécurité
- **Anti-multi-vote** : Restriction par session pour les likes
- **Validation des fichiers** : Vérification des types et tailles
- **CSRF Protection** : Tous les formulaires sont protégés
- **Middleware** : Routes admin protégées par authentification et rôle admin

### Responsive Design
- **Grille responsive** : Adaptation mobile, tablette, desktop
- **Lightbox responsive** : Visionneuse adaptative
- **Filtres mobile** : Interface adaptée aux petits écrans

## Utilisation

### Pour l'Administrateur

1. **Accéder à l'administration** : `/admin/galerie`
2. **Créer un événement** :
   - Cliquer sur "Nouvel Événement"
   - Remplir les informations obligatoires
   - Ajouter une image de couverture (optionnel)
   - Enregistrer

3. **Ajouter des photos** :
   - Ouvrir les détails de l'événement
   - Utiliser la zone de drag & drop ou le bouton de sélection
   - Les photos sont automatiquement traitées et ajoutées

4. **Gérer les photos** :
   - Cliquer sur l'icône d'édition pour modifier une photo
   - Utiliser l'icône d'image pour définir comme couverture
   - Utiliser l'icône de corbeille pour supprimer

### Pour le Visiteur

1. **Accéder à la galerie** : `/galerie`
2. **Filtrer les événements** :
   - Utiliser les filtres par année, catégorie
   - Effectuer une recherche par mot-clé
3. **Voir un événement** : Cliquer sur la carte d'événement
4. **Interagir** :
   - Cliquer sur une photo pour l'agrandir (lightbox)
   - Utiliser les flèches du clavier pour naviguer
   - Cliquer sur le cœur pour liker

## Personnalisation

### Styles CSS
Les styles sont définis directement dans les vues Blade pour faciliter la personnalisation. Vous pouvez modifier les classes Bootstrap et les styles personnalisés selon vos besoins.

### Catégories d'événements
Les catégories prédéfinies sont :
- Conférence
- Séminaire
- Formation
- Événement sportif
- Cérémonie
- Réunion
- Autre

Vous pouvez ajouter ou modifier ces catégories dans le formulaire de création.

### Limites
- **Taille maximale des fichiers** : 5 Mo (configurable dans le contrôleur)
- **Formats acceptés** : JPEG, PNG, WebP
- **Éléments par page** : 12 (configurable dans le contrôleur)

## Dépannage

### Problèmes courants

1. **Images ne s'affichent pas**
   - Vérifiez les permissions des répertoires `public/storage/`
   - Vérifiez que GD Library est installée sur le serveur

2. **Upload échoue**
   - Vérifiez la limite de taille dans `php.ini` (`upload_max_filesize`, `post_max_size`)
   - Vérifiez les permissions d'écriture

3. **Likes ne fonctionnent pas**
   - Vérifiez que les sessions sont activées
   - Vérifiez la configuration CSRF

## Maintenance

### Nettoyage
- Supprimer régulièrement les événements et photos obsolètes
- Optimiser la base de données avec `php artisan optimize`

### Sauvegarde
- Sauvegarder régulièrement le répertoire `public/storage/events/`
- Sauvegarder la base de données (tables events, photos, likes)

## Support

Pour toute question ou problème, contactez l'équipe de développement ou consultez la documentation Laravel.

---

**Version** : 1.0  
**Date** : 14 Septembre 2026  
**Développeur** : Devin AI  
**Projet** : Gnonel Web Application