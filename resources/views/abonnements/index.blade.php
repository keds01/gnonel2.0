@extends('layouts.back_layout')
@section('title')
    Abonnements
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
                            <li class="breadcrumb-item active">Abonnements</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Abonnements</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-4">
                @if (isset($abonnement))
                    <form method="POST" id="new-form" action="{{ route('abonnements.update', $abonnement->id) }}">
                    @else
                        <form method="POST" id="new-form" action="{{ route('abonnements.store') }}">
                @endif
                <div class="card">
                    <div class="card-body">
                        @if (!isset($abonnement))
                            <h4 class="header-title">Créer un abonnement</h4>
                        @else
                            <h4 class="header-title">Modifier un abonnement</h4>
                        @endif
                        @csrf

                        @if (isset($abonnement))
                            {{ method_field('PUT') }}
                        @endif
                        
                        <!-- Libellé -->
                        <div class="form-group">
                            <label for="libelle">Libelle *</label>
                            <input type="text" 
                                class="form-control @error('libelle') is-invalid @enderror" 
                                name="libelle"
                                value="{{ old('libelle', $abonnement->libelle ?? '') }}" 
                                id="libelle" 
                                required>
                            @error('libelle')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <!-- Monnaie -->
                        <div class="form-group">
                            <label for="monnaie">Monnaie *</label>
                            <select class="form-control @error('monnaie') is-invalid @enderror" name="monnaie" id="monnaie" required>
                                <option value="FCFA" {{ old('monnaie', $abonnement->monnaie ?? 'FCFA') == 'FCFA' ? 'selected' : '' }}>FCFA</option>
                                <option value="EUR" {{ old('monnaie', $abonnement->monnaie ?? '') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="USD" {{ old('monnaie', $abonnement->monnaie ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                            </select>
                            @error('monnaie')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <!-- Nombre de licences (fixe) -->
                        <div class="form-group">
                            <label for="count">Nombre de licences dans le pack *</label>
                            <input type="number" 
                                class="form-control @error('count') is-invalid @enderror" 
                                name="count" 
                                value="{{ old('count', $abonnement->count ?? 5) }}" 
                                id="count" 
                                min="1"
                                required>
                            <small class="text-muted">Le client ne pourra pas modifier ce nombre</small>
                            @error('count')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <!-- Prix de gros du pack -->
                        <div class="form-group">
                            <label for="prix_gros">Prix du pack (en gros) *</label>
                            <input type="number" 
                                class="form-control @error('prix_gros') is-invalid @enderror" 
                                name="prix_gros" 
                                value="{{ old('prix_gros', $abonnement->prix_gros ?? '') }}" 
                                id="prix_gros"
                                required>
                            <small class="text-muted">Prix total que le client paiera pour le pack</small>
                            @error('prix_gros')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <!-- Option Bonus -->
                        <div class="form-group">
                            <label>Options de bonus</label><br>
                            <input type="checkbox" 
                                id="is_pack_fixe" 
                                name="is_pack_fixe" 
                                value="1"
                                @if (old('is_pack_fixe', $abonnement->is_pack_fixe ?? 1) == 1) checked @endif>
                            <label for="is_pack_fixe">Désactiver les bonus pour ce pack (pas de réduction affiliation ni pack >5)</label><br>
                        </div>
                        
                        <hr>
                        
                        <!-- Champs optionnels cachés mais conservés pour compatibilité -->
                        <input type="hidden" name="prix" value="{{ old('prix', $abonnement->prix ?? 0) }}">
                        <input type="hidden" name="categorie" value="{{ old('categorie', $abonnement->categorie ?? 1) }}">
                        <input type="hidden" name="nbjours" value="{{ old('nbjours', $abonnement->nbjours ?? 365) }}">
                        <input type="hidden" name="description" value="Pack de {{ old('count', $abonnement->count ?? 5) }} licences">
                        <input type="hidden" name="choixaut" value="1">
                        <input type="hidden" name="choixop" value="1">
                        
                        <br>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-lg" type="submit">
                                <i class="fe-save"></i> Valider
                            </button>
                            <button class="btn btn-outline-secondary" type="reset">
                                Annuler
                            </button>
                        </div>
                    </div>
                    <div class="card-footer text-end d-none">
                        <button class="btn btn-primary" type="submit">Valider</button>
                        <button class="btn btn-danger" type="reset">Annuler</button>
                    </div>
                </div>
                </form>
            </div>
            <div class="col-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Affichage des abonnements</h4>
                        <br>
                        <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <td>Libelle</td>
                                    <td>Monnaie</td>
                                    <td>Licences</td>
                                    <td>Prix du Pack</td>
                                    <td>Bonus</td>
                                    <td>Actions</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($abonnements as $abonnement)
                                    <tr>
                                        <td>{{ $abonnement->libelle }}</td>
                                        <td>{{ $abonnement->monnaie }}</td>
                                        <td>{{ $abonnement->count ?? 1 }}</td>
                                        <td>{{ number_format($abonnement->prix_gros ?? $abonnement->prix, 0, ',', ' ') }}</td>
                                        <td>
                                            @if ($abonnement->is_pack_fixe == 1)
                                                <span class="badge bg-danger">Désactivés</span>
                                            @else
                                                <span class="badge bg-success">Activés</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-secondary" type="button" data-bs-toggle="modal"
                                                data-bs-target="#detailsCard{{ $abonnement->id }}">
                                                <i class="fe-info"></i>
                                            </button>
                                            <a href="{{ route('abonnements.edit', $abonnement->id) }}"
                                                class="btn btn-info">
                                                <i class="fe-edit"></i>
                                            </a>
                                            <button class="btn btn-danger" type="button" data-bs-toggle="modal"
                                                data-bs-target="#deleteCard{{ $abonnement->id }}">
                                                <i class="fe-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="deleteCard{{ $abonnement->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Suppression</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Etes-vous sûr de supprimer cet abonnement?</p>
                                                </div>
                                                <div class="modal-footer bg-whitesmoke br">

                                                    <button type="button" class="btn btn-danger" style="color:white;"
                                                        data-bs-dismiss="modal">Fermer</button>
                                                    <a href="{{ route('delete_abonnement', $abonnement->id) }}"
                                                        class="btn btn-success">
                                                        Oui</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="detailsCard{{ $abonnement->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="myLargeModalLabel{{ $abonnement->id }}"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="myLargeModalLabel{{ $abonnement->id }}">
                                                        Détails</h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>
                                                        <strong class="">Libelle:</strong>
                                                        {{ $abonnement->libelle }}
                                                        <hr>
                                                        <strong class="">Monnaie:</strong>
                                                        {{ $abonnement->monnaie }}
                                                        <hr>
                                                        <strong class="">Nombre de licences:</strong>
                                                        {{ $abonnement->count ?? 1 }}
                                                        <hr>
                                                        <strong class="">Prix du pack:</strong>
                                                        {{ number_format($abonnement->prix_gros ?? $abonnement->prix, 0, ',', ' ') }} {{ $abonnement->monnaie }}
                                                        <hr>
                                                        <strong class="">Bonus:</strong>
                                                        @if ($abonnement->is_pack_fixe == 1)
                                                            <span class="badge bg-danger">Désactivés</span>
                                                        @else
                                                            <span class="badge bg-success">Activés</span>
                                                        @endif
                                                        <hr>
                                                        <strong class="">Durée:</strong>
                                                        {{ $abonnement->nbjours ?? 365 }} jours
                                                    </p>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div> <!-- end card -->
            </div>
        </div>
    </div>
@endsection
