@extends('layouts.landing')
@section('title')
    Spécifications techniques
@endsection
@section('styles')
    <link href="{{ asset('backoffice/css/config/default/bootstrap_.min.css') }}" rel="stylesheet" type="text/css"
        id="bs-default-stylesheet" />
    <link href="{{ asset('backoffice/css/config/default/app_.min.css') }}" rel="stylesheet" type="text/css"
        id="app-default-stylesheet" />

    <style>
        .pages-intro p {
            margin-top: 15px
        }

        .common-hero {
            background-color: var(--vtc-bg-common-bg2) !important;
        }
    </style>
@endsection
@section('content')
    <hr>
    <div class="team1 mt-1 bg-white mb-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 m-auto text-center">
                    <div class="heading1 mt-4">
                        <h2 class="text-anime-style-3">Vitrine des spécifications techniques</h2>
                        <div class="space8"></div>
                        <div class="contact10">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="contact-from px-3 py-3">
                                            <div class="form-area">
                                                <div class="row">
                                                    <div class="col-md-3 mb-3">
                                                        <div class="single-input my-0">
                                                            <select name="pays" class="form-control" required
                                                                id="pays">
                                                                <option value="0" disabled="true" selected="true">
                                                                    --- Selectionner pays ---
                                                                </option>
                                                                @foreach ($pays as $pay)
                                                                    <option value="{{ $pay->id }}">
                                                                        {{ $pay->nom_pays }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <div class="single-input my-0"">
                                                            <select name="categorie" class="form-control" id="categorie"
                                                                required>
                                                                <option value="0" disabled="true" selected="true">
                                                                    --- Selectionner catégorie ---</option>
                                                                @foreach ($categories as $categorie)
                                                                    <option value="{{ $categorie->id }}">
                                                                        {{ $categorie->nom_categorie }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <div class="single-input my-0"">
                                                            <input type="text" placeholder="Mot clé" id="recherche"
                                                                name="recherche" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 mb-3">
                                                        <div class="single-input my-0"">
                                                            <button class="theme-btn16" onclick="filter()"
                                                                style="background-color: #0d8813" type="button">Filter
                                                                <span><i
                                                                        class="fa-solid fa-arrow-right"></i></span></button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="space30"></div>
            <div class="row" id="specs">
                @foreach ($specs as $spe)
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card product-box">
                            <div class="card-body">
                                <div class="product-action">
                                    <button type="button" onclick="$('#loginRequiredModal').modal('show');"
                                        class="btn btn-success btn-xs waves-effect waves-light"><i
                                            class="mdi mdi-download"></i>
                                        Télécharger</button>
                                </div>

                                <div class="bg-light">
                                    <img src="{{ asset('assets/img/spec.png') }}"" alt="product-pic" class="img-fluid" />
                                </div>

                                <div class="product-info">
                                    <div class="row align-items-center">
                                        <div class="col text-center">
                                            <h5 class="font-16 mt-0">
                                                <div class="button-list" id="tooltip-container{{ $spe->id }}">
                                                    <span style="color: black">
                                                        {{ $spe->libelle }} 
                                                        <span class="fas fa-info-circle" 
                                                            data-bs-container="#tooltip-container{{ $spe->id }}" 
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top" 
                                                            title="{{ $spe->contexte }}"></span>
                                                    </span>
                                                </div>
                                            </h5>
                                        </div>
                                    </div> <!-- end row -->
                                </div> <!-- end product info-->
                            </div>
                        </div> <!-- end card-->
                    </div>
                @endforeach
            </div>
            <div class="space30"></div>
            <div class="row">
                <div class="col-12 m-auto">
                    <div class="theme-pagination pagination text-center">
                        {{ $specs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- Modal pour invitation à se connecter -->
    <div class="modal fade" id="loginRequiredModal" tabindex="-1" role="dialog" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginRequiredModalLabel">Connexion requise</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Pour télécharger des spécifications techniques, vous devez être connecté.</p>
                    <p>Si vous n'avez pas encore de compte, vous pouvez en créer un gratuitement.</p>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-danger" style="color:white;" data-bs-dismiss="modal">Fermer</button>
                    <a href="{{ route('login') }}" class="btn btn-primary">Se connecter</a>
                    <a href="{{ route('register') }}" class="btn btn-success">Créer un compte</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        function filter() {
            var row = $('#specs');
            row.empty();
            $.ajax({
                type: 'post',
                url: "{{ url('filtrerspec') }}",
                data: {
                    'pays': $('#pays').val(),
                    'categorie': $('#categorie').val(),
                    'recherche': $('#recherche').val(),
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    console.log(response.donnes);

                    var data = response.donnes;
                    var col = '';
                    if (data.length > 0) {
                        for (var i = 0; i < response.donnes.length; i++) {
                            var id = data[i].idreference;
                            var libelle = data[i].libelle;
                            var pays = data[i].nom_pays;
                            var categorie = data[i].nom_categorie;
                            var lien = data[i].lien;
                            var contexte = data[i].contexte;
                            col += `
                                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card product-box">
                        <div class="card-body">
                            <div class="product-action">
                                <button type="button" onclick="$('#loginRequiredModal').modal('show');"
                                    class="btn btn-success btn-xs waves-effect waves-light"><i class="mdi mdi-download"></i>
                                    Télécharger</button>
                            </div>

                            <div class="bg-light">
                                <img src="/assets/img/spec.png" alt="product-pic" class="img-fluid" />
                            </div>

                            <div class="product-info">
                                <div class="row align-items-center">
                                    <div class="col text-center">
                                        <h5 class="font-16 mt-0">
                                            <div class="button-list" id="tooltip-container${id}">
                                                <span style="color: black">
                                                    ${libelle} 
                                                    <span class="fas fa-info-circle" 
                                                        data-bs-container="#tooltip-container${id}"
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top"
                                                        title="${contexte}"></span>
                                                </span>
                                            </div>
                                        </h5>
                                    </div>
                                </div> <!-- end row -->
                            </div> <!-- end product info-->
                        </div>
                    </div> <!-- end card-->
                </div> <!-- end col-->
                                `;
                            // col += `
                        //  <div class="col-lg-3" style="margin-bottom:50px">
                        //     <label style="color:#1b87fa">${libelle}</label>
                        //     <img src="{{ asset('assets/img/spec.png') }}" style="border: 1px solid;border-color: #1b87fa;height:250px;">
                        //     <button class="spec" style="color:#1b87fa;margin-top:3px;background-color:white;border-color: #3fa46a;float: right;">
                        //         <a href="{{ asset('images/uploads/') }}/${libelle}">Télécharger</a></button></div>
                        // `;
                        }
                        row.append(col);
                    } else {
                        col +=
                            '<div class="col-lg-12 text-center mt-4"><label style="color:gray;font-size:22px;">Pas de données</label></div>';
                        row.append(col);

                    }

                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            // Configuration de l'autocomplétion pour le champ de recherche
            $("#recherche").autocomplete({
                source: function(request, response) {
                    // Afficher l'indicateur de chargement
                    $("#recherche").addClass("loading");
                    
                    $.ajax({
                        url: "{{ route('spec.public.suggestions') }}",
                        method: "POST",
                        dataType: "json",
                        data: { 
                            term: request.term,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            // Retirer l'indicateur de chargement
                            $("#recherche").removeClass("loading");
                            
                            if (data.length === 0) {
                                // Aucun résultat
                                var noResult = [{
                                    label: "Aucun résultat trouvé",
                                    value: "",
                                    disabled: true
                                }];
                                response(noResult);
                            } else {
                                response(data);
                            }
                        },
                        error: function(xhr) {
                            // Retirer l'indicateur de chargement
                            $("#recherche").removeClass("loading");
                            
                            console.error("Erreur d'autocomplétion:", xhr);
                            var errorMsg = [{
                                label: "Erreur lors de la recherche",
                                value: "",
                                disabled: true
                            }];
                            response(errorMsg);
                        }
                    });
                },
                minLength: 2,
                delay: 300,
                select: function(event, ui) {
                    // Éviter la sélection des messages d'erreur ou "aucun résultat"
                    if (ui.item.disabled) {
                        event.preventDefault();
                        return false;
                    }
                    
                    $("#recherche").val(ui.item.value);
                    // Lancer la recherche après sélection
                    filter();
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                var li = $('<li>');
                
                if (item.disabled) {
                    li.addClass('ui-state-disabled');
                }
                
                li.append(item.label).appendTo(ul);
                return li;
            };
        });
    </script>
    
    <style>
        /* Style pour l'indicateur de chargement */
        input.loading {
            background-image: url('data:image/gif;base64,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');
            background-position: right center;
            background-repeat: no-repeat;
            background-size: 20px 20px;
            padding-right: 25px;
        }
        
        /* Style pour les éléments d'autocomplétion */
        .ui-autocomplete {
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 5px;
            border: 1px solid #ccc;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .ui-autocomplete .ui-menu-item {
            padding: 5px;
            cursor: pointer;
        }
        
        .ui-autocomplete .ui-state-disabled {
            color: #999;
            cursor: default;
        }
        
        .ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
            background-color: #007bff;
            color: white;
        }
    </style>
@endsection
