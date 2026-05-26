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

            return [
                'topic' => $topic,
                'study' => is_array($decoded) ? $decoded : $this->fallbackStudy($topic),
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
        $content = $client->chat($messages, 3200);
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
            'introducao' => ucfirst($term).' na Bíblia aponta para uma vida orientada pela Palavra de Deus. Este tema pode ser estudado observando o contexto de cada passagem e como ela se conecta com a fé, a obediência e a esperança cristã.',
            'significado_biblico' => 'Biblicamente, '.$term.' não é apenas uma ideia abstrata; é uma realidade vivida diante de Deus, revelada nas Escrituras e aplicada no relacionamento com o Senhor e com o próximo.',
            'aplicacao_pratica' => 'Leia os textos abaixo com calma, observe o contexto do capítulo e abra a explicação de cada versículo para aprofundar seu estudo.',
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
            'versiculos' => count($verses) > 0 ? array_slice($verses, 0, 12) : $fallback['versiculos'],
        ];
    }

    private function seedReferencesFor(string $slug): array
    {
        $map = [
            'fe' => [['Hebreus 11:1','novo','hebreus',11,'1','Ora, a fé é a certeza daquilo que esperamos e a prova das coisas que não vemos.'], ['Romanos 10:17','novo','romanos',10,'17','Consequentemente, a fé vem por se ouvir a mensagem, e a mensagem é ouvida mediante a palavra de Cristo.'], ['Marcos 11:24','novo','marcos',11,'24','Tudo o que vocês pedirem em oração, creiam que já o receberam, e assim lhes sucederá.']],
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
            'louvor' => [['Salmos 150:6','antigo','salmos',150,'6','Tudo o que tem vida louve o Senhor.'], ['Salmos 100:2','antigo','salmos',100,'2','Prestem culto ao Senhor com alegria.'], ['Hebreus 13:15','novo','hebreus',13,'15','Ofereçamos continuamente a Deus um sacrifício de louvor.']],
        ];

        return array_map(fn ($item) => [
            'referencia' => $item[0],
            'testamento' => $item[1],
            'livro_slug' => $item[2],
            'capitulo' => $item[3],
            'versos' => $item[4],
            'texto' => $item[5],
            'motivo' => 'Uma passagem importante para estudar este tema com contexto bíblico.',
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
