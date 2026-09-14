@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestion de la Galerie d'Événements</h3>
                    <a href="{{ route('gallery.admin.create') }}" class="btn btn-primary float-right">
                        <i class="fas fa-plus"></i> Nouvel Événement
                    </a>
                </div>
                <div class="card-body">
                    @if(session('flash_message_success'))
                        <div class="alert alert-success">
                            {{ session('flash_message_success') }}
                        </div>
                    @endif
                    
                    @if($events->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun événement créé</p>
                            <a href="{{ route('gallery.admin.create') }}" class="btn btn-primary">
                                Créer le premier événement
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Titre</th>
                                        <th>Date</th>
                                        <th>Catégorie</th>
                                        <th>Photos</th>
                                        <th>Likes</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($events as $event)
                                    <tr>
                                        <td>
                                            @if($event->cover_image)
                                                <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" 
                                                     class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image text-white"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $event->title }}</td>
                                        <td>{{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y') }}</td>
                                        <td>{{ $event->category ?? '-' }}</td>
                                        <td>{{ $event->photos_count ?? $event->photos->count() }}</td>
                                        <td>{{ $event->likes_count }}</td>
                                        <td>
                                            @if($event->is_active)
                                                <span class="badge badge-success">Actif</span>
                                            @else
                                                <span class="badge badge-secondary">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('gallery.admin.show', $event->id) }}" 
                                                   class="btn btn-sm btn-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('gallery.admin.edit', $event->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('gallery.admin.destroy', $event->id) }}" 
                                                      method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement et toutes ses photos ?');">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection