<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendAgentMessageRequest;
use App\Http\Requests\StoreAgentConversationRequest;
use App\Models\AgentConversation;
use App\Models\Application;
use App\Services\QuestionGeneratorService;
use Illuminate\Http\Request;

class AgentConversationController extends Controller
{
    public function index(Request $request)
    {
        $conversations = AgentConversation::where('user_id', $request->user()->id)
            ->with('messages')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($conversations);
    }

    public function store(StoreAgentConversationRequest $request)
    {
        $conversation = AgentConversation::create([
            'user_id' => $request->user()->id,
            'context_type' => $request->input('context_type'),
            'context_id' => $request->input('context_id'),
            'status' => 'active',
        ]);

        return response()->json(['data' => $conversation], 201);
    }

    public function messages(Request $request, int $id)
    {
        $conversation = AgentConversation::findOrFail($id);

        if ($conversation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->paginate($request->integer('per_page', 50));

        return response()->json($messages);
    }

    public function sendMessage(SendAgentMessageRequest $request, int $id)
    {
        $conversation = AgentConversation::findOrFail($id);

        if ($conversation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $message = (new QuestionGeneratorService)->sendMessage($conversation, $request->input('content'));

        return response()->json(['data' => $message], 201);
    }

    public function generateQuestions(Request $request, int $applicationId)
    {
        $application = Application::with('jobOffer')->findOrFail($applicationId);

        if ($application->jobOffer->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $result = (new QuestionGeneratorService)->generate($application);

        return response()->json(['data' => $result], 200);
    }
}
