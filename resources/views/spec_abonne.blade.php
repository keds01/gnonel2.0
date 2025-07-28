@extends('layouts.back_layout')
@section('title')
    Vitrine des spécifications techniques
@endsection
@section('style')
<style>
    .ui-autocomplete {
        position: absolute;
        z-index: 9999;
        background-color: white;
        border: 1px solid #ccc;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .ui-menu-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f3f3f3;
    }
    .ui-menu-item:hover, .ui-state-focus {
        background-color: #f5f5f5;
    }
    .ui-helper-hidden-accessible {
        display: none;
    }
    .autocomplete-loading {
        background: url('/assets/img/loading.gif') no-repeat right center;
        background-size: 20px;
    }
    #spec-loader {
        display: none;
        text-align: center;
        padding: 20px;
    }
    .spec-card {
        min-height: 320px;
    }
</style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row mb-1">
            <div class="col-12">
                <div class="alert alert-danger">Veuillez lire le contexte d'utilisation pour un meilleur usage. En cas de besoin, vous pouvez apporter des modifications selon votre propre contexte.</div>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="mdi mdi-information-outline me-2"></i>
                    {{ $message }}
                    @if($specsCount == 0)
                        <p class="mt-2 mb-0"><small>Aucune spécification n'est disponible pour le moment. Veuillez réessayer plus tard.</small></p>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
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
                                                    <input type="search" class="form-control my-1 my-lg-0" id="recherche"
                                                        name="recherche" placeholder="Mot clé (libellé, contexte, etc)">
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
            <div id="spec-results-message" class="alert alert-info mt-2" style="display:none"></div>
            <div class="row justify-content-center" id="spec-list">
            @foreach ($specs as $spe)
                <div class="col-md-6 col-lg-4 col-xl-3 d-flex">
                        <div class="card product-box spec-card w-100">
                        <div class="card-body">
                            <div class="product-action">
                                <a href="{{ url('view-spec/' . $spe->lien) }}" target="_blank" class="btn btn-success btn-xs waves-effect waves-light download-btn">
                                    <i class="mdi mdi-eye"></i> Télécharger
                                </a>
                            </div>
                            <div class="bg-light">
                                    <img src="{{ asset('assets/img/spec.png') }}" alt="product-pic" class="img-fluid" />
                            </div>
                            <div class="product-info">
                                <div class="row align-items-center">
                                    <div class="col text-center">
                                        <h5 class="font-16 mt-0">
                                            <div class="button-list" id="tooltip-container{{ $spe->id }}">
                                                    <a style="color: black" target="_blank" data-bs-container="#tooltip-container{{ $spe->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $spe->contexte }}">
                                                    {{ $spe->libelle }} <span class="fa fa-info-circle"></span>
                                                </a>
                                            </div>
                                        </h5>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            @endforeach
        </div>
            <div id="spec-pagination" class="mt-3">{!! $specs->links() !!}</div>
        </div>
        </div>
    </div>
@endsection
@section('script')
<script type="text/javascript">
        $(document).ready(function() {
    let currentPage = 1;
    let lastRequest = {};
    // Fonction pour générer le HTML d'une carte spec
    function renderSpecCard(spec) {
        return `<div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card product-box spec-card">
                        <div class="card-body">
                            <div class="product-action">
                        <button type="button" onclick="downloadSpec('${spec.lien}', '${spec.libelle}')" class="btn btn-success btn-xs waves-effect waves-light download-btn">
                                    <i class="mdi mdi-eye"></i> Visualiser
                                </button>
                            </div>
                            <div class="bg-light">
                        <img src="{{ asset('assets/img/spec.png') }}" alt="product-pic" class="img-fluid" />
                            </div>
                            <div class="product-info">
                                <div class="row align-items-center">
                                    <div class="col text-center">
                                        <h5 class="font-16 mt-0">
                                    <div class="button-list" id="tooltip-container${spec.id}">
                                        <a style="color: black" target="_blank" data-bs-container="#tooltip-container${spec.id}" data-bs-toggle="tooltip" data-bs-placement="top" title="${spec.contexte}">
                                            ${spec.libelle} <span class="fa fa-info-circle"></span>
                                                </a>
                                            </div>
                                        </h5>
                                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }
    // Fonction pour afficher les specs
    function displaySpecs(data, paginationHtml) {
        let html = '';
        if (data.length > 0) {
            data.forEach(function(spec) {
                html += renderSpecCard(spec);
            });
            $('#spec-results-message').hide();
                        } else {
            html = '<div class="col-12 text-center"><div class="alert alert-warning">Aucun résultat trouvé pour votre recherche.</div></div>';
            $('#spec-results-message').show().text('Aucun résultat trouvé pour votre recherche.').removeClass('alert-info alert-success').addClass('alert-warning');
        }
        $('#spec-list').html(html);
        $('#spec-pagination').html(paginationHtml || '');
    }
    // Fonction pour effectuer la recherche AJAX
    function fetchSpecs(page = 1, isAdvanced = false) {
        const paysId = $('#pays').val();
        const categorieId = $('#categorie').val();
        const motcle = isAdvanced ? $('#recherche').val() : '';
        if (!paysId) {
            $('#spec-results-message').text('Veuillez sélectionner un pays pour la recherche.').show().removeClass('alert-danger alert-success').addClass('alert-warning');
            return;
        }
        $('#spec-loader').show();
        $('#spec-results-message').hide();
        $('#spec-list').html('');
        $.ajax({
            type: 'POST',
            url: "{{ url('specs/abonne/search') }}",
            data: {
                '_token': '{{ csrf_token() }}',
                'pays': paysId,
                'categorie': categorieId,
                'motcle': motcle,
                'page': page
            },
            dataType: 'json',
            success: function(response) {
                $('#spec-loader').hide();
                displaySpecs(response.data, response.pagination);
                $('#spec-results-message').text('La recherche a retourné ' + response.data.length + ' résultats.').show().removeClass('alert-danger alert-warning').addClass('alert-success');
            },
            error: function(xhr, status, error) {
                $('#spec-loader').hide();
                $('#spec-results-message').text('Erreur lors de la recherche: ' + error).show().removeClass('alert-info alert-success alert-warning').addClass('alert-danger');
            }
        });
    }
    // Bouton Visualiser
    $('#visualiser').on('click', function() {
        currentPage = 1;
        fetchSpecs(1, false);
    });
    // Recherche avancée
    $('#search-advanced').on('click', function() {
        currentPage = 1;
        fetchSpecs(1, true);
    });
    // Réinitialiser
    $('#reset-filters').on('click', function() {
        $('#pays').val('');
        $('#categorie').val('');
        $('#recherche').val('');
        $('#spec-list').html('');
        $('#spec-pagination').html('');
        $('#spec-results-message').text('Veuillez sélectionner au moins un pays et cliquer sur "Visualiser" pour commencer la recherche.').show().removeClass('alert-success alert-danger alert-warning').addClass('alert-info');
    });
    // Pagination AJAX (à implémenter côté backend)
    $(document).on('click', '.spec-page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            fetchSpecs(page, false);
        }
    });
    // Autocomplétion mot-clé
    $('#recherche').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "{{ route('spec.search.suggestions') }}",
                type: 'POST',
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    term: request.term,
                    pays_id: $('#pays').val() || 0,
                    categorie_id: $('#categorie').val() || 0
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 2,
        delay: 300
    });
});
// Fonction globale pour visualiser une spec
function downloadSpec(filename, title) {
    var viewBtns = $('.download-btn');
    viewBtns.attr('disabled', true);
    viewBtns.html('<i class="fas fa-spinner fa-spin"></i> Chargement...');
    var viewUrl = "{{ route('view.spec', '') }}/" + encodeURIComponent(filename);
    var newTab = window.open(viewUrl, '_blank');
    setTimeout(function() {
        viewBtns.attr('disabled', false);
        viewBtns.html('<i class="mdi mdi-eye"></i> Télécharger');
        if (!newTab || newTab.closed || typeof newTab.closed == 'undefined') {
            alert("Le navigateur a bloqué l'ouverture de l'onglet. Veuillez autoriser les popups pour ce site.");
        }
    }, 500);
}
    </script>
@endsection
