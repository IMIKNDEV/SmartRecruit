<?php

use App\Http\Controllers\Api\AgentConversationController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InterviewController;
use App\Http\Controllers\Api\JobOfferController;
use App\Http\Controllers\Api\ReplyTemplateController;
use App\Http\Controllers\Api\SavedFilterController;
use App\Http\Controllers\Api\ShortlistController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/job-offers', [JobOfferController::class, 'index']);
Route::get('/job-offers/{jobOffer}', [JobOfferController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    Route::middleware('role:recruiter')->group(function () {
        Route::get('/recruiter/job-offers', [JobOfferController::class, 'mine']);
        Route::get('/recruiter/applications', [ApplicationController::class, 'recent']);
        Route::get('/recruiter/applications/trashed', [ApplicationController::class, 'trashed']);
        Route::apiResource('job-offers', JobOfferController::class)->except(['index', 'show']);
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::apiResource('saved-filters', SavedFilterController::class);
        Route::post('/applications/compare', [ApplicationController::class, 'compare']);
        Route::get('/job-offers/{id}/shortlist', [ShortlistController::class, 'index']);
        Route::get('/job-offers/{id}/shortlist/export', [ShortlistController::class, 'export']);
        Route::get('/applications/{id}/suggestions', [ApplicationController::class, 'suggestions']);
        Route::get('/reply-templates', [ReplyTemplateController::class, 'index']);
        Route::put('/reply-templates/{key}', [ReplyTemplateController::class, 'update']);
        Route::post('/applications/{id}/generate-questions', [AgentConversationController::class, 'generateQuestions']);
        Route::post('/applications/{id}/analyze', [ApplicationController::class, 'analyze']);
        Route::get('/applications/{id}/cv', [ApplicationController::class, 'cv']);
    });

    Route::middleware('role:candidate')->group(function () {
        Route::post('/job-offers/{id}/apply', [ApplicationController::class, 'apply']);
        Route::get('/applications', [ApplicationController::class, 'myApplications']);
    });

    Route::get('/applications/{id}', [ApplicationController::class, 'show']);
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);
    Route::post('/applications/{id}/restore', [ApplicationController::class, 'restore']);
    Route::put('/applications/{id}/status', [ApplicationController::class, 'updateStatus']);
    Route::put('/applications/status/batch', [ApplicationController::class, 'batchUpdateStatus']);
    Route::put('/applications/{id}/notes', [ApplicationController::class, 'updateNotes']);
    Route::put('/applications/{id}/tags', [ApplicationController::class, 'updateTags']);
    Route::get('/job-offers/{id}/applications', [ApplicationController::class, 'byJob']);
    Route::post('/applications/{id}/interviews', [InterviewController::class, 'store']);
    Route::put('/interviews/{id}/complete', [InterviewController::class, 'complete']);
    Route::put('/interviews/{id}/cancel', [InterviewController::class, 'cancel']);
    Route::get('/applications/{id}/interviews', [InterviewController::class, 'index']);
    Route::get('/agent-conversations', [AgentConversationController::class, 'index']);
    Route::post('/agent-conversations', [AgentConversationController::class, 'store']);
    Route::get('/agent-conversations/{id}/messages', [AgentConversationController::class, 'messages']);
    Route::post('/agent-conversations/{id}/messages', [AgentConversationController::class, 'sendMessage']);
});
