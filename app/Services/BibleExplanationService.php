<?php

namespace App\Services;

use App\Models\BibleExplanation;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client;

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
                'origin' => 'cache',
                'explanation' => $explanation->explanation_text,
                'source' => $explanation->source,
            ];
        }

        // Otherwise, generate a new explanation
        $explanationText = $this->generateExplanationViaAPI($testament, $book, $chapter, $verses);

        // Save to database
        BibleExplanation::create([
            'testament' => $testament,
            'book' => $book,
            'chapter' => $chapter,
            'verses' => $verses,
            'explanation_text' => $explanationText,
            'source' => 'gpt-4', // Adjust according to the API you're using
            'access_count' => 1,
        ]);

        return [
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
        // Check for API key availability
        $apiKey = config('services.openai.api_key');

        // Check if the API key looks valid (not empty and not starting with 'sk-your-')
        if (empty($apiKey) || strpos($apiKey, 'sk-your-') === 0) {
            Log::warning('Invalid or missing OpenAI API key, using fallback content');

            return $this->generateFallbackExplanation($testament, $book, $chapter, $verses);
        }

        try {
            // Get prompt for the AI
            $prompt = $this->buildPrompt($testament, $book, $chapter, $verses);

            // Set up the OpenAI client with API key from config
            $client = OpenAI::client($apiKey);

            // Create the chat completion request with GPT-3.5 Turbo
            $response = $client->chat()->create([
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert in theology and biblical studies, capable of explaining verses and chapters with historical context, theological meaning, and practical application.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            // Extract the explanation from the response
            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('Exception when generating explanation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Provide fallback content instead of error message
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
    
        // Format the passage description
        $passageText = $isFullChapter
            ? "o capítulo {$chapter} do livro de {$book} ({$testament} Testamento)"
            : "os versículos {$verses} do capítulo {$chapter} do livro de {$book} ({$testament} Testamento)";
    
        // Specific instructions for full chapter or selected verses
        $specificInstructions = $isFullChapter
            ? "Analise o contexto geral do capítulo, fornecendo uma visão abrangente de seus temas e propósito. "
              . "Em seguida, explique cada versículo como um comentário bíblico, destacando seu significado e conexão com o todo. "
              . "Conclua com uma síntese teológica que una os principais ensinamentos e aplicações do capítulo."
            : "Analise detalhadamente cada versículo listado ({$verses}), considerando o contexto do capítulo como um todo. "
              . "Se forem múltiplos versículos (ex.: '1-3' ou '1,3,5'), comente TODOS individualmente, explicando seu significado e relevância. "
              . "Certifique-se de abordar cada versículo ou intervalo explicitamente, sem omitir nenhum.";
    
        // Conditional instruction for original text and translation
        $originalTextInstruction = "Se o texto original (hebraico, aramaico ou grego) e sua tradução literal estiverem disponíveis, inclua-os na seção 'Texto Original e Tradução'. "
                                 . "Forneça a transliteração (se aplicável) e, opcionalmente, uma análise de palavras-chave no idioma original. "
                                 . "Se o texto original ou tradução não estiver disponível, OMITA completamente a seção 'Texto Original e Tradução'.";
    
        return <<<EOD
    Você é um teólogo cristão experiente, especializado em exegese e hermenêutica bíblica, com profundo conhecimento dos textos originais, contexto histórico e aplicações práticas.
    
    Sua missão é tornar a Palavra de Deus clara, acessível e inspiradora para todos, em linha com o propósito do LuxApp. Adote um tom respeitoso, acolhedor e motivador, escrevendo em português simples para leigos, jovens cristãos e buscadores espirituais, sem comprometer a profundidade teológica.
    
    Baseie-se em teólogos confiáveis, como John Stott, R.C. Sproul, F.F. Bruce, Martyn Lloyd-Jones, Craig Keener, Hernandes Dias Lopes e Augustus Nicodemus. Mantenha fidelidade às Escrituras e ao contexto original, evitando interpretações subjetivas ou místicas.
    
    Explique {$passageText} para glorificar a Palavra de Deus, oferecendo uma exegese e hermenêutica profunda, detalhada e rica, mas ainda assim acessível. Explore:
    
    1. O significado original no idioma bíblico (hebraico, aramaico ou grego), incluindo análise etimológica de palavras-chave
    2. Contexto histórico, cultural, geográfico e social da época, incluindo costumes e práticas relevantes
    3. Contexto literário, incluindo gênero, estrutura e relação com o livro como um todo
    4. Implicações teológicas profundas, doutrinas relacionadas e temas espirituais
    5. Interpretações históricas de teólogos importantes ao longo da história da igreja
    6. Aplicações práticas detalhadas e relevantes para a vida contemporânea
    7. Conexões com outros textos bíblicos, tanto do Antigo quanto do Novo Testamento
    
    {$specificInstructions}
    
    {$originalTextInstruction}
    
    Siga EXATAMENTE esta estrutura em sua resposta, usando HTML:
    
    <div class='bible-explanation'>
        <h2 class='main-title'>Explorando {$book} {$chapter}" . ($verses ? ":$verses" : '') . "</h2>
    
        <h3 class='section-title'>Texto Bíblico</h3>
        <p class='bible-text'>[Insira o texto bíblico em português, usando uma tradução fiel como a Almeida Revista e Atualizada ou Nova Versão Internacional]</p>
    
        <!-- Inclua a seção abaixo SOMENTE se o texto original e/ou tradução estiverem disponíveis -->
        <h3 class='section-title'>Texto Original e Tradução</h3>
        <div class='original-translation'>
            <div class='original-text'>[Insira o texto no idioma original (hebraico, aramaico ou grego)]</div>
            <div class='transliteration'>[Insira a transliteração, se aplicável]</div>
            <div class='translation'>[Insira a tradução literal do texto original]</div>
            <div class='word-analysis'>[Opcional: análise de palavras-chave no idioma original, destacando seu significado]</div>
        </div>
    
        <h3 class='section-title'>Contexto Histórico e Cultural</h3>
        <p>[Explique detalhadamente o contexto histórico, cultural, geográfico e literário do texto, incluindo informações sobre o autor, público original, propósito, situação política e religiosa da época, e costumes relevantes]</p>
    
        <h3 class='section-title'>Análise Teológica Detalhada</h3>
        <p>[Explique profundamente o significado teológico e espiritual do texto, analisando cada aspecto importante. Inclua interpretações de teólogos importantes ao longo da história e doutrinas relacionadas]</p>
        
        <h3 class='section-title'>Aplicações Práticas</h3>
        <ul class='applications'>
            <li>[Aplicação prática 1: como o texto pode ser vivido hoje, com exemplos concretos]</li>
            <li>[Aplicação prática 2: implicações para a vida espiritual, relacionamentos e decisões]</li>
            <li>[Aplicação prática 3: desafios e oportunidades que o texto apresenta]</li>
            <li>[Aplicação prática 4: como aplicar este texto em diferentes contextos de vida]</li>
        </ul>
    
        <h3 class='section-title'>Conexões com Outros Textos Bíblicos</h3>
        <ul class='connections'>
            <li>[Conexão 1: referência bíblica, explicação detalhada e como ela ilumina o texto atual]</li>
            <li>[Conexão 2: referência bíblica, explicação detalhada e como ela complementa o texto atual]</li>
            <li>[Conexão 3: referência bíblica que mostra o cumprimento ou desenvolvimento do tema]</li>
            <li>[Conexão 4: referência bíblica que oferece uma perspectiva adicional ou contraste]</li>
        </ul>
        
        <h3 class='section-title'>Perspectivas Teológicas</h3>
        <p>[Apresente diferentes perspectivas teológicas sobre o texto, de diferentes tradições cristãs, mantendo fidelidade à ortodoxia bíblica]</p>
    
        <div class='reflection'>
            <blockquote>
                <p><em>[Reflexão final inspiradora, conectando o texto à vida espiritual do leitor]</em></p>
            </blockquote>
        </div>
    </div>
    
    Substitua os textos entre colchetes pelo conteúdo real. Mantenha a estrutura HTML exata, mas preencha com explicações relevantes, profundas e inspiradoras. Use linguagem acessível, respeitosa e alinhada com o propósito do LuxApp de iluminar a Palavra de Deus.
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
            ."<h2 class='main-title'>Explorando {$bookLabel} {$chapter}{$versesText}</h2>"

            ."<h3 class='section-title'>Texto Bíblico</h3>"
            ."<p class='bible-text'>Texto não disponível nesta demonstração. Em um ambiente de produção, você veria aqui o texto bíblico completo.</p>"

            ."<h3 class='section-title'>Texto Original e Tradução</h3>"
            ."<div class='original-translation'>"
            ."    <div class='original-text'>Texto original não disponível nesta demonstração. Em um ambiente de produção, você veria aqui o texto no idioma original.</div>"
            ."    <div class='transliteration'>Transliteração não disponível nesta demonstração.</div>"
            ."    <div class='translation'>Tradução não disponível nesta demonstração. Em um ambiente de produção, você veria aqui a tradução literal do texto.</div>"
            ."    <div class='word-analysis'>Análise de palavras-chave não disponível nesta demonstração.</div>"
            .'</div>'

            ."<h3 class='section-title'>Contexto Histórico e Cultural</h3>"
            ."<p>Este livro faz parte do {$testamentLabel} Testamento e tem importância significativa na história bíblica. "
            ."O capítulo {$chapter} foi escrito em um período importante da história de Israel, refletindo as realidades culturais, "
            ."sociais e religiosas da época. O autor aborda temas relevantes para o contexto original e para os leitores atuais, "
            ."estabelecendo princípios duradouros da revelação divina.</p>"

            ."<h3 class='section-title'>Análise Teológica Detalhada</h3>"
            ."<p>Este texto revela aspectos importantes da natureza de Deus, Seu plano redentor e Sua relação com a humanidade. "
            ."Teólogos ao longo da história da igreja têm destacado a profundidade deste texto e suas implicações para a fé cristã. "
            ."A passagem contém verdades fundamentais que se conectam com temas centrais das Escrituras.</p>"
            
            ."<h3 class='section-title'>Aplicações Práticas</h3>"
            ."<ul class='applications'>"
            .'<li>Fé e confiança em Deus são fundamentais para uma vida espiritual plena, especialmente em momentos de incerteza</li>'
            .'<li>A obediência aos mandamentos divinos traz bênçãos e sabedoria para nossas decisões cotidianas</li>'
            .'<li>O relacionamento com Deus deve ser cultivado diariamente através da oração, meditação na Palavra e adoração</li>'
            .'<li>Podemos aplicar os princípios deste texto em nossos relacionamentos, trabalho e vida comunitária</li>'
            .'</ul>'

            ."<h3 class='section-title'>Conexões com outros textos bíblicos</h3>"
            ."<ul class='connections'>"
            .'<li>Este texto se relaciona com os ensinamentos de Jesus em Mateus 22:37-40 sobre o amor a Deus e ao próximo, mostrando como o amor é o cumprimento da lei</li>'
            .'<li>Há paralelos com os Salmos que exaltam a fidelidade e a misericórdia divina, especialmente o Salmo 119 que celebra a Palavra de Deus</li>'
            .'<li>O livro de Romanos desenvolve muitos dos temas teológicos presentes nesta passagem, aprofundando seu significado</li>'
            .'<li>Há conexões com o livro de Apocalipse, que mostra o cumprimento final dos propósitos de Deus</li>'
            .'</ul>'
            
            ."<h3 class='section-title'>Perspectivas Teológicas</h3>"
            ."<p>Este texto tem sido interpretado de diversas formas ao longo da história da igreja, com diferentes ênfases "
            ."dependendo da tradição teológica. Mantendo a fidelidade à ortodoxia bíblica, podemos apreciar as contribuições "
            ."de diferentes perspectivas para uma compreensão mais rica e completa da passagem.</p>"
            
            ."<h3 class='section-title'>Referências Bibliográficas</h3>"
            ."<ul class='references'>"
            .'<li>Comentário Bíblico Expositivo - Warren W. Wiersbe</li>'
            .'<li>Manual Bíblico de Halley - Henry H. Halley</li>'
            .'<li>Novo Comentário Bíblico - D. Guthrie, J. A. Motyer</li>'
            .'<li>Comentário Bíblico Moody - Charles Ryrie</li>'
            .'<li>Comentário Histórico-Cultural da Bíblia - Craig S. Keener</li>'
            .'</ul>'

            ."<div class='reflection'>"
            .'<blockquote>'
            .'<p><em>Que possamos refletir sobre estas palavras sagradas e aplicá-las em nossa vida diária, '
            .'crescendo em sabedoria e na graça de nosso Senhor.</em></p>'
            .'</blockquote>'
            .'</div>'

            ."<p class='bible-explanation-note'><em>Nota: Esta é uma explicação genérica. "
            .'Para uma análise mais profunda e personalizada, por favor configure uma chave de API válida do OpenAI no sistema.</em></p>'

            .'</div>';
    }
}
