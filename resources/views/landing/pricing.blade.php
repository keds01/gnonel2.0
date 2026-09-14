@extends('layouts.landing')
@section('title')
    Nos tarifs
@endsection
@section('content')
    <div class="pricing-plan-page pb120 mt-5 _relative">
        <div class="container">
            <div class="princing-plans">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 m-auto text-center">
                            <div class="heading2">
                                <h2 class="text-anime-style-3 text-black">Veuillez choisir une formule d’abonnement et
                                    profiter de
                                    nombreux avantages</h2>
                                <div class="space16"></div>
                                <p data-aos="fade-left" class="text-black" data-aos-duration="900">Pour la sécurité de vos
                                    paiements, les
                                    transactions sont soumises à la norme internationale PCI-DSS et le protocole de sécurité
                                    3D Secure
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="space60"></div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <div class="plan-toggle-wrap" data-aos="fade-up" data-aos-duration="700">
                                <div class="toggle-inner toggle-inner2">
                                    <input id="ce-toggle" checked type="checkbox">
                                    <span class="custom-toggle"></span>
                                    <span class="t-month text-black">Local</span>
                                    <span class="t-year text-black">International</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div id="yearly" style="display:none;">
                            <div class="row" style="display: flex; flex-wrap: wrap;">
                                {{-- Formule Basique - Local (première carte) --}}
                                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="700" style="display: flex;">
                                    <div class="pricing-box" style="width: 100%;">
                                        <div class="pricing-box-single" style="height: 100%; display: flex; flex-direction: column;">
                                            <div class="heading2" style="flex: 1;">
                                                <h5>Formule Basique</h5>
                                                <p>Accès gratuit sans engagement</p>
                                                <h3>0 FCFA/an</h3>
                                                <p>Tarif annuel</p>
                                            </div>
                                            <a href="{{ url('/register') }}"
                                                class="pricing-btn">S'ABONNER</a>
                                            <p class="h-pera" style="flex: 1; min-height: 60px;">Pour tout opérateur économique</p>
                                        </div>
                                    </div>
                                </div>
                                
                                @foreach ($abonnements as $abonnement)
                                    @if ($abonnement && $abonnement->is_international == 0)
                                        @php
                                            $isPackFixe = $abonnement->is_pack_fixe ?? 0;
                                            $prixAffiche = $isPackFixe ? ($abonnement->prix_gros ?? $abonnement->prix) : $abonnement->prix;
                                            $count = $abonnement->count ?? 1;
                                        @endphp
                                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="700" style="display: flex;">
                                            <div
                                                class="pricing-box {{ $loop->index == 1 || $loop->index == 2 ? 'active' : '' }}" style="width: 100%;">
                                                <div class="pricing-box-single" style="height: 100%; display: flex; flex-direction: column;">
                                                    <div class="heading2">
                                                        <h5>{{ $abonnement->libelle ?? 'Abonnement' }}</h5>
                                                        <p>{{ $abonnement->description ?? '' }}</p>
                                                        <h3>{{ number_format($prixAffiche, 0, ',', ' ') }} FCFA/an</h3>
                                                        <p>Tarif annuel @if($isPackFixe)({{ $count }} licences)@endif</p>
                                                    </div>
                                                    <a href="{{ route('creer_abonne', $abonnement->libelle) }}"
                                                        class="pricing-btn">S'ABONNER</a>
                                                    <p class="h-pera">{{ $abonnement->category->description ?? '' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div id="monthly">
                            <div class="row" style="display: flex; flex-wrap: wrap;">
                                {{-- Formule Basique - International (première carte) --}}
                                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="700" style="display: flex;">
                                    <div class="pricing-box" style="width: 100%;">
                                        <div class="pricing-box-single" style="height: 100%; display: flex; flex-direction: column;">
                                            <div class="heading2" style="flex: 1;">
                                                <h5>Formule Basique</h5>
                                                <p>Accès gratuit sans engagement</p>
                                                <h3>0 FCFA/an</h3>
                                                <p>Tarif annuel</p>
                                            </div>
                                            <a href="{{ url('/register') }}"
                                                class="pricing-btn">S'ABONNER</a>
                                            <p class="h-pera" style="flex: 1; min-height: 60px;">Pour tout opérateur économique</p>
                                        </div>
                                    </div>
                                </div>

                                @foreach ($abonnements as $abonnement)
                                    @if ($abonnement && $abonnement->is_international == 1)
                                        @php
                                            $isPackFixe = $abonnement->is_pack_fixe ?? 0;
                                            $prixAffiche = $isPackFixe ? ($abonnement->prix_gros ?? $abonnement->prix) : $abonnement->prix;
                                            $count = $abonnement->count ?? 1;
                                        @endphp
                                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="700" style="display: flex;">
                                            <div
                                                class="pricing-box {{ $loop->index == 0 || $loop->index == 1 ? 'active' : '' }}" style="width: 100%;">
                                                <div class="pricing-box-single" style="height: 100%; display: flex; flex-direction: column;">
                                                    <div class="heading2">
                                                        <h5>{{ $abonnement->libelle ?? 'Abonnement' }}</h5>
                                                        <p>{{ $abonnement->description ?? '' }}</p>
                                                        <h3>{{ number_format($prixAffiche, 0, ',', ' ') }} FCFA/an</h3>
                                                        <p>Tarif annuel @if($isPackFixe)({{ $count }} licences)@endif</p>
                                                    </div>
                                                    <a href="{{ route('creer_abonne', $abonnement->libelle) }}"
                                                        class="pricing-btn">S'ABONNER</a>
                                                    <p class="h-pera">{{ $abonnement->category->description ?? '' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
        <img class="shape-left" src="/frontoffice/img/shapes/home2-shape2.png" alt="">
    </div>
@endsection
