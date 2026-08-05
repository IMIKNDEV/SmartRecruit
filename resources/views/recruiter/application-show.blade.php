@extends('layouts.app')

@section('title', 'Détail de la candidature')

@section('body_attrs')
 data-page="recruiterApplicationShow"
 data-application-id="{{ $applicationId ?? 0 }}"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1 id="appName">Candidature</h1>
        <p class="page-sub" id="appJob">—</p>
    </div>
    <a class="btn btn-ghost" href="#" id="appBackLink"><x-icon name="arrow-left" :size="16" /> Retour</a>
</div>

<div id="appDetail" class="detail-grid" style="align-items:start">
    <div class="detail-main" style="display:grid;gap:16px">
        <div class="card card-pad empty" id="appLoading">Chargement…</div>
    </div>
</div>
@endsection
