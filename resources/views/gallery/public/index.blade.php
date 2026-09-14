@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-images"></i> Galerie d'Événements
                </h1>
            </div>
            
            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('gallery.public.index') }}" method="GET" class="row">
                        <div class="col-md-3 mb-3">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Titre ou description...">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="year" class="form-label">Année</label>
                            <select class="form-control" id="year" name="year">
                                <option value="">Toutes les années</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="category" class="form-label">Catégorie</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                        </div>
                    </form>
                    
                    @if(request()->hasAny(['search', 'year', 'category']))
                        <div class="mt-2">
                            <a href="{{ route('gallery.public.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> Réinitialiser les filtres
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Grille des événements -->
            @if($events->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun événement trouvé</p>
                    @if(request()->hasAny(['search', 'year', 'category']))
                        <a href="{{ route('gallery.public.index') }}" class="btn btn-primary">
                            Voir tous les événements
                        </a>
                    @endif
                </div>
            @else
                <div class="row">
                    @foreach($events as $event)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card event-card h-100">
                                <div class="card-img-top position-relative">
                                    @if($event->cover_image)
                                        <img src="{{ $event->cover_image_url }}" 
                                             alt="{{ $event->title }}" 
                                             class="img-fluid w-100" 
                                             style="height: 250px; object-fit: cover; cursor: pointer;"
                                             onclick="window.location.href='{{ route('gallery.public.show', $event->id) }}'">
                                    @else
                                        <div class="bg-secondary d-flex align-items-center justify-content-center" 
                                             style="height: 250px; cursor: pointer;"
                                             onclick="window.location.href='{{ route('gallery.public.show', $event->id) }}'">
                                            <i class="fas fa-image fa-3x text-white"></i>
                                        </div>
                                    @endif
                                    
                                    <div class="position-absolute bottom-0 left-0 right-0 p-2" 
                                         style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                                        <div class="d-flex justify-content-between align-items-center text-white">
                                            <span>
                                                <i class="fas fa-images"></i> {{ $event->photos_count }}
                                            </span>
                                            <span>
                                                <i class="fas fa-heart"></i> {{ $event->likes_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title">{{ $event->title }}</h5>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y') }}
                                        @if($event->category)
                                            <span class="ml-2">
                                                <i class="fas fa-tag"></i> {{ $event->category }}
                                            </span>
                                        @endif
                                    </p>
                                    @if($event->description)
                                        <p class="card-text text-truncate">{{ Str::limit($event->description, 100) }}</p>
                                    @endif
                                    
                                    <div class="mt-3">
                                        <a href="{{ route('gallery.public.show', $event->id) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Voir les photos
                                        </a>
                                        
                                        <button type="button" class="btn btn-outline-danger btn-sm float-right" 
                                                onclick="likeEvent({{ $event->id }}, this)">
                                            <i class="far fa-heart"></i> <span class="likes-count">{{ $event->likes_count }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $events->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.event-card {
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}
.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.card-img-top {
    overflow: hidden;
}
.card-img-top img {
    transition: transform 0.3s;
}
.event-card:hover .card-img-top img {
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
</style>

<script>
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
            likeIcon.classList.add('fas');
            likeIcon.classList.add('text-danger');
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
</script>
@endsection