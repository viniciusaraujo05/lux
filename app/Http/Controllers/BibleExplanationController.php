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
     * @param  string  $testament
     * @param  string  $book
     * @param  string  $chapter
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExplanation(Request $request, $testament, $book, $chapter)
    {
        $verses = $request->query('verses');
        $bookName = $this->slugToBookName($book);

        // Verificar cache primeiro para resposta rápida
        $cacheKey = "bible_explanation:{$testament}:{$bookName}:{$chapter}:".($verses ?? 'all');
        
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return response()->json(\Illuminate\Support\Facades\Cache::get($cacheKey));
        }

        // Verificar se existe no banco de dados
        $existing = \App\Models\BibleExplanation::select(['id', 'explanation_text', 'source', 'access_count'])
            ->where([
                'testament' => $testament,
                'book' => $bookName,
                'chapter' => (int) $chapter,
            ])
            ->when($verses === null, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('verses')->orWhere('verses', '');
                });
            }, function ($query) use ($verses) {
                $query->where('verses', $verses);
            })
            ->first();

        if ($existing) {
            $existing->increment('access_count');
            
            $decodedExplanation = json_decode($existing->explanation_text, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decodedExplanation = $existing->explanation_text;
            }

            $result = [
                'id' => $existing->id,
                'origin' => 'db_fast',
                'explanation' => $decodedExplanation,
                'source' => $existing->source,
            ];
            
            // Cache para próximas requisições
            \Illuminate\Support\Facades\Cache::put($cacheKey, $result, 60 * 60 * 24);
            
            return response()->json($result);
        }

        // Se não existe, usar o serviço normal (que pode ser demorado)
        $result = $this->explanationService->getExplanation(
            $testament,
            $bookName,
            (int) $chapter,
            $verses
        );

        return response()->json($result);
    }

    /**
     * Converte o slug do livro para o nome correto em português.
     */
    private function slugToBookName($slug)
    {
        $map = [
            'genesis' => 'Gênesis',
            'exodo' => 'Êxodo',
            'levitico' => 'Levítico',
            'numeros' => 'Números',
            'deuteronomio' => 'Deuteronômio',
            'josue' => 'Josué',
            'juizes' => 'Juízes',
            'rute' => 'Rute',
            '1-samuel' => '1 Samuel',
            '2-samuel' => '2 Samuel',
            '1-reis' => '1 Reis',
            '2-reis' => '2 Reis',
            '1-cronicas' => '1 Crônicas',
            '2-cronicas' => '2 Crônicas',
            'esdras' => 'Esdras',
            'neemias' => 'Neemias',
            'ester' => 'Ester',
            'jo' => 'Jó',
            'salmos' => 'Salmos',
            'proverbios' => 'Provérbios',
            'eclesiastes' => 'Eclesiastes',
            'cantico-dos-canticos' => 'Cântico dos Cânticos',
            'isaias' => 'Isaías',
            'jeremias' => 'Jeremias',
            'lamentacoes' => 'Lamentações',
            'ezequiel' => 'Ezequiel',
            'daniel' => 'Daniel',
            'oseias' => 'Oseias',
            'joel' => 'Joel',
            'amos' => 'Amós',
            'obadias' => 'Obadias',
            'jonas' => 'Jonas',
            'miqueias' => 'Miqueias',
            'naum' => 'Naum',
            'habacuque' => 'Habacuque',
            'sofonias' => 'Sofonias',
            'ageu' => 'Ageu',
            'zacarias' => 'Zacarias',
            'malaquias' => 'Malaquias',
            'mateus' => 'Mateus',
            'marcos' => 'Marcos',
            'lucas' => 'Lucas',
            'joao' => 'João',
            'atos' => 'Atos',
            'romanos' => 'Romanos',
            '1-corintios' => '1 Coríntios',
            '2-corintios' => '2 Coríntios',
            'galatas' => 'Gálatas',
            'efesios' => 'Efésios',
            'filipenses' => 'Filipenses',
            'colossenses' => 'Colossenses',
            '1-tessalonicenses' => '1 Tessalonicenses',
            '2-tessalonicenses' => '2 Tessalonicenses',
            '1-timoteo' => '1 Timóteo',
            '2-timoteo' => '2 Timóteo',
            'tito' => 'Tito',
            'filemom' => 'Filemom',
            'hebreus' => 'Hebreus',
            'tiago' => 'Tiago',
            '1-pedro' => '1 Pedro',
            '2-pedro' => '2 Pedro',
            '1-joao' => '1 João',
            '2-joao' => '2 João',
            '3-joao' => '3 João',
            'judas' => 'Judas',
            'apocalipse' => 'Apocalipse',
        ];
        $slug = strtolower($slug);

        return $map[$slug] ?? ucfirst($slug);
    }
}
