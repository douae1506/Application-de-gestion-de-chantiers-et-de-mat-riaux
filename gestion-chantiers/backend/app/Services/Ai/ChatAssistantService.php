<?php

namespace App\Services\Ai;


class ChatAssistantService
{
    public function __construct(
        private readonly GeminiClient $gemini,
        private readonly AiDataService $dataService,
    ) {
    }

    /**
     * @param string $message Question posée par l'utilisateur
     * @param array<int, array{role: string, content: string}> $history Historique affiché dans la page (rôles 'user'/'assistant')
     */
    public function ask(string $message, array $history = []): string
    {
        $snapshot = $this->dataService->buildSnapshot();

        $systemInstruction = <<<TXT
Tu es l'assistant IA intégré à une application de gestion de chantiers de
construction (chantiers, projets, phases, produits, dépôts de stock,
fournisseurs, mouvements d'entrée/sortie/transfert).

Règles :
- Réponds toujours en français, de façon claire, concise et professionnelle.
- Base-toi UNIQUEMENT sur les données JSON fournies ci-dessous : ce sont les
  données réelles de l'application au moment de la question.
- Si l'information demandée n'apparaît pas dans ces données, dis-le
  clairement plutôt que d'inventer une réponse.
- Pour les questions de comptage ou de classement, donne le chiffre exact
  présent dans les données.
- Reste bref : quelques phrases, ou une courte liste à puces si utile.

Données actuelles de l'application (JSON) :
TXT . "\n" . json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $contents = [];
        foreach ($history as $entry) {
            $contents[] = [
                'role' => ($entry['role'] ?? 'user') === 'assistant' ? 'model' : 'user',
                'text' => (string) ($entry['content'] ?? ''),
            ];
        }
        $contents[] = ['role' => 'user', 'text' => $message];

        return trim($this->gemini->generate($systemInstruction, $contents));
    }
}
