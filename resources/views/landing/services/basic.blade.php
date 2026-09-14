@extends('layouts.landing')
@section('title')
    Formule Basique
@endsection
@section('content')
    <div class="service-details-all sp">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    @include('partials.landing.services-sidebar')
                </div>

                <div class="col-lg-8">
                    <div class="service-details-area left-padding">
                        <article>
                            <div class="heading1">
                                <div class="image">
                                    <img class="rounded-2" src="/frontoffice/images/formules/business.jpg" alt="Formule Basique">
                                </div>
                                <div class="space30"></div>
                                <p>
                                    Pour tous les utilisateurs
                                </p>
                                <div class="space30"></div>
                                <h3>Avantages</h3>
                            </div>
                        </article>

                        <div class="space20"></div>

                        <div class="faq-all-area">
                            <div class="accordion accordion1 accordion-flush" id="accordionFlushExample">
                                <div class="accordion-item active">
                                    <br>
                                    <div id="flush-collapseOne" class="accordion-collapse collapse show"
                                        aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">
                                            Accès gratuit aux fonctionnalités essentielles pour tester la plateforme
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item active">
                                    <br>
                                    <div id="flush-collapseTwo" class="accordion-collapse collapse show"
                                        aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">Découvrir les fonctionnalités de base de Gnonel sans engagement ;</div>
                                    </div>
                                </div>
                                <div class="accordion-item active">
                                    <br>
                                    <div id="flush-collapseThree" class="accordion-collapse collapse show"
                                        aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">Accéder aux informations essentielles pour la passation des marchés ;</div>
                                    </div>
                                </div>
                                <div class="accordion-item active">
                                    <br>
                                    <div id="flush-collapseFour" class="accordion-collapse collapse show"
                                        aria-labelledby="flush-headingFour" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">Une première approche pratique et utile pour tous les acteurs de la commande publique, privée ou institutionnelle.</div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="space20"></div>

                        <article>
                            <div class="heading1">
                                <p>
                                    La formule basique offre un accès gratuit aux fonctionnalités essentielles de la plateforme Gnonel. Elle est conçue pour permettre à tous les acteurs de la commande publique, privée ou institutionnelle de découvrir et d'utiliser les outils de base sans aucun engagement financier.
                                </p>
                                <div class="space20"></div>
                                <p>
                                    Cette formule vous permet de vous familiariser avec la plateforme et de comprendre comment Gnonel peut faciliter vos démarches de passation de marchés, tout en ayant accès aux informations et fonctionnalités pratiques dont vous avez besoin au quotidien.
                                </p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
