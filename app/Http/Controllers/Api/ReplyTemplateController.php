<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ReplyTemplates;
use Illuminate\Http\Request;

class ReplyTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = collect(ReplyTemplates::all())
            ->map(fn (array $template, string $key) => [
                'key' => $key,
                'title' => $template['title'],
                'content' => $template['content'],
            ])
            ->values();

        return response()->json(['data' => $templates]);
    }

    public function update(Request $request, string $key)
    {
        $template = ReplyTemplates::get($key);

        if (! $template) {
            abort(404);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        ReplyTemplates::update($key, $validated['content']);

        $template = ReplyTemplates::get($key);

        return response()->json([
            'data' => [
                'key' => $key,
                'title' => $template['title'],
                'content' => $template['content'],
            ],
        ]);
    }
}
