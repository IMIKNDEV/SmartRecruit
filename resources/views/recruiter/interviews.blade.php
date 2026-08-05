@extends('layouts.app')

@section('title', 'Entretiens')

@section('body_attrs')
 data-page="recruiterInterviews"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Entretiens</h1>
        <p class="page-sub">Planifiez, évaluez et suivez les entretiens de vos candidats.</p>
    </div>
</div>

<div class="filters-bar" style="margin-bottom:14px;display:flex;gap:8px;flex-wrap:wrap" id="interviewFilters"></div>

<div class="card">
    <div class="table-wrap" style="padding:22px">
        <div id="interviewsBox"><div class="empty">Chargement…</div></div>
    </div>
</div>
@endsection
