<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyRequest;
use App\Http\Requests\BatchUpdateStatusRequest;
use App\Http\Requests\UpdateApplicationNotesRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Http\Requests\UpdateApplicationTagsRequest;
use App\Http\Resources\ApplicationResource;
use App\Jobs\CalculateMatchingScoreJob;
use App\Models\Application;
use App\Models\JobOffer;
use App\Services\BadgeService;
use Illuminate\Http\Request;

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
            ->with(['jobOffer', 'analysis'])
            ->paginate($request->integer('per_page', 15));

        return ApplicationResource::collection($applications);
    }

    public function show(Request $request, int $id)
    {
        $application = Application::with(['candidate', 'jobOffer', 'analysis'])->findOrFail($id);

        $this->authorize('view', $application);

        return new ApplicationResource($application);
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
