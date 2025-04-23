<?php

namespace App\Http\Controllers;

use App\Services\BibleExplanationService;
use Illuminate\Http\Request;

class BibleExplanationController extends Controller
{
    protected $explanationService;
    
    public function __construct(BibleExplanationService $explanationService)
    {
        $this->explanationService = $explanationService;
    }
    
    /**
     * Get explanation for a biblical passage
     *
     * @param Request $request
     * @param string $testament
     * @param string $book
     * @param string $chapter
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExplanation(Request $request, $testament, $book, $chapter)
    {
        $verses = $request->query('verses');
        
        $result = $this->explanationService->getExplanation(
            $testament, 
            $book, 
            (int) $chapter, 
            $verses
        );
        
        return response()->json($result);
    }
}
