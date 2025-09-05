<?php

namespace App\Services;

use App\Models\BibleExplanation;
use App\Services\Ai\OpenAiClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookContextService
{
    protected OpenAiClient $openAiClient;

    public function __construct(OpenAiClient $openAiClient)
    {
        $this->openAiClient = $openAiClient;
    }

    /**
     * Get book context explanation
     */
    public function getBookContext(string $testament, string $book): array
    {
        // Verificar se já existe no banco de dados
        $existing = BibleExplanation::where('testament', $testament)
            ->where('book', $book)
            ->whereNull('chapter') // Contexto do livro não tem capítulo
            ->whereNull('verses')  // Nem versículos
            ->first();

        if ($existing) {
            return [
                'context' => $existing->explanation_text,
                'origin' => 'database',
                'id' => $existing->id,
            ];
        }

        // Gerar novo contexto usando IA
        $context = $this->generateBookContext($testament, $book);
        
        // Salvar no banco de dados
        $explanation = BibleExplanation::create([
            'testament' => $testament,
            'book' => $book,
            'chapter' => null,
            'verses' => null,
            'explanation_text' => $context,
            'source' => 'gpt-5', // Adicionando a fonte da explicação
        ]);

        return [
            'context' => $context,
            'origin' => 'ai_generated',
            'id' => $explanation->id,
        ];
    }

    /**
     * Generate book context using AI
     */
    protected function generateBookContext(string $testament, string $book): string
    {
        $prompt = $this->buildBookContextPrompt($testament, $book);
        
        try {
            // Usando formato JSON para economizar tokens
            $messages = [
                ['role' => 'system', 'content' => 'Você é um especialista em análise bíblica. Responda APENAS em formato JSON válido.'],
                ['role' => 'user', 'content' => $prompt . "\n\nPor favor, responda em formato JSON válido com a estrutura solicitada."]
            ];
            $response = $this->openAiClient->chat($messages, 4000);
            return $response;
        } catch (\Exception $e) {
            Log::error('Error generating book context with AI', [
                'testament' => $testament,
                'book' => $book,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Build comprehensive prompt for book context analysis
     */
    protected function buildBookContextPrompt(string $testament, string $book): string
    {
        $testamentName = $testament === 'antigo' ? 'Antigo Testamento' : 'Novo Testamento';
        
        return "
Você é um teólogo e exegeta bíblico especializado. Sua tarefa é fornecer uma análise completa e profunda do contexto do livro bíblico de {$book} ({$testamentName}).

IMPORTANTE: Responda APENAS em formato JSON válido seguindo exatamente esta estrutura:

{
  \"sections\": [
    {
      \"id\": \"identificacao-genero\",
      \"title\": \"Identificação do Gênero Literário\",
      \"content\": \"Identifique e explique detalhadamente o gênero literário predominante do livro de {$book}. Inclua características específicas do gênero, como isso influencia a interpretação, subgêneros presentes e comparação com outros livros do mesmo gênero na Bíblia.\"
    },
    {
      \"id\": \"contexto-historico\",
      \"title\": \"Contexto Histórico e Cultural\",
      \"subsections\": [
        {
          \"title\": \"Período Histórico\",
          \"content\": \"Descreva o período histórico em que o livro foi escrito e os eventos que retrata.\"
        },
        {
          \"title\": \"Contexto Político\",
          \"content\": \"Analise a situação política da época, incluindo impérios, reis e estruturas de poder.\"
        },
        {
          \"title\": \"Contexto Social e Cultural\",
          \"content\": \"Examine os costumes, tradições, estrutura social e práticas culturais relevantes.\"
        },
        {
          \"title\": \"Contexto Religioso\",
          \"content\": \"Descreva o ambiente religioso, práticas de culto, e questões teológicas da época.\"
        }
      ]
    },

    {
      \"id\": \"autoria-intencao\",
      \"title\": \"Autoria e Intenção do Escritor\",
      \"subsections\": [
        {
          \"title\": \"Questões de Autoria\",
          \"content\": \"Discuta a autoria tradicional e as evidências internas e externas.\"
        },
        {
          \"title\": \"Data de Composição\",
          \"content\": \"Analise quando o livro foi provavelmente escrito e as evidências para essa datação.\"
        },
        {
          \"title\": \"Propósito e Intenção\",
          \"content\": \"Explique por que o livro foi escrito e qual era a intenção do autor.\"
        },
        {
          \"title\": \"Audiência Original\",
          \"content\": \"Identifique para quem o livro foi originalmente escrito e suas circunstâncias.\"
        }
      ]
    },

    {
      \"id\": \"mensagem-fe\",
      \"title\": \"Mensagem de Fé Transmitida\",
      \"subsections\": [
        {
          \"title\": \"Temas Teológicos Principais\",
          \"content\": \"Identifique e explique os principais temas teológicos do livro.\"
        },
        {
          \"title\": \"Mensagem Central\",
          \"content\": \"Resuma a mensagem central que o livro comunica sobre Deus, fé e vida cristã.\"
        },
        {
          \"title\": \"Lições Espirituais\",
          \"content\": \"Destaque as principais lições espirituais e práticas que o livro oferece.\"
        }
      ]
    },

    {
      \"id\": \"relacao-biblia\",
      \"title\": \"Relação com Toda a Bíblia e Progresso da Revelação\",
      \"subsections\": [
        {
          \"title\": \"Lugar no Cânon\",
          \"content\": \"Explique como o livro se encaixa na estrutura geral da Bíblia.\"
        },
        {
          \"title\": \"Contribuição para a Revelação Progressiva\",
          \"content\": \"Analise como o livro contribui para o desenvolvimento da revelação divina.\"
        },
        {
          \"title\": \"Conexões com Outros Livros\",
          \"content\": \"Identifique conexões temáticas e textuais com outros livros bíblicos.\"
        }
      ]
    },

    {
      \"id\": \"doutrinas-presentes\",
      \"title\": \"Análise das Doutrinas Presentes\",
      \"subsections\": [
        {
          \"title\": \"Doutrina de Deus\",
          \"content\": \"Como o livro revela aspectos do caráter e natureza de Deus.\"
        },
        {
          \"title\": \"Cristologia\",
          \"content\": \"Elementos cristológicos presentes ou prefigurados no livro.\"
        },
        {
          \"title\": \"Soteriologia\",
          \"content\": \"Aspectos relacionados à salvação apresentados no livro.\"
        },
        {
          \"title\": \"Outras Doutrinas Relevantes\",
          \"content\": \"Outras doutrinas importantes desenvolvidas ou mencionadas no livro.\"
        }
      ]
    },

    {
      \"id\": \"leitura-canonica\",
      \"title\": \"Leitura Canônica e Papel no Plano Divino\",
      \"subsections\": [
        {
          \"title\": \"Unidade Canônica\",
          \"content\": \"Como o livro contribui para a unidade temática da Escritura.\"
        },
        {
          \"title\": \"Papel no Plano Redentor\",
          \"content\": \"Como o livro se encaixa no plano redentor de Deus.\"
        },
        {
          \"title\": \"Elementos Tipológicos e Proféticos\",
          \"content\": \"Tipos, símbolos e profecias presentes que apontam para Cristo ou o futuro.\"
        },
        {
          \"title\": \"Aplicação Contemporânea\",
          \"content\": \"Como os princípios do livro se aplicam à vida cristã hoje.\"
        }
      ]
    }
  ]
}

Seja abrangente, acadêmico, mas acessível. Use linguagem clara e forneça exemplos específicos do texto bíblico quando apropriado. Mantenha uma perspectiva evangélica conservadora, respeitando a autoridade e inspiração das Escrituras. RESPONDA APENAS COM O JSON VÁLIDO, SEM TEXTO ADICIONAL.
";
    }
}
