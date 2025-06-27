<?php

namespace App\Http\Controllers;

use App\Models\BibleExplanation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /**
     * Gera o sitemap.xml para o site
     */
    public function sitemap()
    {
        $ttl = 60 * 60 * 24; // 24h
        $xml = Cache::remember('sitemap_xml', $ttl, function () {
            $livros = [
            'antigo' => [
                'genesis', 'exodo', 'levitico', 'numeros', 'deuteronomio', 'josue', 'juizes', 'rute', 
                '1samuel', '2samuel', '1reis', '2reis', '1cronicas', '2cronicas', 'esdras', 'neemias',
                'ester', 'jo', 'salmos', 'proverbios', 'eclesiastes', 'canticos', 'isaias', 'jeremias', 
                'lamentacoes', 'ezequiel', 'daniel', 'oseias', 'joel', 'amos', 'obadias', 'jonas', 
                'miqueias', 'naum', 'habacuque', 'sofonias', 'ageu', 'zacarias', 'malaquias'
            ],
            'novo' => [
                'mateus', 'marcos', 'lucas', 'joao', 'atos', 'romanos', '1corintios', '2corintios',
                'galatas', 'efesios', 'filipenses', 'colossenses', '1tessalonicenses', '2tessalonicenses',
                '1timoteo', '2timoteo', 'tito', 'filemom', 'hebreus', 'tiago', '1pedro', '2pedro',
                '1joao', '2joao', '3joao', 'judas', 'apocalipse'
            ]
        ];
        
        // Número aproximado de capítulos para cada livro
        $capitulos = [
            'genesis' => 50, 'exodo' => 40, 'levitico' => 27, 'numeros' => 36, 'deuteronomio' => 34,
            'josue' => 24, 'juizes' => 21, 'rute' => 4, '1samuel' => 31, '2samuel' => 24,
            '1reis' => 22, '2reis' => 25, '1cronicas' => 29, '2cronicas' => 36, 'esdras' => 10,
            'neemias' => 13, 'ester' => 10, 'jo' => 42, 'salmos' => 150, 'proverbios' => 31,
            'eclesiastes' => 12, 'canticos' => 8, 'isaias' => 66, 'jeremias' => 52, 'lamentacoes' => 5,
            'ezequiel' => 48, 'daniel' => 12, 'oseias' => 14, 'joel' => 3, 'amos' => 9,
            'obadias' => 1, 'jonas' => 4, 'miqueias' => 7, 'naum' => 3, 'habacuque' => 3,
            'sofonias' => 3, 'ageu' => 2, 'zacarias' => 14, 'malaquias' => 4, 'mateus' => 28,
            'marcos' => 16, 'lucas' => 24, 'joao' => 21, 'atos' => 28, 'romanos' => 16,
            '1corintios' => 16, '2corintios' => 13, 'galatas' => 6, 'efesios' => 6, 'filipenses' => 4,
            'colossenses' => 4, '1tessalonicenses' => 5, '2tessalonicenses' => 3, '1timoteo' => 6,
            '2timoteo' => 4, 'tito' => 3, 'filemom' => 1, 'hebreus' => 13, 'tiago' => 5,
            '1pedro' => 5, '2pedro' => 3, '1joao' => 5, '2joao' => 1, '3joao' => 1,
            'judas' => 1, 'apocalipse' => 22
        ];
        
        $urls = [];
        
        // URLs para a página inicial e páginas principais
        $urls[] = [
            'loc' => url('/'),
            'priority' => '1.0',
            'changefreq' => 'daily'
        ];
        
        $urls[] = [
            'loc' => url('/biblia'),
            'priority' => '0.9',
            'changefreq' => 'daily'
        ];
        
        // URLs para todos os testamentos, livros e capítulos da Bíblia
        foreach ($livros as $testamento => $livrosTestamento) {
            $urls[] = [
                'loc' => url("/biblia/{$testamento}"),
                'priority' => '0.8',
                'changefreq' => 'monthly'
            ];
            
            foreach ($livrosTestamento as $livro) {
                $urls[] = [
                    'loc' => url("/biblia/{$testamento}/{$livro}"),
                    'priority' => '0.8',
                    'changefreq' => 'monthly'
                ];
                
                $totalCapitulos = $capitulos[$livro] ?? 30; // Valor padrão se não estiver na lista
                
                for ($capitulo = 1; $capitulo <= $totalCapitulos; $capitulo++) {
                    $urls[] = [
                        'loc' => url("/biblia/{$testamento}/{$livro}/{$capitulo}"),
                        'priority' => '0.7',
                        'changefreq' => 'monthly'
                    ];
                    
                    // URL para a explicação de capítulo completo
                    $urls[] = [
                        'loc' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}"),
                        'priority' => '0.8',
                        'changefreq' => 'monthly'
                    ];
                    
                    // Versão SEO-friendly da URL de explicação
                    $tituloSEO = $this->getTituloSEO($testamento, $livro, $capitulo);
                    if ($tituloSEO) {
                        $urls[] = [
                            'loc' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}/{$tituloSEO}"),
                            'priority' => '0.8',
                            'changefreq' => 'monthly'
                        ];
                    }
                }
            }
        }
        
        // Adiciona explicações de versículos específicos do banco de dados
        $explicacoes = BibleExplanation::where('verses', '!=', null)
            ->orderBy('testament')
            ->orderBy('book')
            ->orderBy('chapter')
            ->get();
            
        foreach ($explicacoes as $explicacao) {
            $testamento = $explicacao->testament;
            $livro = $explicacao->book;
            $capitulo = $explicacao->chapter;
            $versiculos = $explicacao->verses;
            
            // URL normal
            $urls[] = [
                'loc' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}?verses={$versiculos}"),
                'priority' => '0.8',
                'changefreq' => 'monthly'
            ];
            
            // URL SEO-friendly
            $slug = Str::slug($versiculos);
            $urls[] = [
                'loc' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}/{$slug}-explicacao-biblica"),
                'priority' => '0.8',
                'changefreq' => 'monthly'
            ];
        }
        
        $xml = view('seo.sitemap', ['urls' => $urls])->render();
        return $xml;
        });
        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml'
        ]);
    
    
    /**
     * Gera arquivo robots.txt
     */
    public function robots()
    {
        $content = "User-agent: *\nAllow: /\n\nSitemap: " . url('/sitemap.xml');
        return Response::make($content, 200, [
            'Content-Type' => 'text/plain'
        ]);
    }
    
    /**
     * Obter um título SEO-friendly para um capítulo
     */
    private function getTituloSEO($testamento, $livro, $capitulo)
    {
        $titulos = [
            'genesis-1' => 'criacao-do-mundo',
            'genesis-2' => 'criacao-do-homem-e-da-mulher',
            'genesis-3' => 'queda-do-homem',
            'mateus-5' => 'sermao-do-monte',
            'mateus-6' => 'pai-nosso-e-ensinamentos',
            'joao-3' => 'nicodemos-e-novo-nascimento',
            'apocalipse-21' => 'novo-ceu-e-nova-terra',
            // Adicione mais mapeamentos de títulos conforme necessário
        ];
        
        $chave = "{$livro}-{$capitulo}";
        return $titulos[$chave] ?? null;
    }
}
