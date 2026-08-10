<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSavedFilterRequest;
use App\Http\Requests\UpdateSavedFilterRequest;
use App\Http\Resources\SavedFilterResource;
use App\Models\SavedFilter;
use Illuminate\Http\Request;

class SavedFilterController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->user()->savedFilters()
            ->paginate($request->integer('per_page', 15));

        return SavedFilterResource::collection($filters);
    }

    public function store(StoreSavedFilterRequest $request)
    {
        $filter = SavedFilter::create([
            ...$request->validated(),
            'recruiter_id' => $request->user()->id,
        ]);

        return (new SavedFilterResource($filter))->response()->setStatusCode(201);
    }

    public function show(Request $request, SavedFilter $savedFilter)
    {
        if ($savedFilter->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new SavedFilterResource($savedFilter);
    }

    public function update(UpdateSavedFilterRequest $request, SavedFilter $savedFilter)
    {
        if ($savedFilter->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $savedFilter->update($request->validated());

        return new SavedFilterResource($savedFilter);
    }

    public function destroy(Request $request, SavedFilter $savedFilter)
    {
        if ($savedFilter->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $savedFilter->delete();

        return response()->noContent();
    }
}
