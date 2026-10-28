<?php

namespace App\Http\Controllers;

use App\Contracts\ChatbotRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected ChatbotRepositoryInterface $chatbot;

    public function __construct(ChatbotRepositoryInterface $chatbot)
    {
        $this->chatbot = $chatbot;
    }

    /**
     * Proxy endpoint frontend untuk komunikasi dengan Backend AI Senat.
     * Route: POST /api/chatbot/send
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $response = $this->chatbot->sendMessage(
            $validated['message'],
            $validated['history'] ?? []
        );

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
}
