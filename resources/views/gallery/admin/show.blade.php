@extends('layouts.back_layout')

@section('title')
    Détails de l'événement: {{ $event->title }}
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
    
    .photo-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .photo-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    
    .photo-card img {
        transition: transform 0.3s;
    }
    
    .photo-card:hover img {
        transform: scale(1.05);
    }
    
    .event-info-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 10px;
        padding: 25px;
    }
    
    .stat-badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .stat-badge.photos {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .stat-badge.likes {
        background: #fce4ec;
        color: #c2185b;
    }
    
    .btn-action {
        border-radius: 8px;
        padding: 8px 12px;
        transition: all 0.3s;
    }
    
    .btn-action:hover {
        transform: scale(1.05);
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Détails de l'événement: {{ $event->title }}</h3>
                    <div class="float-right">
                        <a href="{{ route('gallery.admin.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                        <a href="{{ route('gallery.admin.edit', $event->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('flash_message_success'))
                        <div class="alert alert-success">
                            {{ session('flash_message_success') }}
                        </div>
                    @endif
                    
                    <!-- Informations de l'événement -->
                    <div class="event-info-card mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                @if($event->cover_image)
                                    <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" 
                                         class="img-fluid rounded" style="max-height: 200px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                @else
                                    <div class="bg-secondary d-flex align-items-center justify-content-center rounded mx-auto" 
                                         style="height: 200px; width: 200px;">
                                        <i class="fas fa-image fa-3x text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <h3 style="color: #1b87fa; margin-bottom: 15px;">{{ $event->title }}</h3>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y') }}
                                    @if($event->category)
                                        <span class="ml-3">
                                            <i class="fas fa-tag"></i> {{ $event->category }}
                                        </span>
                                    @endif
                                </p>
                                @if($event->description)
                                    <p class="mb-3">{{ $event->description }}</p>
                                @endif
                                <div class="d-flex gap-2">
                                    <span class="stat-badge photos">
                                        <i class="fas fa-images"></i> {{ $event->photos->count() }} photos
                                    </span>
                                    <span class="stat-badge likes">
                                        <i class="fas fa-heart"></i> {{ $event->likes_count }} likes
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Zone d'upload en lot -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-upload"></i> Ajouter des photos (Import en lot)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="dropzone" class="upload-zone">
                                <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Glissez-déposez vos photos ici ou cliquez pour sélectionner</p>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                                    Sélectionner des fichiers
                                </button>
                                <p class="small text-muted mt-2">
                                    Formats acceptés: JPEG, PNG, WebP (max 5 Mo par fichier)
                                </p>
                            </div>
                            
                            <!-- Barre de progression -->
                            <div id="progressContainer" class="mt-3 d-none">
                                <div class="progress">
                                    <div id="progressBar" class="progress-bar" role="progressbar" 
                                         style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        0%
                                    </div>
                                </div>
                                <p id="progressText" class="small text-muted mt-1">Téléchargement en cours...</p>
                            </div>
                            
                            <!-- Liste des fichiers sélectionnés -->
                            <div id="fileList" class="mt-3"></div>
                        </div>
                    </div>
                    
                    <!-- Grille des photos -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-images"></i> Photos ({{ $event->photos->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($event->photos->isEmpty())
                                <div class="text-center py-5">
                                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune photo ajoutée</p>
                                    <p class="small text-muted">Utilisez la zone d'upload ci-dessus pour ajouter des photos</p>
                                </div>
                            @else
                                <div class="row" id="photosGrid">
                                    @foreach($event->photos as $photo)
                                        <div class="col-md-3 col-sm-4 col-6 mb-3 photo-item" data-photo-id="{{ $photo->id }}">
                                            <div class="card photo-card">
                                                <div class="card-img-top position-relative">
                                                    <img src="{{ $photo->thumbnail_url }}" 
                                                         alt="{{ $photo->caption ?? 'Photo' }}" 
                                                         class="img-fluid w-100" 
                                                         style="height: 200px; object-fit: cover; cursor: pointer;"
                                                         onclick="openPhotoModal({{ $photo->id }})">
                                                    
                                                    @if($photo->is_featured)
                                                        <span class="badge badge-warning position-absolute" 
                                                              style="top: 10px; right: 10px;">
                                                            <i class="fas fa-star"></i> À la une
                                                        </span>
                                                    @endif
                                                    
                                                    @if($photo->is_hidden)
                                                        <span class="badge badge-secondary position-absolute" 
                                                              style="top: 10px; left: 10px;">
                                                            <i class="fas fa-eye-slash"></i> Masquée
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="card-body p-2">
                                                    <p class="small mb-1 text-truncate">{{ $photo->caption ?? 'Sans légende' }}</p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-heart"></i> {{ $photo->likes_count }}
                                                        </small>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-primary btn-action" 
                                                                    onclick="editPhoto({{ $photo->id }})" title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            @if(!$event->cover_image || $event->cover_image != $photo->image_path)
                                                                <button type="button" class="btn btn-outline-warning btn-action" 
                                                                        onclick="setAsCover({{ $photo->id }})" title="Définir comme couverture">
                                                                    <i class="fas fa-image"></i>
                                                                </button>
                                                            @endif
                                                            <button type="button" class="btn btn-outline-danger btn-action" 
                                                                    onclick="deletePhoto({{ $photo->id }})" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'édition de photo -->
<div class="modal fade" id="photoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la photo</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="photoForm">
                    <input type="hidden" id="photoId" name="photo_id">
                    
                    <div class="form-group">
                        <label for="caption">Légende</label>
                        <textarea class="form-control" id="caption" name="caption" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured">
                        <label class="form-check-label" for="is_featured">
                            Mettre en avant (À la une)
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_hidden" name="is_hidden">
                        <label class="form-check-label" for="is_hidden">
                            Masquer cette photo
                        </label>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label for="order">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="order" name="order" min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="savePhoto()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
const eventId = {{ $event->id }};
const uploadUrl = "{{ route('gallery.admin.bulk-upload', $event->id) }}";

// Gestion du drag & drop
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');
const progressText = document.getElementById('progressText');
const fileList = document.getElementById('fileList');

dropzone.addEventListener('click', () => fileInput.click());

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
});

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    const validFiles = Array.from(files).filter(file => {
        const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        const maxSize = 5 * 1024 * 1024; // 5 Mo
        
        if (!validTypes.includes(file.type)) {
            alert(`Le fichier ${file.name} n'est pas une image valide (JPEG, PNG, WebP uniquement)`);
            return false;
        }
        
        if (file.size > maxSize) {
            alert(`Le fichier ${file.name} dépasse la taille maximale de 5 Mo`);
            return false;
        }
        
        return true;
    });
    
    if (validFiles.length > 0) {
        uploadFiles(validFiles);
    }
}

function uploadFiles(files) {
    const formData = new FormData();
    files.forEach(file => {
        formData.append('photos[]', file);
    });
    
    progressContainer.classList.remove('d-none');
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';
    progressText.textContent = 'Téléchargement en cours...';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', uploadUrl, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    
    xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
            progressBar.textContent = Math.round(percentComplete) + '%';
        }
    };
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                progressText.textContent = response.message;
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                progressText.textContent = 'Erreur: ' + response.message;
                progressBar.classList.add('bg-danger');
            }
        } else {
            progressText.textContent = 'Erreur lors du téléchargement';
            progressBar.classList.add('bg-danger');
        }
    };
    
    xhr.onerror = function() {
        progressText.textContent = 'Erreur de connexion';
        progressBar.classList.add('bg-danger');
    };
    
    xhr.send(formData);
}

function editPhoto(photoId) {
    const photoElement = document.querySelector(`[data-photo-id="${photoId}"]`);
    const photoData = {
        caption: photoElement.querySelector('.small.mb-1').textContent,
        is_featured: photoElement.querySelector('.badge-warning') !== null,
        is_hidden: photoElement.querySelector('.badge-secondary') !== null
    };
    
    document.getElementById('photoId').value = photoId;
    document.getElementById('caption').value = photoData.caption;
    document.getElementById('is_featured').checked = photoData.is_featured;
    document.getElementById('is_hidden').checked = photoData.is_hidden;
    
    $('#photoModal').modal('show');
}

function savePhoto() {
    const photoId = document.getElementById('photoId').value;
    const formData = new FormData(document.getElementById('photoForm'));
    
    fetch(`{{ route('gallery.admin.update-photo', ':id') }}`.replace(':id', photoId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#photoModal').modal('hide');
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur: ' + error);
    });
}

function deletePhoto(photoId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')) {
        fetch(`{{ route('gallery.admin.delete-photo', ':id') }}`.replace(':id', photoId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-photo-id="${photoId}"]`).remove();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            alert('Erreur: ' + error);
        });
    }
}

function setAsCover(photoId) {
    fetch(`{{ route('gallery.admin.set-cover', ':id') }}`.replace(':id', photoId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur: ' + error);
    });
}
</script>
@endsection