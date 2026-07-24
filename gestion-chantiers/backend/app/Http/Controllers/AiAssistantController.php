<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Services\Ai\ChantierResumeService;
use App\Services\Ai\ChatAssistantService;
use App\Services\Ai\StockAnalyseService;
use Illuminate\Http\Request;
use RuntimeException;

class AiAssistantController extends Controller
{
    // Fonctionnalité 
    public function chat(Request $request, ChatAssistantService $assistant)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['sometimes', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:2000'],
        ]);

        try {
            $reply = $assistant->ask($validated['message'], $validated['history'] ?? []);

            return response()->json(['reply' => $reply]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }



}
