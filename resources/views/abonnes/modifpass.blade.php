@extends('layouts.back_layout')
@section('title')
    Modification du mot de passe
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
                            <li class="breadcrumb-item"><a href="{{ route('profile') }}">Mon compte</a></li>
                            <li class="breadcrumb-item active">Modifier le mot de passe</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Modifier le mot de passe</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Modifier le mot de passe</h4>
                        <p class="text-muted">Veuillez remplir le formulaire ci-dessous pour modifier votre mot de passe.</p>

                        @if(session('okk'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('okk') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('message'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{route('postmodifpass')}}" method="post" class="mt-3">
                            @csrf
                            <div class="mb-3">
                                <label for="old" class="form-label">Ancien mot de passe</label>
                                <input class="form-control @error('old') is-invalid @enderror" value="{{ old('old') }}" name="old" required="true" type="password" id="old"> 
                                @error('old')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe</label>
                                <input class="form-control @error('password') is-invalid @enderror" value="{{ old('password') }}" required="true" name="password" type="password" id="password"> 
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmation du nouveau mot de passe</label>
                                <input class="form-control @error('password_confirmation') is-invalid @enderror" value="{{ old('password_confirmation') }}" required="true" name="password_confirmation" type="password" id="password_confirmation"> 
                                @error('password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mt-4 text-end">
                                <a href="{{ route('profile') }}" class="btn btn-secondary me-2">Annuler</a>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div> <!-- end card -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>

    </script>
@endsection