@extends('layouts.minimal')
@section('title', 'Visualisation du document: ' . $fileName)

@section('styles')
<style>
    .pdf-container {
        width: 100%;
        height: 100vh;
        position: relative;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }
    .pdf-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    .doc-title {
        background-color: rgba(255, 255, 255, 0.8);
        padding: 8px 15px;
        margin: 0;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .close-btn {
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 101;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0 m-0">
    <h4 class="doc-title">
        <i class="mdi mdi-file-document-outline me-1"></i>
        {{ $fileName }}
        <a href="javascript:window.close();" class="btn btn-sm btn-danger float-end close-btn">
            <i class="mdi mdi-close"></i> Fermer
        </a>
    </h4>
    
    <div class="pdf-container">
        <!-- Pour les PDFs -->
        @if(pathinfo($fileName, PATHINFO_EXTENSION) == 'pdf')
            <iframe src="{{ $fileUrl }}#toolbar=0" class="pdf-iframe" type="application/pdf"></iframe>
        @else
            <!-- Pour les images et autres types de documents -->
            <iframe src="{{ $fileUrl }}" class="pdf-iframe"></iframe>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Empêcher le clic droit pour éviter le téléchargement facile
        $(document).bind('contextmenu', function(e) {
            return false;
        });
        
        // Focus sur l'iframe pour une meilleure expérience utilisateur
        $('.pdf-iframe').focus();
    });
</script>
@endsection
