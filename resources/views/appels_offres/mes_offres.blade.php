@extends('layouts.back_layout')

@section('title')
    Mes appels d'offres
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
                            <li class="breadcrumb-item active">Mes appels d'offres</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Mes appels d'offres publiés</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title mb-0">Liste de mes appels d'offres</h4>
                            <a href="{{ route('autorite.offre.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Nouvel appel d'offre
                            </a>
                        </div>

                        <table id="mes-offres-table" class="table table-striped dt-responsive nowrap w-100">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>N°</th>
                                    <th>Référence</th>
                                    <th>Intitulé</th>
                                    <th>Catégorie</th>
                                    <th>Date publication</th>
                                    <th>Date clôture</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offres as $offre)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $offre->reference }}</td>
                                        <td>{{ $offre->libelle_appel }}</td>
                                        <td>{{ $offre->nom_categorie }}</td>
                                        <td>{{ \Carbon\Carbon::parse($offre->date_publication)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($offre->date_cloture)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($offre->status == 1)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-danger">Clôturé</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('autorite.offre.edit', $offre->id) }}" class="btn btn-sm btn-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('autorite.offre.delete', $offre->id) }}" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet appel d\'offre ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Aucun appel d'offre publié</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('#mes-offres-table').DataTable({
                "oLanguage": {
                    "sProcessing": "Traitement en cours ...",
                    "sLengthMenu": "Afficher _MENU_ lignes",
                    "sZeroRecords": "Aucun résultat trouvé",
                    "sEmptyTable": "Aucune donnée disponible",
                    "sInfo": "Lignes _START_ à _END_ sur _TOTAL_",
                    "sInfoEmpty": "Aucune ligne affichée",
                    "sInfoFiltered": "(Filtrer un maximum de_MAX_)",
                    "sSearch": "Chercher:",
                    "oPaginate": {
                        "sFirst": "Premier",
                        "sLast": "Dernier",
                        "sNext": "Suivant",
                        "sPrevious": "Précédent"
                    }
                }
            });
        });
    </script>
@endsection
