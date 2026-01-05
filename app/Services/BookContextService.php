<?php

namespace App\Services;

use App\Models\BibleExplanation;
use App\Services\Ai\AiClientFactory;
use App\Services\Ai\PromptBuilderService;
use Illuminate\Support\Facades\Log;

class BookContextService
{
    protected PromptBuilderService $promptBuilder;

    protected ExplanationValidationService $validator;

    public function __construct(
        PromptBuilderService $promptBuilder,
        ExplanationValidationService $validator
    ) {
        $this->promptBuilder = $promptBuilder;
        $this->validator = $validator;
    }

    /**
     * Get book context explanation
     */
    public function getBookContext(string $testament, string $book): array
    {
        // Verificar se já existe no banco de dados
        $existing = BibleExplanation::where('testament', $testament)
            ->where('book', $book)
            ->whereNull('chapter') // Contexto do livro não tem capítulo
            ->whereNull('verses')  // Nem versículos
            ->first();

        if ($existing) {
            return [
                'context' => $existing->explanation_text,
                'origin' => 'database',
                'id' => $existing->id,
            ];
        }

        // Gerar novo contexto usando IA
        $context = $this->generateBookContext($testament, $book);

        // Salvar no banco de dados
        $explanation = BibleExplanation::create([
            'testament' => $testament,
            'book' => $book,
            'chapter' => null,
            'verses' => null,
            'explanation_text' => $context,
            'source' => 'gpt-5', // Adicionando a fonte da explicação
        ]);

        return [
            'context' => $context,
            'origin' => 'ai_generated',
            'id' => $explanation->id,
        ];
    }

    /**
     * Generate book context using AI
     */
    protected function generateBookContext(string $testament, string $book): string
    {
        $client = AiClientFactory::make();
        $prompt = $this->promptBuilder->buildBookContextPrompt($testament, $book);
        $systemMessage = $this->promptBuilder->getSystemMessage();

        try {
            $messages = [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => $prompt],
            ];

            $response = $client->chat($messages, 4000);

            // Extract and validate JSON
            $jsonString = $this->validator->extractJsonFromMarkdown($response);
            $decoded = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Try to repair
                $repaired = $this->validator->repairJson($jsonString);
                if ($repaired !== null) {
                    $jsonString = $repaired;
                }
            }

            return $jsonString;
        } catch (\Exception $e) {
            Log::error('Error generating book context with AI', [
                'testament' => $testament,
                'book' => $book,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
