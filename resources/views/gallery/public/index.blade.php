@extends('layouts.landing')

@section('title')
    Galerie
@endsection

@section('content')
<div class="service-page-service sp">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="heading1 text-center">
                    <span class="span">Notre Galerie</span>
                    <h2>Découvrez nos moments forts à travers nos photos</h2>
                    <div class="space16"></div>
                    <p>Explorez nos événements et souvenirs à travers notre collection de photos</p>
                </div>
            </div>
        </div>
        
        <div class="space60"></div>
        
        <!-- Filtres -->
        <div class="row">
            <div class="col-lg-12">
                <div class="contact1-form">
                    <div class="heading1">
                        <h3>Filtrer les événements</h3>
                    </div>
                    <form action="{{ route('gallery.public.index') }}" method="GET" class="row">
                        <div class="col-md-4 mb-3">
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Rechercher un événement...">
                        </div>
                        
                        <div class="col-md-3 mb-3">
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
                            <select class="form-control" id="category" name="category">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <button type="submit" class="theme-btn1">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                        </div>
                    </form>
                    
                    @if(request()->hasAny(['search', 'year', 'category']))
                        <div class="mt-3">
                            <a href="{{ route('gallery.public.index') }}" class="theme-btn2">
                                <i class="fas fa-times"></i> Réinitialiser les filtres
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="space60"></div>
        
        <!-- Grille des événements -->
        @if($events->isEmpty())
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="heading1">
                        <i class="fas fa-images fa-3x" style="color: #ccc;"></i>
                        <div class="space30"></div>
                        <h3>Aucun événement trouvé</h3>
                        @if(request()->hasAny(['search', 'year', 'category']))
                            <a href="{{ route('gallery.public.index') }}" class="theme-btn1 mt-3">
                                Voir tous les événements
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($events as $event)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service1-box">
                            <div class="image overlay-anim" onclick="window.location.href='{{ route('gallery.public.show', $event->id) }}'" style="cursor: pointer;">
                                @if($event->cover_image)
                                    <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image fa-3x" style="color: #ccc;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="hover-area">
                                <div class="space16"></div>
                                <div class="heading1-w">
                                    <h4><a href="{{ route('gallery.public.show', $event->id) }}">{{ $event->title }}</a></h4>
                                    <div class="space16"></div>
                                    <div class="meta" style="color: #666; font-size: 0.9rem;">
                                        <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y') }}
                                        @if($event->category)
                                            <span style="margin-left: 15px;">
                                                <i class="fas fa-tag"></i> {{ $event->category }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="space16"></div>
                                    @if($event->description)
                                        <p>{{ Str::limit($event->description, 100) }}</p>
                                    @endif
                                    <div class="space16"></div>
                                    <div class="stats" style="display: flex; gap: 15px;">
                                        <span style="color: #3fa46a;">
                                            <i class="fas fa-images"></i> {{ $event->photos_count }} photos
                                        </span>
                                        <span style="color: #ff6b6b;">
                                            <i class="fas fa-heart"></i> {{ $event->likes_count }} likes
                                        </span>
                                    </div>
                                    <div class="space16"></div>
                                    <a href="{{ route('gallery.public.show', $event->id) }}" class="theme-btn1">
                                        <i class="fas fa-eye"></i> Voir les photos
                                    </a>
                                    <button type="button" class="theme-btn2" style="margin-left: 10px;" 
                                            onclick="event.stopPropagation(); likeEvent({{ $event->id }}, this)">
                                        <i class="fas fa-heart"></i> <span class="likes-count">{{ $event->likes_count }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="space60"></div>
            <div class="row">
                <div class="col-lg-12 text-center">
                    {{ $events->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<script>
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
</script>
@endsection