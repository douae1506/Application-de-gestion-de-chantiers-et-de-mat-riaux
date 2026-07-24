<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;


class GeminiClient
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    private ?string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash');
    }

    /**
     * Envoie une conversation à Gemini et retourne le texte généré.
     *
     * @param string $systemInstruction Instruction système décrivant le rôle de l'IA
     * @param array<int, array{role: string, text: string}> $contents Messages du tour de conversation ('user' ou 'model')
     * @param bool $jsonMode Si true, force Gemini à répondre avec un JSON strict
     */
    public function generate(string $systemInstruction, array $contents, bool $jsonMode = false): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException(
                "La clé API Gemini n'est pas configurée. Ajoutez GEMINI_API_KEY dans le fichier .env du backend."
            );
        }

        if (empty($contents)) {
            throw new RuntimeException('Aucun message à envoyer à Gemini.');
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]],
            ],
            'contents' => array_map(
                fn (array $m) => [
                    'role' => $m['role'],
                    'parts' => [['text' => $m['text']]],
                ],
                $contents
            ),
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 1024,
            ],
        ];

        if ($jsonMode) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $response = Http::timeout(30)
            ->asJson()
            ->post(self::BASE_URL . "/{$this->model}:generateContent?key={$this->apiKey}", $payload);

        if ($response->failed()) {
            $message = $response->json('error.message') ?? 'Erreur inconnue lors de l\'appel à Gemini.';
            Log::warning('Gemini API error', ['status' => $response->status(), 'message' => $message]);
            throw new RuntimeException("Le service IA est momentanément indisponible ({$message}).");
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if ($text === null || $text === '') {
            $finishReason = $response->json('candidates.0.finishReason');
            throw new RuntimeException(
                "Gemini n'a renvoyé aucune réponse exploitable" . ($finishReason ? " ({$finishReason})." : '.')
            );
        }

        return $text;
    }
}
