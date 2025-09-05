import React, { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ChevronDown, ChevronRight, HelpCircle, BookOpen, Search, Heart } from 'lucide-react';

interface FAQItem {
  question: string;
  answer: string;
  category: 'geral' | 'navegacao' | 'conteudo' | 'tecnico';
}

const faqData: FAQItem[] = [
  {
    category: 'geral',
    question: 'O que é o Verso a verso?',
    answer: 'O Verso a verso é uma plataforma digital dedicada ao estudo da Bíblia, oferecendo explicações detalhadas, contexto histórico e aplicações práticas para cada capítulo e versículo das Escrituras Sagradas.'
  },
  {
    category: 'geral',
    question: 'O uso da plataforma é gratuito?',
    answer: 'Sim! O Verso a verso é completamente gratuito. Nosso objetivo é democratizar o acesso ao conhecimento bíblico para todos, independentemente de sua situação financeira.'
  },
  {
    category: 'navegacao',
    question: 'Como navegar pela Bíblia na plataforma?',
    answer: 'A navegação segue uma estrutura simples: primeiro escolha entre Velho ou Novo Testamento, depois selecione o livro desejado, em seguida o capítulo, e por fim os versículos específicos que deseja estudar.'
  },
  {
    category: 'navegacao',
    question: 'Posso estudar versículos específicos ou apenas capítulos completos?',
    answer: 'Você pode estudar tanto capítulos completos quanto versículos específicos. Para versículos individuais, clique no número do versículo desejado. Para capítulos completos, use o botão "Explicar Capítulo".'
  },
  {
    category: 'conteudo',
    question: 'Qual é a base teológica das explicações?',
    answer: 'Nossas explicações são baseadas em princípios hermenêuticos sólidos, considerando o contexto histórico, cultural, literário e teológico. Utilizamos uma abordagem evangélica conservadora, respeitando a autoridade e inspiração das Escrituras.'
  },
  {
    category: 'conteudo',
    question: 'As explicações incluem referências cruzadas?',
    answer: 'Sim! Cada explicação inclui referências cruzadas relevantes que ajudam a conectar diferentes passagens bíblicas, proporcionando uma compreensão mais ampla e profunda do texto.'
  },
  {
    category: 'conteudo',
    question: 'Posso usar o conteúdo para ensinar ou pregar?',
    answer: 'Absolutamente! Nosso conteúdo foi criado para ser uma ferramenta de apoio para pastores, professores, líderes e estudantes da Bíblia. Sinta-se livre para usar as explicações em seus estudos, aulas e pregações.'
  },
  {
    category: 'tecnico',
    question: 'A plataforma funciona em dispositivos móveis?',
    answer: 'Sim! O Verso a verso foi desenvolvido com design responsivo, funcionando perfeitamente em smartphones, tablets e computadores. Você pode estudar a Bíblia em qualquer lugar.'
  },
  {
    category: 'tecnico',
    question: 'Preciso criar uma conta para usar a plataforma?',
    answer: 'Não é necessário criar uma conta para acessar as explicações bíblicas. O conteúdo está disponível livremente para todos os visitantes.'
  },
  {
    category: 'geral',
    question: 'Como posso apoiar o projeto?',
    answer: 'Você pode apoiar o Verso a verso através de doações voluntárias. Há opções de doação via PIX e outras plataformas. Seu apoio nos ajuda a manter e melhorar continuamente a plataforma.'
  },
  {
    category: 'conteudo',
    question: 'Com que frequência o conteúdo é atualizado?',
    answer: 'Estamos constantemente trabalhando para melhorar e expandir nosso conteúdo. Novas explicações e melhorias são adicionadas regularmente com base no feedback dos usuários.'
  },
  {
    category: 'tecnico',
    question: 'Posso sugerir melhorias ou reportar problemas?',
    answer: 'Sim! Valorizamos muito o feedback dos usuários. Você pode entrar em contato conosco através da seção de feedback disponível em cada explicação ou através de nossos canais de contato.'
  }
];

const categories = {
  geral: { name: 'Geral', icon: HelpCircle },
  navegacao: { name: 'Navegação', icon: Search },
  conteudo: { name: 'Conteúdo', icon: BookOpen },
  tecnico: { name: 'Técnico', icon: Heart }
};

export default function FAQ() {
  const [openItems, setOpenItems] = useState<Set<number>>(new Set());
  const [selectedCategory, setSelectedCategory] = useState<string>('all');

  const toggleItem = (index: number) => {
    const newOpenItems = new Set(openItems);
    if (newOpenItems.has(index)) {
      newOpenItems.delete(index);
    } else {
      newOpenItems.add(index);
    }
    setOpenItems(newOpenItems);
  };

  const filteredFAQ = selectedCategory === 'all' 
    ? faqData 
    : faqData.filter(item => item.category === selectedCategory);

  return (
    <>
      <Head>
        <title>FAQ - Perguntas Frequentes | Verso a verso</title>
        <meta name="description" content="Encontre respostas para as perguntas mais frequentes sobre o Verso a verso, nossa plataforma de estudo bíblico." />
      </Head>
      <AppLayout>
        <div className="container max-w-4xl mx-auto p-4 sm:p-6">
          <div className="space-y-8">
            {/* Header */}
            <div className="text-center">
              <h1 className="text-3xl sm:text-4xl font-bold text-foreground mb-4">
                Perguntas Frequentes
              </h1>
              <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
                Encontre respostas para as dúvidas mais comuns sobre o Verso a verso
              </p>
            </div>

            {/* Category Filter */}
            <Card>
              <CardHeader>
                <CardTitle>Filtrar por categoria</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex flex-wrap gap-2">
                  <button
                    onClick={() => setSelectedCategory('all')}
                    className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                      selectedCategory === 'all'
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted hover:bg-muted/80 text-muted-foreground'
                    }`}
                  >
                    Todas
                  </button>
                  {Object.entries(categories).map(([key, { name, icon: Icon }]) => (
                    <button
                      key={key}
                      onClick={() => setSelectedCategory(key)}
                      className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                        selectedCategory === key
                          ? 'bg-primary text-primary-foreground'
                          : 'bg-muted hover:bg-muted/80 text-muted-foreground'
                      }`}
                    >
                      <Icon size={16} />
                      {name}
                    </button>
                  ))}
                </div>
              </CardContent>
            </Card>

            {/* FAQ Items */}
            <div className="space-y-4">
              {filteredFAQ.map((item, index) => {
                const isOpen = openItems.has(index);
                const CategoryIcon = categories[item.category].icon;
                
                return (
                  <Card key={index} className="overflow-hidden">
                    <button
                      onClick={() => toggleItem(index)}
                      className="w-full text-left p-4 hover:bg-muted/50 transition-colors"
                    >
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                          <CategoryIcon size={20} className="text-primary flex-shrink-0" />
                          <h3 className="font-semibold text-foreground pr-4">
                            {item.question}
                          </h3>
                        </div>
                        {isOpen ? (
                          <ChevronDown size={20} className="text-muted-foreground flex-shrink-0" />
                        ) : (
                          <ChevronRight size={20} className="text-muted-foreground flex-shrink-0" />
                        )}
                      </div>
                    </button>
                    {isOpen && (
                      <div className="px-4 pb-4">
                        <div className="pl-8">
                          <p className="text-muted-foreground leading-relaxed">
                            {item.answer}
                          </p>
                        </div>
                      </div>
                    )}
                  </Card>
                );
              })}
            </div>

            {/* Contact Section */}
            <Card className="border-primary/20 bg-primary/5">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <HelpCircle className="h-5 w-5 text-primary" />
                  Não encontrou sua resposta?
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground leading-relaxed mb-4">
                  Se você não encontrou a resposta para sua pergunta, ficaremos felizes em ajudar! 
                  Entre em contato conosco através do sistema de feedback disponível em cada explicação 
                  bíblica ou nos apoie para continuarmos melhorando a plataforma.
                </p>
                <p className="text-sm text-muted-foreground italic">
                  "Peça, e dar-se-vos-á; buscai, e encontrareis; batei, e abrir-se-vos-á." - Mateus 7:7
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </AppLayout>
    </>
  );
}