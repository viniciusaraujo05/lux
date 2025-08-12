<?php

namespace App\Http\Controllers;

use App\Services\SchemaService;
use Inertia\Inertia;

class ThematicStudyController extends Controller
{
    protected $schemaService;

    public function __construct(SchemaService $schemaService)
    {
        $this->schemaService = $schemaService;
    }

    /**
     * Exibe página de temas bíblicos
     */
    public function index()
    {
        $themes = $this->getThematicTopics();

        return Inertia::render('temas/index', [
            'themes' => $themes,
            'title' => 'Temas Bíblicos - Estudos Temáticos Aprofundados | Verso a verso',
            'description' => 'Explore os principais temas da Bíblia com explicações detalhadas, contexto histórico e aplicações práticas. Estudos bíblicos temáticos para aprofundar sua fé.',
            'keywords' => 'temas bíblicos, estudos temáticos, estudos bíblicos, teologia bíblica, doutrinas bíblicas',
        ]);
    }

    /**
     * Exibe estudo temático específico
     */
    public function show($slug)
    {
        $theme = $this->getThematicTopic($slug);

        if (! $theme) {
            abort(404);
        }

        $passages = $this->getThemePassages($theme['id']);

        // Gerar schema.org especializado para este tema
        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $theme['title'],
            'description' => $theme['description'],
            'author' => [
                '@type' => 'Organization',
                'name' => 'Verso a verso - Bíblia Explicada',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Verso a verso - Bíblia Explicada',
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url()->current(),
            ],
            'about' => [
                '@type' => 'Thing',
                'name' => $theme['title'],
            ],
            'keywords' => $theme['keywords'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return Inertia::render('temas/show', [
            'theme' => $theme,
            'passages' => $passages,
            'schemaJson' => $schemaJson,
            'title' => $theme['title'].' - Estudo Bíblico Temático | Verso a verso',
            'description' => $theme['description'],
            'keywords' => $theme['keywords'],
        ]);
    }

    /**
     * Retorna todos os temas bíblicos disponíveis
     */
    private function getThematicTopics()
    {
        // Em uma implementação real, estes viriam de um banco de dados
        return [
            [
                'id' => 'salvacao',
                'slug' => 'salvacao-pela-fe',
                'title' => 'Salvação pela Fé',
                'description' => 'Estudo aprofundado sobre o plano de salvação divino através da fé em Jesus Cristo, explicando conceitos como justificação, redenção, e vida eterna.',
                'image' => '/images/themes/salvation.jpg',
                'keywords' => 'salvação, fé, justificação, graça, redenção, vida eterna, Jesus Cristo',
                'category' => 'Doutrinas Fundamentais',
            ],
            [
                'id' => 'oracao',
                'slug' => 'poder-da-oracao',
                'title' => 'O Poder da Oração',
                'description' => 'Descubra como a oração transforma vidas, com exemplos bíblicos de grandes orações, técnicas para uma vida de oração eficaz e promessas divinas para quem ora.',
                'image' => '/images/themes/prayer.jpg',
                'keywords' => 'oração, intercessão, comunicação com Deus, promessas, exemplos de oração',
                'category' => 'Vida Cristã',
            ],
            [
                'id' => 'graca',
                'slug' => 'graca-divina',
                'title' => 'A Graça Divina',
                'description' => 'Compreenda o conceito bíblico da graça de Deus, o favor imerecido que transforma vidas, liberta do pecado e nos capacita para uma nova vida.',
                'image' => '/images/themes/grace.jpg',
                'keywords' => 'graça divina, favor, misericórdia, perdão, novo nascimento',
                'category' => 'Doutrinas Fundamentais',
            ],
            [
                'id' => 'segunda-vinda',
                'slug' => 'segunda-vinda-de-cristo',
                'title' => 'A Segunda Vinda de Cristo',
                'description' => 'Estudo detalhado sobre as profecias e promessas da volta de Jesus Cristo, os sinais dos tempos e a esperança cristã do encontro com o Salvador.',
                'image' => '/images/themes/second-coming.jpg',
                'keywords' => 'segunda vinda, volta de Jesus, arrebatamento, juízo final, profecias, apocalipse',
                'category' => 'Escatologia',
            ],
            [
                'id' => 'amor',
                'slug' => 'amor-de-deus',
                'title' => 'O Amor de Deus',
                'description' => 'Explore as diferentes facetas do amor divino demonstrado nas Escrituras, desde a criação até a cruz, e como este amor deve moldar nossas vidas.',
                'image' => '/images/themes/gods-love.jpg',
                'keywords' => 'amor de Deus, agape, compaixão, misericórdia, sacrifício',
                'category' => 'Atributos Divinos',
            ],
        ];
    }

    /**
     * Retorna detalhes de um tema específico
     */
    private function getThematicTopic($slug)
    {
        $topics = $this->getThematicTopics();

        foreach ($topics as $topic) {
            if ($topic['slug'] === $slug) {
                return $topic;
            }
        }

        return null;
    }

    /**
     * Retorna passagens bíblicas relacionadas a um tema
     */
    private function getThemePassages($themeId)
    {
        // Mapeamento de temas para passagens bíblicas relevantes
        $passageMap = [
            'salvacao' => [
                [
                    'testament' => 'novo',
                    'book' => 'joao',
                    'chapter' => 3,
                    'verses' => '16',
                    'text' => 'Porque Deus amou o mundo de tal maneira que deu o seu Filho unigênito, para que todo aquele que nele crê não pereça, mas tenha a vida eterna.',
                    'title' => 'João 3:16 - O amor de Deus e a salvação',
                ],
                [
                    'testament' => 'novo',
                    'book' => 'romanos',
                    'chapter' => 10,
                    'verses' => '9-10',
                    'text' => 'Se, com a tua boca, confessares Jesus como Senhor e, em teu coração, creres que Deus o ressuscitou dentre os mortos, serás salvo. Porque com o coração se crê para justiça e com a boca se confessa a respeito da salvação.',
                    'title' => 'Romanos 10:9-10 - Confissão e fé para salvação',
                ],
                [
                    'testament' => 'novo',
                    'book' => 'efesios',
                    'chapter' => 2,
                    'verses' => '8-9',
                    'text' => 'Porque pela graça sois salvos, mediante a fé; e isto não vem de vós, é dom de Deus; não de obras, para que ninguém se glorie.',
                    'title' => 'Efésios 2:8-9 - Salvação pela graça mediante a fé',
                ],
            ],
            'oracao' => [
                [
                    'testament' => 'novo',
                    'book' => 'mateus',
                    'chapter' => 6,
                    'verses' => '9-13',
                    'text' => 'Portanto, vós orareis assim: Pai nosso, que estás nos céus, santificado seja o teu nome; venha o teu reino; faça-se a tua vontade, assim na terra como no céu...',
                    'title' => 'Mateus 6:9-13 - A oração do Pai Nosso',
                ],
                [
                    'testament' => 'novo',
                    'book' => 'filipenses',
                    'chapter' => 4,
                    'verses' => '6-7',
                    'text' => 'Não andeis ansiosos por coisa alguma; antes, em tudo, sejam os vossos pedidos conhecidos diante de Deus pela oração e pela súplica, com ações de graças. E a paz de Deus, que excede todo o entendimento, guardará os vossos corações e as vossas mentes em Cristo Jesus.',
                    'title' => 'Filipenses 4:6-7 - Oração contra a ansiedade',
                ],
            ],
            'graca' => [
                [
                    'testament' => 'novo',
                    'book' => 'joao',
                    'chapter' => 1,
                    'verses' => '16-17',
                    'text' => 'Porque todos nós recebemos da sua plenitude e graça sobre graça. Porque a lei foi dada por meio de Moisés; a graça e a verdade vieram por meio de Jesus Cristo.',
                    'title' => 'João 1:16-17 - Graça sobre graça em Cristo',
                ],
            ],
            'segunda-vinda' => [
                [
                    'testament' => 'novo',
                    'book' => 'mateus',
                    'chapter' => 24,
                    'verses' => '30-31',
                    'text' => 'Então, aparecerá no céu o sinal do Filho do Homem; todos os povos da terra se lamentarão e verão o Filho do Homem vindo sobre as nuvens do céu, com poder e muita glória. E ele enviará os seus anjos, com grande clangor de trombeta, os quais reunirão os seus escolhidos, dos quatro ventos, de uma a outra extremidade dos céus.',
                    'title' => 'Mateus 24:30-31 - A vinda de Cristo nas nuvens',
                ],
                [
                    'testament' => 'novo',
                    'book' => '1tessalonicenses',
                    'chapter' => 4,
                    'verses' => '16-17',
                    'text' => 'Porquanto o Senhor mesmo, dada a sua palavra de ordem, ouvida a voz do arcanjo, e ressoada a trombeta de Deus, descerá dos céus, e os mortos em Cristo ressuscitarão primeiro; depois, nós, os vivos, os que ficarmos, seremos arrebatados juntamente com eles, entre nuvens, para o encontro do Senhor nos ares, e, assim, estaremos para sempre com o Senhor.',
                    'title' => '1 Tessalonicenses 4:16-17 - O arrebatamento da igreja',
                ],
            ],
            'amor' => [
                [
                    'testament' => 'novo',
                    'book' => '1corintios',
                    'chapter' => 13,
                    'verses' => '4-8',
                    'text' => 'O amor é paciente, é benigno; o amor não arde em ciúmes, não se ufana, não se ensoberbece, não se conduz inconvenientemente, não procura os seus interesses, não se exaspera, não se ressente do mal; não se alegra com a injustiça, mas regozija-se com a verdade; tudo sofre, tudo crê, tudo espera, tudo suporta. O amor jamais acaba...',
                    'title' => '1 Coríntios 13:4-8 - A definição do amor',
                ],
                [
                    'testament' => 'novo',
                    'book' => '1joao',
                    'chapter' => 4,
                    'verses' => '7-8',
                    'text' => 'Amados, amemo-nos uns aos outros, porque o amor procede de Deus; e todo aquele que ama é nascido de Deus e conhece a Deus. Aquele que não ama não conhece a Deus, pois Deus é amor.',
                    'title' => '1 João 4:7-8 - Deus é amor',
                ],
            ],
        ];

        return $passageMap[$themeId] ?? [];
    }
}
