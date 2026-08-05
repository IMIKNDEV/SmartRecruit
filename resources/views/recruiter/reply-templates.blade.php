@extends('layouts.app')

@section('title', 'Modèles de réponse')

@section('body_attrs')
 data-page="recruiterTemplates"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Modèles de réponse</h1>
        <p class="page-sub">Modèles de messages réutilisables lors des changements de statut.</p>
    </div>
</div>

<div class="card">
    <div style="padding:22px">
        <div id="templatesBox"><div class="empty">Chargement…</div></div>
    </div>
</div>
@endsection
