@extends('layouts.back_layout')
@section('title')
    Appels d'offres
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
                            <li class="breadcrumb-item active">Appels d'offres</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Appels d'offres en cours</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title mb-0">Filtrer par pays</h4>
                            @if(Auth::user()->type_user == 5 || Auth::user()->type_user == 0)
                                <a href="{{ route('autorite.offre.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Publier un appel d'offre
                                </a>
                            @endif
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <select class="form-control" id="pays" name="pays">
                                    <option value="" selected>--- Tous les pays ---</option>
                                    @foreach($pays as $pay)
                                        <option value="{{$pay->id}}" @if(($data['pays'] ?? null) == $pay->id) selected @endif>{{$pay->nom_pays}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                       <table id="appels-offres-table" class="table table-striped dt-responsive nowrap w-100">
    <thead class="bg-primary text-white text-center">
        <tr>
            <th style="width:5%">N°</th>
            <th style="width:35%">Intitulé de l'offre</th>
            <th style="width:25%">Autorité Contractante</th>
            <th style="width:15%">Date publication</th>
            <th style="width:20%">Date de clôture</th>
        </tr>
    </thead>
    <tbody>
        @foreach($offres as $offre)
            <tr style="cursor:pointer" onclick="detail({{ $offre->id }})">
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $offre->libelle_appel }}</td>
                <td>{{ $offre->raison_social }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($offre->date_publication)->format('d/m/Y') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($offre->date_cloture)->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
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
            var table = $('#appels-offres-table').DataTable({
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

            $("#pays").on("change", function () {
                var paysId = $(this).val();
                if (paysId) {
                    $.ajax({
                        type: 'get',
                        url: "{{ url('search-offre') }}/" + paysId,
                        success: function(response) {
                            table.clear();
                            var data = response.donnes;
                            if (data.length > 0) {
                                var rows = data.map((offre, i) => [
                                    i + 1,
                                    offre.libelle_appel,
                                    offre.raison_social,
                                    moment(offre.date_publication).format('DD/MM/YYYY'),
                                    moment(offre.date_cloture).format('DD/MM/YYYY HH:mm')
                                ]);
                                table.rows.add(rows).draw();
                            }
                        },
                        error: function() {
                            alert("Erreur lors du chargement des offres.");
                        }
                    });
                } else {
                    window.location.href = "{{ route('rechercheoffre') }}";
                }
            });
        });

        function detail(id) {
            window.location.href = "{{ url('Details') }}/" + id;
        }
    </script>
@endsection
