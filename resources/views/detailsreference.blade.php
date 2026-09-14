@extends('layouts.back_layout')

@section('title')
    Détails Référence Technique
@endsection

@section('content')
    <?php
    $autorite = \Illuminate\Support\Facades\DB::table('autoritecontractantes')
        ->join('pays', 'pays.id', '=', 'autoritecontractantes.id_pays')
        ->where('autoritecontractantes.id', $reference->autorite_contractante)
        ->first();
    $operateur = \Illuminate\Support\Facades\DB::table('operateurs')
        ->join('pays', 'pays.id', '=', 'operateurs.id_pays')
        ->where('operateurs.id', $reference->operateur)
        ->first();
    $type = \Illuminate\Support\Facades\DB::table('categories')->where('id', $reference->type_marche)->first();
    ?>

    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('selectoperateur') }}">Références techniques</a></li>
                            <li class="breadcrumb-item active">Détails Référence</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Détails Référence Technique</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <a href="{{ route('selectoperateur') }}" class="btn btn-secondary mb-3">
                            <i class="mdi mdi-arrow-left"></i> Retour à la liste
                        </a>

                        <h4 class="header-title mb-3">Référence #{{ $reference->numeroreference }}</h4>

                        <table class="table table-striped table-bordered dt-responsive nowrap w-100">
                            <tbody>
                                <tr>
                                    <td style="width: 35%;"><b>Index</b></td>
                                    <td>{{ $reference->numeroreference }}</td>
                                </tr>
                                <tr>
                                    <td><b>Libellé du marché</b></td>
                                    <td>{{ $reference->libelle_marche }}</td>
                                </tr>
                                <tr>
                                    <td><b>Numéro de marché/contrat</b></td>
                                    <td>
                                        @if ($reference->reference_marche)
                                            {{ $reference->reference_marche }}
                                        @else
                                            <span class="text-muted">Non renseigné</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Date du contrat</b></td>
                                    <td>
                                        @if ($reference->date_contrat)
                                            {{ \Carbon\Carbon::parse($reference->date_contrat)->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @if ($reference->show_amount == 1 && $reference->montant > 0)
                                <tr>
                                    <td><b>Montant du marché</b></td>
                                    <td>{{ number_format($reference->montant, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><b>Pays de l'autorité contractante</b></td>
                                    <td>{{ $autorite->nom_pays ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><b>Autorité contractante</b></td>
                                    <td><b>{{ $autorite->raison_social ?? '-' }}</b></td>
                                </tr>
                                <tr>
                                    <td><b>Type de marché</b></td>
                                    <td>{{ $type->nom_categorie ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><b>Pays du titulaire</b></td>
                                    <td>{{ $operateur->nom_pays ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><b>Titulaire du marché</b></td>
                                    <td><b>{{ $operateur->raison_social ?? '-' }}</b></td>
                                </tr>
                                <tr>
                                    <td><b>Année d'exécution</b></td>
                                    <td>{{ $reference->annee_execution }}</td>
                                </tr>
                                @if ($reference->sous_traitance)
                                <tr>
                                    <td><b>Sous traitance par</b></td>
                                    <td>{{ $reference->sous_traitance }}</td>
                                </tr>
                                @endif
                                @if ($reference->groupement)
                                <tr>
                                    <td><b>En groupement avec</b></td>
                                    <td>{{ $reference->groupement }}</td>
                                </tr>
                                @endif
                                @if ($reference->compte)
                                <tr>
                                    <td><b>Consultant pour le compte de</b></td>
                                    <td>{{ $reference->compte }}</td>
                                </tr>
                                @endif
                                @if ($reference->preuve_execution)
                                <tr>
                                    <td><b>Preuve d'exécution</b></td>
                                    <td>
                                        <a href="{{ asset('images/uploads/' . $reference->preuve_execution) }}" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="mdi mdi-eye"></i> Voir la pièce jointe
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
