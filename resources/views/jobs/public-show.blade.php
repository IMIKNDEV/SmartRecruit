@extends('layouts.public-jobs')

@section('title', 'Job offer')

@section('body_attrs')
 data-page="jobShow"
@endsection

@section('content')
<div class="mx-auto max-w-6xl px-6 py-14 sm:py-16">
    <div id="jobDetail" data-job-id="{{ $jobId }}">
        <div class="empty"><p>Loading job offer…</p></div>
    </div>
</div>
@endsection
