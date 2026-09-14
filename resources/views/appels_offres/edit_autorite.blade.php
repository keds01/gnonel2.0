@extends('layouts.back_layout')

@section('title')
    Modifier un appel d'offre
@endsection

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('autorite.mes.offres') }}">Mes appels d'offres</a></li>
                            <li class="breadcrumb-item active">Modifier</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Modifier un appel d'offre</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('autorite.offre.update', $offre->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="reference">Numéro Appel d'offre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('reference') is-invalid @enderror"
                                            name="reference" value="{{ old('reference', $offre->reference) }}" id="reference" required>
                                        @error('reference')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="secteur">Secteur d'activité <span class="text-danger">*</span></label>
                                        <select class="form-control @error('secteur') is-invalid @enderror" name="secteur" required>
                                            <option value="" disabled>--- Sélectionner ---</option>
                                            @foreach ($secteur_activites as $secteur_activite)
                                                <option value="{{ $secteur_activite->idsecteuractivite }}" {{ old('secteur', $offre->idsecteuractivite) == $secteur_activite->idsecteuractivite ? 'selected' : '' }}>
                                                    {{ $secteur_activite->libellesecteuractivite }}</option>
                                            @endforeach
                                        </select>
                                        @error('secteur')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="libelle">Libellé de l'appel d'offre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('libelle') is-invalid @enderror"
                                            name="libelle" value="{{ old('libelle', $offre->libelle_appel) }}" id="libelle" required>
                                        @error('libelle')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="categorie">Catégorie <span class="text-danger">*</span></label>
                                        <select class="form-control @error('categorie') is-invalid @enderror" name="categorie" required>
                                            <option value="" disabled>--- Sélectionner ---</option>
                                            @foreach ($categories as $categorie)
                                                <option value="{{ $categorie->id }}" {{ old('categorie', $offre->id_categorie) == $categorie->id ? 'selected' : '' }}>
                                                    {{ $categorie->nom_categorie }}</option>
                                            @endforeach
                                        </select>
                                        @error('categorie')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="date_publication">Date de publication <span class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('date_publication') is-invalid @enderror"
                                            name="date_publication" value="{{ old('date_publication', date('Y-m-d', strtotime($offre->date_publication))) }}"
                                            id="date_publication" required>
                                        @error('date_publication')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="date_cloture">Date de Clôture <span class="text-danger">*</span></label>
                                        <input type="datetime-local"
                                            class="form-control @error('date_cloture') is-invalid @enderror"
                                            name="date_cloture" 
                                            value="{{ old('date_cloture', date('Y-m-d\TH:i', strtotime($offre->date_cloture))) }}" 
                                            id="date_cloture" required>
                                        @error('date_cloture')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="mode">Mode de passation <span class="text-danger">*</span></label>
                                        <select class="form-control @error('mode') is-invalid @enderror" name="mode" required>
                                            <option value="" disabled>--- Sélectionner ---</option>
                                            @foreach ($modes as $mode)
                                                <option value="{{ $mode->id }}" {{ old('mode', $offre->mode_id) == $mode->id ? 'selected' : '' }}>
                                                    {{ $mode->libelle }}</option>
                                            @endforeach
                                        </select>
                                        @error('mode')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="source">Source <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('source') is-invalid @enderror"
                                            name="source" value="{{ old('source', $offre->source) }}" id="source" required>
                                        @error('source')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="contact">Personne à contacter <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('contact') is-invalid @enderror"
                                            name="contact" value="{{ old('contact', $offre->contact) }}" id="contact" required
                                            placeholder="Nom, Téléphone, Email...">
                                        @error('contact')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="form-group">
                                        <label for="description">Description / Autres informations <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                            name="description" id="description" rows="4" required>{{ old('description', $offre->description) }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i> Modifier l'appel d'offre
                                    </button>
                                    <a href="{{ route('autorite.mes.offres') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Annuler
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
