# Plano de Ação: Resolver "Rastreada, mas não indexada no momento"

## Problema Identificado
Mais de 2.000 páginas com status "Rastreada, mas não indexada no momento" no Google Search Console.

## Causas Prováveis

### 1. **Problemas de Crawl Budget**
- Google rastreia mas não indexa devido a baixa prioridade percebida
- Falta de sinais de atualização (lastmod) no sitemap
- Sitemap muito grande (todas URLs em um arquivo)

### 2. **Conteúdo Percebido como Duplicado**
- Múltiplas URLs para o mesmo conteúdo (com/sem slug, com/sem query params)
- Falta de diferenciação clara entre páginas similares

### 3. **Autoridade de Página Baixa**
- Links internos fracos entre páginas relacionadas
- Páginas "órfãs" sem links apontando para elas

### 4. **Sinais de Qualidade Insuficientes**
- Falta de engajamento do usuário
- Tempo de carregamento pode estar afetando

## Soluções Implementadas

### ✅ 1. Adicionado `lastmod` ao Sitemap
**Impacto:** ALTO - Sinaliza ao Google quando priorizar rastreamento

**O que foi feito:**
- Adicionado tag `<lastmod>` em todas URLs do sitemap
- Alterado `changefreq` de páginas de explicação de `monthly` para `weekly`
- Aumentado `priority` de páginas de explicação de `0.8` para `0.9`

**Resultado esperado:**
- Google identifica páginas atualizadas recentemente
- Melhora na priorização de rastreamento
- Páginas de explicação (conteúdo principal) têm maior prioridade

### ✅ 2. Template para Sitemap Index
**Impacto:** MÉDIO-ALTO - Organiza melhor o sitemap

**O que foi feito:**
- Criado template `sitemap-index.blade.php`
- Preparado estrutura para dividir sitemap em múltiplos arquivos

**Próximo passo:** Implementar controller para gerar sitemaps separados:
- `sitemap-explicacoes.xml` - Páginas de explicação (prioridade máxima)
- `sitemap-amp.xml` - Páginas AMP
- `sitemap-biblia.xml` - Páginas de leitura da Bíblia
- `sitemap-principal.xml` - Páginas principais

## Soluções Recomendadas (A Implementar)

### 🔧 3. Implementar Links Internos Estratégicos
**Impacto:** MUITO ALTO - Aumenta autoridade e crawlability

**Como implementar:**

#### A. Links de Navegação Contextual
Adicionar em cada página de explicação:

```php
// No final de cada explicação de capítulo
- Link para capítulo anterior: "← Capítulo X"
- Link para próximo capítulo: "Capítulo X+1 →"
- Link para o livro: "Ver todos os capítulos de {Livro}"
- Link para o testamento: "Explorar {Antigo/Novo} Testamento"
```

#### B. Seção "Capítulos Relacionados"
```php
// Sugestões inteligentes baseadas em:
- Mesmo livro (capítulos adjacentes)
- Mesmo tema teológico
- Referências cruzadas mencionadas no conteúdo
- Capítulos populares do mesmo testamento
```

#### C. Breadcrumbs Clicáveis
```html
<!-- Já existe JSON-LD, adicionar HTML visível -->
<nav aria-label="breadcrumb">
  Início > Bíblia > Antigo Testamento > Jeremias > Capítulo 43
</nav>
```

**Arquivo a modificar:** `resources/js/pages/explicacao/index.tsx`

### 🔧 4. Melhorar Structured Data (Schema.org)
**Impacto:** MÉDIO-ALTO - Ajuda Google a entender melhor o conteúdo

**Adicionar schemas:**

```json
{
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "O que significa {Livro} {Capítulo}:{Verso}?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Resumo da explicação..."
      }
    }
  ]
}
```

```json
{
  "@type": "WebPage",
  "speakable": {
    "@type": "SpeakableSpecification",
    "cssSelector": [".main-explanation", ".verse-text"]
  }
}
```

**Arquivo a modificar:** `routes/web.php` (adicionar ao $jsonLd)

### 🔧 5. Otimizar Core Web Vitals
**Impacto:** MÉDIO - Afeta experiência e indexação

**Ações:**

#### A. Lazy Loading de Componentes Pesados
```tsx
// Em explicacao/index.tsx
const AdSense = lazy(() => import('@/components/AdSense'));
const Footer = lazy(() => import('@/components/footer'));
```

#### B. Preload de Recursos Críticos
```blade
<!-- Em app.blade.php -->
<link rel="preload" href="/fonts/main.woff2" as="font" crossorigin>
<link rel="preconnect" href="https://pagead2.googlesyndication.com">
```

#### C. Otimizar Imagens (se houver)
- Usar WebP com fallback
- Implementar lazy loading
- Adicionar width/height para evitar layout shift

### 🔧 6. Criar Sitemap Dinâmico por Seções
**Impacto:** MÉDIO - Melhora processamento pelo Google

**Implementar em SeoController.php:**

```php
public function sitemapIndex()
{
    $sitemaps = [
        ['loc' => url('/sitemap-explicacoes.xml'), 'lastmod' => now()->toIso8601String()],
        ['loc' => url('/sitemap-amp.xml'), 'lastmod' => now()->toIso8601String()],
        ['loc' => url('/sitemap-biblia.xml'), 'lastmod' => now()->toIso8601String()],
        ['loc' => url('/sitemap-principal.xml'), 'lastmod' => now()->toIso8601String()],
    ];
    
    return view('seo.sitemap-index', ['sitemaps' => $sitemaps]);
}

public function sitemapExplicacoes() { /* Apenas URLs de explicação */ }
public function sitemapAmp() { /* Apenas URLs AMP */ }
public function sitemapBiblia() { /* Apenas URLs de leitura */ }
public function sitemapPrincipal() { /* Home, testamentos, livros */ }
```

**Adicionar rotas em web.php:**
```php
Route::get('/sitemap.xml', [SeoController::class, 'sitemapIndex']);
Route::get('/sitemap-explicacoes.xml', [SeoController::class, 'sitemapExplicacoes']);
Route::get('/sitemap-amp.xml', [SeoController::class, 'sitemapAmp']);
Route::get('/sitemap-biblia.xml', [SeoController::class, 'sitemapBiblia']);
Route::get('/sitemap-principal.xml', [SeoController::class, 'sitemapPrincipal']);
```

### 🔧 7. Adicionar Meta Tags Adicionais
**Impacto:** BAIXO-MÉDIO - Sinais adicionais de qualidade

```blade
<!-- Em app.blade.php, adicionar: -->
<meta name="article:published_time" content="{{ $publishedTime ?? now()->toIso8601String() }}">
<meta name="article:modified_time" content="{{ now()->toIso8601String() }}">
<meta name="article:section" content="Estudo Bíblico">
<meta name="article:tag" content="{{ $keywords }}">
```

### 🔧 8. Implementar Paginação para Capítulos Longos
**Impacto:** MÉDIO - Melhora experiência e indexação

Para capítulos muito longos (ex: Salmos 119), considerar:
- Dividir em seções menores
- Implementar paginação
- Manter URL canônica apontando para versão completa

### 🔧 9. Adicionar Conteúdo Único por Página
**Impacto:** MUITO ALTO - Diferencia páginas similares

**Estratégias:**

#### A. Enriquecer Metadados
```php
// Adicionar informações específicas por capítulo
$contextInfo = [
    'genesis-1' => 'A criação do mundo em 7 dias',
    'genesis-2' => 'A criação de Adão e Eva no Jardim do Éden',
    // ... expandir para capítulos principais
];
```

#### B. Adicionar Seção "Você Sabia?"
- Curiosidades históricas
- Contexto arqueológico
- Fatos sobre autoria e data

#### C. Perguntas Frequentes Específicas
- "Por que este capítulo é importante?"
- "Como este capítulo se relaciona com o Novo Testamento?"
- "Qual a aplicação prática deste capítulo?"

### 🔧 10. Solicitar Indexação Manual (Curto Prazo)
**Impacto:** IMEDIATO mas limitado

**Processo:**
1. No Google Search Console, ir em "Inspeção de URL"
2. Inserir URLs das páginas mais importantes
3. Clicar em "Solicitar indexação"
4. Priorizar:
   - Páginas com mais tráfego potencial
   - Capítulos mais conhecidos (João 3, Salmos 23, etc.)
   - Páginas recém-criadas ou atualizadas

**Limitação:** Google limita solicitações manuais (~10-20 por dia)

## Cronograma de Implementação Sugerido

### Semana 1 (Impacto Imediato)
- ✅ Adicionar lastmod ao sitemap (FEITO)
- ✅ Criar template sitemap index (FEITO)
- 🔧 Implementar sitemap dividido por seções
- 🔧 Adicionar links internos de navegação (anterior/próximo)
- 🔧 Limpar cache e reenviar sitemap ao Google Search Console

### Semana 2 (Melhorias de Qualidade)
- 🔧 Implementar breadcrumbs HTML visíveis
- 🔧 Adicionar seção "Capítulos Relacionados"
- 🔧 Enriquecer structured data (FAQPage, WebPage)
- 🔧 Adicionar meta tags article:published_time

### Semana 3 (Otimizações de Performance)
- 🔧 Implementar lazy loading de componentes
- 🔧 Otimizar Core Web Vitals
- 🔧 Adicionar preload de recursos críticos

### Semana 4 (Conteúdo e Monitoramento)
- 🔧 Adicionar conteúdo único por capítulo importante
- 🔧 Implementar seção "Você Sabia?"
- 🔧 Monitorar Google Search Console para verificar melhorias
- 🔧 Solicitar indexação manual das páginas prioritárias

## Métricas para Acompanhar

### Google Search Console
- **Cobertura:** Redução de "Rastreada, mas não indexada"
- **Desempenho:** Aumento de impressões e cliques
- **Core Web Vitals:** LCP, FID, CLS devem estar em "Bom"

### Google Analytics
- **Páginas/Sessão:** Deve aumentar com links internos
- **Taxa de Rejeição:** Deve diminuir
- **Tempo na Página:** Deve aumentar

### Objetivos
- **Mês 1:** Reduzir páginas não indexadas em 30%
- **Mês 2:** Reduzir páginas não indexadas em 60%
- **Mês 3:** Reduzir páginas não indexadas em 80%+

## Comandos Úteis

```bash
# Limpar cache do sitemap
php artisan cache:clear

# Verificar sitemap gerado
curl https://versoaverso.site/sitemap.xml

# Testar performance
lighthouse https://versoaverso.site/explicacao/antigo/jeremias/43/43-explicacao-biblica
```

## Recursos Adicionais

### Ferramentas de Teste
- **Google Search Console:** Inspeção de URL
- **Google PageSpeed Insights:** Core Web Vitals
- **Schema.org Validator:** Validar structured data
- **XML Sitemap Validator:** Verificar sintaxe do sitemap

### Documentação
- [Google: Rastreada, mas não indexada](https://developers.google.com/search/docs/crawling-indexing/large-site-managing-crawl-budget)
- [Schema.org Article](https://schema.org/Article)
- [Sitemap Protocol](https://www.sitemaps.org/protocol.html)

## Notas Importantes

1. **Paciência:** Mudanças de indexação podem levar 2-4 semanas para refletir
2. **Priorização:** Focar primeiro nas páginas com maior potencial de tráfego
3. **Monitoramento:** Acompanhar métricas semanalmente
4. **Iteração:** Ajustar estratégia baseado nos resultados

---

**Última atualização:** 12 de janeiro de 2026
**Status:** Implementação em andamento
