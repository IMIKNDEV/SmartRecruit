@extends('layouts.app')

@section('title', 'Mes candidatures')

@section('body_attrs')
 data-page="candidateApplications"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Mes candidatures</h1>
        <p class="page-sub">Suivez vos candidatures et leurs statuts en temps réel.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('jobs.index') }}"><x-icon name="search" :size="16" /> Explorer les offres</a>
</div>

<div class="card">
    <div class="table-wrap" style="padding:22px">
        <div id="candAppsBox"><div class="empty">Chargement…</div></div>
    </div>
</div>
@endsection
