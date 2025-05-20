import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { ChevronDown, ChevronUp, Search } from 'lucide-react';
import Footer from '@/components/footer';

type FaqItem = {
  question: string;
  answer: string;
  category: string;
};

export default function FAQ() {
  const [searchTerm, setSearchTerm] = useState('');
  const [expandedItems, setExpandedItems] = useState<{[key: string]: boolean}>({});
  
  const faqItems: FaqItem[] = [
    {
      question: "O que faz o Verbum - Bíblia Explicada diferente de outros sites bíblicos?",
      answer: "O Verbum oferece explicações detalhadas e contextualizadas de cada passagem bíblica, combinando rigor teológico com linguagem acessível. Diferente de muitos sites que oferecem apenas o texto bíblico ou comentários superficiais, proporcionamos uma análise aprofundada que inclui contexto histórico, análise linguística original, implicações teológicas e aplicações práticas contemporâneas para cada capítulo e versículo.",
      category: "Sobre o Verbum"
    },
    {
      question: "Como as explicações bíblicas são criadas?",
      answer: "Nossas explicações são geradas utilizando inteligência artificial avançada, mas passam por um rigoroso processo de validação teológica e editorial. Combinamos a eficiência da IA com a supervisão de especialistas em teologia, garantindo que as informações sejam precisas, contextualizadas e fiéis aos textos originais. Cada explicação é revisada para assegurar consistência doutrinária e precisão acadêmica antes de ser publicada.",
      category: "Sobre o Verbum"
    },
    {
      question: "Como o Verbum garante a qualidade e precisão das explicações?",
      answer: "Garantimos a qualidade através de um processo de três etapas: 1) Geração inicial do conteúdo com IA treinada em fontes teológicas respeitáveis; 2) Revisão humana por especialistas em teologia, história bíblica e línguas originais; 3) Sistema de feedback dos usuários que permite reportar qualquer imprecisão. Cada página de explicação conta com uma função para o usuário indicar problemas ou inconsistências, o que nos ajuda a melhorar continuamente o conteúdo.",
      category: "Sobre o Verbum"
    },
    {
      question: "Por que o Verbum é gratuito?",
      answer: "O Verbum é gratuito porque acreditamos que o conhecimento bíblico de qualidade deve ser acessível a todos. Nossa missão é espalhar o melhor conteúdo bíblico possível sem barreiras financeiras, permitindo que pessoas de todas as condições socioeconômicas possam aprofundar seu conhecimento das Escrituras. Sustentamos o projeto através de doações voluntárias e parcerias, sem comprometer nosso compromisso com a acessibilidade universal.",
      category: "Sobre o Verbum"
    },
    {
      question: "Por que algumas passagens têm explicações mais longas que outras?",
      answer: "A extensão das explicações varia de acordo com a complexidade teológica, a profundidade histórica e a riqueza de aplicações da passagem. Versículos que contêm conceitos teológicos fundamentais, referências culturais específicas da época, ou que são frequentemente mal interpretados geralmente recebem explicações mais detalhadas para garantir uma compreensão completa.",
      category: "Sobre o Verbum"
    },
    {
      question: "Por que devo estudar a Bíblia com explicações detalhadas?",
      answer: "Estudar a Bíblia com explicações detalhadas oferece vários benefícios: 1) Proporciona um entendimento mais profundo do texto original e seu significado; 2) Ajuda a evitar interpretações incorretas ou superficiais; 3) Revela nuances culturais e históricas que podem não ser evidentes para leitores contemporâneos; 4) Conecta diferentes passagens bíblicas, mostrando a coerência interna das Escrituras; 5) Facilita a aplicação dos princípios bíblicos à vida moderna.",
      category: "Estudo Bíblico"
    },
    {
      question: "Como posso começar meu estudo bíblico se sou iniciante?",
      answer: "Para iniciantes, recomendamos: 1) Comece com os Evangelhos (Mateus, Marcos, Lucas ou João) para conhecer a vida e os ensinamentos de Jesus; 2) Use nossas explicações para entender o contexto e o significado de cada passagem; 3) Estabeleça uma rotina regular de leitura, mesmo que seja apenas alguns versículos por dia; 4) Anote dúvidas e insights; 5) Se possível, compartilhe seu estudo com outras pessoas para enriquecer a experiência. O Verbum foi projetado para ajudar leitores de todos os níveis, oferecendo explicações que tornam os textos mais acessíveis para iniciantes.",
      category: "Estudo Bíblico"
    },
    {
      question: "Qual é a importância do contexto histórico e cultural para entender a Bíblia?",
      answer: "O contexto histórico e cultural é essencial para uma interpretação precisa das Escrituras porque: 1) A Bíblia foi escrita em épocas, culturas e idiomas específicos, muito diferentes dos nossos; 2) Expressões, costumes e referências que eram óbvias para os leitores originais podem ser incompreensíveis para nós sem o conhecimento adequado; 3) Muitos mal-entendidos e interpretações errôneas ocorrem quando ignoramos o contexto original; 4) Conhecer o contexto ajuda a distinguir entre princípios universais e aplicações culturalmente específicas. Em nossas explicações, nos esforçamos para fornecer esse contexto de forma clara e relevante.",
      category: "Estudo Bíblico"
    },
    {
      question: "Como abordam diferentes interpretações teológicas de uma mesma passagem?",
      answer: "Reconhecemos que existem diferentes tradições e interpretações legítimas dentro do cristianismo. Nossa abordagem é: 1) Apresentar a interpretação mais amplamente aceita e fundamentada academicamente; 2) Quando relevante, mencionar interpretações alternativas significativas; 3) Enfatizar o núcleo teológico compartilhado pelas principais tradições cristãs; 4) Manter um tom respeitoso e ecumênico, reconhecendo a diversidade dentro da unidade da fé cristã; 5) Focar nos princípios essenciais e nas aplicações práticas que transcendem diferenças denominacionais.",
      category: "Teologia"
    },
    {
      question: "Como posso saber se uma explicação bíblica é confiável?",
      answer: "Para avaliar a confiabilidade de uma explicação bíblica, considere: 1) Se está fundamentada no texto original, não apenas em traduções; 2) Se considera o contexto histórico, literário e cultural; 3) Se está alinhada com os princípios interpretativos estabelecidos e aceitos por estudiosos respeitados; 4) Se reconhece diferentes perspectivas quando apropriado; 5) Se está em harmonia com o conjunto das Escrituras; 6) Se é transparente sobre suas fontes e métodos. No Verbum, nos comprometemos com esses princípios de confiabilidade e rigor acadêmico em todas as nossas explicações.",
      category: "Teologia"
    },
    {
      question: "O que significa interpretar a Bíblia 'literalmente'?",
      answer: "Interpretar a Bíblia 'literalmente' não significa interpretar tudo ao pé da letra, mas sim respeitar o gênero literário e a intenção comunicativa de cada passagem. Uma interpretação literal adequada: 1) Reconhece quando o texto usa linguagem poética, metafórica, hiperbólica ou apocalíptica; 2) Considera as convenções literárias da época; 3) Busca entender o que o autor pretendia comunicar aos destinatários originais; 4) Diferencia entre descrições narrativas e prescrições normativas. No Verbum, buscamos honrar a intenção original dos textos, interpretando cada passagem de acordo com seu gênero literário e contexto histórico.",
      category: "Teologia"
    },
    {
      question: "Como posso aplicar ensinamentos de textos antigos à minha vida hoje?",
      answer: "Para aplicar os ensinamentos bíblicos ao contexto contemporâneo: 1) Compreenda primeiro o significado original no contexto histórico e cultural; 2) Identifique os princípios espirituais, éticos e teológicos permanentes; 3) Diferencie entre aplicações culturalmente específicas e princípios universais; 4) Considere como esses princípios se aplicam a situações análogas em nosso tempo; 5) Busque sabedoria através de oração e reflexão sobre como viver esses princípios em seu contexto específico. Nossas explicações no Verbum sempre incluem sugestões de aplicações práticas para ajudar a fazer essa ponte entre o texto antigo e a vida contemporânea.",
      category: "Aplicação Prática"
    },
    {
      question: "Como o Verbum pode ajudar em grupos de estudo bíblico?",
      answer: "O Verbum é um excelente recurso para grupos de estudo bíblico porque: 1) Fornece contexto histórico e cultural que enriquece as discussões; 2) Esclarece passagens difíceis ou potencialmente confusas; 3) Oferece perguntas reflexivas que podem estimular conversas significativas; 4) Sugere aplicações práticas que podem ser discutidas em grupo; 5) Ajuda a manter o estudo focado no significado real do texto, evitando interpretações descontextualizadas. Incentivamos líderes de grupos a usar nossas explicações como base para preparar seus estudos, adaptando-as às necessidades específicas de seu grupo.",
      category: "Aplicação Prática"
    }
  ];
  
  // Filtrar FAQs com base na pesquisa
  const filteredFaqs = faqItems.filter(item => 
    item.question.toLowerCase().includes(searchTerm.toLowerCase()) || 
    item.answer.toLowerCase().includes(searchTerm.toLowerCase())
  );
  
  // Agrupar por categorias
  const groupedFaqs: {[key: string]: FaqItem[]} = {};
  filteredFaqs.forEach(item => {
    if (!groupedFaqs[item.category]) {
      groupedFaqs[item.category] = [];
    }
    groupedFaqs[item.category].push(item);
  });
  
  const toggleItem = (questionId: string) => {
    setExpandedItems(prev => ({
      ...prev,
      [questionId]: !prev[questionId]
    }));
  };
  
  // Gerar FAQ Schema.org JSON-LD
  const generateFaqSchema = () => {
    const faqSchema = {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": faqItems.map(item => ({
        "@type": "Question",
        "name": item.question,
        "acceptedAnswer": {
          "@type": "Answer",
          "text": item.answer
        }
      }))
    };
    
    return JSON.stringify(faqSchema);
  };
  
  return (
    <>
      <Head>
        <title>Perguntas Frequentes | Verbum - Bíblia Explicada</title>
        <meta name="description" content="Encontre respostas para perguntas comuns sobre estudo bíblico, interpretação de passagens e como aproveitar ao máximo o Verbum - Bíblia Explicada." />
        <meta name="keywords" content="FAQ bíblia, perguntas sobre a bíblia, como estudar a bíblia, interpretação bíblica, dúvidas bíblicas" />
        <script type="application/ld+json">{generateFaqSchema()}</script>
      </Head>
      
      <main className="container max-w-4xl mx-auto px-4 py-8">
        <section className="mb-10">
          <h1 className="text-3xl font-bold text-primary mb-4 text-center">Perguntas Frequentes</h1>
          <p className="text-center text-muted-foreground mb-8 max-w-2xl mx-auto">
            Encontre respostas para as dúvidas mais comuns sobre estudo bíblico e sobre como usar o Verbum - Bíblia Explicada.
          </p>
          
          <div className="relative mb-8">
            <div className="absolute inset-y-0 left-3 flex items-center pointer-events-none">
              <Search className="text-muted-foreground" size={20} />
            </div>
            <input
              type="text"
              placeholder="Buscar perguntas ou palavras-chave..."
              className="w-full pl-10 pr-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </section>
        
        {Object.keys(groupedFaqs).length > 0 ? (
          Object.entries(groupedFaqs).map(([category, items]) => (
            <section key={category} className="mb-8">
              <h2 className="text-xl font-semibold mb-4 text-primary border-b pb-2">{category}</h2>
              <div className="space-y-4">
                {items.map((item, index) => {
                  const questionId = `faq-${category}-${index}`;
                  const isExpanded = expandedItems[questionId] || false;
                  
                  return (
                    <div
                      key={questionId}
                      className="border rounded-lg overflow-hidden"
                    >
                      <button
                        className={`w-full text-left p-4 flex justify-between items-center focus:outline-none ${isExpanded ? 'bg-primary/5' : 'bg-card'}`}
                        onClick={() => toggleItem(questionId)}
                        aria-expanded={isExpanded}
                      >
                        <h3 className="font-medium pr-8">{item.question}</h3>
                        {isExpanded ? (
                          <ChevronUp className="flex-shrink-0 text-primary" size={20} />
                        ) : (
                          <ChevronDown className="flex-shrink-0 text-muted-foreground" size={20} />
                        )}
                      </button>
                      
                      {isExpanded && (
                        <div className="p-4 bg-card border-t prose max-w-none">
                          <p>{item.answer}</p>
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            </section>
          ))
        ) : (
          <div className="text-center py-10">
            <p className="text-muted-foreground mb-4">Nenhum resultado encontrado para "{searchTerm}"</p>
            <button
              className="text-primary hover:underline"
              onClick={() => setSearchTerm('')}
            >
              Limpar busca
            </button>
          </div>
        )}
        
        <section className="mt-12 bg-primary/5 rounded-xl p-6 border border-primary/10">
          <h2 className="text-xl font-semibold mb-4 text-center">Ainda tem dúvidas?</h2>
          <p className="text-center mb-6">
            Se não encontrou a resposta que procura, entre em contato conosco.
          </p>
          <div className="flex justify-center">
            <p
              className="text-center"
            >
contato@verbumbiblia.com            </p>
          </div>
        </section>
      </main>
      <Footer />
    </>
  );
}
