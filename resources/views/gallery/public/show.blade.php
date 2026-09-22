@extends('layouts.landing')

@section('title')
    {{ $event->title }}
@endsection

@section('content')
<div class="service-page-service sp">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="heading1 text-center">
                    <span class="span">Détails de l'événement</span>
                    <h2>{{ $event->title }}</h2>
                    <div class="space16"></div>
                    <div class="meta" style="color: #666; font-size: 1rem;">
                        <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y') }}
                        @if($event->category)
                            <span style="margin-left: 20px;">
                                <i class="fas fa-tag"></i> {{ $event->category }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="space60"></div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="contact1-form">
                    <div class="heading1">
                        <h3>Informations sur l'événement</h3>
                    </div>
                    @if($event->description)
                        <p>{{ $event->description }}</p>
                    @endif
                    <div class="space30"></div>
                    <div class="stats" style="display: flex; gap: 20px;">
                        <span style="color: #3fa46a;">
                            <i class="fas fa-images"></i> {{ $event->photos->count() }} photos
                        </span>
                        <span style="color: #ff6b6b;">
                            <i class="fas fa-heart"></i> {{ $event->likes_count }} likes
                        </span>
                    </div>
                    <div class="space30"></div>
                    <a href="{{ route('gallery.public.index') }}" class="theme-btn2">
                        <i class="fas fa-arrow-left"></i> Retour à la galerie
                    </a>
                    <button type="button" class="theme-btn1" style="margin-left: 10px;" onclick="likeEvent({{ $event->id }}, this)">
                        <i class="fas fa-heart"></i> <span class="likes-count">{{ $event->likes_count }}</span> likes
                    </button>
                </div>
            </div>
        </div>
        
        <div class="space60"></div>
        
        <!-- Grille des photos -->
        @if($event->photos->isEmpty())
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="heading1">
                        <i class="fas fa-images fa-3x" style="color: #ccc;"></i>
                        <div class="space30"></div>
                        <h3>Aucune photo disponible pour cet événement</h3>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($event->photos as $index => $photo)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service1-box">
                            <div class="image overlay-anim" onclick="openLightbox({{ $index }})" style="cursor: pointer;">
                                <img src="{{ $photo->thumbnail_url }}" alt="{{ $photo->caption ?? 'Photo' }}" style="width: 100%; height: 100%; object-fit: cover;">
                                
                                @if($photo->is_featured)
                                    <div style="position: absolute; top: 10px; right: 10px; background: #ffc107; color: white; padding: 5px 10px; border-radius: 15px;">
                                        <i class="fas fa-star"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="hover-area">
                                <div class="space16"></div>
                                <div class="heading1-w">
                                    @if($photo->caption)
                                        <h4>{{ Str::limit($photo->caption, 50) }}</h4>
                                    @else
                                        <h4>Sans légende</h4>
                                    @endif
                                    <div class="space16"></div>
                                    <span style="color: #ff6b6b;">
                                        <i class="fas fa-heart"></i> {{ $photo->likes_count }} likes
                                    </span>
                                    <div class="space16"></div>
                                    <button type="button" class="theme-btn1" onclick="event.stopPropagation(); likePhoto({{ $photo->id }}, this)">
                                        <i class="fas fa-heart"></i> <span class="likes-count">{{ $photo->likes_count }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Lightbox -->
<div class="modal fade" id="lightboxModal" tabindex="-1" role="dialog" data-keyboard="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lightboxCaption"></h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 position-relative">
                <img id="lightboxImage" src="" alt="" class="img-fluid w-100" style="max-height: 80vh; object-fit: contain;">
                
                <!-- Navigation -->
                <button type="button" class="btn position-absolute" 
                        style="left: 20px; top: 50%; transform: translateY(-50%); background: #1b87fa; border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;"
                        onclick="navigateLightbox(-1)">
                    <i class="fas fa-chevron-left fa-2x"></i>
                </button>
                <button type="button" class="btn position-absolute" 
                        style="right: 20px; top: 50%; transform: translateY(-50%); background: #1b87fa; border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;"
                        onclick="navigateLightbox(1)">
                    <i class="fas fa-chevron-right fa-2x"></i>
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" id="lightboxLikeBtn">
                    <i class="far fa-heart"></i> <span id="lightboxLikesCount">0</span>
                </button>
                <span id="lightboxCounter">1 / 1</span>
            </div>
        </div>
    </div>
</div>

<script>
const photos = @json($event->photos);
let currentPhotoIndex = 0;
let currentPhotoId = null;

console.log('Photos loaded:', photos);

function openLightbox(index) {
    console.log('Opening lightbox for index:', index);
    currentPhotoIndex = index;
    const photo = photos[index];
    currentPhotoId = photo.id;
    
    console.log('Photo data:', photo);
    console.log('Image URL:', photo.image_url);
    
    const lightboxImage = document.getElementById('lightboxImage');
    
    if (photo.image_url) {
        lightboxImage.src = photo.image_url;
        lightboxImage.onerror = function() {
            console.error('Failed to load image:', photo.image_url);
            lightboxImage.src = '/frontoffice/images/placeholder.jpg'; // Image par défaut
        };
    } else {
        console.error('Image URL is null');
        lightboxImage.src = '/frontoffice/images/placeholder.jpg';
    }
    
    document.getElementById('lightboxCaption').textContent = photo.caption || 'Sans légende';
    document.getElementById('lightboxCounter').textContent = `${index + 1} / ${photos.length}`;
    document.getElementById('lightboxLikesCount').textContent = photo.likes_count;
    
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
    
    const likeBtn = document.getElementById('lightboxLikeBtn');
    likeBtn.querySelector('i').classList.remove('fas', 'text-danger');
    likeBtn.querySelector('i').classList.add('far');
}

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

document.getElementById('lightboxLikeBtn').addEventListener('click', function() {
    if (currentPhotoId) {
        likePhoto(currentPhotoId, this);
    }
});

function likeEvent(eventId, button) {
    const likesCount = button.querySelector('.likes-count');
    
    fetch(`{{ route('gallery.like.event', ':id') }}`.replace(':id', eventId), {
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
            likesCount.textContent = data.likes_count;
            button.style.background = '#3fa46a';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function likePhoto(photoId, button) {
    const likesCount = button.querySelector('.likes-count');
    
    fetch(`{{ route('gallery.like.photo', ':id') }}`.replace(':id', photoId), {
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
            likesCount.textContent = data.likes_count;
            button.style.background = '#3fa46a';
            
            const photoIndex = photos.findIndex(p => p.id === photoId);
            if (photoIndex !== -1) {
                photos[photoIndex].likes_count = data.likes_count;
                if (currentPhotoId === photoId) {
                    document.getElementById('lightboxLikesCount').textContent = data.likes_count;
                }
            }
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