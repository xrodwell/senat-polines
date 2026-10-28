<?php

namespace App\Contracts;

interface ChatbotRepositoryInterface
{
    /**
     * Kirim pesan ke backend AI / Chatbot service.
     *
     * @param string $message
     * @param array $history
     * @return array
     */
    public function sendMessage(string $message, array $history = []): array;
}
