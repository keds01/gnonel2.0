@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Bouton retour -->
            <a href="{{ route('gallery.public.index') }}" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour à la galerie
            </a>
            
            <!-- En-tête de l'événement -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h1 class="h2">{{ $event->title }}</h1>
                            <p class="text-muted mb-2">
                                <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y') }}
                                @if($event->category)
                                    <span class="ml-3">
                                        <i class="fas fa-tag"></i> {{ $event->category }}
                                    </span>
                                @endif
                            </p>
                            @if($event->description)
                                <p>{{ $event->description }}</p>
                            @endif
                        </div>
                        <div class="col-md-4 text-md-right">
                            <div class="d-flex flex-column align-items-md-end">
                                <span class="badge badge-info mb-2">
                                    <i class="fas fa-images"></i> {{ $event->photos->count() }} photos
                                </span>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="likeEvent({{ $event->id }}, this)">
                                    <i class="far fa-heart"></i> <span class="likes-count">{{ $event->likes_count }}</span> likes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Grille des photos -->
            @if($event->photos->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucune photo disponible pour cet événement</p>
                </div>
            @else
                <div class="row" id="photosGrid">
                    @foreach($event->photos as $index => $photo)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card photo-card">
                                <div class="card-img-top position-relative">
                                    <img src="{{ $photo->thumbnail_url }}" 
                                         alt="{{ $photo->caption ?? 'Photo' }}" 
                                         class="img-fluid w-100" 
                                         style="height: 250px; object-fit: cover; cursor: pointer;"
                                         data-index="{{ $index }}"
                                         data-photo-id="{{ $photo->id }}"
                                         data-full-image="{{ $photo->image_url }}"
                                         data-caption="{{ $photo->caption ?? '' }}"
                                         onclick="openLightbox({{ $index }})"
                                         loading="lazy">
                                    
                                    @if($photo->is_featured)
                                        <span class="badge badge-warning position-absolute" 
                                              style="top: 10px; right: 10px;">
                                            <i class="fas fa-star"></i>
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="card-body p-2">
                                    @if($photo->caption)
                                        <p class="small mb-1 text-truncate">{{ $photo->caption }}</p>
                                    @else
                                        <p class="small mb-1 text-muted">Sans légende</p>
                                    @endif
                                    
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" 
                                            onclick="likePhoto({{ $photo->id }}, this); event.stopPropagation();">
                                        <i class="far fa-heart"></i> <span class="likes-count">{{ $photo->likes_count }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Lightbox -->
<div class="modal fade" id="lightboxModal" tabindex="-1" role="dialog" data-keyboard="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="lightboxCaption"></h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 position-relative">
                <img id="lightboxImage" src="" alt="" class="img-fluid w-100" style="max-height: 80vh; object-fit: contain;">
                
                <!-- Navigation -->
                <button type="button" class="btn btn-dark position-absolute" 
                        style="left: 10px; top: 50%; transform: translateY(-50%);"
                        onclick="navigateLightbox(-1)">
                    <i class="fas fa-chevron-left fa-2x"></i>
                </button>
                <button type="button" class="btn btn-dark position-absolute" 
                        style="right: 10px; top: 50%; transform: translateY(-50%);"
                        onclick="navigateLightbox(1)">
                    <i class="fas fa-chevron-right fa-2x"></i>
                </button>
            </div>
            <div class="modal-footer border-0 bg-dark">
                <button type="button" class="btn btn-outline-danger" id="lightboxLikeBtn">
                    <i class="far fa-heart"></i> <span id="lightboxLikesCount">0</span>
                </button>
                <span class="text-white" id="lightboxCounter">1 / 1</span>
            </div>
        </div>
    </div>
</div>

<style>
.photo-card {
    transition: transform 0.3s, box-shadow 0.3s;
}
.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.photo-card img {
    transition: transform 0.3s;
}
.photo-card:hover img {
    transform: scale(1.05);
}
.like-animation {
    animation: likePulse 0.3s ease-in-out;
}
@keyframes likePulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}
#lightboxModal .modal-content {
    border-radius: 0;
}
</style>

<script>
const photos = @json($event->photos);
let currentPhotoIndex = 0;
let currentPhotoId = null;

function openLightbox(index) {
    currentPhotoIndex = index;
    const photo = photos[index];
    currentPhotoId = photo.id;
    
    document.getElementById('lightboxImage').src = photo.image_url;
    document.getElementById('lightboxCaption').textContent = photo.caption || 'Sans légende';
    document.getElementById('lightboxCounter').textContent = `${index + 1} / ${photos.length}`;
    document.getElementById('lightboxLikesCount').textContent = photo.likes_count;
    
    // Reset like button state
    const likeBtn = document.getElementById('lightboxLikeBtn');
    likeBtn.querySelector('i').classList.remove('fas', 'text-danger');
    likeBtn.querySelector('i').classList.add('far');
    
    $('#lightboxModal').modal('show');
}

function navigateLightbox(direction) {
    currentPhotoIndex += direction;
    
    if (currentPhotoIndex < 0) {
        currentPhotoIndex = photos.length - 1;
    } else if (currentPhotoIndex >= photos.length) {
        currentPhotoIndex = 0;
    }
    
    const photo = photos[currentPhotoIndex];
    currentPhotoId = photo.id;
    
    document.getElementById('lightboxImage').src = photo.image_url;
    document.getElementById('lightboxCaption').textContent = photo.caption || 'Sans légende';
    document.getElementById('lightboxCounter').textContent = `${currentPhotoIndex + 1} / ${photos.length}`;
    document.getElementById('lightboxLikesCount').textContent = photo.likes_count;
    
    // Reset like button state
    const likeBtn = document.getElementById('lightboxLikeBtn');
    likeBtn.querySelector('i').classList.remove('fas', 'text-danger');
    likeBtn.querySelector('i').classList.add('far');
}

// Navigation au clavier
document.addEventListener('keydown', function(e) {
    if ($('#lightboxModal').hasClass('show')) {
        if (e.key === 'ArrowLeft') {
            navigateLightbox(-1);
        } else if (e.key === 'ArrowRight') {
            navigateLightbox(1);
        } else if (e.key === 'Escape') {
            $('#lightboxModal').modal('hide');
        }
    }
});

// Like depuis la lightbox
document.getElementById('lightboxLikeBtn').addEventListener('click', function() {
    if (currentPhotoId) {
        likePhoto(currentPhotoId, this);
    }
});

function likeEvent(eventId, button) {
    const likeIcon = button.querySelector('i');
    const likesCount = button.querySelector('.likes-count');
    
    fetch(`{{ route('gallery.like.event', '') }}${eventId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            likeIcon.classList.remove('far');
            likeIcon.classList.add('fas', 'text-danger');
            likesCount.textContent = data.likes_count;
            button.classList.add('like-animation');
            
            setTimeout(() => {
                button.classList.remove('like-animation');
            }, 300);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function likePhoto(photoId, button) {
    const likeIcon = button.querySelector('i');
    const likesCount = button.querySelector('.likes-count');
    
    fetch(`{{ route('gallery.like.photo', '') }}${photoId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            likeIcon.classList.remove('far');
            likeIcon.classList.add('fas', 'text-danger');
            likesCount.textContent = data.likes_count;
            button.classList.add('like-animation');
            
            // Update the photo in the array and lightbox if open
            const photoIndex = photos.findIndex(p => p.id === photoId);
            if (photoIndex !== -1) {
                photos[photoIndex].likes_count = data.likes_count;
                if (currentPhotoId === photoId) {
                    document.getElementById('lightboxLikesCount').textContent = data.likes_count;
                }
            }
            
            setTimeout(() => {
                button.classList.remove('like-animation');
            }, 300);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endsection