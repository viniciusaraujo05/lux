<?php

namespace App\Services;

use App\Models\BibleExplanation;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BibleExplanationService
{
    /**
     * Get an explanation from the database or generate a new one using the API.
     *
     * @param  string  $testament
     * @param  string  $book
     * @param  int  $chapter
     * @param  string|null  $verses
     * @return array
     */
    public function getExplanation($testament, $book, $chapter, $verses = null)
    {
        // Normalize the verses string (sort and remove duplicates)
        if ($verses) {
            $versesArray = explode(',', $verses);
            $versesArray = array_map('intval', $versesArray);
            sort($versesArray);
            $versesArray = array_unique($versesArray);
            $verses = implode(',', $versesArray);
        }

        // Find in database
        $explanation = BibleExplanation::where([
            'testament' => $testament,
            'book' => $book,
            'chapter' => $chapter,
            'verses' => $verses,
        ])->first();

        // If found, increment the access counter and return
        if ($explanation) {
            $explanation->incrementAccessCount();

            return [
                'id' => $explanation->id,
                'origin' => 'cache',
                'explanation' => $explanation->explanation_text,
                'source' => $explanation->source,
            ];
        }

        // Otherwise, generate a new explanation
        $explanationText = $this->generateExplanationViaAPI($testament, $book, $chapter, $verses);

        // Save to database
        $newExplanation = BibleExplanation::create([
            'testament' => $testament,
            'book' => $book,
            'chapter' => $chapter,
            'verses' => $verses,
            'explanation_text' => $explanationText,
            'source' => 'gpt-4', // Adjust according to the API you're using
            'access_count' => 1,
        ]);

        return [
            'id' => $newExplanation->id,
            'origin' => 'api',
            'explanation' => $explanationText,
            'source' => 'gpt-4', // Adjust according to the API you're using
        ];
    }

    /**
     * Generate an explanation using an AI API (example implementation).
     *
     * @param  string  $testament
     * @param  string  $book
     * @param  int  $chapter
     * @param  string|null  $verses
     * @return string
     */
    private function generateExplanationViaAPI($testament, $book, $chapter, $verses = null)
    {
        // Use a chave da Perplexity (configure em config/services.php)
        $apiKey = config('services.perplexity.api_key');

        try {
            $prompt = $this->buildPrompt($testament, $book, $chapter, $verses);

            $model = config('services.perplexity.model', 'pplx-7b-online'); // Ou 'sonar-medium-online'

            logger('Modelo sendo usado: ' . $model);

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Você é um especialista em teologia e estudos bíblicos, capaz de explicar versículos e capítulos com contexto histórico, significado teológico e aplicação prática. Forneça uma explicação completa e detalhada com toda a formatação HTML necessária. NÃO responda com mensagens introdutórias, apenas comece com a explicação completa. Inclua o texto do versículo, contexto histórico, análise teológica e aplicações práticas.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ];

            $payload = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.6,
                'max_tokens' => 4000,
                'presence_penalty' => 0.1,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)
                ->post('https://api.perplexity.ai/chat/completions', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $content = $responseData['choices'][0]['message']['content'] ?? '';

                if (
                    strlen($content) < 200 ||
                    str_contains($content, 'permita-me') ||
                    str_contains($content, 'vou explicar') ||
                    str_contains($content, 'alguns instantes')
                ) {

                    Log::warning('Resposta muito curta ou apenas introdutória, tentando novamente');

                    $retryMessages = [
                        [
                            'role' => 'system',
                            'content' => 'Você é um especialista em teologia e estudos bíblicos. FORNEÇA IMEDIATAMENTE uma explicação COMPLETA e DETALHADA do texto bíblico, sem mensagens introdutórias. Inclua o texto do versículo, contexto histórico, análise teológica e aplicações práticas. Use formatação HTML conforme solicitado no prompt. NÃO diga que vai explicar ou que precisa de tempo, simplesmente forneça a explicação completa.',
                        ],
                        [
                            'role' => 'user',
                            'content' => 'IMPORTANTE: Forneça uma explicação completa e detalhada, não apenas uma introdução. ' . $prompt,
                        ],
                    ];

                    $retryPayload = [
                        'model' => $model,
                        'messages' => $retryMessages,
                        'temperature' => 0.5,
                        'max_tokens' => 4000,
                        'presence_penalty' => 0.2,
                        'frequency_penalty' => 0.2,
                    ];

                    $retryResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])->timeout(120)
                        ->post('https://api.perplexity.ai/chat/completions', $retryPayload);

                    if ($retryResponse->successful()) {
                        $retryData = $retryResponse->json();
                        $retryContent = $retryData['choices'][0]['message']['content'] ?? '';
                        $retryContent = preg_replace('/^```html[\r\n]+|```$/i', '', trim($retryContent));
                        $retryContent = preg_replace('/^```[\r\n]+|```$/i', '', trim($retryContent));
                        // Remove artefatos como [2][4) do final do texto
                        $retryContent = preg_replace('/\[\d+\]\[\d+\)[\s]*$/', '', $retryContent);
                        return trim($retryContent);
                    }
                }

                // Remove blocos de código markdown (```html ... ```)
                $content = preg_replace('/^```html[\r\n]+|```$/i', '', trim($content));
                $content = preg_replace('/^```[\r\n]+|```$/i', '', trim($content));
                // Remove blocos de código markdown (```html ... ```)
                $content = preg_replace('/^```html[\r\n]+|```$/i', '', trim($content));
                $content = preg_replace('/^```[\r\n]+|```$/i', '', trim($content));
                // Remove artefatos como [2][4) do final do texto
                $content = preg_replace('/\[\d+\]\[\d+\)[\s]*$/', '', $content);
                return trim($content);
            } else {
                if (str_contains($response->body(), 'cURL error 28')) {
                    Log::error('Perplexity API Timeout', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    throw new \Exception('A requisição para a API demorou demais para responder. Tente novamente em instantes.');
                }
                Log::error('Perplexity API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Error calling Perplexity API: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Exception when generating explanation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->generateFallbackExplanation($testament, $book, $chapter, $verses);
        }
    }


    /**
     * Build the prompt for the AI API.
     *
     * @param  string  $testament
     * @param  string  $book
     * @param  int  $chapter
     * @param  string|null  $verses
     * @return string
     */
    private function buildPrompt($testament, $book, $chapter, $verses = null)
    {
        $isFullChapter = $verses === null;

        $passageText = $isFullChapter
            ? "o capítulo {$chapter} do livro de {$book} ({$testament} Testamento)"
            : "os versículos {$verses} do capítulo {$chapter} do livro de {$book} ({$testament} Testamento)";

        // Instruções específicas para diferentes casos
        if ($isFullChapter) {
            $specificInstructions =
                "Resuma de forma clara e objetiva o capítulo, sem detalhar versículo por versículo. Siga as instruções:\n"
                . "1. Diga em poucas frases sobre o que esse capítulo fala (tema central e propósito).\n"
                . "2. Liste os principais pontos e acontecimentos do capítulo, em tópicos.\n"
                . "3. Explique brevemente o contexto histórico do capítulo e do livro ao qual ele pertence.\n"
                . "4. Evite repetições, rodeios ou explicações longas. Seja direto e conciso.\n"
                . "5. NÃO inclua introduções, despedidas, nem explique versículo por versículo.\n"
                . "6. Responda apenas com HTML simples, usando <ul> para tópicos e <p> para textos.";
        } else {
            // Verifica se é um único versículo
            $isSingleVerse = preg_match('/^\d+$/', $verses);
            $isDoubleVerse = preg_match('/^\d+-\d+$/', $verses) && (intval(explode('-', $verses)[1]) - intval(explode('-', $verses)[0]) === 1);

            if ($isSingleVerse) {
                $specificInstructions =
                    "1. Separe as principais frases ou palavras-chave do versículo e explique cada uma individualmente, de forma didática.\n"
                    . "2. Apresente essas explicações em uma lista HTML (<ul class='key-phrases'>) com cada frase/palavra-chave como um <li>.\n"
                    . '3. Considere o contexto do capítulo e relacione cada parte à mensagem global.';
            } elseif ($isDoubleVerse) {
                $specificInstructions =
                    "1. Para cada versículo, separe as principais frases ou palavras-chave e explique cada uma individualmente, de forma didática.\n"
                    . "2. Apresente as explicações de cada versículo em uma lista HTML (<ul class='key-phrases'>) separada para cada versículo.\n"
                    . '3. Considere o contexto do capítulo e relacione cada parte à mensagem global.';
            } else {
                $specificInstructions =
                    "1. Analise detalhadamente cada versículo listado ({$verses}), considerando o contexto do capítulo.\n"
                    . "2. Comente TODOS os versículos individualmente, explicando significado, relevância e conexões.\n"
                    . '3. Certifique-se de abordar cada versículo ou intervalo explicitamente, sem omitir nenhum.';
            }
        }

        $originalTextInstruction =
            "Se o texto original (hebraico, aramaico ou grego) e sua tradução literal estiverem disponíveis, inclua-os na secção 'Texto Original e Tradução'."
            . ' Forneça a transliteração (se aplicável) e, opcionalmente, uma análise de palavras-chave no idioma original.'
            . ' Se o texto original ou tradução não estiver disponível, omita completamente essa secção.';

        // Não incluir instrução de texto original no resumo de capítulo
        $originalTextInstructionForPrompt = ($isFullChapter ? '' : $originalTextInstruction);

        return <<<EOD
    IMPORTANTE: Forneça IMEDIATAMENTE uma explicação COMPLETA e DETALHADA, sem mensagens introdutórias. Comece diretamente com a explicação, usando a estrutura HTML abaixo.
    
    Você é um teólogo cristão experiente, especialista em exegese bíblica, com profundo conhecimento dos textos originais, contexto histórico e aplicações práticas.
    
    - Escreva EXCLUSIVAMENTE em português brasileiro, num tom respeitoso, acolhedor e motivador, acessível a leigos, jovens cristãos e buscadores espirituais, sem perder profundidade teológica.
    - Os versículos devem ser EXATAMENTE os solicitados, sem substituí-los por outros ou omiti-los, e SEMPRE em português brasileiro.
    - Baseie-se em teólogos confiáveis (John Stott, R.C. Sproul, F.F. Bruce, Martyn Lloyd-Jones, Craig Keener, Hernandes Dias Lopes, Augustus Nicodemus).
    - Mantenha fidelidade às Escrituras e ao contexto original, evitando interpretações subjetivas ou místicas.
    - Explique {$passageText} para glorificar a Palavra de Deus, oferecendo exegese profunda, detalhada e acessível.
    - Siga estas etapas:
        1. Analise o significado original no idioma bíblico, incluindo análise etimológica de palavras-chave.
        2. Explique o contexto histórico, cultural, geográfico e social.
        3. Aborde o contexto literário, género, estrutura e relação com o livro.
        4. Apresente implicações teológicas profundas, doutrinas relacionadas e temas espirituais.
        5. Cite interpretações históricas de teólogos importantes.
        6. Forneça aplicações práticas detalhadas e relevantes para a vida contemporânea.
        7. Relacione com outros textos bíblicos relevantes.
    INSTRUÇÕES RÍGIDAS:
        - Use apenas informações de fontes acadêmicas e comentaristas bíblicos clássicos reconhecidos (Edersheim, Keener, Bruce, N.T. Wright, NICOT/NICNT, Word Biblical Commentary, etc.).
        - NÃO utilize especulações, tradições populares, interpretações modernas não fundamentadas ou informações de blogs/sites não acadêmicos.
        - NÃO extrapole além do texto, contexto histórico-cultural e doutrinas reconhecidas.
        - Seja detalhista e preciso em cada ponto, justificando cada afirmação com base em contexto histórico, linguístico e teológico.
        - Sempre que possível, explique termos originais (hebraico), costumes da época, contexto político-social e conexões com outros textos bíblicos.
        - Não repita informações entre as seções.
    
    {$specificInstructions}
    
    {$originalTextInstructionForPrompt}
    
    Siga EXATAMENTE esta estrutura HTML na resposta (não altere nem adicione comentários):
    
    <div class='bible-explanation'>
        <h2 class='main-title'>Explorando {$book} {$chapter}" . ($verses ? ":$verses" : '') . "</h2>
        <h3 class='section-title'>Texto Bíblico</h3>
        <p class='bible-text'>[Texto bíblico em português, versão fiel como Almeida Revista e Atualizada ou Nova Versão Internacional]</p>
        <h3 class='section-title'>Análise de Expressões Importantes</h3>
        <ul class='key-phrases'>
            <li><strong>"[Frase ou palavra-chave 1]"</strong>: [explicação detalhada]</li>
            <li><strong>"[Frase ou palavra-chave 2]"</strong>: [explicação detalhada]</li>
            <li><strong>"[Frase ou palavra-chave 3]"</strong>: [explicação detalhada]</li>
        </ul>
        <h3 class='section-title'>Texto Original e Tradução</h3>
        <div class='original-translation'>
            <div class='original-text'>[Texto original]</div>
            <div class='transliteration'>[Transliteração, se aplicável]</div>
            <div class='translation'>[Tradução literal]</div>
            <div class='word-analysis'>[Análise de palavras-chave, opcional]</div>
        </div>
        <h3 class='section-title'>Contexto Histórico e Cultural</h3>
        <p>[Contexto histórico, cultural, geográfico e literário detalhado]</p>
        <h3 class='section-title'>Análise Teológica Detalhada</h3>
        <p>[Significado teológico e espiritual, com interpretações de teólogos e doutrinas relacionadas]</p>
        <h3 class='section-title'>Aplicações Práticas</h3>
        <ul class='applications'>
            <li>[Aplicação prática 1]</li>
            <li>[Aplicação prática 2]</li>
            <li>[Aplicação prática 3]</li>
            <li>[Aplicação prática 4]</li>
        </ul>
        <h3 class='section-title'>Conexões com Outros Textos Bíblicos</h3>
        <ul class='connections'>
            <li>[Conexão 1]</li>
            <li>[Conexão 2]</li>
            <li>[Conexão 3]</li>
            <li>[Conexão 4]</li>
        </ul>
        <h3 class='section-title'>Perspectivas Teológicas</h3>
        <p>[Perspectivas de diferentes tradições cristãs, mantendo ortodoxia bíblica]</p>
        <div class='reflection'>
            <blockquote>
                <p><em>[Reflexão final inspiradora]</em></p>
            </blockquote>
        </div>
    </div>
    EOD;
    }

    /**
     * Generate a fallback explanation when the API is not available.
     *
     * @param  string  $testament
     * @param  string  $book
     * @param  int  $chapter
     * @param  string|null  $verses
     * @return string
     */
    private function generateFallbackExplanation($testament, $book, $chapter, $verses = null)
    {
        $versesText = $verses ? ":$verses" : '';
        $bookLabel = ucfirst($book);
        $testamentLabel = ucfirst($testament);

        return '<div class="bible-explanation">'
            . "<h2 class='main-title'>Explorando {$bookLabel} {$chapter}{$versesText}</h2>"

            . "<h3 class='section-title'>Texto Bíblico</h3>"
            . "<p class='bible-text'>Texto não disponível nesta demonstração. Em um ambiente de produção, você veria aqui o texto bíblico completo.</p>"

            . "<h3 class='section-title'>Texto Original e Tradução</h3>"
            . "<div class='original-translation'>"
            . "    <div class='original-text'>Texto original não disponível nesta demonstração. Em um ambiente de produção, você veria aqui o texto no idioma original.</div>"
            . "    <div class='transliteration'>Transliteração não disponível nesta demonstração.</div>"
            . "    <div class='translation'>Tradução não disponível nesta demonstração. Em um ambiente de produção, você veria aqui a tradução literal do texto.</div>"
            . "    <div class='word-analysis'>Análise de palavras-chave não disponível nesta demonstração.</div>"
            . '</div>'

            . "<h3 class='section-title'>Contexto Histórico e Cultural</h3>"
            . "<p>Este livro faz parte do {$testamentLabel} Testamento e tem importância significativa na história bíblica. "
            . "O capítulo {$chapter} foi escrito em um período importante da história de Israel, refletindo as realidades culturais, "
            . 'sociais e religiosas da época. O autor aborda temas relevantes para o contexto original e para os leitores atuais, '
            . 'estabelecendo princípios duradouros da revelação divina.</p>'

            . "<h3 class='section-title'>Análise Teológica Detalhada</h3>"
            . '<p>Este texto revela aspectos importantes da natureza de Deus, Seu plano redentor e Sua relação com a humanidade. '
            . 'Teólogos ao longo da história da igreja têm destacado a profundidade deste texto e suas implicações para a fé cristã. '
            . 'A passagem contém verdades fundamentais que se conectam com temas centrais das Escrituras.</p>'

            . "<h3 class='section-title'>Aplicações Práticas</h3>"
            . "<ul class='applications'>"
            . '<li>Fé e confiança em Deus são fundamentais para uma vida espiritual plena, especialmente em momentos de incerteza</li>'
            . '<li>A obediência aos mandamentos divinos traz bênçãos e sabedoria para nossas decisões cotidianas</li>'
            . '<li>O relacionamento com Deus deve ser cultivado diariamente através da oração, meditação na Palavra e adoração</li>'
            . '<li>Podemos aplicar os princípios deste texto em nossos relacionamentos, trabalho e vida comunitária</li>'
            . '</ul>'

            . "<h3 class='section-title'>Conexões com outros textos bíblicos</h3>"
            . "<ul class='connections'>"
            . '<li>Este texto se relaciona com os ensinamentos de Jesus em Mateus 22:37-40 sobre o amor a Deus e ao próximo, mostrando como o amor é o cumprimento da lei</li>'
            . '<li>Há paralelos com os Salmos que exaltam a fidelidade e a misericórdia divina, especialmente o Salmo 119 que celebra a Palavra de Deus</li>'
            . '<li>O livro de Romanos desenvolve muitos dos temas teológicos presentes nesta passagem, aprofundando seu significado</li>'
            . '<li>Há conexões com o livro de Apocalipse, que mostra o cumprimento final dos propósitos de Deus</li>'
            . '</ul>'

            . "<h3 class='section-title'>Perspectivas Teológicas</h3>"
            . '<p>Este texto tem sido interpretado de diversas formas ao longo da história da igreja, com diferentes ênfases '
            . 'dependendo da tradição teológica. Mantendo a fidelidade à ortodoxia bíblica, podemos apreciar as contribuições '
            . 'de diferentes perspectivas para uma compreensão mais rica e completa da passagem.</p>'

            . "<h3 class='section-title'>Referências Bibliográficas</h3>"
            . "<ul class='references'>"
            . '<li>Comentário Bíblico Expositivo - Warren W. Wiersbe</li>'
            . '<li>Manual Bíblico de Halley - Henry H. Halley</li>'
            . '<li>Novo Comentário Bíblico - D. Guthrie, J. A. Motyer</li>'
            . '<li>Comentário Bíblico Moody - Charles Ryrie</li>'
            . '<li>Comentário Histórico-Cultural da Bíblia - Craig S. Keener</li>'
            . '</ul>'

            . "<div class='reflection'>"
            . '<blockquote>'
            . '<p><em>Que possamos refletir sobre estas palavras sagradas e aplicá-las em nossa vida diária, '
            . 'crescendo em sabedoria e na graça de nosso Senhor.</em></p>'
            . '</blockquote>'
            . '</div>'

            . "<p class='bible-explanation-note'><em>Nota: Esta é uma explicação genérica. "
            . 'Para uma análise mais profunda e personalizada, por favor configure uma chave de API válida do OpenAI no sistema.</em></p>'

            . '</div>';
    }
}
