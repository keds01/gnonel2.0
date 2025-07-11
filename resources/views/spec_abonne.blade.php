@extends('layouts.back_layout')
@section('title')
    Vitrine des spécifications techniques
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
                            <li class="breadcrumb-item active">Vitrine des spécifications techniques</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Vitrine des spécifications techniques</h4>
                </div>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-12">
                <div class="alert alert-danger">Veuillez lire le contexte d'utilisation pour un meilleur usage. En cas de
                    besoin, vous pouvez apporter des modifications selon votre votre propre contexte.</div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="me-sm-3">
                                    <select class="form-select my-1 my-lg-0" name="pays" required id="pays">
                                        <option value="0" disabled="true" selected="true">--- Selectionner pays ---
                                        </option>
                                        @foreach ($pays as $pay)
                                            <option value="{{ $pay->id }}">{{ $pay->nom_pays }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="me-sm-3">
                                    <select class="form-select my-1 my-lg-0" name="categorie" id="categorie" required>
                                        <option value="0" disabled="true" selected="true">--- Selectionner catégorie
                                            ---</option>
                                        @foreach ($categories as $categorie)
                                            <option value="{{ $categorie->id }}">{{ $categorie->nom_categorie }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="me-3">
                                    <input type="search" class="form-control my-1 my-lg-0" id="recherche" name="recherche"
                                        placeholder="Mot clé...">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="text-lg-start my-1 my-lg-0">
                                    <button id="filtrer" type="button"
                                        class="btn btn-success waves-effect waves-light"><i class="mdi mdi-filter me-1"></i>
                                        Filtrer</button>
                                </div>
                            </div><!-- end col-->
                        </div> <!-- end row -->
                    </div>
                </div> <!-- end card -->
            </div> <!-- end col-->
        </div>
        <!-- end row-->

        <div class="row" id="toadd">
            @foreach ($specs as $spe)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card product-box">
                        <div class="card-body">
                            <div class="product-action">
                                <button type="button" 
                                    onclick="downloadSpec('{{ $spe->lien }}', '{{ $spe->libelle }}')"
                                    class="btn btn-success btn-xs waves-effect waves-light download-btn">
                                    <i class="mdi mdi-download"></i> Télécharger
                                </button>
                            </div>

                            <div class="bg-light">
                                <img src="{{ asset('assets/img/spec.png') }}"" alt="product-pic" class="img-fluid" />
                            </div>

                            <div class="product-info">
                                <div class="row align-items-center">
                                    <div class="col text-center">
                                        <h5 class="font-16 mt-0">
                                            <div class="button-list" id="tooltip-container{{ $spe->id }}">
                                                <a style="color: black" target="_blank"
                                                    data-bs-container="#tooltip-container{{ $spe->id }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ $spe->contexte }}">
                                                    {{ $spe->libelle }} <span class="fa fa-info-circle"></span>
                                                </a>
                                            </div>
                                        </h5>
                                    </div>
                                </div> <!-- end row -->
                            </div> <!-- end product info-->
                        </div>
                    </div> <!-- end card-->
                </div> <!-- end col-->
            @endforeach

        </div>
        <!-- end row-->

        <div class="row">
            <div class="col-12">
                {{ $specs->links() }}
                {{-- <ul class="pagination pagination-rounded justify-content-end mb-3">
                    <li class="page-item">
                        <a class="page-link" href="javascript: void(0);" aria-label="Previous">
                            <span aria-hidden="true">«</span>
                            <span class="visually-hidden">Previous</span>
                        </a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="javascript: void(0);">1</a></li>
                    <li class="page-item"><a class="page-link" href="javascript: void(0);">2</a></li>
                    <li class="page-item"><a class="page-link" href="javascript: void(0);">3</a></li>
                    <li class="page-item"><a class="page-link" href="javascript: void(0);">4</a></li>
                    <li class="page-item"><a class="page-link" href="javascript: void(0);">5</a></li>
                    <li class="page-item">
                        <a class="page-link" href="javascript: void(0);" aria-label="Next">
                            <span aria-hidden="true">»</span>
                            <span class="visually-hidden">Next</span>
                        </a>
                    </li>
                </ul> --}}
            </div> <!-- end col-->
        </div>
    </div>
@endsection
@section('script')
    <!-- Modal pour limite de téléchargement -->
    <div class="modal fade" id="downloadLimitModal" tabindex="-1" role="dialog" aria-labelledby="downloadLimitModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="downloadLimitModalLabel">Limite de téléchargements atteinte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Vous avez atteint votre limite de téléchargements (3 fichiers).</p>
                    <p>Pour télécharger davantage de spécifications techniques, veuillez souscrire à un abonnement.</p>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-danger" style="color:white;" data-bs-dismiss="modal">Fermer</button>
                    <a href="{{ route('pricing') }}" class="btn btn-success">Découvrir nos abonnements</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fonction pour télécharger une spécification avec limitation
        function downloadSpec(filename, title) {
            // URL pour la vérification des permissions
            var checkUrl = "{{ route('download.spec', '') }}/" + encodeURIComponent(filename);
            
            // Afficher un indicateur de chargement sur tous les boutons
            var downloadBtns = $('.download-btn');
            downloadBtns.attr('disabled', true);
            downloadBtns.html('<i class="fas fa-spinner fa-spin"></i> Chargement...');
            
            console.log("Téléchargement initié pour:", filename);
            
            // Vérifier les permissions de téléchargement
            $.ajax({
                type: 'GET',
                url: checkUrl,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log("Réponse du serveur:", response);
                    
                    // Rétablir tous les boutons
                    downloadBtns.attr('disabled', false);
                    downloadBtns.html('<i class="mdi mdi-download"></i> Télécharger');
                    
                    if (response.limit_reached) {
                        // Limite de téléchargement atteinte
                        $('#downloadLimitModal').modal('show');
                    } 
                    else if (response.success && response.download_url) {
                        // Méthode 1 : Téléchargement direct par fenêtre
                        window.location.href = response.download_url;
                        
                        // Méthode 2 : Iframe de secours (si la redirection directe échoue)
                        setTimeout(function() {
                            console.log("Téléchargement de secours via iframe");
                            var iframe = document.createElement('iframe');
                            iframe.style.display = 'none';
                            iframe.src = response.download_url;
                            document.body.appendChild(iframe);
                            
                            // Nettoyer l'iframe après le téléchargement
                            setTimeout(function() {
                                if (document.body.contains(iframe)) {
                                    document.body.removeChild(iframe);
                                }
                            }, 5000);
                        }, 1000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erreur AJAX:", status, error);
                    console.log("Détails de l'erreur:", xhr.responseText);
                    
                    // Rétablir tous les boutons
                    downloadBtns.attr('disabled', false);
                    downloadBtns.html('<i class="mdi mdi-download"></i> Télécharger');
                    
                    if (xhr.status === 401) {
                        // L'utilisateur n'est pas connecté
                        alert('Vous devez être connecté pour télécharger des spécifications.');
                        window.location.href = "{{ route('login') }}";
                    } else {
                        // Autre erreur
                        alert('Une erreur est survenue lors du téléchargement. Veuillez réessayer. (' + (xhr.responseText || error) + ')');
                    }
                }
            });
        }
        
        $(document).ready(function() {
            // Activation de l'autocomplétion améliorée
            var $rechercheInput = $("#recherche");
            
            // Afficher un indicateur visuel pendant le chargement des suggestions
            $rechercheInput.on('autocompletefocus', function() {
                $(this).addClass('autocomplete-loading');
            }).on('autocompletecomplete', function() {
                $(this).removeClass('autocomplete-loading');
            });

            // Configuration de l'autocomplétion
            $rechercheInput.autocomplete({
                delay: 300,  // délai avant déclenchement pour éviter trop de requêtes
                source: function(request, response) {
                    // Ne rien faire si moins de 2 caractères
                    if (request.term.length < 2) {
                        return response([]);
                    }
                
                    // Afficher indicateur de chargement
                    $rechercheInput.addClass('autocomplete-loading');
                    console.log("Recherche de suggestions pour: " + request.term);
                    
                    // Appel AJAX pour récupérer les suggestions
                    $.ajax({
                        url: "{{ route('spec.search.suggestions') }}",
                        method: "POST", // S'assurer d'utiliser POST comme configuré dans la route
                        dataType: "json",
                        data: {
                            term: request.term,
                            pays_id: $('#pays').val() || 0,
                            categorie_id: $('#categorie').val() || 0,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            // Retirer l'indicateur de chargement
                            $rechercheInput.removeClass('autocomplete-loading');
                            console.log("Réponse reçue:", data);
                            
                            // Si la réponse est un tableau vide
                            if ($.isArray(data) && data.length === 0) {
                                response([{ label: 'Aucun résultat trouvé', value: '', disabled: true }]);
                            }
                            // Si c'est un tableau avec des valeurs (succès normal)
                            else if ($.isArray(data)) {
                                // Formater les résultats
                                response($.map(data, function(item) {
                                    return {
                                        label: item,
                                        value: item
                                    };
                                }));
                            }
                            // Si c'est un objet avec un message
                            else if (typeof data === 'object' && data[0]) {
                                // C'est probablement un message d'erreur ou d'information
                                response([{ label: data[0], value: '', disabled: true }]);
                            }
                            else {
                                // Cas par défaut si structure inconnue
                                response([{ label: 'Format de réponse non reconnu', value: '', disabled: true }]);
                            }
                        },
                        error: function(xhr, status, error) {
                            // Retirer l'indicateur de chargement
                            $rechercheInput.removeClass('autocomplete-loading');
                            console.error("Erreur lors de la récupération des suggestions:", error);
                            console.log("Détails:", xhr.responseText);
                            response([{ label: 'Erreur de connexion', value: '', disabled: true }]);
                        }
                    });
                },
                minLength: 1,  // Permettre la recherche dès le premier caractère, le filtrage réel se fait à 2 caractères
                select: function(event, ui) {
                    if (ui.item.disabled) {
                        event.preventDefault(); // Empêcher la sélection
                        return false;
                    }
                    
                    // Action à effectuer lorsqu'une suggestion est sélectionnée
                    $("#recherche").val(ui.item.value);
                    // Déclencher le filtre immédiatement si désiré
                    // $("#filtrer").click();
                    return true;
                },
                open: function() {
                    $(this).removeClass('ui-corner-all').addClass('ui-corner-top');
                },
                close: function() {
                    $(this).removeClass('ui-corner-top').addClass('ui-corner-all');
                    $(this).removeClass('autocomplete-loading'); // S'assurer que l'indicateur est retiré
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                // Personnalisation du rendu des éléments de la liste
                if (item.disabled) {
                    return $("<li class='ui-state-disabled'>").append(item.label).appendTo(ul);
                } else {
                    return $("<li>").append("<div>" + item.label + "</div>").appendTo(ul);
                }
            };
            
            // Ajouter un style CSS pour l'indicateur de chargement avec spinner CSS
            $("<style>\n\
            .autocomplete-loading { \n\
                position: relative; \n\
            }\n\
            .autocomplete-loading:after { \n\
                content: ''; \n\
                position: absolute; \n\
                right: 10px; \n\
                top: 50%; \n\
                transform: translateY(-50%); \n\
                width: 15px; \n\
                height: 15px; \n\
                border-radius: 50%; \n\
                border: 2px solid #f3f3f3; \n\
                border-top: 2px solid #3498db; \n\
                animation: spin 0.8s linear infinite; \n\
            }\n\
            @keyframes spin { \n\
                0% { transform: translateY(-50%) rotate(0deg); } \n\
                100% { transform: translateY(-50%) rotate(360deg); } \n\
            }\n\
            </style>").appendTo("head");

            $("#filtrer").on("click", function() {
                var row = $('#toadd');
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
                                <button type="button" 
                                    onclick="downloadSpec('${lien}', '${libelle}')"
                                    class="btn btn-success btn-xs waves-effect waves-light download-btn">
                                    <i class="mdi mdi-download"></i> Télécharger
                                </button>
                            </div>

                            <div class="bg-light">
                                <img src="/assets/img/spec.png" alt="product-pic" class="img-fluid" />
                            </div>

                            <div class="product-info">
                                <div class="row align-items-center">
                                    <div class="col text-center">
                                        <h5 class="font-16 mt-0">
                                            <div class="button-list" id="tooltip-container${id}">
                                                <a style="color: black" target="_blank"
                                                    data-bs-container="#tooltip-container${id}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="${contexte}">
                                                    ${libelle} <span class="fa fa-info-circle"></span>
                                                </a>
                                            </div>
                                        </h5>
                                    </div>
                                </div> <!-- end row -->
                            </div> <!-- end product info-->
                        </div>
                    </div> <!-- end card-->
                </div> <!-- end col-->
                                `;
                                // '<div class="col-lg-3" style="margin-bottom:50px"><label style="color:#1b87fa">' +
                                // libelle +
                                // '</label><img src="{{ asset('assets/img/spec.png') }}" style="border: 1px solid;border-color: #1b87fa;height:250px;"><button class="spec" style="color:#1b87fa;margin-top:3px;background-color:white;border-color: #3fa46a;float: right;"><a href="{{ asset('images/uploads/') }}/' +
                                // lien + '">Télécharger</a></button></div>';
                            }
                            row.append(col);
                        } else {
                            col +=
                                '<div class="col-lg-12 spec text-center" style="margin-bottom:50px"><label style="color:#1b87fa;font-size:22px;">Pas de données</label></div>';
                            row.append(col);

                        }
                    }
                });
            })



        });
    </script>
@endsection
