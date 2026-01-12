<?php

namespace App\Http\Controllers;

use App\Models\BibleExplanation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class SeoController extends Controller
{
    /**
     * Dados dos livros e capítulos (compartilhado entre métodos)
     */
    private function getLivrosData()
    {
        return [
            'livros' => [
                'antigo' => [
                    'genesis', 'exodo', 'levitico', 'numeros', 'deuteronomio', 'josue', 'juizes', 'rute',
                    '1samuel', '2samuel', '1reis', '2reis', '1cronicas', '2cronicas', 'esdras', 'neemias',
                    'ester', 'jo', 'salmos', 'proverbios', 'eclesiastes', 'canticos', 'isaias', 'jeremias',
                    'lamentacoes', 'ezequiel', 'daniel', 'oseias', 'joel', 'amos', 'obadias', 'jonas',
                    'miqueias', 'naum', 'habacuque', 'sofonias', 'ageu', 'zacarias', 'malaquias',
                ],
                'novo' => [
                    'mateus', 'marcos', 'lucas', 'joao', 'atos', 'romanos', '1corintios', '2corintios',
                    'galatas', 'efesios', 'filipenses', 'colossenses', '1tessalonicenses', '2tessalonicenses',
                    '1timoteo', '2timoteo', 'tito', 'filemom', 'hebreus', 'tiago', '1pedro', '2pedro',
                    '1joao', '2joao', '3joao', 'judas', 'apocalipse',
                ],
            ],
            'capitulos' => [
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
                'judas' => 1, 'apocalipse' => 22,
            ],
        ];
    }

    /**
     * Sitemap Index - Divide em múltiplos sitemaps
     */
    public function sitemapIndex()
    {
        $sitemaps = [
            [
                'loc' => url('/sitemap-antigo-testamento.xml'),
                'lastmod' => now()->toIso8601String(),
            ],
            [
                'loc' => url('/sitemap-novo-testamento.xml'),
                'lastmod' => now()->toIso8601String(),
            ],
            [
                'loc' => url('/sitemap-amp.xml'),
                'lastmod' => now()->toIso8601String(),
            ],
            [
                'loc' => url('/sitemap-principal.xml'),
                'lastmod' => now()->toIso8601String(),
            ],
        ];

        $xml = view('seo.sitemap-index', ['sitemaps' => $sitemaps])->render();

        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Sitemap Principal - Home, testamentos, livros
     */
    public function sitemapPrincipal()
    {
        $ttl = 60 * 60 * 24;
        $xml = Cache::remember('sitemap_principal_xml', $ttl, function () {
            $data = $this->getLivrosData();
            $livros = $data['livros'];
            $urls = [];

            // Página inicial
            $urls[] = [
                'loc' => url('/'),
                'priority' => '1.0',
                'changefreq' => 'daily',
                'lastmod' => now()->toIso8601String(),
            ];

            // Página da Bíblia
            $urls[] = [
                'loc' => url('/biblia'),
                'priority' => '0.9',
                'changefreq' => 'daily',
                'lastmod' => now()->toIso8601String(),
            ];

            // Testamentos e livros
            foreach ($livros as $testamento => $livrosTestamento) {
                $urls[] = [
                    'loc' => url("/biblia/{$testamento}"),
                    'priority' => '0.8',
                    'changefreq' => 'monthly',
                    'lastmod' => now()->toIso8601String(),
                ];

                foreach ($livrosTestamento as $livro) {
                    $urls[] = [
                        'loc' => url("/biblia/{$testamento}/".Str::slug($livro, '-')),
                        'priority' => '0.8',
                        'changefreq' => 'monthly',
                        'lastmod' => now()->toIso8601String(),
                    ];
                }
            }

            return view('seo.sitemap', ['urls' => $urls])->render();
        });

        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Sitemap Antigo Testamento - Capítulos e explicações
     */
    public function sitemapAntigoTestamento()
    {
        $ttl = 60 * 60 * 24;
        $xml = Cache::remember('sitemap_antigo_xml', $ttl, function () {
            return $this->generateTestamentSitemap('antigo');
        });

        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Sitemap Novo Testamento - Capítulos e explicações
     */
    public function sitemapNovoTestamento()
    {
        $ttl = 60 * 60 * 24;
        $xml = Cache::remember('sitemap_novo_xml', $ttl, function () {
            return $this->generateTestamentSitemap('novo');
        });

        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Gera sitemap para um testamento específico
     */
    private function generateTestamentSitemap($testamento)
    {
        $data = $this->getLivrosData();
        $livros = $data['livros'][$testamento];
        $capitulos = $data['capitulos'];
        $urls = [];

        foreach ($livros as $livro) {
            $totalCapitulos = $capitulos[$livro] ?? 30;

            for ($capitulo = 1; $capitulo <= $totalCapitulos; $capitulo++) {
                // URL de leitura do capítulo
                $urls[] = [
                    'loc' => url("/biblia/{$testamento}/".Str::slug($livro, '-')."/{$capitulo}"),
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                    'lastmod' => now()->toIso8601String(),
                ];

                // URL de explicação do capítulo completo
                $urls[] = [
                    'loc' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}"),
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                    'lastmod' => now()->toIso8601String(),
                ];

                // Versão SEO-friendly da URL de explicação
                $tituloSEO = $this->getTituloSEO($testamento, $livro, $capitulo);
                if ($tituloSEO) {
                    $urls[] = [
                        'loc' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}/{$tituloSEO}"),
                        'priority' => '0.9',
                        'changefreq' => 'weekly',
                        'lastmod' => now()->toIso8601String(),
                    ];
                }
            }
        }

        // Adiciona explicações de versículos específicos do banco de dados
        $explicacoes = BibleExplanation::where('verses', '!=', null)
            ->where('testament', $testamento)
            ->orderBy('book')
            ->orderBy('chapter')
            ->get();

        foreach ($explicacoes as $explicacao) {
            $livro = $explicacao->book;
            $capitulo = $explicacao->chapter;
            $versiculos = $explicacao->verses;

            // URL SEO-friendly
            $slug = Str::slug($versiculos);
            $urls[] = [
                'loc' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}/{$slug}-explicacao-biblica"),
                'priority' => '0.9',
                'changefreq' => 'weekly',
                'lastmod' => now()->toIso8601String(),
            ];
        }

        return view('seo.sitemap', ['urls' => $urls])->render();
    }

    /**
     * Sitemap AMP - Todas as páginas AMP
     */
    public function sitemapAmp()
    {
        $ttl = 60 * 60 * 24;
        $xml = Cache::remember('sitemap_amp_xml', $ttl, function () {
            $data = $this->getLivrosData();
            $livros = $data['livros'];
            $capitulos = $data['capitulos'];
            $urls = [];

            foreach ($livros as $testamento => $livrosTestamento) {
                foreach ($livrosTestamento as $livro) {
                    $totalCapitulos = $capitulos[$livro] ?? 30;

                    for ($capitulo = 1; $capitulo <= $totalCapitulos; $capitulo++) {
                        // URL AMP para explicação de capítulo completo
                        $urls[] = [
                            'loc' => url("/amp/explicacao/{$testamento}/{$livro}/{$capitulo}"),
                            'priority' => '0.9',
                            'changefreq' => 'weekly',
                            'lastmod' => now()->toIso8601String(),
                        ];

                        // URL AMP para versão SEO-friendly
                        $tituloSEO = $this->getTituloSEO($testamento, $livro, $capitulo);
                        if ($tituloSEO) {
                            $urls[] = [
                                'loc' => url("/amp/explicacao/{$testamento}/{$livro}/{$capitulo}/{$tituloSEO}"),
                                'priority' => '0.9',
                                'changefreq' => 'weekly',
                                'lastmod' => now()->toIso8601String(),
                            ];
                        }
                    }
                }
            }

            // Adiciona URLs AMP de versículos específicos
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
                $slug = Str::slug($versiculos);

                $urls[] = [
                    'loc' => url("/amp/explicacao/{$testamento}/{$livro}/{$capitulo}/{$slug}-explicacao-biblica"),
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                    'lastmod' => now()->toIso8601String(),
                ];
            }

            return view('seo.sitemap', ['urls' => $urls])->render();
        });

        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Redireciona sitemap.xml antigo para o novo sitemap index
     */
    public function sitemap()
    {
        return $this->sitemapIndex();
    }

    /**
     * Gera arquivo robots.txt
     */
    public function robots()
    {
        $content = "User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml');

        return Response::make($content, 200, [
            'Content-Type' => 'text/plain',
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
