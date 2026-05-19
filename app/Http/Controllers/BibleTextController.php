<?php

namespace App\Http\Controllers;

use App\Services\BibleTextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BibleTextController extends Controller
{
    public function __construct(private BibleTextService $bibleTextService) {}

    public function getChapterText(Request $request, string $version, string $testament, string $book, string $chapter): JsonResponse
    {
        try {
            $data = $this->bibleTextService->getChapter(
                $version,
                $book,
                (int) $chapter
            );

            return response()->json($data);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'error' => 'invalid_request',
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'error' => 'server_error',
                'message' => 'Não foi possível carregar o texto bíblico neste momento.',
            ], 500);
        }
    }
}
