<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyRequest;
use App\Http\Requests\BatchUpdateStatusRequest;
use App\Http\Requests\UpdateApplicationNotesRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Http\Requests\UpdateApplicationTagsRequest;
use App\Http\Resources\ApplicationAnalysisResource;
use App\Http\Resources\ApplicationResource;
use App\Jobs\CalculateMatchingScoreJob;
use App\Models\Application;
use App\Models\JobOffer;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    protected array $validTransitions = [
        'received' => ['interview', 'refused'],
        'interview' => ['accepted', 'refused'],
        'accepted' => [],
        'refused' => [],
    ];

    public function apply(ApplyRequest $request, int $id)
    {
        $jobOffer = JobOffer::findOrFail($id);

        if ($jobOffer->status !== 'active') {
            abort(404);
        }

        $exists = Application::query()
            ->withTrashed()
            ->where('candidate_id', $request->user()->id)
            ->where('job_offer_id', $jobOffer->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already applied to this job.'], 422);
        }

        $cvPath = $request->file('cv')->store('cvs/'.$request->user()->id, 'public');

        $application = Application::create([
            'candidate_id' => $request->user()->id,
            'job_offer_id' => $jobOffer->id,
            'cv_path' => $cvPath,
            'cover_letter' => $request->input('cover_letter'),
            'status' => 'received',
        ]);

        CalculateMatchingScoreJob::dispatch($application);

        return (new ApplicationResource($application))->response()->setStatusCode(201);
    }

    public function byJob(Request $request, int $id)
    {
        $jobOffer = JobOffer::findOrFail($id);

        if ($jobOffer->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $applications = $jobOffer->applications()
            ->with(['candidate', 'analysis'])
            ->leftJoin('application_analysis', 'application_analysis.application_id', '=', 'applications.id')
            ->select('applications.*')
            ->orderByDesc('application_analysis.matching_score')
            ->paginate($request->integer('per_page', 15));

        return ApplicationResource::collection($applications);
    }

    public function myApplications(Request $request)
    {
        $applications = $request->user()->applications()
            ->with(['jobOffer', 'analysis', 'interviews'])
            ->paginate($request->integer('per_page', 15));

        return ApplicationResource::collection($applications);
    }

    /**
     * Recent applications across all of the recruiter's own job offers.
     * Latest first; deleted (soft-deleted) applications are excluded by the
     * model's global scope — they only live on in dashboard analytics.
     */
    public function recent(Request $request)
    {
        $applications = Application::with(['candidate', 'jobOffer', 'analysis', 'interviews'])
            ->whereHas('jobOffer', fn ($q) => $q->where('recruiter_id', $request->user()->id))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 50));

        return ApplicationResource::collection($applications);
    }

    /**
     * Soft-deleted applications across the recruiter's own job offers,
     * newest deletion first. Only applications whose offer is still visible
     * are listed (matching the `recent` endpoint).
     */
    public function trashed(Request $request)
    {
        $applications = Application::onlyTrashed()
            ->with(['candidate', 'jobOffer', 'analysis'])
            ->whereHas('jobOffer', fn ($q) => $q->where('recruiter_id', $request->user()->id))
            ->orderByDesc('deleted_at')
            ->paginate($request->integer('per_page', 50));

        return ApplicationResource::collection($applications);
    }

    /**
     * Restore a soft-deleted application (recruiter, own job offer only).
     * The application becomes visible again in lists and pipelines.
     */
    public function restore(Request $request, int $id)
    {
        $application = Application::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $application);

        $application->restore();

        return new ApplicationResource($application->load(['candidate', 'jobOffer', 'analysis']));
    }

    /**
     * Soft-delete an application (recruiter, own job offer only). The row is
     * kept for the analytical dashboard; only the application lists/pipelines
     * stop showing it.
     */
    public function destroy(Request $request, int $id)
    {
        $application = Application::findOrFail($id);

        $this->authorize('delete', $application);

        $application->delete();

        return response()->noContent();
    }

    public function show(Request $request, int $id)
    {
        $application = Application::with(['candidate.badges', 'jobOffer', 'analysis', 'interviews'])->findOrFail($id);

        $this->authorize('view', $application);

        return new ApplicationResource($application);
    }

    /**
     * Re-run the AI (Groq) compatibility analysis on demand (recruiter, own
     * job offer only). The calculation is dispatched to the queue so the
     * HTTP request returns immediately; the front-end polls
     * GET /applications/{id}/analysis until the score is stored.
     */
    public function analyze(Request $request, int $id)
    {
        $application = Application::findOrFail($id);

        $this->authorize('view', $application);

        CalculateMatchingScoreJob::dispatch($application);

        return response()->json(['status' => 'processing'], 202);
    }

    /**
     * Lightweight polling endpoint: returns the stored analysis row, or
     * data: null while the queued CalculateMatchingScoreJob is still
     * computing (recruiter, own job offer only).
     */
    public function analysis(Request $request, int $id)
    {
        $application = Application::with('analysis')->findOrFail($id);

        $this->authorize('view', $application);

        return response()->json([
            'data' => $application->analysis ? new ApplicationAnalysisResource($application->analysis) : null,
        ]);
    }

    /**
     * Stream the candidate's CV (recruiter, own job offer only). Inline
     * disposition — the front-end fetches it as a blob to view/download.
     */
    public function cv(Request $request, int $id)
    {
        $application = Application::findOrFail($id);

        $this->authorize('view', $application);

        $disk = Storage::disk('public');

        if (! $application->cv_path || ! $disk->exists($application->cv_path)) {
            abort(404);
        }

        return $disk->response($application->cv_path, basename($application->cv_path));
    }

    public function updateStatus(UpdateApplicationStatusRequest $request, int $id)
    {
        $application = Application::findOrFail($id);

        $this->authorize('updateStatus', $application);

        $target = $request->input('status');

        if (! in_array($target, $this->validTransitions[$application->status], true)) {
            return response()->json([
                'message' => "Cannot transition from {$application->status} to {$target}",
            ], 422);
        }

        $application->update(['status' => $target]);

        if ($target === 'accepted') {
            (new BadgeService)->checkInterviewBadge($application);
        }

        return new ApplicationResource($application->load('jobOffer'));
    }

    public function batchUpdateStatus(BatchUpdateStatusRequest $request)
    {
        $applications = Application::with('jobOffer')->whereIn('id', $request->input('ids'))->get();

        foreach ($applications as $application) {
            if ($application->jobOffer->recruiter_id !== $request->user()->id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        $target = $request->input('status');
        $updated = [];
        $skipped = [];

        foreach ($applications as $application) {
            if (! in_array($target, $this->validTransitions[$application->status], true)) {
                $skipped[] = $application->id;

                continue;
            }

            $application->update(['status' => $target]);
            $updated[] = $application->id;
        }

        return response()->json([
            'data' => ApplicationResource::collection($applications->fresh('jobOffer')),
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }

    public function updateNotes(UpdateApplicationNotesRequest $request, int $id)
    {
        $application = Application::findOrFail($id);

        $this->authorize('addNotes', $application);

        $application->update($request->validated());

        return new ApplicationResource($application->load('jobOffer'));
    }

    public function updateTags(UpdateApplicationTagsRequest $request, int $id)
    {
        $application = Application::findOrFail($id);

        $this->authorize('updateTags', $application);

        $application->update(['tags' => $request->input('tags', [])]);

        return new ApplicationResource($application->load('jobOffer'));
    }
}
