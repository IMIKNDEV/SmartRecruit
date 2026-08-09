<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Blade front-end for SmartRecruit
|--------------------------------------------------------------------------
| Server-rendered shells. Data is consumed from the REST API (/api/*) by
| vanilla JS (public/js/app.js) using a Bearer token stored in localStorage.
| There is NO session auth on web routes: access control is enforced in JS
| (redirect to /login when missing, role check via data-role).
| Missing endpoints (dashboard stats, shortlist, reply-templates) currently
| render bundled mock data until their controllers are implemented.
*/

// ---------- Legacy redirects (old FR slugs -> EN slugs, keep bookmarks alive) ----------
Route::redirect('/offres', '/jobs', 301);
Route::redirect('/offres/{id}', '/jobs/{id}', 301);
Route::redirect('/connexion', '/login', 301);
Route::redirect('/inscription', '/register', 301);
Route::redirect('/profil', '/profile', 301);
Route::redirect('/mes-candidatures', '/my-applications', 301);
Route::redirect('/postuler/{id}', '/apply/{id}', 301);
Route::redirect('/recruteur/offres', '/recruiter/jobs', 301);
Route::redirect('/recruteur/offres/creer', '/recruiter/jobs/create', 301);
Route::redirect('/recruteur/offres/{id}/modifier', '/recruiter/jobs/{id}/edit', 301);
Route::redirect('/recruteur/offres/{id}/candidatures', '/recruiter/jobs/{id}/applications', 301);
Route::redirect('/recruteur/offres/{id}/shortlist', '/recruiter/jobs/{id}/shortlist', 301);
Route::redirect('/recruteur/candidatures', '/recruiter/applications', 301);
Route::redirect('/recruteur/candidatures/{id}', '/recruiter/applications/{id}', 301);
Route::redirect('/recruteur/entretiens', '/recruiter/interviews', 301);
Route::redirect('/recruteur/modeles', '/recruiter/templates', 301);
Route::redirect('/recruteur/filtres', '/recruiter/filters', 301);

// ---------- Public ----------
Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/jobs', function () {
    return view('jobs.public-index');
})->name('jobs.index');

Route::get('/jobs/{id}', function ($id) {
    return view('jobs.public-show', ['jobId' => (int) $id]);
})->name('jobs.show');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// ---------- Authenticated (enforced in JS) ----------
Route::get('/profile', function () {
    return view('profile');
})->name('profile');

// Candidate
Route::get('/my-applications', function () {
    return view('candidate.applications');
})->name('candidate.applications');

Route::get('/apply/{id}', function ($id) {
    return view('candidate.apply', ['jobId' => (int) $id]);
})->name('candidate.apply');

// Recruiter
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::get('/recruiter/jobs', function () {
    return view('recruiter.jobs-index');
})->name('recruiter.jobs');

Route::get('/recruiter/jobs/create', function () {
    return view('recruiter.jobs-create');
})->name('recruiter.jobs.create');

Route::get('/recruiter/jobs/{id}/edit', function ($id) {
    return view('recruiter.jobs-edit', ['jobId' => (int) $id]);
})->name('recruiter.jobs.edit');

Route::get('/recruiter/jobs/{id}/applications', function ($id) {
    return view('recruiter.applications-index', ['jobId' => (int) $id]);
})->name('recruiter.job-applications');

Route::get('/recruiter/jobs/{id}/shortlist', function ($id) {
    return view('recruiter.shortlist', ['jobId' => (int) $id]);
})->name('recruiter.shortlist');

Route::get('/recruiter/applications', function () {
    return view('recruiter.applications-index', ['jobId' => null]);
})->name('recruiter.applications');

Route::get('/recruiter/applications/{id}', function ($id) {
    return view('recruiter.application-show', ['applicationId' => (int) $id]);
})->name('recruiter.application');

Route::get('/recruiter/interviews', function () {
    return view('recruiter.interviews');
})->name('recruiter.interviews');

Route::get('/recruiter/templates', function () {
    return view('recruiter.reply-templates');
})->name('recruiter.templates');

Route::get('/recruiter/filters', function () {
    return view('recruiter.saved-filters');
})->name('recruiter.filters');

Route::get('/recruiter/agent', function () {
    return view('recruiter.agent-chat');
})->name('recruiter.agent');
