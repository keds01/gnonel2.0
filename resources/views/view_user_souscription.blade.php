<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Gnonel | Abonnement</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('backoffice/images/favicon.ico') }}">

    <!-- App css -->
    <link href="{{ asset('backoffice/css/config/default/bootstrap_.min.css') }}" rel="stylesheet" type="text/css"
        id="bs-default-stylesheet" />
    <link href="{{ asset('backoffice/css/config/default/app_.min.css') }}" rel="stylesheet" type="text/css"
        id="app-default-stylesheet" />

    <link href="{{ asset('backoffice/css/config/default/bootstrap-dark.min.css') }}" rel="stylesheet" type="text/css"
        id="bs-dark-stylesheet" />
    <link href="{{ asset('backoffice/css/config/default/app-dark.min.css') }}" rel="stylesheet" type="text/css"
        id="app-dark-stylesheet" />

    <link href="{{ asset('backoffice/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backoffice/libs/selectize/css/selectize.bootstrap3.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- icons -->
    <link href="{{ asset('backoffice/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

</head>

<body class="loading authentication-bg authentication-bg-pattern">

    <?php
    $tva = \Illuminate\Support\Facades\DB::table('configurations')->first()->tva / 100;
    
    $priht = $souscriptions->montant_finale_apaye / (1 + $tva);
    $prixtva = $priht * $tva;
    
    ?>
    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
        <h5 class="display-5 text-white">PAIEMENT GNONEL</h5>
        <p class="text-white-50">Valider votre souscription avec un paiement sans contact</p>
    </div>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-xl-10">
                <div class="card rounded-3 text-black">
                    <div class="row g-0">
                        <div class="col-lg-7">
                            <div class="card-body p-md-5">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h5>REF000{{ $souscriptions->idsouscription }}#</h5>
                                    </div>
                                    <br><br>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td class="p-1">Souscription</td>
                                                <td class="p-1"><b>{{ $souscriptions->libelle }}</b></td>
                                            </tr>
                                            <tr>
                                                <td class="p-1">Nombre d'abonnements</td>
                                                <td class="p-1"><b><?= $souscriptions->count ?> </b></td>
                                            </tr>
                                            <tr>
                                                <td class="p-1">
                                       @php
    // Prix initial = montant_finale_apaye + discount_pack + discount_recom
    $prixInitial = $souscriptions->montant_finale_apaye + ($souscriptions->discount_pack ?? 0) + ($souscriptions->discount_recom ?? 0);
@endphp
<tr>
    <td class="p-1">Prix total</td>
    <td class="p-1"><b>{{ number_format($prixInitial, 0, '.', ' ') }} FCFA</b></td>
</tr>
@if ($souscriptions->discount_recom != null && $souscriptions->discount_recom > 0)
<tr>
    <td class="p-1">Réduction/recommandation</td>
    <td class="p-1" style="color:#0c43cc">- {{ number_format($souscriptions->discount_recom, 0, '.', ' ') }} FCFA</td>
</tr>
@endif
@if ($souscriptions->discount_pack != null && $souscriptions->discount_pack > 0)
<tr>
    <td class="p-1">Réduction/pack</td>
    <td class="p-1" style="color:#0c43cc">- {{ number_format($souscriptions->discount_pack, 0, '.', ' ') }} FCFA</td>
</tr>
@endif
<tr>
    <td class="p-1"><b>Prix à payer</b></td>
    <td class="p-1"><b style="font-size:1.1em">{{ number_format($souscriptions->montant_finale_apaye, 0, '.', ' ') }} FCFA</b></td>
</tr>
                                            </tr>
                                            <tr>
                                                <td class="p-1">Date</td>
                                                <td class="p-1">
                                                    <b>{{ \Carbon\Carbon::parse($souscriptions->created_at)->format('d/m/Y') }}</b>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="col-lg-12">
                                        <a href="{{ route('valider_souscription', $souscriptions->idsouscription) }}"
                                            class="btn btn-sm mt-3"
                                            style='background-color: #3232cc; border-bottom-color: #3232cc; color:#ffffff'>Procéder
                                            au paiement</a>
                                        <a class="btn btn-danger btn-sm mt-3"
                                            href="{{ route('annuler_abonnement', $souscriptions->idsouscription) }}">Annuler
                                            le paiement</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 d-flex align-items-center bg-secondary" style='color:#ffffff'>
                            <div class="text-white px-3 py-2">
                                <h4 class="mb-4 text-white">{{ $souscriptions->libelle }}</h4>
                                <?php $date = \Carbon\Carbon::now();
                                $date->addDays($souscriptions->nbjours);
                                
                                ?>
                                <p class="small mb-0">Souscription valable jusqu'au
                                    {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="{{ asset('backoffice/js/vendor.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('backoffice/js/app.min.js') }}"></script>

    <!-- Plugins js-->
    <script src="{{ asset('backoffice/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>

    <!-- Init js-->
    <script src="{{ asset('backoffice/js/pages/form-wizard.init.js') }}"></script>

    <script src="{{ asset('backoffice/libs/selectize/js/standalone/selectize.min.js') }}"></script>
    <script src="{{ asset('backoffice/libs/select2/js/select2.min.js') }}"></script>
</body>

</html>
