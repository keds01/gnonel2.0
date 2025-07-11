@extends('layouts.back_layout')
@section('title')
    Relévés de références techniques
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
                            <li class="breadcrumb-item active">Relévés de références techniques</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Relévés de références techniques</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-5">
                                <div class="me-sm-3">
                                    <select class="form-select my-1 my-lg-0" name="payst" required id="payst">
                                        <option value="0" disabled="true" selected="true">--- Selectionner pays ---
                                        </option>
                                        @foreach ($pays as $pay)
                                            <option value="{{ $pay->id }}"
                                                @if ($paysId != null && $pay->id == $paysId) selected @endif>
                                                {{ $pay->nom_pays }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-5">
                                <div class="me-sm-3">
                                    <select class="form-select  my-1 my-lg-0" name="operateur" required id="titulaire">
                                        <option value="" disabled="true" selected="true">--- Sélectionner l'operateur
                                            ---
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-3">
                                <div class="me-3">
                                    <input type="search" class="form-control my-1 my-lg-0" id="gnonelid" name="gnonelid" autocomplete="off"
                                        placeholder="Gnonel ID">
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <div class="text-lg-start my-1 my-lg-0">
                                    <button id="visualiser" type="button"
                                        class="btn btn-success waves-effect waves-light"><i class="mdi mdi-filter me-1"></i>
                                        Visualiser</button>
                                </div>
                            </div><!-- end col-->
                        </div> <!-- end row -->
                    </div>
                </div> <!-- end card -->
            </div> <!-- end col-->
        </div>
        <!-- end row-->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Affichage des relévés de références techniques</h4>
                        <br>
                        <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <td>Index</td>
                                    <td style="width:40%">Libellés</td>
                                    <td style="width:20%">Autorité contractante</td>
                                    <td>Année</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div> <!-- end card -->
            </div>
        </div>
        <!-- end row-->
    </div>

    <div class="modal fade" id="myModal2" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    « Cet opérateur économique n’est pas abonné à gnonel.com ». Voulez-vous lui recommander ce site ?
                </div>
                <div class="modal-footer">
                    <button type="button" id="non" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                    <a class="btn btn-primary" style="text-decoration: none;color: white;"
                        href="{{ route('recommanders.index') }}">Oui</a>
                </div>
            </div>
        </div>
    </div>
    @if (Session::has('add_ok'))
        <script>
            window.onload = function() {
                var myModal = new bootstrap.Modal(document.getElementById('myModal2'));
                myModal.show();
            };
        </script>
    @endif
@endsection
@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            var paysId = parseInt("{{ $paysId }}");
            var operateurId = parseInt("{{ $operateurId }}");

            // Configuration de l'autocomplétion pour le champ Gnonel ID
            $("#gnonelid").autocomplete({
                delay: 300,
                minLength: 2,
                source: function(request, response) {
                    // Afficher indicateur de chargement
                    $("#gnonelid").addClass("autocomplete-loading");
                    console.log("Recherche de Gnonel ID pour: " + request.term);
                    
                    // Appel AJAX pour récupérer les suggestions
                    $.ajax({
                        url: "{{ route('gnonel.search.suggestions') }}",
                        method: "POST",
                        dataType: "json",
                        data: {
                            term: request.term,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            // Retirer l'indicateur de chargement
                            $("#gnonelid").removeClass("autocomplete-loading");
                            console.log("Suggestions de Gnonel ID reçues:", data);
                            
                            // Si la réponse est un tableau vide
                            if ($.isArray(data) && data.length === 0) {
                                response([{ label: 'Aucun Gnonel ID trouvé', value: '', disabled: true }]);
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
                            else {
                                response([{ label: 'Erreur de format', value: '', disabled: true }]);
                            }
                        },
                        error: function(xhr, status, error) {
                            // Retirer l'indicateur de chargement
                            $("#gnonelid").removeClass("autocomplete-loading");
                            console.error("Erreur lors de la récupération des Gnonel ID:", error);
                            response([{ label: 'Erreur de connexion', value: '', disabled: true }]);
                        }
                    });
                },
                select: function(event, ui) {
                    if (ui.item.disabled) {
                        event.preventDefault(); // Empêcher la sélection
                        return false;
                    }
                    return true;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                // Personnalisation du rendu des éléments de la liste
                if (item.disabled) {
                    return $("<li class='ui-state-disabled'>").append(item.label).appendTo(ul);
                } else {
                    return $("<li>").append("<div>" + item.label + "</div>").appendTo(ul);
                }
            };
            
            $("#non").on("click", function() {
                $('#myModal2').hide();
            })


            $("#visualiser").on("click", function() {
                $.ajax({
                    type: 'get',
                    url: "{{ url('view/all_references') }}/" + $("#titulaire option:selected")
                        .val(),
                    data: {
                        'gnonelid': $('#gnonelid').val()
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response.donnes);
                        // table.$("tr").remove();
                        var table = $('#datatable-buttons').DataTable({
                            "destroy": true,
                            "ordering": false
                        });
                        table.rows().remove().draw();
                        var data = response.donnes;
                        var tr = '';
                        if (data.length > 0) {
                            for (var i = 0; i < response.donnes.length; i++) {
                                var id = data[i].idreference;
                                var ref = data[i].reference_marche;
                                var numref = "";
                                if (data[i].numeroreference != null) {
                                    numref = data[i].numeroreference;
                                }
                                var lib = data[i].libelle_marche;
                                var anne = data[i].annee_execution;
                                var autorite = data[i].autorite_contractante;
                                var type = data[i].nom_categorie;
                                tr += '<tr style="cursor:pointer" onclick="detail(' + id +
                                    ')">';
                                tr += '<td style="cursor:pointer">' + numref + '</td>'
                                //tr +='<td style="cursor:pointer">'+ref+'</td>';
                                tr += '<td style="cursor:pointer;width:40%">' + (lib.length >
                                        50 ? lib.substring(0,
                                            50) +
                                        '...' : lib) +
                                    '</td>';
                                tr += '<td style="cursor:pointer;width:20%">' + (autorite
                                        .length > 30 ? autorite.substring(0, 30) + '...' :
                                        autorite) +
                                    '</td>';
                                tr += '<td style="cursor:pointer">' + anne + '</td>';
                                tr += '</tr>';
                            }
                            table.rows.add($(tr)).draw();
                        } else {

                        }

                    }
                });
            })

            detail = function(id) {
                window.location.href = "{{ url('view/detailsreference') }}/" + id;
            }

            $(document).on('change', '#pays', function() {
                var idpays = $(this).val();
                var div = $(this).parent();
                var op = " ";

                $.ajax({
                    type: 'get',
                    url: '{!! URL::to('ajaxgetautorite') !!}',
                    data: {
                        'id': idpays
                    },
                    dataType: 'json',
                    success: function(data) {
                        //console.log('success');

                        //console.log(data);

                        //op+='<option value="0" disabled="true" selected="true">--- Préselectionner le pays ---</option>';

                        for (var i = 0; i < data.length; i++) {
                            op += '<option value="' + data[i].id + '">' + data[i]
                                .raison_social + '</option>';
                        }

                        if (data.length == 0) {
                            op =
                                '<option value="" disabled="true" selected="true">Aucune information</option>';
                        }

                        document.getElementById("autorite").innerHTML = op;

                        //console.log(op);
                    },
                    error: function() {
                        console.log('error');
                    }
                });
            });


            $(document).on('change', '#payst', function() {
                var idpays = $(this).val();
                var div = $(this).parent();
                var opt = " ";

                $.ajax({

                    type: 'get',
                    url: '{!! URL::to('ajaxgetoperateurwithreference') !!}',
                    data: {
                        'id': idpays
                    },
                    dataType: 'json',
                    success: function(data) {
                        //console.log('success');

                        //console.log(data);

                        //op+='<option value="0" disabled="true" selected="true">--- Préselectionner le pays ---</option>';

                        for (var i = 0; i < data.length; i++) {
                            opt += '<option value="' + data[i].id + '">' + data[i]
                                .raison_social + ' (' + data[i].total_references +
                                ') </option>';
                        }



                        if (data.length == 0) {
                            opt =
                                '<option value="" disabled="true" selected="true">Aucune information</option>';
                        }

                        document.getElementById("titulaire").innerHTML = opt;

                        if (paysId > 0 && operateurId > 0) {
                            $('#titulaire').val(operateurId);
                            $('#visualiser').click();
                        }


                    },
                    error: function() {
                        console.log('error');
                    }


                });
            });


            if (paysId > 0) {
                $('#payst').change();

            }
        });
    </script>
@endsection
