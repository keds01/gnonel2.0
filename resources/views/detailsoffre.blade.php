@extends('layouts.back_layout')
@section('title')
    Détails appel d'offre
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
                            <li class="breadcrumb-item"><a href="{{ route('rechercheoffre') }}">Appels d'offres</a></li>
                            <li class="breadcrumb-item active">Détails</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Détails de l'appel d'offre</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-3" style="color: #1b87fa;">{{$offres[0]->libelle_appel}}</h4>
                        
                        <table class="table table-bordered table-striped" style="width:100%">
                            <tbody>
                                <tr style="background-color: #1b87fa;">
                                    <th style="color:white; width: 30%;">Rubrique</th>
                                    <th style="color:white;">Détails</th>
                                </tr>
                                <tr>
                                    <td><strong>Numéro appel d'offre :</strong></td>
                                    <td>{{$offres[0]->reference}}</td>
                                </tr>
                                <tr>
                                    <td><strong>Autorité Contractante :</strong></td>
                                    <td>{{$offres[0]->raison_social}}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pays :</strong></td>
                                    <td>{{$offres[0]->nom_pays}}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date de publication :</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($offres[0]->date_publication)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date de clôture :</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($offres[0]->date_cloture)->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Source :</strong></td>
                                    <td>{{$offres[0]->source}}</td>
                                </tr>
                                <tr>
                                    <td><strong>Personne à contacter :</strong></td>
                                    <td>{{$offres[0]->contact}}</td>
                                </tr>
                                <tr>
                                    <td><strong>Autre information :</strong></td>
                                    <td>{{$offres[0]->description}}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-3">
                            <a href="{{ route('rechercheoffre') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>

    </script>
@endsection
