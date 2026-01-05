<?php

namespace App\Services\Ai;

class PromptBuilderService
{
    /**
     * Build optimized prompt for verse explanation
     */
    public function buildVerseExplanationPrompt(string $testament, string $book, int $chapter, string $verses): string
    {
        $passageRef = "{$book} {$chapter}:{$verses}";

        return <<<EOD
Analise {$passageRef} ({$testament} Testamento). Retorne JSON válido:

{
  "titulo_principal_e_texto_biblico": {"titulo": "string", "texto": "string"},
  "contexto_detalhado": {
    "introducao": "string",
    "contexto_literario": "string",
    "contexto_historico": "string",
    "contexto_teologico": "string"
  },
  "analise_exegetica": {
    "introducao": "string",
    "analises": [{"verso": "string", "analise": "string"}]
  },
  "explicacao_do_versiculo": {
    "significado_profundo": "string",
    "contexto_original": "string",
    "palavras_chave": ["string"],
    "interpretacao_teologica": "string"
  },
  "temas_principais": {
    "temas": [{"tema": "string", "descricao": "string"}]
  },
  "aplicacao_contemporanea": {
    "pontos_aplicacao": ["string"],
    "perguntas_reflexao": ["string"]
  },
  "referencias_cruzadas": {
    "referencias": [{"passagem": "string", "explicacao": "string"}]
  },
  "interprete_luz_de_cristo": {
    "conexao": "string"
  }
}

Regras: Português BR, conciso (2-3 frases/campo), máx 3-5 itens em arrays, perspectiva evangélica.
EOD;
    }

    /**
     * Build optimized prompt for chapter summary
     */
    public function buildChapterSummaryPrompt(string $testament, string $book, int $chapter): string
    {
        $passageRef = "{$book} {$chapter}";

        return <<<EOD
Analise {$passageRef} ({$testament} Testamento). Retorne JSON válido:

{
  "contexto_geral": {
    "contexto_do_livro": {
      "autor_e_data": "string",
      "audiencia_original": "string",
      "proposito_do_livro": "string",
      "contexto_historico_cultural": "string"
    },
    "contexto_do_capitulo_no_livro": "string"
  },
  "resumo_do_capitulo": "string",
  "estrutura_do_capitulo": {
    "divisoes": [{"secao": "string", "tema": "string"}]
  },
  "temas_principais": ["string"],
  "personagens_importantes": [{"nome": "string", "papel": "string"}],
  "versiculos_chave": [{"versiculo": "string", "razao": "string"}],
  "teologia_do_capitulo": {
    "doutrinas": ["string"],
    "revelacao_de_deus": "string",
    "conexao_crista": "string"
  },
  "aplicacao_pratica": {
    "licoes": ["string"],
    "desafios": ["string"]
  }
}

Regras: Português BR, conciso (2-4 frases/campo), máx 3-5 itens em arrays, perspectiva evangélica.
EOD;
    }

    /**
     * Build optimized prompt for book context
     */
    public function buildBookContextPrompt(string $testament, string $book): string
    {
        $testamentName = $testament === 'antigo' ? 'Antigo Testamento' : 'Novo Testamento';

        return <<<EOD
Analise o livro de {$book} ({$testamentName}). Retorne JSON válido:

{
  "sections": [
    {"id": "identificacao-genero", "title": "Gênero Literário", "content": "string"},
    {"id": "contexto-historico", "title": "Contexto Histórico", "subsections": [
      {"title": "Período Histórico", "content": "string"},
      {"title": "Contexto Político", "content": "string"},
      {"title": "Contexto Social", "content": "string"},
      {"title": "Contexto Religioso", "content": "string"}
    ]},
    {"id": "autoria-intencao", "title": "Autoria e Intenção", "subsections": [
      {"title": "Autoria e Data", "content": "string"},
      {"title": "Propósito e Audiência", "content": "string"}
    ]},
    {"id": "mensagem-fe", "title": "Mensagem de Fé", "subsections": [
      {"title": "Temas Teológicos", "content": "string"},
      {"title": "Mensagem Central", "content": "string"}
    ]},
    {"id": "relacao-biblia", "title": "Relação com a Bíblia", "subsections": [
      {"title": "Lugar no Cânon", "content": "string"},
      {"title": "Conexões", "content": "string"}
    ]},
    {"id": "doutrinas", "title": "Doutrinas", "subsections": [
      {"title": "Doutrina de Deus", "content": "string"},
      {"title": "Cristologia", "content": "string"},
      {"title": "Soteriologia", "content": "string"}
    ]},
    {"id": "aplicacao", "title": "Aplicação Hoje", "content": "string"}
  ]
}

Regras: Português BR, conciso (3-5 frases/campo), perspectiva evangélica.
EOD;
    }

    /**
     * Build system message for JSON mode
     */
    public function getSystemMessage(): string
    {
        return 'Especialista em teologia bíblica. Retorne APENAS JSON válido, sem markdown ou texto extra.';
    }
}
