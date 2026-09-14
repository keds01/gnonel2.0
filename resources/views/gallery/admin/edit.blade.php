@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Modifier l'événement: {{ $event->title }}</h3>
                    <a href="{{ route('gallery.admin.show', $event->id) }}" class="btn btn-secondary float-right">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('gallery.admin.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Titre de l'événement <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $event->title) }}" required>
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
                                    <input type="file" class="form-control-file @error('cover_image') is-invalid @enderror" 
                                           id="cover_image" name="cover_image" accept="image/*">
                                    <small class="form-text text-muted">Formats acceptés: JPEG, PNG, WebP (max 5 Mo)</small>
                                    @if($event->cover_image)
                                        <div class="mt-2">
                                            <img src="{{ $event->cover_image_url }}" alt="Image actuelle" 
                                                 class="img-thumbnail" style="max-height: 100px;">
                                            <p class="small text-muted">Image actuelle</p>
                                        </div>
                                    @endif
                                    @error('cover_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description globale</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4">{{ old('description', $event->description) }}</textarea>
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
@endsection