@extends('layouts.back_layout')
@section('title')
    Recherche de fournisseurs
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
                            <li class="breadcrumb-item active">
                                Recherche de fournisseurs
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title">
                        Recherche de fournisseurs
                    </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="filter-form" onsubmit="return false;">
                            <input type="hidden" id="reference" name="reference" value="">
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="me-sm-3">
                                        <select class="form-select my-1 my-lg-0" name="pays" id="pays">
                                            <option value="">--- Selectionner pays ---</option>
                                            @foreach ($pays as $pay)
                                                <option value="{{ $pay->id }}">{{ $pay->nom_pays }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="me-sm-3">
                                        <select class="form-select my-1 my-lg-0" name="categorie" id="categorie">
                                            <option value="">--- Toutes catégories ---</option>
                                            @foreach ($categories as $categorie)
                                                <option value="{{ $categorie->id }}">{{ $categorie->nom_categorie }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-6 col-md-2">
                                    <div class="text-lg-start my-1 my-lg-0">
                                        <button id="visualiser" type="button" class="btn btn-success waves-effect waves-light">
                                            <i class="mdi mdi-filter me-1"></i> Visualiser
                                        </button>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-6 col-md-2">
                                    <div class="text-lg-start my-1 my-lg-0">
                                        <button id="reset-filters" type="button" class="btn btn-light waves-effect waves-light">
                                            <i class="mdi mdi-refresh me-1"></i> Réinitialiser
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Recherche avancée -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="accordion" id="accordionSearch">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingAdvanced">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapseAdvanced" aria-expanded="false" aria-controls="collapseAdvanced">
                                                Recherche avancée
                                            </button>
                                        </h2>
                                        <div id="collapseAdvanced" class="accordion-collapse collapse" aria-labelledby="headingAdvanced" 
                                            data-bs-parent="#accordionSearch">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <input type="search" class="form-control my-1 my-lg-0" id="reference_advanced" name="reference_advanced" placeholder="Mot clé (libellé, opérateur, etc)">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button id="search-advanced" type="button" class="btn btn-primary waves-effect waves-light w-100">
                                                            <i class="mdi mdi-magnify me-1"></i> Rechercher
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end card -->
            </div> <!-- end col-->
        </div>
        <!-- end row-->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Liste des fournisseurs</h4>
                        <div id="search-results-message" class="alert alert-info mt-2" style="display:none"></div>
                        <div class="table-responsive">
                            <table id="search-results-table" class="table table-striped table-bordered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width:75%">Reference technique</th>
                                        <th style="width:25%">Opérateur économique</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="text-center">Aucun résultat. Veuillez effectuer une recherche.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="search-pagination" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row-->
    </div>
@endsection
@section('script')
<script type="text/javascript">
$(document).ready(function() {
    function fetchFournisseurs(page = 1, isAdvanced = false) {
        const paysId = $('#pays').val();
        const categorieId = $('#categorie').val();
        const referenceText = isAdvanced ? $('#reference').val() : '';
        $('#search-results-message').hide();
        $.ajax({
            type: 'POST',
            url: "{{ url('references/recherche') }}?page=" + page,
            data: {
                '_token': '{{ csrf_token() }}',
                'pays': paysId,
                'categorie': categorieId,
                'reference': referenceText
            },
            dataType: 'json',
            success: function(response) {
                // Mettre à jour le tableau
                let html = '';
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(item) {
                        const reference = item.libelle_marche || '';
                        const referenceNo = item.reference_marche || '';
                        const operateur = item.operateur || '';
                        const categorie = item.categorie || '';

                        const annee = item.annee_execution || '';
                        const operateur_id = item.operateur_id || '';
                        const pays_id = item.pays_id || '';
                        const link = "/view/selectoperateur?operateur_id=" + operateur_id + "&pays_id=" + pays_id;
                        const displayRef = reference.split(' ').length > 6
                            ? reference.split(' ').slice(0, 6).join(' ') + '...'
                            : reference;
                        html += '<tr>';
                        html += '<td>' + displayRef + '</td>';
                        html += '<td><a href="' + link + '">' + operateur + '</a></td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="2" class="text-center">Aucun résultat trouvé pour votre recherche.</td></tr>';
                }
                $('#search-results-table tbody').html(html);
                // Pagination
                $('#search-pagination').html(response.pagination || '');
            },
            error: function(xhr, status, error) {
                $('#search-results-message').text('Erreur lors de la recherche: ' + error).show();
            }
        });
    }
    // Visualiser
    $('#visualiser').on('click', function() {
        fetchFournisseurs(1, false);
    });
    // Réinitialiser
    $('#reset-filters').on('click', function() {
        $('#pays').val('');
        $('#categorie').val('');
        $('#reference').val('');
        fetchFournisseurs(1, false);
    });
    // Pagination dynamique
    $(document).on('click', '#search-pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetchFournisseurs(page, false);
    });
    // Affichage initial (optionnel)
    // fetchFournisseurs(1, false);
    // Recherche avancée
    $('#search-advanced').on('click', function() {
        // Copier la valeur du champ de recherche avancée vers le champ caché
        $('#reference').val($('#reference_advanced').val());
        // Fermer l'accordéon après la recherche (optionnel)
        // $('#collapseAdvanced').collapse('hide');
        fetchFournisseurs(1, true);
    });
    
    // Permettre la recherche avancée en appuyant sur Entrée
    $('#reference_advanced').on('keypress', function(e) {
        if(e.which === 13) { // Code de la touche Entrée
            e.preventDefault();
            $('#search-advanced').click();
        }
    });
    
    // Configuration de l'autocomplétion pour le champ de recherche avancée
    $('#reference_advanced').autocomplete({
        source: function(request, response) {
            // Récupérer les valeurs des filtres
            var paysId = $('#pays').val();
            var categorieId = $('#categorie').val();
            
            $.ajax({
                url: "{{ route('basefournisseurs.autocomplete') }}",
                dataType: "json",
                data: {
                    term: request.term,
                    pays_id: paysId,
                    categorie_id: categorieId
                },
                success: function(data) {
                    response(data);
                },
                error: function() {
                    response([]);
                }
            });
        },
        minLength: 2,
        delay: 300,
        select: function(event, ui) {
            // Quand une suggestion est sélectionnée, la mettre dans le champ et déclencher la recherche
            $("#reference_advanced").val(ui.item.value);
            $("#reference").val(ui.item.value);
            fetchFournisseurs(1, true);
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        // Personnalisation du rendu des suggestions
        return $('<li>')
            .append('<div>' + item.label + '</div>')
            .appendTo(ul);
    };
});
</script>
@endsection
