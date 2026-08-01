<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobOfferRequest;
use App\Http\Requests\UpdateJobOfferRequest;
use App\Http\Resources\JobOfferResource;
use App\Models\JobOffer;
use Illuminate\Http\Request;

class JobOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = JobOffer::query()
            ->where('status', $request->input('status', 'active'));

        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->input('contract_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('tech_stack', 'like', "%{$search}%");
            });
        }

        $perPage = $request->integer('per_page', 15);

        return JobOfferResource::collection($query->paginate($perPage));
    }

    public function show(JobOffer $jobOffer)
    {
        if ($jobOffer->status !== 'active') {
            abort(404);
        }

        return new JobOfferResource($jobOffer->load('recruiter'));
    }

    public function store(StoreJobOfferRequest $request)
    {
        $jobOffer = JobOffer::create([
            ...$request->validated(),
            'recruiter_id' => $request->user()->id,
        ]);

        return (new JobOfferResource($jobOffer))->response()->setStatusCode(201);
    }

    public function update(UpdateJobOfferRequest $request, JobOffer $jobOffer)
    {
        $this->authorize('update', $jobOffer);

        $jobOffer->update($request->validated());

        return new JobOfferResource($jobOffer);
    }

    public function destroy(JobOffer $jobOffer)
    {
        $this->authorize('delete', $jobOffer);

        $jobOffer->delete();

        return response()->noContent();
    }
}
