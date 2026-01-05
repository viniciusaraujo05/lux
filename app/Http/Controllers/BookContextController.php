<?php

namespace App\Http\Controllers;

use App\Services\BookContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookContextController extends Controller
{
    protected BookContextService $bookContextService;

    public function __construct(BookContextService $bookContextService)
    {
        $this->bookContextService = $bookContextService;
    }

    /**
     * Get book context explanation
     */
    public function getBookContext(Request $request, string $testament, string $book): JsonResponse
    {
        try {
            $result = $this->bookContextService->getBookContext($testament, $book);

            return response()->json([
                'context' => $result['context'],
                'origin' => $result['origin'],
                'id' => $result['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting book context', [
                'testament' => $testament,
                'book' => $book,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to get book context',
                'message' => 'Erro interno do servidor. Tente novamente em alguns instantes.',
            ], 500);
        }
    }
}
