<?php

namespace App\Services;

class SlugService
{
    // Mapeamento de nomes de livros para slugs
    private static $livrosParaSlugs = [
        // Velho Testamento
        'Gênesis' => 'genesis',
        'Êxodo' => 'exodo',
        'Levítico' => 'levitico',
        'Números' => 'numeros',
        'Deuteronômio' => 'deuteronomio',
        'Josué' => 'josue',
        'Juízes' => 'juizes',
        'Rute' => 'rute',
        '1 Samuel' => '1-samuel',
        '2 Samuel' => '2-samuel',
        '1 Reis' => '1-reis',
        '2 Reis' => '2-reis',
        '1 Crônicas' => '1-cronicas',
        '2 Crônicas' => '2-cronicas',
        'Esdras' => 'esdras',
        'Neemias' => 'neemias',
        'Ester' => 'ester',
        'Jó' => 'jo',
        'Salmos' => 'salmos',
        'Provérbios' => 'proverbios',
        'Eclesiastes' => 'eclesiastes',
        'Cânticos' => 'canticos',
        'Isaías' => 'isaias',
        'Jeremias' => 'jeremias',
        'Lamentações' => 'lamentacoes',
        'Ezequiel' => 'ezequiel',
        'Daniel' => 'daniel',
        'Oséias' => 'oseias',
        'Joel' => 'joel',
        'Amós' => 'amos',
        'Obadias' => 'obadias',
        'Jonas' => 'jonas',
        'Miquéias' => 'miqueias',
        'Naum' => 'naum',
        'Habacuque' => 'habacuque',
        'Sofonias' => 'sofonias',
        'Ageu' => 'ageu',
        'Zacarias' => 'zacarias',
        'Malaquias' => 'malaquias',

        // Novo Testamento
        'Mateus' => 'mateus',
        'Marcos' => 'marcos',
        'Lucas' => 'lucas',
        'João' => 'joao',
        'Atos' => 'atos',
        'Romanos' => 'romanos',
        '1 Coríntios' => '1-corintios',
        '2 Coríntios' => '2-corintios',
        'Gálatas' => 'galatas',
        'Efésios' => 'efesios',
        'Filipenses' => 'filipenses',
        'Colossenses' => 'colossenses',
        '1 Tessalonicenses' => '1-tessalonicenses',
        '2 Tessalonicenses' => '2-tessalonicenses',
        '1 Timóteo' => '1-timoteo',
        '2 Timóteo' => '2-timoteo',
        'Tito' => 'tito',
        'Filemom' => 'filemom',
        'Hebreus' => 'hebreus',
        'Tiago' => 'tiago',
        '1 Pedro' => '1-pedro',
        '2 Pedro' => '2-pedro',
        '1 João' => '1-joao',
        '2 João' => '2-joao',
        '3 João' => '3-joao',
        'Judas' => 'judas',
        'Apocalipse' => 'apocalipse',
    ];

    // Mapeamento inverso de slugs para nomes de livros
    private static $slugsParaLivros = [];

    /**
     * Inicializa o mapeamento inverso
     */
    public static function init()
    {
        if (empty(self::$slugsParaLivros)) {
            foreach (self::$livrosParaSlugs as $livro => $slug) {
                self::$slugsParaLivros[$slug] = $livro;
            }
        }
    }

    /**
     * Converte um nome de livro para slug
     */
    public static function livroParaSlug(string $livro): string
    {
        return self::$livrosParaSlugs[$livro] ?? self::gerarSlug($livro);
    }

    /**
     * Converte um slug para nome de livro
     */
    public static function slugParaLivro(string $slug): string
    {
        self::init();

        return self::$slugsParaLivros[$slug] ?? self::formatarSlug($slug);
    }

    /**
     * Gera um slug a partir de um texto
     * Usado como fallback caso o livro não esteja no mapeamento
     */
    private static function gerarSlug(string $texto): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);

        return trim($slug, '-');
    }

    /**
     * Formata um slug para exibição
     * Usado como fallback caso o slug não esteja no mapeamento inverso
     */
    private static function formatarSlug(string $slug): string
    {
        $texto = str_replace('-', ' ', $slug);

        return ucwords($texto);
    }
}
