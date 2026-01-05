<?php

namespace App\Http\Controllers;

use App\Services\BibleExplanationServiceRefactored;
use App\Services\MetricsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamingExplanationController extends Controller
{
    private BibleExplanationServiceRefactored $explanationService;

    private MetricsService $metricsService;

    public function __construct(
        BibleExplanationServiceRefactored $explanationService,
        MetricsService $metricsService
    ) {
        $this->explanationService = $explanationService;
        $this->metricsService = $metricsService;
    }

    /**
     * Stream explanation generation with Server-Sent Events
     */
    public function stream(Request $request, string $testament, string $book, int $chapter)
    {
        $verses = $request->query('verses');
        $startTime = microtime(true);

        return new StreamedResponse(function () use ($testament, $book, $chapter, $verses, $startTime) {
            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable nginx buffering

            // Send initial status
            $this->sendEvent('status', [
                'message' => 'Iniciando geração...',
                'progress' => 0,
            ]);

            try {
                // Check cache first
                $this->sendEvent('status', [
                    'message' => 'Verificando cache...',
                    'progress' => 10,
                ]);

                $result = $this->explanationService->getExplanation($testament, $book, $chapter, $verses);

                if ($result['origin'] === 'cache' || $result['origin'] === 'db') {
                    // Send cached result immediately
                    $this->sendEvent('status', [
                        'message' => 'Carregado do cache',
                        'progress' => 100,
                    ]);

                    $this->sendEvent('complete', $result);
                } else {
                    // Simulate progress for API generation
                    $this->sendEvent('status', [
                        'message' => 'Gerando explicação com IA...',
                        'progress' => 30,
                    ]);

                    sleep(1); // Small delay to show progress

                    $this->sendEvent('status', [
                        'message' => 'Processando resposta...',
                        'progress' => 70,
                    ]);

                    $this->sendEvent('status', [
                        'message' => 'Finalizando...',
                        'progress' => 90,
                    ]);

                    $this->sendEvent('complete', $result);
                }

                // Record metrics
                $duration = (int) ((microtime(true) - $startTime) * 1000);
                $this->metricsService->recordExplanationGeneration(
                    $testament,
                    $book,
                    $chapter,
                    $verses,
                    $result['origin'],
                    $duration
                );

            } catch (\Exception $e) {
                $this->sendEvent('error', [
                    'message' => 'Erro ao gerar explicação: '.$e->getMessage(),
                ]);
            }

            // Close connection
            $this->sendEvent('close', []);

            if (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Send SSE event
     */
    private function sendEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: '.json_encode($data)."\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
