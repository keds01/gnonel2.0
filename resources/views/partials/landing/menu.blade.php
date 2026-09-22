<div class="header-elements">
    <div class="site-logo">
        <a href="{{ route('index') }}">
            <img src="/frontoffice/images/logo.png" alt="">
        </a>
    </div>


    <div class="main-menu-ex main-menu-ex1">
        <ul>
            <li>
                <a href="{{ route('index') }}">Accueil</a>
            </li>
            <li class="dropdown-menu-parrent"><a href="{{ route('nos_service') }}" class="main1">
                    Nos services <i class="fa-solid fa-angle-down"></i></a>
                <ul>
                    <li><a href="{{ route('service-basic') }}">Formule Basique</a></li>
                    <li><a href="{{ route('service-business') }}">Formules Business</a></li>
                    <li><a href="{{ route('service-pro') }}">Formules PRO</a></li>
                    <li><a href="{{ route('service-mix') }}">Formules MIX</a></li>
                    {{-- Lien archivé - Page Options temporairement inaccessible --}}
                    {{-- <li><a href="{{ route('service-options') }}">Options</a></li> --}}
                </ul>
            </li>
            <li>
                <a href="{{ route('pricing') }}">Tarifs</a>
            </li>
            <li>
                <a href="{{ route('gallery.public.index') }}">Galerie</a>
            </li>

        </ul>
    </div>


    <div class="header2-buttons">
        <div class="button">
            @php
                $isAuthenticated = false;
                try {
                    $isAuthenticated = auth()->check();
                } catch (\Exception $e) {
                    $isAuthenticated = false;
                }
            @endphp
            @if ($isAuthenticated)
                <a class="theme-btn12" href="{{ route('home') }}">Mon compte</a>
            @else
                <a class="theme-btn12" href="{{ route('login') }}">Se connecter</a>
            @endif
        </div>
    </div>

</div>
