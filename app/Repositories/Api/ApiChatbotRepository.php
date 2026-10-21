<?php

namespace App\Repositories\Api;

use App\Contracts\ChatbotRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiChatbotRepository implements ChatbotRepositoryInterface
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.backend_api.url', 'http://127.0.0.1:8001'), '/');
    }

    public function sendMessage(string $message, array $history = []): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/api/chatbot/chat", [
                'message' => $message,
                'history' => $history,
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [
                    'reply' => 'Maaf, respon chatbot tidak dikenali.',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('ApiChatbotRepository request failed: ' . $e->getMessage());
        }

        return [
            'reply' => 'Maaf, layanan chatbot asisten Senat sedang sibuk. Silakan coba kembali nanti.',
            'fallback' => true,
        ];
    }
}
