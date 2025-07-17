<?php

namespace App\Services;

class SchemaService
{
    /**
     * Gera Schema.org para explicações bíblicas com marcação especializada
     */
    public function generateBibleSchema($testament, $book, $chapter, $verses = null, $explanation = null)
    {
        // Schema base para artigo
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => ucfirst($book) . ' ' . $chapter . ($verses ? ':'.$verses : '') . ' - Explicação Bíblica',
            'datePublished' => date('c'),
            'dateModified' => date('c'),
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Verso a verso - Bíblia Explicada',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => url('/images/logo.png'),
                    'width' => 600,
                    'height' => 60
                ]
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url()->current()
            ]
        ];
        
        // Adicionar schema para conteúdo religioso
        $schema['about'] = [
            '@type' => 'CreativeWork',
            'name' => 'Bíblia Sagrada',
            'alternateName' => 'Escrituras Sagradas',
        ];
        
        // Referência específica à passagem bíblica
        $schema['exampleOfWork'] = [
            '@type' => 'Book',
            'name' => 'Bíblia',
            'bookEdition' => $testament == 'antigo' ? 'Antigo Testamento' : 'Novo Testamento',
            'bookFormat' => 'Texto religioso',
            'inLanguage' => 'pt-BR',
            'author' => [
                '@type' => 'Person',
                'name' => $this->getBookAuthor($book)
            ],
            'hasPart' => [
                '@type' => 'Chapter',
                'name' => ucfirst($book) . ' ' . $chapter,
                'position' => (int) $chapter
            ]
        ];
        
        // Schema para FAQ se tiver explicação
        if ($explanation && $verses) {
            $schema['@type'] = ['Article', 'FAQPage'];
            $schema['mainEntity'] = [];
            
            // Criar FAQ para cada versículo
            $versesList = explode(',', $verses);
            foreach ($versesList as $verse) {
                $schema['mainEntity'][] = [
                    '@type' => 'Question',
                    'name' => "O que significa {$book} {$chapter}:{$verse}?",
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $this->extractVerseExplanation($verse, $explanation)
                    ]
                ];
            }
        }
        
        // Adicionar breadcrumbs
        $schema['breadcrumb'] = $this->generateBreadcrumbSchema($testament, $book, $chapter, $verses);
        
        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Gera Schema.org para breadcrumbs
     */
    public function generateBreadcrumbSchema($testament, $book, $chapter, $verses = null)
    {
        $testimonyLabel = $testament == 'antigo' ? 'Antigo Testamento' : 'Novo Testamento';
        $bookLabel = ucfirst($book);
        
        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Bíblia',
                'item' => url('/biblia')
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $testimonyLabel,
                'item' => url("/biblia/{$testament}")
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $bookLabel,
                'item' => url("/biblia/{$testament}/{$book}")
            ],
            [
                '@type' => 'ListItem',
                'position' => 4,
                'name' => "Capítulo {$chapter}",
                'item' => url("/biblia/{$testament}/{$book}/{$chapter}")
            ]
        ];
        
        if ($verses) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 5,
                'name' => "Versículos {$verses}",
                'item' => url("/explicacao/{$testament}/{$book}/{$chapter}?verses={$verses}")
            ];
        }
        
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];
    }
    
    /**
     * Extrai a explicação de um versículo específico
     */
    private function extractVerseExplanation($verse, $explanation)
    {
        // Simplificado - em uma implementação real, você extrairia a parte relevante
        return "Explicação teológica detalhada sobre {$verse}...";
    }
    
    /**
     * Retorna o autor tradicional do livro bíblico
     */
    private function getBookAuthor($book)
    {
        $authors = [
            'genesis' => 'Moisés',
            'exodo' => 'Moisés',
            'levitico' => 'Moisés',
            'numeros' => 'Moisés',
            'deuteronomio' => 'Moisés',
            'salmos' => 'Davi e outros',
            'proverbios' => 'Salomão',
            'isaias' => 'Isaías',
            'mateus' => 'Mateus',
            'marcos' => 'Marcos',
            'lucas' => 'Lucas',
            'joao' => 'João',
            'romanos' => 'Paulo',
            'apocalipse' => 'João'
        ];
        
        return $authors[$book] ?? 'Autor bíblico';
    }
}
