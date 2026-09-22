@extends('layouts.back_layout')

@section('title')
    Modifier l'événement: {{ $event->title }}
@endsection

@section('content')
<style>
    .upload-zone {
        border: 2px dashed #ccc;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #f8f9fa;
    }
    
    .upload-zone:hover {
        border-color: #1b87fa;
        background: #e9f2ff;
    }
    
    .upload-zone.dragover {
        border-color: #3fa46a;
        background: #e8f5e9;
    }
    
    .upload-zone i {
        font-size: 3rem;
        color: #ccc;
        margin-bottom: 15px;
    }
    
    .upload-zone p {
        color: #666;
        margin-bottom: 0;
    }
    
    .preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .preview-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .preview-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    
    .preview-item .remove-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255,0,0,0.8);
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .preview-item .remove-btn:hover {
        background: rgba(255,0,0,1);
    }
    
    .current-image {
        position: relative;
        display: inline-block;
    }
    
    .current-image img {
        max-height: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .current-image .remove-current {
        position: absolute;
        top: -10px;
        right: -10px;
        background: rgba(255,0,0,0.8);
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Modifier l'événement: {{ $event->title }}</h3>
                    <a href="{{ route('gallery.admin.show', $event->id) }}" class="btn btn-secondary float-right">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('gallery.admin.update', $event->id) }}" method="POST" enctype="multipart/form-data" id="eventForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Titre de l'événement <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $event->title) }}" required placeholder="Ex: Conférence annuelle 2024">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="event_date">Date de l'événement <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('event_date') is-invalid @enderror" 
                                           id="event_date" name="event_date" value="{{ old('event_date', $event->event_date) }}" required>
                                    @error('event_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Catégorie / Type d'événement</label>
                                    <select class="form-control @error('category') is-invalid @enderror" 
                                            id="category" name="category">
                                        <option value="">-- Sélectionner une catégorie --</option>
                                        <option value="conférence" {{ $event->category == 'conférence' ? 'selected' : '' }}>Conférence</option>
                                        <option value="séminaire" {{ $event->category == 'séminaire' ? 'selected' : '' }}>Séminaire</option>
                                        <option value="formation" {{ $event->category == 'formation' ? 'selected' : '' }}>Formation</option>
                                        <option value="événement sportif" {{ $event->category == 'événement sportif' ? 'selected' : '' }}>Événement sportif</option>
                                        <option value="cérémonie" {{ $event->category == 'cérémonie' ? 'selected' : '' }}>Cérémonie</option>
                                        <option value="réunion" {{ $event->category == 'réunion' ? 'selected' : '' }}>Réunion</option>
                                        <option value="autre" {{ $event->category == 'autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cover_image">Image de couverture</label>
                                    <div class="upload-zone" id="coverDropZone">
                                        <input type="file" id="cover_image" name="cover_image" accept="image/*" style="display: none;">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Glissez-déposez l'image ici ou cliquez pour sélectionner</p>
                                        <small class="text-muted">Formats acceptés: JPEG, PNG, WebP (max 5 Mo)</small>
                                    </div>
                                    <div id="coverPreview" class="preview-container"></div>
                                    @if($event->cover_image)
                                        <div class="mt-3">
                                            <p class="small text-muted mb-2">Image actuelle:</p>
                                            <div class="current-image">
                                                <img src="{{ $event->cover_image_url }}" alt="Image actuelle">
                                                <input type="hidden" id="remove_cover" name="remove_cover" value="0">
                                            </div>
                                        </div>
                                    @endif
                                    @error('cover_image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description globale</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" placeholder="Décrivez l'événement...">{{ old('description', $event->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                            <a href="{{ route('gallery.admin.show', $event->id) }}" class="btn btn-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cover image upload zone
const coverDropZone = document.getElementById('coverDropZone');
const coverInput = document.getElementById('cover_image');
const coverPreview = document.getElementById('coverPreview');

coverDropZone.addEventListener('click', () => coverInput.click());

coverDropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    coverDropZone.classList.add('dragover');
});

coverDropZone.addEventListener('dragleave', () => {
    coverDropZone.classList.remove('dragover');
});

coverDropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    coverDropZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        coverInput.files = files;
        handleCoverPreview(files[0]);
    }
});

coverInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleCoverPreview(e.target.files[0]);
    }
});

function handleCoverPreview(file) {
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            coverPreview.innerHTML = `
                <div class="preview-item">
                    <img src="${e.target.result}" alt="Aperçu">
                    <button type="button" class="remove-btn" onclick="removeCover()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
}

function removeCover() {
    coverInput.value = '';
    coverPreview.innerHTML = '';
}
</script>
@endsection