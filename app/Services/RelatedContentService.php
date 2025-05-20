<?php

namespace App\Services;

use App\Models\BibleExplanation;
use Illuminate\Support\Facades\DB;

class RelatedContentService
{
    /**
     * Encontra conteúdo relacionado baseado no testamento, livro, capítulo e versículos
     *
     * @param string $testament
     * @param string $book
     * @param int $chapter
     * @param string|null $verses
     * @return array
     */
    public function getRelatedContent($testament, $book, $chapter, $verses = null)
    {
        $relatedLinks = [];
        
        // 1. Contexto teológico - Capítulos próximos do mesmo livro
        $this->addContextualChapters($relatedLinks, $testament, $book, $chapter);
        
        // 2. Referências cruzadas temáticas
        $this->addThematicLinks($relatedLinks, $testament, $book, $chapter, $verses);
        
        // 3. Livros relacionados (para estudo comparativo)
        $this->addRelatedBooks($relatedLinks, $testament, $book);
        
        // 4. Explicações populares (baseadas em estatísticas de acesso)
        $this->addPopularExplanations($relatedLinks);
        
        return $relatedLinks;
    }
    
    /**
     * Adiciona links para capítulos próximos do mesmo livro
     */
    private function addContextualChapters(&$relatedLinks, $testament, $book, $chapter)
    {
        $currentChapter = (int) $chapter;
        
        // Capítulo anterior
        if ($currentChapter > 1) {
            $prevChapter = $currentChapter - 1;
            $relatedLinks[] = [
                'url' => "/explicacao/{$testament}/{$book}/{$prevChapter}",
                'title' => ucfirst($book) . " {$prevChapter} - Capítulo anterior",
                'type' => 'chapter',
                'relation' => 'previous'
            ];
        }
        
        // Próximo capítulo (estimativa simplificada do número máximo de capítulos)
        $maxChapters = $this->getEstimatedMaxChapters($book);
        if ($currentChapter < $maxChapters) {
            $nextChapter = $currentChapter + 1;
            $relatedLinks[] = [
                'url' => "/explicacao/{$testament}/{$book}/{$nextChapter}",
                'title' => ucfirst($book) . " {$nextChapter} - Próximo capítulo",
                'type' => 'chapter',
                'relation' => 'next'
            ];
        }
    }
    
    /**
     * Adiciona links temáticos baseados em mapeamentos conhecidos de referências cruzadas
     */
    private function addThematicLinks(&$relatedLinks, $testament, $book, $chapter, $verses)
    {
        // Mapeamento simplificado de referências cruzadas conhecidas
        $crossReferences = $this->getCrossReferences($testament, $book, $chapter, $verses);
        
        foreach ($crossReferences as $reference) {
            $relatedLinks[] = [
                'url' => "/explicacao/{$reference['testament']}/{$reference['book']}/{$reference['chapter']}" . 
                        ($reference['verses'] ? "?verses={$reference['verses']}" : ""),
                'title' => ucfirst($reference['book']) . " {$reference['chapter']}" . 
                        ($reference['verses'] ? ":{$reference['verses']}" : "") . 
                        " - {$reference['description']}",
                'type' => 'thematic',
                'relation' => $reference['relation'] ?? 'related'
            ];
        }
    }
    
    /**
     * Adiciona livros relacionados para estudo comparativo
     */
    private function addRelatedBooks(&$relatedLinks, $testament, $book)
    {
        $relatedBooks = $this->getRelatedBooks($testament, $book);
        
        foreach ($relatedBooks as $relatedBook) {
            $relatedLinks[] = [
                'url' => "/biblia/{$testament}/{$relatedBook['book']}",
                'title' => ucfirst($relatedBook['book']) . " - {$relatedBook['description']}",
                'type' => 'book',
                'relation' => $relatedBook['relation'] ?? 'context'
            ];
        }
    }
    
    /**
     * Adiciona explicações populares baseadas em estatísticas de acesso
     */
    private function addPopularExplanations(&$relatedLinks)
    {
        // Idealmente, buscar do banco de dados as explicações mais acessadas
        // Por enquanto, usando exemplos estáticos
        $popularExplanations = [
            [
                'testament' => 'novo',
                'book' => 'joao',
                'chapter' => 3,
                'verses' => '16',
                'title' => 'João 3:16 - Porque Deus amou o mundo de tal maneira...'
            ],
            [
                'testament' => 'novo',
                'book' => 'mateus',
                'chapter' => 5,
                'verses' => null,
                'title' => 'Mateus 5 - O Sermão do Monte'
            ],
            [
                'testament' => 'antigo',
                'book' => 'salmos',
                'chapter' => 23,
                'verses' => null,
                'title' => 'Salmos 23 - O Senhor é meu pastor'
            ]
        ];
        
        foreach ($popularExplanations as $explanation) {
            $relatedLinks[] = [
                'url' => "/explicacao/{$explanation['testament']}/{$explanation['book']}/{$explanation['chapter']}" . 
                        ($explanation['verses'] ? "?verses={$explanation['verses']}" : ""),
                'title' => $explanation['title'],
                'type' => 'popular',
                'relation' => 'popular'
            ];
        }
    }
    
    /**
     * Retorna uma estimativa do número máximo de capítulos para um livro
     */
    private function getEstimatedMaxChapters($book)
    {
        $chaptersMap = [
            'genesis' => 50,
            'exodo' => 40,
            'levitico' => 27,
            'numeros' => 36,
            'deuteronomio' => 34,
            'josue' => 24,
            'juizes' => 21,
            'rute' => 4,
            '1samuel' => 31,
            '2samuel' => 24,
            '1reis' => 22,
            '2reis' => 25,
            '1cronicas' => 29,
            '2cronicas' => 36,
            'esdras' => 10,
            'neemias' => 13,
            'ester' => 10,
            'jo' => 42,
            'salmos' => 150,
            'proverbios' => 31,
            'eclesiastes' => 12,
            'canticos' => 8,
            'isaias' => 66,
            'jeremias' => 52,
            'lamentacoes' => 5,
            'ezequiel' => 48,
            'daniel' => 12,
            'oseias' => 14,
            'joel' => 3,
            'amos' => 9,
            'obadias' => 1,
            'jonas' => 4,
            'miqueias' => 7,
            'naum' => 3,
            'habacuque' => 3,
            'sofonias' => 3,
            'ageu' => 2,
            'zacarias' => 14,
            'malaquias' => 4,
            'mateus' => 28,
            'marcos' => 16,
            'lucas' => 24,
            'joao' => 21,
            'atos' => 28,
            'romanos' => 16,
            '1corintios' => 16,
            '2corintios' => 13,
            'galatas' => 6,
            'efesios' => 6,
            'filipenses' => 4,
            'colossenses' => 4,
            '1tessalonicenses' => 5,
            '2tessalonicenses' => 3,
            '1timoteo' => 6,
            '2timoteo' => 4,
            'tito' => 3,
            'filemom' => 1,
            'hebreus' => 13,
            'tiago' => 5,
            '1pedro' => 5,
            '2pedro' => 3,
            '1joao' => 5,
            '2joao' => 1,
            '3joao' => 1,
            'judas' => 1,
            'apocalipse' => 22
        ];
        
        return $chaptersMap[$book] ?? 30; // Valor padrão aproximado
    }
    
    /**
     * Retorna referências cruzadas conhecidas para a passagem atual
     */
    private function getCrossReferences($testament, $book, $chapter, $verses)
    {
        // Em uma implementação real, isso seria buscado de um banco de dados ou API
        // Aqui usamos alguns exemplos estáticos baseados em padrões comuns
        
        $key = "{$book}-{$chapter}" . ($verses ? "-{$verses}" : "");
        
        $knownReferences = [
            // Gênesis
            'genesis-1' => [
                ['testament' => 'novo', 'book' => 'joao', 'chapter' => 1, 'verses' => '1-3', 'description' => 'No princípio era o Verbo', 'relation' => 'theological'],
                ['testament' => 'antigo', 'book' => 'salmos', 'chapter' => 19, 'verses' => null, 'description' => 'Os céus declaram a glória de Deus', 'relation' => 'thematic']
            ],
            'genesis-3' => [
                ['testament' => 'novo', 'book' => 'romanos', 'chapter' => 5, 'verses' => '12-21', 'description' => 'Consequências do pecado de Adão', 'relation' => 'theological'],
                ['testament' => 'novo', 'book' => 'apocalipse', 'chapter' => 12, 'verses' => null, 'description' => 'A antiga serpente', 'relation' => 'symbolic']
            ],
            
            // Salmos
            'salmos-23' => [
                ['testament' => 'novo', 'book' => 'joao', 'chapter' => 10, 'verses' => '1-18', 'description' => 'Jesus como o Bom Pastor', 'relation' => 'fulfillment'],
                ['testament' => 'antigo', 'book' => 'ezequiel', 'chapter' => 34, 'verses' => null, 'description' => 'Deus como Pastor de Israel', 'relation' => 'thematic']
            ],
            
            // Isaías
            'isaias-53' => [
                ['testament' => 'novo', 'book' => 'mateus', 'chapter' => 27, 'verses' => null, 'description' => 'Crucificação de Jesus', 'relation' => 'fulfillment'],
                ['testament' => 'novo', 'book' => 'atos', 'chapter' => 8, 'verses' => '26-40', 'description' => 'O eunuco etíope lê Isaías', 'relation' => 'interpretation']
            ],
            
            // Novo Testamento
            'joao-3-16' => [
                ['testament' => 'novo', 'book' => 'romanos', 'chapter' => 5, 'verses' => '8', 'description' => 'O amor de Deus demonstrado', 'relation' => 'thematic'],
                ['testament' => 'novo', 'book' => '1joao', 'chapter' => 4, 'verses' => '9-10', 'description' => 'Deus enviou seu Filho', 'relation' => 'parallel']
            ],
            'mateus-5' => [
                ['testament' => 'novo', 'book' => 'lucas', 'chapter' => 6, 'verses' => '20-49', 'description' => 'Sermão da planície', 'relation' => 'parallel'],
                ['testament' => 'antigo', 'book' => 'salmos', 'chapter' => 1, 'verses' => null, 'description' => 'Os caminhos do justo e do ímpio', 'relation' => 'background']
            ]
        ];
        
        // Retorna referências para a passagem específica ou para o capítulo
        $chapterKey = "{$book}-{$chapter}";
        $verseKey = "{$book}-{$chapter}-{$verses}";
        
        $references = [];
        
        if (isset($knownReferences[$verseKey])) {
            $references = array_merge($references, $knownReferences[$verseKey]);
        }
        
        if (isset($knownReferences[$chapterKey])) {
            $references = array_merge($references, $knownReferences[$chapterKey]);
        }
        
        // Limitar a 5 referências para não sobrecarregar
        return array_slice($references, 0, 5);
    }
    
    /**
     * Retorna livros relacionados para estudo comparativo
     */
    private function getRelatedBooks($testament, $book)
    {
        // Mapeamento de livros relacionados para estudo comparativo
        $relatedBooksMap = [
            // Agrupamentos do Antigo Testamento
            'genesis' => [
                ['book' => 'exodo', 'description' => 'Continuação da história do povo de Israel', 'relation' => 'sequential'],
                ['book' => 'apocalipse', 'description' => 'Do início ao fim da história bíblica', 'relation' => 'theological']
            ],
            'exodo' => [
                ['book' => 'genesis', 'description' => 'Origens do povo de Israel', 'relation' => 'background'],
                ['book' => 'hebreus', 'description' => 'Interpretação do sistema sacrificial', 'relation' => 'interpretation']
            ],
            
            // Evangelhos
            'mateus' => [
                ['book' => 'marcos', 'description' => 'Versão mais concisa dos eventos', 'relation' => 'synoptic'],
                ['book' => 'lucas', 'description' => 'Narrativa detalhada com ênfase histórica', 'relation' => 'synoptic'],
                ['book' => 'isaias', 'description' => 'Profecias messiânicas cumpridas', 'relation' => 'fulfillment']
            ],
            'marcos' => [
                ['book' => 'mateus', 'description' => 'Narrativa com foco no ensino de Jesus', 'relation' => 'synoptic'],
                ['book' => 'lucas', 'description' => 'Narrativa detalhada com ênfase histórica', 'relation' => 'synoptic']
            ],
            'lucas' => [
                ['book' => 'mateus', 'description' => 'Narrativa com foco no ensino de Jesus', 'relation' => 'synoptic'],
                ['book' => 'marcos', 'description' => 'Versão mais concisa dos eventos', 'relation' => 'synoptic'],
                ['book' => 'atos', 'description' => 'Continuação da história da igreja primitiva', 'relation' => 'sequential']
            ],
            'joao' => [
                ['book' => '1joao', 'description' => 'Temas teológicos semelhantes', 'relation' => 'thematic'],
                ['book' => 'apocalipse', 'description' => 'Linguagem e simbolismo semelhantes', 'relation' => 'thematic']
            ],
            
            // Cartas paulinas
            'romanos' => [
                ['book' => 'galatas', 'description' => 'Temas da justificação pela fé', 'relation' => 'thematic'],
                ['book' => 'hebreus', 'description' => 'Interpretação teológica do Antigo Testamento', 'relation' => 'thematic']
            ]
        ];
        
        // Retornar livros relacionados ou um array vazio se não houver mapeamento
        return $relatedBooksMap[$book] ?? [];
    }
}
