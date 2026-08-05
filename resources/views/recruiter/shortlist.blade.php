@extends('layouts.app')

@section('title', 'Shortlist de l\'offre')

@section('body_attrs')
 data-page="recruiterShortlist"
 data-job-id="{{ $jobId ?? 0 }}"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1 id="shortlistTitle">Shortlist</h1>
        <p class="page-sub" id="shortlistSub">Top 5 des candidats triés par score de compatibilité.</p>
    </div>
    <div style="display:flex;gap:10px">
        <a class="btn btn-ghost" href="#" id="shortlistBack"><x-icon name="arrow-left" :size="16" /> Retour</a>
        <button class="btn btn-primary" id="exportCsv" type="button"><x-icon name="download" :size="16" /> Export CSV</button>
        <button class="btn btn-ghost" id="exportPdf" type="button"><x-icon name="export" :size="16" /> Export PDF</button>
    </div>
</div>

<div class="card">
    <div class="table-wrap" style="padding:22px">
        <div id="shortlistBox"><div class="empty">Chargement…</div></div>
    </div>
</div>
@endsection
