<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class StaticContentService
{
    private array $bibleStructure;

    public function __construct()
    {
        // Carrega estrutura da Bíblia
        $jsonPath = public_path('data/bible-structure.json');
        if (File::exists($jsonPath)) {
            $this->bibleStructure = json_decode(File::get($jsonPath), true) ?? [];
        } else {
            $this->bibleStructure = $this->getDefaultStructure();
        }
    }

    /**
     * Get static content for book page
     */
    public function getBookContent(string $testament, string $book): array
    {
        $bookData = $this->getBookData($testament, $book);

        return [
            'title' => "{$book} - ".($testament === 'antigo' ? 'Antigo Testamento' : 'Novo Testamento').' | Verso a verso',
            'description' => "Estude o livro de {$book} com {$bookData['chapters']} capítulos. Explicações detalhadas, contexto histórico e aplicações práticas para cada capítulo.",
            'keywords' => "{$book}, bíblia, ".($testament === 'antigo' ? 'antigo testamento' : 'novo testamento').', estudo bíblico, explicação',
            'book' => $book,
            'testament' => $testament,
            'chapters' => $bookData['chapters'],
            'content' => "O livro de {$book} contém {$bookData['chapters']} capítulos com ensinamentos profundos e relevantes para a vida cristã.",
        ];
    }

    /**
     * Get static content for chapter page
     */
    public function getChapterContent(string $testament, string $book, int $chapter): array
    {
        $bookData = $this->getBookData($testament, $book);

        return [
            'title' => "{$book} {$chapter} - Texto Bíblico | Verso a verso",
            'description' => "Leia {$book} {$chapter} completo. Texto bíblico com explicações detalhadas e contexto histórico.",
            'keywords' => "{$book} {$chapter}, bíblia, texto bíblico, versículos, estudo",
            'book' => $book,
            'testament' => $testament,
            'chapter' => $chapter,
            'total_chapters' => $bookData['chapters'],
            'content' => "Capítulo {$chapter} de {$book} - Leia o texto completo e acesse explicações detalhadas.",
        ];
    }

    /**
     * Get static fallback content for explanation page
     */
    public function getExplanationFallback(string $testament, string $book, int $chapter, ?string $verses = null): array
    {
        $bookData = $this->getBookData($testament, $book);
        $testamentName = $testament === 'antigo' ? 'Antigo Testamento' : 'Novo Testamento';

        if ($verses) {
            // Explicação de versículo específico - CONTEÚDO RICO PARA SEO
            return [
                'title' => "{$book} {$chapter}:{$verses} - Explicação Bíblica Completa | Verso a verso",
                'description' => "Explicação detalhada e profunda de {$book} {$chapter}:{$verses} com contexto histórico, análise exegética, significado original em hebraico/grego, aplicações práticas e referências cruzadas. Estudo bíblico completo para entender este versículo.",
                'keywords' => "{$book} {$chapter}:{$verses}, explicação bíblica, estudo bíblico, análise exegética, contexto histórico, significado, aplicação prática, versículo, bíblia, teologia, interpretação",
                'book' => $book,
                'testament' => $testament,
                'chapter' => $chapter,
                'verses' => $verses,
                'verse_text' => "Texto bíblico de {$book} {$chapter}:{$verses}",
                'intro' => "Bem-vindo ao estudo completo de {$book} {$chapter}:{$verses}. Este versículo faz parte do {$testamentName} e contém ensinamentos profundos e relevantes para a vida cristã. Nesta página, você encontrará uma análise detalhada que inclui o contexto histórico e cultural da época em que foi escrito, o significado original das palavras no idioma original (hebraico ou grego), a interpretação teológica baseada em comentaristas respeitados, e aplicações práticas para os dias de hoje. Nosso objetivo é ajudá-lo a compreender profundamente a Palavra de Deus e aplicá-la em sua vida diária.",
                'sections' => [
                    'contexto' => [
                        'title' => 'Contexto Histórico e Literário',
                        'content' => "O versículo {$book} {$chapter}:{$verses} está inserido no contexto mais amplo do capítulo {$chapter} de {$book}, que faz parte do {$testamentName}. Para uma compreensão adequada deste texto, é fundamental entender o contexto histórico, cultural e literário em que foi escrito. O livro de {$book} foi escrito em um período específico da história bíblica, dirigido a uma audiência original com suas próprias circunstâncias e desafios. O contexto literário imediato deste versículo mostra como ele se relaciona com os versículos anteriores e posteriores, formando uma unidade de pensamento coerente. Além disso, o contexto histórico nos ajuda a entender as referências culturais, os costumes da época e as questões que o autor estava abordando. Este versículo não deve ser interpretado isoladamente, mas como parte integral da mensagem do capítulo e do livro como um todo.",
                    ],
                    'analise' => [
                        'title' => 'Análise Exegética Detalhada',
                        'content' => "A análise exegética de {$book} {$chapter}:{$verses} revela camadas profundas de significado que vão muito além de uma leitura superficial. Cada palavra no texto original foi cuidadosamente escolhida pelo autor inspirado para transmitir verdades espirituais específicas. O estudo das palavras-chave no idioma original (hebraico para o Antigo Testamento ou grego para o Novo Testamento) nos ajuda a captar nuances que às vezes se perdem na tradução. A estrutura gramatical do versículo também é significativa, revelando ênfases e relações entre conceitos. Comentaristas bíblicos respeitados como John Stott, F.F. Bruce, Craig Keener, R.C. Sproul e Augustus Nicodemus oferecem insights valiosos sobre este texto, baseados em anos de estudo acadêmico e pastoral. Esta análise considera também como este versículo foi interpretado ao longo da história da igreja e como ele se relaciona com outras passagens bíblicas que tratam de temas similares.",
                    ],
                    'significado' => [
                        'title' => 'Significado e Interpretação Teológica',
                        'content' => "O significado teológico de {$book} {$chapter}:{$verses} é profundo e multifacetado. Este versículo contribui para nossa compreensão de doutrinas fundamentais da fé cristã, revelando aspectos importantes do caráter de Deus, da natureza humana, do plano de salvação ou de princípios para a vida cristã. A interpretação correta deste texto requer que consideremos a analogia da fé - ou seja, como ele se harmoniza com o ensino geral das Escrituras. Devemos evitar interpretações que contradigam verdades bíblicas claras estabelecidas em outras partes da Bíblia. O versículo também pode conter tipos, símbolos ou prefigurações que apontam para realidades maiores, especialmente para Cristo e Seu evangelho. A mensagem central deste texto permanece relevante através dos séculos, oferecendo verdades eternas que transcendem culturas e épocas.",
                    ],
                    'aplicacao' => [
                        'title' => 'Aplicação Prática para Hoje',
                        'content' => "A aplicação prática de {$book} {$chapter}:{$verses} à vida cristã contemporânea é tanto desafiadora quanto encorajadora. Embora este versículo tenha sido escrito há séculos, seus princípios são atemporais e podem ser aplicados em diversos contextos da vida moderna. Para aplicar corretamente este texto, devemos primeiro entender seu significado original e então identificar os princípios universais que ele ensina. Estes princípios podem ser aplicados em áreas como nossa vida devocional, relacionamentos familiares, ética no trabalho, decisões financeiras, ministério na igreja e testemunho no mundo. A aplicação adequada requer não apenas conhecimento intelectual, mas também dependência do Espírito Santo para transformar nossa compreensão em obediência prática. Este versículo nos desafia a examinar nossas vidas à luz da Palavra de Deus e a fazer os ajustes necessários para vivermos de acordo com Sua vontade revelada.",
                    ],
                    'referencias' => [
                        'title' => 'Referências Cruzadas e Passagens Relacionadas',
                        'content' => "O estudo de {$book} {$chapter}:{$verses} é enriquecido quando examinamos outras passagens bíblicas que tratam de temas similares ou que citam ou aludem a este texto. As referências cruzadas nos ajudam a ver como a Bíblia é uma unidade coerente, com temas que se desenvolvem ao longo de toda a Escritura. Versículos paralelos podem esclarecer o significado, fornecer exemplos adicionais ou mostrar como o mesmo princípio é aplicado em diferentes contextos. Para o Antigo Testamento, é especialmente importante ver como o Novo Testamento cita ou interpreta o texto. Para o Novo Testamento, devemos buscar as raízes do ensino no Antigo Testamento. Esta abordagem de deixar a Escritura interpretar a Escritura é fundamental para uma hermenêutica sólida e nos protege de interpretações equivocadas.",
                    ],
                ],
                'related_links' => $this->getRelatedLinks($testament, $book, $chapter),
            ];
        }

        // Explicação de capítulo completo - CONTEÚDO MUITO RICO PARA SEO
        return [
            'title' => "{$book} {$chapter} - Explicação Bíblica Completa e Detalhada | Verso a verso",
            'description' => "Estudo bíblico completo e aprofundado de {$book} {$chapter} com contexto histórico detalhado, análise teológica profunda, estrutura literária do capítulo, temas principais, personagens importantes, versículos-chave e aplicações práticas para a vida cristã. Entenda cada aspecto deste capítulo fundamental da Bíblia.",
            'keywords' => "{$book} {$chapter}, explicação bíblica, estudo bíblico, análise teológica, contexto histórico, estrutura, temas, aplicação prática, versículos, bíblia, interpretação, exegese",
            'book' => $book,
            'testament' => $testament,
            'chapter' => $chapter,
            'total_chapters' => $bookData['chapters'],
            'intro' => "Bem-vindo ao estudo completo e detalhado de {$book} capítulo {$chapter}. Este capítulo faz parte do {$testamentName} e é um dos {$bookData['chapters']} capítulos que compõem o livro de {$book}. Nesta análise abrangente, você encontrará tudo o que precisa para compreender profundamente este texto sagrado: o contexto histórico e cultural da época em que foi escrito, a estrutura literária que organiza as ideias do autor, os temas teológicos principais que são desenvolvidos, os personagens importantes e seus papéis na narrativa, os versículos-chave que merecem atenção especial, e aplicações práticas que mostram como estes ensinamentos antigos continuam relevantes para nossa vida hoje. Nosso objetivo é fornecer um recurso completo que sirva tanto para estudo pessoal quanto para preparação de aulas e pregações, sempre mantendo fidelidade ao texto bíblico e respeito pela tradição interpretativa evangélica.",
            'sections' => [
                'contexto_geral' => [
                    'title' => 'Contexto Geral do Capítulo',
                    'content' => "O capítulo {$chapter} de {$book} é uma parte fundamental e estratégica do {$testamentName}. Para compreender adequadamente este capítulo, é essencial situá-lo dentro do contexto mais amplo do livro de {$book} e da Bíblia como um todo. Este capítulo apresenta ensinamentos, narrativas ou profecias que se conectam intimamente com o propósito geral do livro e contribuem para o desenvolvimento da revelação progressiva de Deus. O contexto histórico nos ajuda a entender as circunstâncias em que este texto foi escrito - quem era a audiência original, quais eram seus desafios e necessidades, e como esta mensagem se aplicava à sua situação específica. O contexto literário mostra como este capítulo se relaciona com os capítulos anteriores e posteriores, formando uma narrativa coerente ou um argumento teológico progressivo. Além disso, é importante considerar o contexto canônico - como este capítulo se encaixa no plano redentor de Deus revelado em toda a Escritura, e como ele aponta para Cristo e o evangelho.",
                ],
                'estrutura' => [
                    'title' => 'Estrutura Literária e Divisões do Capítulo',
                    'content' => "A estrutura literária de {$book} {$chapter} revela a organização cuidadosa do pensamento do autor inspirado. Este capítulo pode ser dividido em seções temáticas ou narrativas que facilitam a compreensão de sua mensagem central e mostram como cada parte contribui para o todo. A análise da estrutura nos ajuda a identificar os pontos de ênfase, as transições entre temas, e a progressão lógica ou cronológica dos eventos ou argumentos. Muitas vezes, a estrutura literária inclui recursos como quiasmos, paralelismos, inclusões, ou outras técnicas literárias que eram comuns na literatura bíblica e que adicionam camadas de significado ao texto. Compreender a estrutura também nos ajuda a evitar interpretações que isolam versículos de seu contexto, permitindo que vejamos como cada parte se relaciona com o tema principal do capítulo. Esta análise estrutural é fundamental para uma exegese sólida e para a aplicação correta do texto.",
                ],
                'temas' => [
                    'title' => 'Temas Teológicos Principais',
                    'content' => "Os temas teológicos abordados em {$book} {$chapter} são profundos e multifacetados, revelando verdades importantes sobre Deus, a humanidade, a salvação, e a vida cristã. Estes temas não são apenas relevantes para o contexto original, mas continuam a falar poderosamente aos leitores contemporâneos. Entre os possíveis temas que podem ser explorados neste capítulo estão: a natureza e os atributos de Deus (Sua santidade, amor, justiça, misericórdia, soberania), a condição humana (pecado, necessidade de redenção, dignidade como imagem de Deus), o plano de salvação (promessas, profecias, cumprimento em Cristo), princípios para a vida cristã (santidade, obediência, fé, amor, serviço), e a esperança futura (promessas escatológicas, nova criação). Cada tema é desenvolvido de maneira única neste capítulo, contribuindo para nossa compreensão teológica mais ampla. O estudo cuidadoso destes temas nos ajuda a ver como a Bíblia é uma unidade coerente, com verdades que se desenvolvem e se complementam ao longo de toda a Escritura.",
                ],
                'personagens' => [
                    'title' => 'Personagens Importantes e Seus Papéis',
                    'content' => "Os personagens que aparecem em {$book} {$chapter} desempenham papéis significativos no desenvolvimento da narrativa ou na ilustração dos princípios teológicos sendo ensinados. Cada personagem - seja um indivíduo nomeado, um grupo de pessoas, ou mesmo Deus como personagem ativo na história - contribui para a mensagem do capítulo de maneira única. Ao estudar os personagens, devemos observar suas ações, motivações, diálogos, e como eles respondem a Deus e às circunstâncias. Muitas vezes, os personagens bíblicos servem como exemplos positivos a serem imitados ou exemplos negativos a serem evitados. Eles também podem funcionar como tipos ou prefigurações de realidades maiores, especialmente apontando para Cristo. A análise dos personagens nos ajuda a ver a aplicação prática dos ensinamentos bíblicos, pois vemos princípios abstratos sendo vividos (ou violados) em situações concretas da vida real.",
                ],
                'versiculos_chave' => [
                    'title' => 'Versículos-Chave e Sua Importância',
                    'content' => "Dentro de {$book} {$chapter}, há versículos específicos que merecem atenção especial por sua importância teológica, sua beleza literária, ou sua aplicação prática. Estes versículos-chave frequentemente encapsulam a mensagem central do capítulo, apresentam verdades doutrinárias fundamentais, ou oferecem promessas e comandos particularmente significativos. Alguns versículos se tornaram especialmente conhecidos e amados pela igreja ao longo dos séculos, sendo memorizados, citados em pregações, e usados como fonte de conforto e orientação. Ao identificar e estudar estes versículos-chave, devemos sempre mantê-los em seu contexto, evitando interpretações que os isolem da mensagem mais ampla do capítulo. Cada versículo-chave deve ser compreendido à luz de toda a Escritura, aplicando o princípio de que a Bíblia interpreta a Bíblia. O estudo aprofundado destes versículos pode enriquecer enormemente nossa compreensão e apreciação do capítulo como um todo.",
                ],
                'aplicacao' => [
                    'title' => 'Aplicação Prática para a Vida Cristã Hoje',
                    'content' => "A aplicação prática de {$book} {$chapter} à vida cristã contemporânea é tanto desafiadora quanto encorajadora. Embora este capítulo tenha sido escrito há séculos em um contexto cultural muito diferente do nosso, os princípios espirituais e morais que ele ensina são atemporais e universais. Para aplicar corretamente este texto, devemos seguir um processo cuidadoso: primeiro, entender o significado original no contexto histórico; segundo, identificar os princípios teológicos universais que transcendem cultura e época; terceiro, discernir como estes princípios se aplicam às situações específicas que enfrentamos hoje. As aplicações podem abranger diversas áreas da vida cristã: nossa vida devocional e relacionamento pessoal com Deus, nossos relacionamentos familiares e comunitários, nossa ética no trabalho e nos negócios, nossas decisões sobre finanças e mordomia, nosso ministério e serviço na igreja, e nosso testemunho e missão no mundo. A aplicação adequada requer não apenas conhecimento intelectual do texto, mas também sensibilidade ao Espírito Santo, que ilumina nossa compreensão e nos capacita a viver de acordo com a vontade revelada de Deus. Este capítulo nos desafia a examinar nossas vidas, arrepender-nos onde for necessário, e crescer em santidade e obediência.",
                ],
            ],
            'related_links' => $this->getRelatedLinks($testament, $book, $chapter),
        ];
    }

    /**
     * Get static content for book context page
     */
    public function getBookContextFallback(string $testament, string $book): array
    {
        $bookData = $this->getBookData($testament, $book);

        return [
            'title' => "Contexto do Livro de {$book} | Verso a verso",
            'description' => "Contexto histórico, autoria, data, propósito e estrutura do livro de {$book}. Entenda o background completo deste livro bíblico.",
            'keywords' => "{$book}, contexto histórico, autoria, propósito, estrutura, ".($testament === 'antigo' ? 'antigo testamento' : 'novo testamento'),
            'book' => $book,
            'testament' => $testament,
            'sections' => [
                [
                    'id' => 'genero-literario',
                    'title' => 'Gênero Literário',
                    'content' => "O livro de {$book} pertence a um gênero literário específico que influencia sua interpretação e compreensão.",
                ],
                [
                    'id' => 'contexto-historico',
                    'title' => 'Contexto Histórico',
                    'content' => 'Este livro foi escrito em um contexto histórico e cultural específico que é fundamental para sua compreensão adequada.',
                ],
                [
                    'id' => 'autoria',
                    'title' => 'Autoria e Data',
                    'content' => "A autoria e datação de {$book} são importantes para entender sua mensagem e propósito original.",
                ],
                [
                    'id' => 'proposito',
                    'title' => 'Propósito e Mensagem',
                    'content' => "O livro de {$book} foi escrito com propósitos específicos que continuam relevantes para os leitores contemporâneos.",
                ],
                [
                    'id' => 'temas',
                    'title' => 'Temas Teológicos',
                    'content' => "Os temas teológicos abordados em {$book} revelam aspectos importantes da revelação divina e do plano de Deus.",
                ],
                [
                    'id' => 'aplicacao',
                    'title' => 'Aplicação Contemporânea',
                    'content' => "Os princípios e ensinamentos de {$book} podem ser aplicados na vida cristã contemporânea de maneiras práticas e transformadoras.",
                ],
            ],
        ];
    }

    /**
     * Get book data from structure
     */
    private function getBookData(string $testament, string $book): array
    {
        $chapters = $this->getDefaultChapterCount($book);

        return [
            'chapters' => $chapters,
            'testament' => $testament,
        ];
    }

    /**
     * Get default chapter count for a book
     */
    private function getDefaultChapterCount(string $book): int
    {
        $counts = [
            'Gênesis' => 50, 'Êxodo' => 40, 'Levítico' => 27, 'Números' => 36, 'Deuteronômio' => 34,
            'Josué' => 24, 'Juízes' => 21, 'Rute' => 4, '1 Samuel' => 31, '2 Samuel' => 24,
            '1 Reis' => 22, '2 Reis' => 25, '1 Crônicas' => 29, '2 Crônicas' => 36, 'Esdras' => 10,
            'Neemias' => 13, 'Ester' => 10, 'Jó' => 42, 'Salmos' => 150, 'Provérbios' => 31,
            'Eclesiastes' => 12, 'Cântico dos Cânticos' => 8, 'Isaías' => 66, 'Jeremias' => 52,
            'Lamentações' => 5, 'Ezequiel' => 48, 'Daniel' => 12, 'Oseias' => 14, 'Joel' => 3,
            'Amós' => 9, 'Obadias' => 1, 'Jonas' => 4, 'Miqueias' => 7, 'Naum' => 3,
            'Habacuque' => 3, 'Sofonias' => 3, 'Ageu' => 2, 'Zacarias' => 14, 'Malaquias' => 4,
            'Mateus' => 28, 'Marcos' => 16, 'Lucas' => 24, 'João' => 21, 'Atos' => 28,
            'Romanos' => 16, '1 Coríntios' => 16, '2 Coríntios' => 13, 'Gálatas' => 6, 'Efésios' => 6,
            'Filipenses' => 4, 'Colossenses' => 4, '1 Tessalonicenses' => 5, '2 Tessalonicenses' => 3,
            '1 Timóteo' => 6, '2 Timóteo' => 4, 'Tito' => 3, 'Filemom' => 1, 'Hebreus' => 13,
            'Tiago' => 5, '1 Pedro' => 5, '2 Pedro' => 3, '1 João' => 5, '2 João' => 1,
            '3 João' => 1, 'Judas' => 1, 'Apocalipse' => 22,
        ];

        return $counts[$book] ?? 30;
    }

    /**
     * Get related links
     */
    private function getRelatedLinks(string $testament, string $book, int $chapter): array
    {
        $links = [];

        // Link para capítulo anterior
        if ($chapter > 1) {
            $links[] = [
                'title' => "{$book} ".($chapter - 1),
                'url' => "/explicacao/{$testament}/".strtolower($book).'/'.($chapter - 1),
            ];
        }

        // Link para capítulo seguinte
        $bookData = $this->getBookData($testament, $book);
        if ($chapter < $bookData['chapters']) {
            $links[] = [
                'title' => "{$book} ".($chapter + 1),
                'url' => "/explicacao/{$testament}/".strtolower($book).'/'.($chapter + 1),
            ];
        }

        // Link para contexto do livro
        $links[] = [
            'title' => "Contexto de {$book}",
            'url' => "/contexto/{$testament}/".strtolower($book),
        ];

        return $links;
    }

    /**
     * Get default structure if file doesn't exist
     */
    private function getDefaultStructure(): array
    {
        return [
            'antigo' => [],
            'novo' => [],
        ];
    }
}
