<?php

namespace App\Services;

use App\Models\ThematicStudy;
use App\Services\Ai\AiClientFactory;
use App\Services\Ai\PromptBuilderService;
use Illuminate\Support\Str;

class ThematicStudyService
{
    public function __construct(private PromptBuilderService $promptBuilder) {}

    public function allTopics(): array
    {
        return [
            ['slug' => 'fe', 'label' => 'Fé', 'title' => 'Versículos sobre Fé', 'term' => 'fé', 'description' => 'Versículos sobre fé para fortalecer sua confiança em Deus e compreender o chamado bíblico para crer.'],
            ['slug' => 'confianca', 'label' => 'Confiança', 'title' => 'Versículos sobre Confiança', 'term' => 'confiança', 'description' => 'Passagens bíblicas sobre confiar em Deus em meio às decisões, lutas e incertezas da vida.'],
            ['slug' => 'ofertas', 'label' => 'Ofertas', 'title' => 'Versículos sobre Ofertas', 'term' => 'ofertas', 'description' => 'Textos bíblicos sobre ofertas, generosidade, mordomia e coração grato diante de Deus.'],
            ['slug' => 'amor', 'label' => 'Amor', 'title' => 'Versículos sobre Amor', 'term' => 'amor', 'description' => 'Versículos sobre o amor de Deus, amor ao próximo e o amor como marca da vida cristã.'],
            ['slug' => 'oracao', 'label' => 'Oração', 'title' => 'Versículos sobre Oração', 'term' => 'oração', 'description' => 'Passagens sobre oração, comunhão com Deus, súplica, gratidão e perseverança espiritual.'],
            ['slug' => 'ansiedade', 'label' => 'Ansiedade', 'title' => 'Versículos sobre Ansiedade', 'term' => 'ansiedade', 'description' => 'Versículos para momentos de ansiedade, medo e preocupação, com conforto e direção bíblica.'],
            ['slug' => 'perdao', 'label' => 'Perdão', 'title' => 'Versículos sobre Perdão', 'term' => 'perdão', 'description' => 'Textos bíblicos sobre o perdão de Deus e o chamado para perdoar o próximo.'],
            ['slug' => 'gratidao', 'label' => 'Gratidão', 'title' => 'Versículos sobre Gratidão', 'term' => 'gratidão', 'description' => 'Versículos sobre gratidão, louvor e reconhecimento da bondade de Deus.'],
            ['slug' => 'familia', 'label' => 'Família', 'title' => 'Versículos sobre Família', 'term' => 'família', 'description' => 'Passagens bíblicas sobre família, cuidado, ensino, honra e vida no lar.'],
            ['slug' => 'casamento', 'label' => 'Casamento', 'title' => 'Versículos sobre Casamento', 'term' => 'casamento', 'description' => 'Versículos sobre casamento, aliança, amor, fidelidade e união segundo a Bíblia.'],
            ['slug' => 'sabedoria', 'label' => 'Sabedoria', 'title' => 'Versículos sobre Sabedoria', 'term' => 'sabedoria', 'description' => 'Textos bíblicos sobre sabedoria, discernimento, temor do Senhor e boas escolhas.'],
            ['slug' => 'forca', 'label' => 'Força', 'title' => 'Versículos sobre Força', 'term' => 'força', 'description' => 'Versículos para buscar força em Deus nos dias difíceis e perseverar com esperança.'],
            ['slug' => 'esperanca', 'label' => 'Esperança', 'title' => 'Versículos sobre Esperança', 'term' => 'esperança', 'description' => 'Passagens bíblicas sobre esperança, promessa, consolo e futuro nas mãos de Deus.'],
            ['slug' => 'paz', 'label' => 'Paz', 'title' => 'Versículos sobre Paz', 'term' => 'paz', 'description' => 'Versículos sobre a paz de Deus, reconciliação e descanso para o coração.'],
            ['slug' => 'cura', 'label' => 'Cura', 'title' => 'Versículos sobre Cura', 'term' => 'cura', 'description' => 'Textos bíblicos sobre cura, restauração, consolo e cuidado de Deus.'],
            ['slug' => 'prosperidade', 'label' => 'Prosperidade', 'title' => 'Versículos sobre Prosperidade', 'term' => 'prosperidade', 'description' => 'Versículos sobre prosperidade bíblica, contentamento, trabalho e fidelidade a Deus.'],
            ['slug' => 'obediencia', 'label' => 'Obediência', 'title' => 'Versículos sobre Obediência', 'term' => 'obediência', 'description' => 'Passagens sobre obedecer a Deus, guardar sua Palavra e viver em fidelidade.'],
            ['slug' => 'arrependimento', 'label' => 'Arrependimento', 'title' => 'Versículos sobre Arrependimento', 'term' => 'arrependimento', 'description' => 'Versículos sobre arrependimento, conversão, confissão e retorno ao Senhor.'],
            ['slug' => 'salvacao', 'label' => 'Salvação', 'title' => 'Versículos sobre Salvação', 'term' => 'salvação', 'description' => 'Textos bíblicos sobre salvação pela graça, fé em Cristo e vida eterna.'],
            ['slug' => 'louvor', 'label' => 'Louvor', 'title' => 'Versículos sobre Louvor', 'term' => 'louvor', 'description' => 'Versículos sobre louvor, adoração, cântico e exaltação ao Senhor.'],
        ];
    }

    public function findTopic(string $slug): ?array
    {
        $normalized = Str::slug($slug);
        foreach ($this->allTopics() as $topic) {
            if ($topic['slug'] === $normalized) {
                return $topic + ['keywords' => $this->keywordsFor($topic['term'])];
            }
        }

        return null;
    }

    public function getStudy(string $slug, bool $generate = false): array
    {
        $topic = $this->findTopic($slug);
        if (! $topic) {
            throw new \InvalidArgumentException('Tema não encontrado.');
        }

        $existing = ThematicStudy::where('slug', $topic['slug'])->first();
        if ($existing) {
            $existing->increment('access_count');
            $decoded = json_decode($existing->content, true);
            $study = is_array($decoded) ? $this->normalizeStudy($decoded, $topic) : $this->fallbackStudy($topic);

            if ($generate && $this->isWeakStudy($study)) {
                $study = $this->generateStudy($topic);
                $existing->update([
                    'title' => $topic['title'],
                    'term' => $topic['term'],
                    'content' => json_encode($study, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'source' => 'ai',
                ]);

                return [
                    'topic' => $topic,
                    'study' => $study,
                    'origin' => 'regenerated',
                    'id' => $existing->id,
                ];
            }

            if ($this->isWeakStudy($study) && count($this->fallbackStudy($topic)['versiculos']) > count($study['versiculos'] ?? [])) {
                $study = $this->fallbackStudy($topic);
            }

            return [
                'topic' => $topic,
                'study' => $study,
                'origin' => 'database',
                'id' => $existing->id,
            ];
        }

        if (! $generate) {
            return [
                'topic' => $topic,
                'study' => $this->fallbackStudy($topic),
                'origin' => 'fallback',
                'id' => null,
            ];
        }

        $study = $this->generateStudy($topic);
        $record = ThematicStudy::create([
            'slug' => $topic['slug'],
            'title' => $topic['title'],
            'term' => $topic['term'],
            'content' => json_encode($study, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'source' => 'ai',
        ]);

        return [
            'topic' => $topic,
            'study' => $study,
            'origin' => 'generated',
            'id' => $record->id,
        ];
    }

    private function generateStudy(array $topic): array
    {
        $client = AiClientFactory::make();
        $prompt = $this->promptBuilder->buildThematicVersesPrompt($topic['term'], $topic['title'], $this->seedReferencesFor($topic['slug']));
        $messages = [
            ['role' => 'system', 'content' => $this->promptBuilder->getSystemMessage()],
            ['role' => 'user', 'content' => $prompt],
        ];
        $content = $client->chat($messages, 6500);
        $json = $this->extractJson($content);
        $decoded = json_decode($json, true);

        if (! is_array($decoded) || empty($decoded['versiculos']) || ! is_array($decoded['versiculos'])) {
            return $this->fallbackStudy($topic);
        }

        return $this->normalizeStudy($decoded, $topic);
    }

    private function fallbackStudy(array $topic): array
    {
        $term = $topic['term'];

        return [
            'titulo' => $topic['title'],
            'subtitulo' => 'Passagens bíblicas para estudar '.$term.' com contexto e explicação verso a verso.',
            'introducao' => $this->introFor($topic),
            'significado_biblico' => $this->meaningFor($topic),
            'aplicacao_pratica' => 'Leia os textos abaixo com calma, observe o contexto do capítulo e abra a explicação de cada versículo para aprofundar seu estudo.',
            'historia_biblica' => $this->themeStoryFor($topic['slug']),
            'versiculos' => $this->seedReferencesFor($topic['slug']),
        ];
    }

    private function normalizeStudy(array $study, array $topic): array
    {
        $fallback = $this->fallbackStudy($topic);
        $verses = [];

        foreach (($study['versiculos'] ?? []) as $verse) {
            if (! is_array($verse)) {
                continue;
            }
            $testament = in_array(($verse['testamento'] ?? ''), ['antigo', 'novo'], true) ? $verse['testamento'] : 'novo';
            $book = Str::slug((string) ($verse['livro_slug'] ?? $verse['livro'] ?? 'joao'));
            $chapter = max(1, (int) ($verse['capitulo'] ?? 1));
            $versesRef = trim((string) ($verse['versos'] ?? $verse['versiculo'] ?? '1'));
            $verses[] = [
                'referencia' => trim((string) ($verse['referencia'] ?? ucfirst($book).' '.$chapter.':'.$versesRef)),
                'testamento' => $testament,
                'livro_slug' => $book,
                'capitulo' => $chapter,
                'versos' => $versesRef,
                'texto' => trim((string) ($verse['texto'] ?? '')),
                'motivo' => trim((string) ($verse['motivo'] ?? $verse['explicacao_curta'] ?? 'Este versículo ajuda a compreender o tema à luz da Bíblia.')),
            ];
        }

        return [
            'titulo' => (string) ($study['titulo'] ?? $fallback['titulo']),
            'subtitulo' => (string) ($study['subtitulo'] ?? $fallback['subtitulo']),
            'introducao' => (string) ($study['introducao'] ?? $fallback['introducao']),
            'significado_biblico' => (string) ($study['significado_biblico'] ?? $fallback['significado_biblico']),
            'aplicacao_pratica' => (string) ($study['aplicacao_pratica'] ?? $fallback['aplicacao_pratica']),
            'historia_biblica' => is_array($study['historia_biblica'] ?? null) ? $study['historia_biblica'] : $fallback['historia_biblica'],
            'versiculos' => count($verses) > 0 ? array_slice($verses, 0, 30) : $fallback['versiculos'],
        ];
    }

    private function isWeakStudy(array $study): bool
    {
        $verses = $study['versiculos'] ?? [];
        $story = $study['historia_biblica'] ?? [];
        $storyText = Str::lower((string) ($story['texto'] ?? ''));
        $storyReference = Str::lower((string) ($story['referencia'] ?? ''));

        return count($verses) < 12
            || str_contains($storyText, 'passagens selecionadas abaixo')
            || str_contains($storyReference, 'passagens selecionadas abaixo')
            || str_contains($storyText, 'a bíblia normalmente ensina seus temas');
    }

    private function themeStoryFor(string $slug): array
    {
        if ($slug === 'fe') {
            return [
                'titulo' => 'Abraão: fé que descansa na promessa de Deus',
                'referencia' => 'Gênesis 12; Gênesis 15; Romanos 4; Hebreus 11',
                'texto' => 'A história de Abraão é uma das maiores narrativas bíblicas sobre fé. Deus o chamou para sair da sua terra e confiar em uma promessa que ainda não podia ver. Paulo interpreta essa história em Romanos 4 dizendo que Abraão creu em Deus, e isso lhe foi creditado como justiça. Hebreus 11 também apresenta Abraão como exemplo de obediência confiante: ele saiu sem saber exatamente para onde ia, porque sua segurança estava no caráter de Deus. Teólogos como João Calvino destacaram que a fé se apoia na promessa divina, enquanto John Stott enfatizou que a fé salvadora não é confiança em si mesmo, mas resposta à graça de Deus revelada em Cristo.',
            ];
        }

        if ($slug === 'louvor') {
            return [
                'titulo' => 'Josafá: louvor antes da vitória',
                'referencia' => '2 Crônicas 20:1-30',
                'texto' => 'Quando uma grande multidão veio contra Judá, o rei Josafá teve medo, mas buscou ao Senhor e convocou o povo para jejum e oração. A resposta de Deus veio por meio de Jaaziel: a batalha pertencia ao Senhor, e o povo deveria permanecer firme e ver o livramento. No dia seguinte, Josafá colocou cantores à frente do exército para louvarem a santidade de Deus e confessarem sua misericórdia. A narrativa diz que, quando começaram a cantar e louvar, o Senhor agiu contra os inimigos. Essa história mostra que louvor bíblico não é fuga da realidade, mas confiança adoradora no caráter de Deus em meio à crise. O louvor nasce da fé, recorda quem Deus é e forma uma comunidade que responde à Palavra antes mesmo de ver o desfecho.',
            ];
        }

        return [
            'titulo' => 'Uma história bíblica para entender '.$slug,
            'referencia' => 'Leia as passagens selecionadas abaixo',
            'texto' => 'A Bíblia normalmente ensina seus temas por meio de histórias, promessas, mandamentos e exemplos de fé. Ao estudar este assunto, observe como Deus age, como seu povo responde e como cada passagem aponta para uma vida moldada pela Palavra.',
        ];
    }

    private function introFor(array $topic): string
    {
        if ($topic['slug'] === 'fe') {
            return 'A fé ocupa lugar central na Bíblia porque é a resposta confiante do ser humano à revelação e às promessas de Deus. De Gênesis a Apocalipse, a Escritura mostra que o povo de Deus vive não pela autossuficiência, mas pela confiança no caráter fiel do Senhor. No Novo Testamento, essa fé se concentra em Cristo: sua pessoa, sua obra, sua morte e ressurreição.';
        }

        return ucfirst($topic['term']).' na Bíblia aponta para uma vida orientada pela Palavra de Deus. Este tema deve ser estudado no contexto das passagens, observando como ele se conecta com a aliança, a obediência, a graça e a esperança cristã.';
    }

    private function meaningFor(array $topic): string
    {
        if ($topic['slug'] === 'fe') {
            return 'Biblicamente, fé não é pensamento positivo nem salto irracional no escuro. Hebreus 11:1 fala da fé como certeza e convicção; Romanos 10:17 afirma que ela vem pela Palavra de Cristo; Efésios 2:8 ensina que a salvação é recebida pela fé como dom da graça. Na tradição cristã, Agostinho, Lutero, Calvino, John Stott e R.C. Sproul trataram a fé como confiança pessoal em Deus e apropriação das promessas do evangelho, sempre acompanhada de arrependimento, obediência e fruto.';
        }

        return 'Biblicamente, '.$topic['term'].' não é apenas uma ideia abstrata; é uma realidade revelada nas Escrituras e vivida diante de Deus. Para entender este tema com fidelidade, é importante observar o contexto bíblico, a relação com Cristo e a forma como a igreja cristã historicamente interpretou essas passagens.';
    }

    private function seedReferencesFor(string $slug): array
    {
        $map = [
            'fe' => [
                ['Hebreus 11:1','novo','hebreus',11,'1','Ora, a fé é a certeza daquilo que esperamos e a prova das coisas que não vemos.','Hebreus define fé como confiança firme nas promessas de Deus, mesmo quando aquilo que esperamos ainda não está visível.'],
                ['Romanos 10:17','novo','romanos',10,'17','Consequentemente, a fé vem por se ouvir a mensagem, e a mensagem é ouvida mediante a palavra de Cristo.','Paulo mostra que a fé bíblica nasce da Palavra de Cristo, não de mero otimismo humano.'],
                ['Efésios 2:8','novo','efesios',2,'8','Pois vocês são salvos pela graça, por meio da fé, e isto não vem de vocês, é dom de Deus.','Este texto liga fé à graça: a salvação é recebida pela confiança em Cristo, não conquistada por mérito.'],
                ['2 Coríntios 5:7','novo','2-corintios',5,'7','Porque vivemos por fé, e não pelo que vemos.','A vida cristã caminha pela confiança na fidelidade de Deus acima das aparências imediatas.'],
                ['Habacuque 2:4','antigo','habacuque',2,'4','Mas o justo viverá pela sua fidelidade.','A Escritura apresenta a fé como postura perseverante diante de Deus em tempos de crise.'],
                ['Romanos 4:20','novo','romanos',4,'20','Mesmo assim não duvidou nem foi incrédulo em relação à promessa de Deus, mas foi fortalecido em sua fé e deu glória a Deus.','Abraão é exemplo de fé porque descansou na promessa de Deus apesar das impossibilidades humanas.'],
                ['Gálatas 2:20','novo','galatas',2,'20','A vida que agora vivo no corpo, vivo-a pela fé no filho de Deus, que me amou e se entregou por mim.','Paulo descreve a fé como união pessoal com Cristo e dependência diária do seu amor redentor.'],
                ['Tiago 2:17','novo','tiago',2,'17','Assim também a fé, por si só, se não for acompanhada de obras, está morta.','Tiago lembra que a fé verdadeira produz obediência visível e fruto prático.'],
                ['Marcos 9:24','novo','marcos',9,'24','Creio, ajuda-me a vencer a minha incredulidade!','Este clamor mostra que a fé pode ser sincera mesmo quando luta contra fraqueza e dúvidas.'],
                ['Mateus 17:20','novo','mateus',17,'20','Se vocês tiverem fé do tamanho de um grão de mostarda, poderão dizer a este monte: Vá daqui para lá, e ele irá.','Jesus ensina que o poder da fé está no Deus em quem confiamos, não no tamanho aparente da nossa força.'],
                ['1 Pedro 1:7','novo','1-pedro',1,'7','Assim acontece para que fique comprovado que a fé que vocês têm, muito mais valiosa do que o ouro que perece, mesmo que refinado pelo fogo, é genuína.','Pedro mostra que provações refinam a fé e revelam sua autenticidade diante de Deus.'],
                ['João 20:29','novo','joao',20,'29','Felizes os que não viram e creram.','Jesus abençoa aqueles que confiam no testemunho apostólico sem exigir ver para crer.'],
            ],
            'confianca' => [['Provérbios 3:5','antigo','proverbios',3,'5','Confie no Senhor de todo o seu coração e não se apoie em seu próprio entendimento.'], ['Salmos 56:3','antigo','salmos',56,'3','Mas eu, quando estiver com medo, confiarei em ti.'], ['Jeremias 17:7','antigo','jeremias',17,'7','Bendito é o homem cuja confiança está no Senhor.']],
            'ofertas' => [['2 Coríntios 9:7','novo','2-corintios',9,'7','Cada um dê conforme determinou em seu coração, não com pesar ou por obrigação, pois Deus ama quem dá com alegria.'], ['Provérbios 3:9','antigo','proverbios',3,'9','Honre o Senhor com todos os seus recursos e com os primeiros frutos de todas as suas plantações.'], ['Lucas 6:38','novo','lucas',6,'38','Deem, e será dado a vocês.']],
            'amor' => [['1 Coríntios 13:4','novo','1-corintios',13,'4','O amor é paciente, o amor é bondoso.'], ['1 João 4:8','novo','1-joao',4,'8','Quem não ama não conhece a Deus, porque Deus é amor.'], ['João 3:16','novo','joao',3,'16','Porque Deus tanto amou o mundo que deu o seu Filho unigênito.']],
            'oracao' => [['Filipenses 4:6','novo','filipenses',4,'6','Não andem ansiosos por coisa alguma, mas em tudo, pela oração e súplicas, apresentem seus pedidos a Deus.'], ['Mateus 6:6','novo','mateus',6,'6','Quando você orar, vá para seu quarto, feche a porta e ore a seu Pai.'], ['1 Tessalonicenses 5:17','novo','1-tessalonicenses',5,'17','Orem continuamente.']],
            'ansiedade' => [['1 Pedro 5:7','novo','1-pedro',5,'7','Lancem sobre ele toda a sua ansiedade, porque ele tem cuidado de vocês.'], ['Mateus 6:34','novo','mateus',6,'34','Não se preocupem com o amanhã, pois o amanhã trará as suas próprias preocupações.'], ['Filipenses 4:6','novo','filipenses',4,'6','Não andem ansiosos por coisa alguma.']],
            'perdao' => [['Efésios 4:32','novo','efesios',4,'32','Sejam bondosos e compassivos uns para com os outros, perdoando-se mutuamente.'], ['1 João 1:9','novo','1-joao',1,'9','Se confessarmos os nossos pecados, ele é fiel e justo para perdoar.'], ['Mateus 6:14','novo','mateus',6,'14','Pois se perdoarem as ofensas uns dos outros, o Pai celestial também lhes perdoará.']],
            'gratidao' => [['1 Tessalonicenses 5:18','novo','1-tessalonicenses',5,'18','Deem graças em todas as circunstâncias.'], ['Salmos 100:4','antigo','salmos',100,'4','Entrem por suas portas com ações de graças.'], ['Colossenses 3:17','novo','colossenses',3,'17','Tudo o que fizerem, façam em nome do Senhor Jesus, dando graças.']],
            'familia' => [['Josué 24:15','antigo','josue',24,'15','Eu e a minha família serviremos ao Senhor.'], ['Efésios 6:1','novo','efesios',6,'1','Filhos, obedeçam a seus pais no Senhor.'], ['Salmos 127:3','antigo','salmos',127,'3','Os filhos são herança do Senhor.']],
            'casamento' => [['Gênesis 2:24','antigo','genesis',2,'24','Por essa razão, o homem deixará pai e mãe e se unirá à sua mulher.'], ['Efésios 5:25','novo','efesios',5,'25','Maridos, amem suas mulheres, assim como Cristo amou a igreja.'], ['1 Coríntios 13:7','novo','1-corintios',13,'7','Tudo sofre, tudo crê, tudo espera, tudo suporta.']],
            'sabedoria' => [['Tiago 1:5','novo','tiago',1,'5','Se algum de vocês tem falta de sabedoria, peça-a a Deus.'], ['Provérbios 9:10','antigo','proverbios',9,'10','O temor do Senhor é o princípio da sabedoria.'], ['Provérbios 2:6','antigo','proverbios',2,'6','Pois o Senhor é quem dá sabedoria.']],
            'forca' => [['Filipenses 4:13','novo','filipenses',4,'13','Tudo posso naquele que me fortalece.'], ['Isaías 40:31','antigo','isaias',40,'31','Aqueles que esperam no Senhor renovam as suas forças.'], ['Salmos 46:1','antigo','salmos',46,'1','Deus é o nosso refúgio e a nossa fortaleza.']],
            'esperanca' => [['Romanos 15:13','novo','romanos',15,'13','Que o Deus da esperança os encha de toda alegria e paz.'], ['Jeremias 29:11','antigo','jeremias',29,'11','Porque sou eu que conheço os planos que tenho para vocês.'], ['Hebreus 10:23','novo','hebreus',10,'23','Apeguemo-nos com firmeza à esperança que professamos.']],
            'paz' => [['João 14:27','novo','joao',14,'27','Deixo-lhes a paz; a minha paz lhes dou.'], ['Filipenses 4:7','novo','filipenses',4,'7','E a paz de Deus, que excede todo o entendimento, guardará os seus corações.'], ['Números 6:26','antigo','numeros',6,'26','O Senhor volte para você o seu rosto e lhe dê paz.']],
            'cura' => [['Isaías 53:5','antigo','isaias',53,'5','O castigo que nos trouxe paz estava sobre ele, e pelas suas feridas fomos curados.'], ['Tiago 5:15','novo','tiago',5,'15','A oração feita com fé curará o doente.'], ['Salmos 147:3','antigo','salmos',147,'3','Só ele cura os de coração quebrantado.']],
            'prosperidade' => [['3 João 1:2','novo','3-joao',1,'2','Oro para que você tenha boa saúde e tudo corra bem.'], ['Josué 1:8','antigo','josue',1,'8','Medite nele dia e noite, para que tenha cuidado de fazer tudo o que nele está escrito.'], ['Salmos 1:3','antigo','salmos',1,'3','É como árvore plantada à beira de águas correntes.']],
            'obediencia' => [['João 14:15','novo','joao',14,'15','Se vocês me amam, obedecerão aos meus mandamentos.'], ['Deuteronômio 5:33','antigo','deuteronomio',5,'33','Andem sempre pelo caminho que o Senhor, o seu Deus, lhes ordenou.'], ['Tiago 1:22','novo','tiago',1,'22','Sejam praticantes da palavra, e não apenas ouvintes.']],
            'arrependimento' => [['Atos 3:19','novo','atos',3,'19','Arrependam-se, pois, e voltem-se para Deus.'], ['1 João 1:9','novo','1-joao',1,'9','Se confessarmos os nossos pecados, ele é fiel e justo para perdoar.'], ['2 Crônicas 7:14','antigo','2-cronicas',7,'14','Se o meu povo se humilhar, orar e me buscar, eu ouvirei dos céus.']],
            'salvacao' => [['João 3:16','novo','joao',3,'16','Porque Deus tanto amou o mundo que deu o seu Filho unigênito.'], ['Efésios 2:8','novo','efesios',2,'8','Pois vocês são salvos pela graça, por meio da fé.'], ['Romanos 10:9','novo','romanos',10,'9','Se você confessar com a sua boca que Jesus é Senhor, será salvo.']],
            'louvor' => [
                ['Salmos 150:6', 'antigo', 'salmos', 150, '6', 'Tudo quanto tem fôlego louve ao Senhor.', 'O salmo encerra o Saltério convocando toda criatura viva a louvar o Senhor.'],
                ['Salmos 100:2', 'antigo', 'salmos', 100, '2', 'Servi ao Senhor com alegria; apresentai-vos diante dele com cântico.', 'Louvor envolve serviço alegre e aproximação reverente diante de Deus.'],
                ['Salmos 100:4', 'antigo', 'salmos', 100, '4', 'Entrai pelas portas dele com gratidão, e em seus átrios com louvor.', 'A adoração comunitária é marcada por gratidão, bênção e reconhecimento do nome do Senhor.'],
                ['Salmos 95:1', 'antigo', 'salmos', 95, '1', 'Vinde, cantemos ao Senhor; jubilemos à rocha da nossa salvação.', 'O louvor responde à salvação de Deus com cântico, alegria e confissão pública.'],
                ['Salmos 96:1', 'antigo', 'salmos', 96, '1', 'Cantai ao Senhor um cântico novo; cantai ao Senhor, toda a terra.', 'A glória de Deus merece um louvor renovado e anunciado a todos os povos.'],
                ['Salmos 96:4', 'antigo', 'salmos', 96, '4', 'Porque grande é o Senhor, e digno de louvor, mais temível do que todos os deuses.', 'A razão do louvor está na grandeza incomparável do Senhor.'],
                ['Salmos 103:1', 'antigo', 'salmos', 103, '1', 'Bendize, ó minha alma, ao Senhor, e tudo o que há em mim bendiga o seu santo nome.', 'Davi mostra que o louvor envolve a pessoa inteira, não apenas palavras externas.'],
                ['Salmos 103:2', 'antigo', 'salmos', 103, '2', 'Bendize, ó minha alma, ao Senhor, e não te esqueças de nenhum de seus benefícios.', 'Louvar também é lembrar as misericórdias de Deus para não viver na ingratidão.'],
                ['Salmos 113:3', 'antigo', 'salmos', 113, '3', 'Desde o nascimento do sol até ao ocaso, seja louvado o nome do Senhor.', 'O nome do Senhor deve ser bendito em todo tempo e lugar.'],
                ['Salmos 117:1', 'antigo', 'salmos', 117, '1', 'Louvai ao Senhor, todas as nações; louvai-o, todos os povos.', 'O louvor bíblico tem alcance missionário e chama as nações para Deus.'],
                ['Salmos 145:3', 'antigo', 'salmos', 145, '3', 'Grande é o Senhor, e mui digno de ser louvado; a sua grandeza é insondável.', 'A grandeza infinita de Deus sustenta um louvor que nunca se esgota.'],
                ['Salmos 146:2', 'antigo', 'salmos', 146, '2', 'Louvarei ao Senhor durante a minha vida; cantarei louvores ao meu Deus enquanto eu viver.', 'O louvor não é apenas um momento litúrgico, mas vocação de toda a vida.'],
                ['Salmos 147:1', 'antigo', 'salmos', 147, '1', 'Louvai ao Senhor, porque é bom cantar louvores ao nosso Deus; isto é agradável e decoroso.', 'A Escritura descreve o louvor como bom, belo e apropriado ao povo de Deus.'],
                ['Salmos 149:1', 'antigo', 'salmos', 149, '1', 'Louvai ao Senhor. Cantai ao Senhor um cântico novo, e o seu louvor na congregação dos santos.', 'O louvor tem dimensão congregacional e fortalece a identidade do povo santo.'],
                ['Isaías 25:1', 'antigo', 'isaias', 25, '1', 'Ó Senhor, tu és o meu Deus; exaltar-te-ei e louvarei o teu nome.', 'Isaías liga louvor à confiança nas obras fiéis e maravilhosas de Deus.'],
                ['Isaías 43:21', 'antigo', 'isaias', 43, '21', 'Esse povo que formei para mim, para que publicasse o meu louvor.', 'Deus forma seu povo para anunciar sua glória e viver para seu louvor.'],
                ['2 Crônicas 20:21', 'antigo', '2-cronicas', 20, '21', 'Louvai ao Senhor, porque a sua benignidade dura para sempre.', 'Judá louva antes da batalha, confessando a misericórdia permanente de Deus.'],
                ['Lucas 2:13-14', 'novo', 'lucas', 2, '13-14', 'Glória a Deus nas maiores alturas, paz na terra entre os homens a quem ele quer bem.', 'No nascimento de Cristo, o louvor angelical anuncia a glória de Deus e a paz messiânica.'],
                ['Lucas 19:37', 'novo', 'lucas', 19, '37', 'Toda a multidão dos discípulos passou a louvar a Deus em alta voz por todos os milagres que tinham visto.', 'Os discípulos louvam a Deus ao reconhecerem as obras de Cristo.'],
                ['Atos 16:25', 'novo', 'atos', 16, '25', 'Perto da meia-noite, Paulo e Silas oravam e cantavam hinos a Deus.', 'Mesmo presos, Paulo e Silas mostram que o louvor pode florescer no sofrimento.'],
                ['Romanos 11:36', 'novo', 'romanos', 11, '36', 'Porque dele, e por meio dele, e para ele são todas as coisas. A ele seja a glória para sempre.', 'A teologia de Paulo desemboca em doxologia: tudo existe para a glória de Deus.'],
                ['Efésios 1:6', 'novo', 'efesios', 1, '6', 'Para louvor da glória da sua graça, que ele nos concedeu gratuitamente no Amado.', 'A salvação em Cristo tem como finalidade o louvor da graça de Deus.'],
                ['Efésios 5:19', 'novo', 'efesios', 5, '19', 'Falando entre vós com salmos, hinos e cânticos espirituais, cantando e salmodiando ao Senhor no coração.', 'A vida cheia do Espírito se expressa em cânticos que edificam a igreja e honram o Senhor.'],
                ['Colossenses 3:16', 'novo', 'colossenses', 3, '16', 'Habite ricamente em vós a palavra de Cristo; instruí-vos e aconselhai-vos com salmos, hinos e cânticos espirituais.', 'Louvor cristão deve ser moldado pela Palavra de Cristo e servir à edificação mútua.'],
                ['Hebreus 13:15', 'novo', 'hebreus', 13, '15', 'Ofereçamos sempre, por meio dele, a Deus sacrifício de louvor.', 'O louvor cristão é oferecido a Deus por meio de Cristo como fruto de lábios confessos.'],
                ['Tiago 5:13', 'novo', 'tiago', 5, '13', 'Está alguém alegre? Cante louvores.', 'Tiago apresenta o louvor como resposta adequada à alegria recebida de Deus.'],
                ['1 Pedro 2:9', 'novo', '1-pedro', 2, '9', 'Vós sois raça eleita, sacerdócio real, nação santa, povo de propriedade exclusiva de Deus.', 'A identidade da igreja inclui proclamar as virtudes daquele que nos chamou das trevas para a luz.'],
                ['Apocalipse 5:12', 'novo', 'apocalipse', 5, '12', 'Digno é o Cordeiro que foi morto de receber poder, riqueza, sabedoria, força, honra, glória e louvor.', 'O louvor celestial se concentra no Cordeiro morto e vitorioso.'],
                ['Apocalipse 19:5', 'novo', 'apocalipse', 19, '5', 'Louvai o nosso Deus, todos vós, os seus servos, os que o temeis, pequenos e grandes.', 'No fim da história, todos os servos de Deus são chamados ao louvor.'],
            ],
        ];

        return array_map(fn ($item) => [
            'referencia' => $item[0],
            'testamento' => $item[1],
            'livro_slug' => $item[2],
            'capitulo' => $item[3],
            'versos' => $item[4],
            'texto' => $item[5],
            'motivo' => $item[6] ?? 'Uma passagem importante para estudar este tema com contexto bíblico.',
        ], $map[$slug] ?? $map['fe']);
    }

    private function keywordsFor(string $term): string
    {
        return 'versículos sobre '.$term.', versiculos sobre '.$term.', '.$term.' na Bíblia, estudo bíblico sobre '.$term.', explicação bíblica sobre '.$term;
    }

    private function extractJson(string $content): string
    {
        $content = trim($content);
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
        }

        return trim($content);
    }
}
