<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Blade front-end for SmartRecruit
|--------------------------------------------------------------------------
| Server-rendered shells. Data is consumed from the REST API (/api/*) by
| vanilla JS (public/js/app.js) using a Bearer token stored in localStorage.
| There is NO session auth on web routes: access control is enforced in JS
| (redirect to /connexion when missing, role check via data-role).
| Missing endpoints (dashboard stats, shortlist, reply-templates) currently
| render bundled mock data until their controllers are implemented.
*/

// ---------- Public ----------
Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/offres', function () {
    return view('jobs.public-index');
})->name('jobs.index');

Route::get('/offres/{id}', function ($id) {
    return view('jobs.public-show', ['jobId' => (int) $id]);
})->name('jobs.show');

Route::get('/connexion', function () {
    return view('auth.login');
})->name('login');

Route::get('/inscription', function () {
    return view('auth.register');
})->name('register');

// ---------- Authenticated (enforced in JS) ----------
Route::get('/profil', function () {
    return view('profile');
})->name('profile');

// Candidate
Route::get('/mes-candidatures', function () {
    return view('candidate.applications');
})->name('candidate.applications');

Route::get('/postuler/{id}', function ($id) {
    return view('candidate.apply', ['jobId' => (int) $id]);
})->name('candidate.apply');

// Recruiter
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::get('/recruteur/offres', function () {
    return view('recruiter.jobs-index');
})->name('recruiter.jobs');

Route::get('/recruteur/offres/creer', function () {
    return view('recruiter.jobs-create');
})->name('recruiter.jobs.create');

Route::get('/recruteur/offres/{id}/modifier', function ($id) {
    return view('recruiter.jobs-edit', ['jobId' => (int) $id]);
})->name('recruiter.jobs.edit');

Route::get('/recruteur/offres/{id}/candidatures', function ($id) {
    return view('recruiter.applications-index', ['jobId' => (int) $id]);
})->name('recruiter.job-applications');

Route::get('/recruteur/offres/{id}/shortlist', function ($id) {
    return view('recruiter.shortlist', ['jobId' => (int) $id]);
})->name('recruiter.shortlist');

Route::get('/recruteur/candidatures', function () {
    return view('recruiter.applications-index', ['jobId' => null]);
})->name('recruiter.applications');

Route::get('/recruteur/candidatures/{id}', function ($id) {
    return view('recruiter.application-show', ['applicationId' => (int) $id]);
})->name('recruiter.application');

Route::get('/recruteur/entretiens', function () {
    return view('recruiter.interviews');
})->name('recruiter.interviews');

Route::get('/recruteur/modeles', function () {
    return view('recruiter.reply-templates');
})->name('recruiter.templates');

Route::get('/recruteur/filtres', function () {
    return view('recruiter.saved-filters');
})->name('recruiter.filters');

Route::get('/recruteur/agent', function () {
    return view('recruiter.agent-chat');
})->name('recruiter.agent');
