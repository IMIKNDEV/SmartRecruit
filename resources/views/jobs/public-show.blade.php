@extends('layouts.public-jobs')

@section('title', 'Offre d\'emploi')

@section('body_attrs')
 data-page="jobShow"
@endsection

@section('content')
<div class="container lp-jobs-wrap">
    <div id="jobDetail" data-job-id="{{ $jobId }}">
        <div class="empty"><p>Chargement de l'offre…</p></div>
    </div>
</div>
@endsection
