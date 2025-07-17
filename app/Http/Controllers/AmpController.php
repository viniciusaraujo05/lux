<?php

namespace App\Http\Controllers;

use App\Models\BibleExplanation;
use App\Services\BibleExplanationService;
use App\Services\RelatedContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AmpController extends Controller
{
    protected $explanationService;
    protected $relatedContentService;
    
    public function __construct(
        BibleExplanationService $explanationService,
        RelatedContentService $relatedContentService
    ) {
        $this->explanationService = $explanationService;
        $this->relatedContentService = $relatedContentService;
    }
    
    /**
     * Mostrar versão AMP da explicação bíblica
     */
    public function showExplanation($testament, $book, $chapter, Request $request)
    {
        $verses = $request->query('verses');
        $slug = $request->segment(5); // Captura o slug se estiver presente na URL
        
        // Se temos um slug, extrair os versículos dele
        if ($slug && !$verses) {
            preg_match('/^(\d+(?:-\d+)?)/', $slug, $versesMatch);
            if (!empty($versesMatch)) {
                $verses = $versesMatch[1];
            }
        }
        
        // Buscar a explicação
        $result = $this->explanationService->getExplanation($testament, $book, (int)$chapter, $verses);
        
        // Gerar metadados de SEO
        $title = ucfirst($book) . ' ' . $chapter;
        if ($verses) {
            $title .= ':' . $verses;
        }
        $title .= ' - Explicação Bíblica | Verso a verso';
        
        $description = 'Estudo detalhado de ' . ucfirst($book) . ' ' . $chapter;
        if ($verses) {
            $description .= ':' . $verses;
        }
        $description .= ' com contexto histórico, análise teológica e aplicações práticas. Entenda a Bíblia profundamente.';
        
        // URL canônica (versão não-AMP)
        $canonicalUrl = url("/explicacao/{$testament}/{$book}/{$chapter}");
        if ($verses) {
            if ($slug) {
                $canonicalUrl .= '/' . $verses . '-explicacao-biblica';
            } else {
                $canonicalUrl .= '?verses=' . $verses;
            }
        }
        
        // Obter links relacionados
        $relatedLinks = $this->relatedContentService->getRelatedContent($testament, $book, $chapter, $verses);
        
        // Versículos originais (simulados por enquanto)
        $verseTexts = null;
        if ($verses) {
            $versesList = explode(',', $verses);
            $verseTexts = '';
            
            foreach ($versesList as $verseNumber) {
                $verseTexts .= "Versículo {$verseNumber}: Texto do versículo {$verseNumber} de {$book} {$chapter}. ";
            }
        }
        
        return view('amp.explanation', [
            'testament' => $testament,
            'book' => ucfirst($book),
            'chapter' => $chapter,
            'verses' => $verses,
            'verseTexts' => $verseTexts,
            'explanation' => $result['explanation'],
            'title' => $title,
            'description' => $description,
            'canonicalUrl' => $canonicalUrl,
            'relatedLinks' => $relatedLinks
        ]);
    }
}
