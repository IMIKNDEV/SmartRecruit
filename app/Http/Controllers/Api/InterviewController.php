<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteInterviewRequest;
use App\Http\Requests\StoreInterviewRequest;
use App\Http\Resources\InterviewResource;
use App\Models\Application;
use App\Models\Interview;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function store(StoreInterviewRequest $request, int $id)
    {
        $application = Application::findOrFail($id);

        if ($application->jobOffer->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($application->status === 'received') {
            $application->update(['status' => 'interview']);
        }

        $interview = $application->interviews()->create([
            'scheduled_at' => $request->input('scheduled_at'),
            'link' => $request->input('link'),
            'status' => 'scheduled',
        ]);

        return (new InterviewResource($interview))->response()->setStatusCode(201);
    }

    public function index(Request $request, int $id)
    {
        $application = Application::findOrFail($id);

        if ($application->jobOffer->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $interviews = $application->interviews()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->paginate($request->integer('per_page', 15));

        return InterviewResource::collection($interviews);
    }

    public function complete(CompleteInterviewRequest $request, int $id)
    {
        $interview = Interview::with('application.jobOffer')->findOrFail($id);

        if ($interview->application->jobOffer->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($interview->status !== 'scheduled') {
            return response()->json([
                'message' => "Cannot complete an interview with status {$interview->status}",
            ], 422);
        }

        $interview->update([
            'status' => 'completed',
            'score_technique' => $request->input('score_technique'),
            'score_communication' => $request->input('score_communication'),
            'score_motivation' => $request->input('score_motivation'),
        ]);

        return new InterviewResource($interview);
    }

    public function cancel(Request $request, int $id)
    {
        $interview = Interview::with('application.jobOffer')->findOrFail($id);

        if ($interview->application->jobOffer->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($interview->status !== 'scheduled') {
            return response()->json([
                'message' => "Cannot cancel an interview with status {$interview->status}",
            ], 422);
        }

        $interview->update(['status' => 'cancelled']);

        return new InterviewResource($interview);
    }
}
