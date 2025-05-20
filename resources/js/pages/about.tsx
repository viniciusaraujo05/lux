import React from 'react';
import { Head } from '@inertiajs/react';
import Footer from '@/components/footer';
import { Book, Users, Award, MessageCircle, Heart } from 'lucide-react';

export default function About() {
  return (
    <>
      <Head>
        <title>Sobre o Verbum - Bíblia Explicada | Nossa Missão e Valores</title>
        <meta name="description" content="Conheça a história e a missão do Verbum - Bíblia Explicada. Criamos explicações bíblicas detalhadas e acessíveis para aproximar as pessoas da Palavra de Deus." />
        <meta name="keywords" content="Verbum, Bíblia explicada, projeto bíblico, estudo bíblico, explicações bíblicas, análise teológica" />
        
        {/* Open Graph / Facebook */}
        <meta property="og:type" content="website" />
        <meta property="og:url" content={window.location.href} />
        <meta property="og:title" content="Sobre o Verbum - Bíblia Explicada | Nossa Missão e Valores" />
        <meta property="og:description" content="Conheça a história e a missão do Verbum - Bíblia Explicada. Criamos explicações bíblicas detalhadas e acessíveis para aproximar as pessoas da Palavra de Deus." />
        
        {/* Twitter */}
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:url" content={window.location.href} />
        <meta property="twitter:title" content="Sobre o Verbum - Bíblia Explicada | Nossa Missão e Valores" />
        <meta property="twitter:description" content="Conheça a história e a missão do Verbum - Bíblia Explicada. Criamos explicações bíblicas detalhadas e acessíveis para aproximar as pessoas da Palavra de Deus." />
      </Head>
      
      <main className="container max-w-4xl mx-auto px-4 py-8">
        <section className="mb-12">
          <div className="text-center mb-8">
            <h1 className="text-3xl font-bold text-primary mb-4">Sobre o Verbum - Bíblia Explicada</h1>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              Tornando a Bíblia acessível, compreensível e aplicável para todos
            </p>
          </div>
          
          <div className="prose prose-lg max-w-none">
            <p>
              <strong>Verbum - Bíblia Explicada</strong> nasceu da paixão por tornar as Escrituras acessíveis a todos, 
              independentemente do seu nível de conhecimento bíblico. Nossa missão é oferecer explicações 
              detalhadas, bem fundamentadas e de fácil compreensão para cada capítulo e versículo da Bíblia.
            </p>
            
            <p>
              Combinamos rigor teológico com linguagem acessível, contexto histórico com aplicações contemporâneas, 
              e profundidade acadêmica com relevância prática. Queremos ser uma ponte entre o texto original e 
              a vida diária dos leitores.
            </p>
          </div>
        </section>
        
        <section className="mb-12 grid grid-cols-1 md:grid-cols-2 gap-8">
          <div className="bg-card rounded-xl shadow-sm border p-6">
            <div className="flex items-center mb-4">
              <div className="bg-primary/10 p-3 rounded-full mr-4">
                <Book className="text-primary" size={24} />
              </div>
              <h2 className="text-xl font-semibold">Nossa Abordagem</h2>
            </div>
            <div className="space-y-4">
              <p>
                Para cada passagem bíblica, oferecemos:
              </p>
              <ul className="space-y-2">
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Contexto histórico, cultural e literário detalhado</span>
                </li>
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Análise teológica clara e fundamentada</span>
                </li>
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Aplicações práticas para a vida contemporânea</span>
                </li>
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Conexões com outras passagens bíblicas</span>
                </li>
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Explicações de palavras-chave nos idiomas originais</span>
                </li>
              </ul>
            </div>
          </div>
          
          <div className="bg-card rounded-xl shadow-sm border p-6">
            <div className="flex items-center mb-4">
              <div className="bg-primary/10 p-3 rounded-full mr-4">
                <Users className="text-primary" size={24} />
              </div>
              <h2 className="text-xl font-semibold">Nosso Público</h2>
            </div>
            <div className="space-y-4">
              <p>
                O Verbum foi criado para servir a diversos perfis de leitores:
              </p>
              <ul className="space-y-2">
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Cristãos em busca de um entendimento mais profundo da Palavra</span>
                </li>
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Novos leitores da Bíblia que precisam de contexto</span>
                </li>
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Líderes e professores preparando estudos e mensagens</span>
                </li>
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Estudiosos buscando referências confiáveis</span>
                </li>
                <li className="flex items-start">
                  <span className="text-primary mr-2">•</span>
                  <span>Pessoas em busca de respostas para questões de fé e vida</span>
                </li>
              </ul>
            </div>
          </div>
        </section>
        
        <section className="mb-12">
          <div className="text-center mb-6">
            <h2 className="text-2xl font-bold text-primary">Nossos Valores</h2>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-card rounded-xl shadow-sm border p-6 text-center">
              <Award className="mx-auto text-primary mb-4" size={32} />
              <h3 className="text-lg font-semibold mb-2">Excelência Teológica</h3>
              <p className="text-muted-foreground">
                Comprometidos com a precisão e fidelidade às Escrituras, baseando nossas explicações em pesquisa sólida.
              </p>
            </div>
            
            <div className="bg-card rounded-xl shadow-sm border p-6 text-center">
              <MessageCircle className="mx-auto text-primary mb-4" size={32} />
              <h3 className="text-lg font-semibold mb-2">Clareza e Acessibilidade</h3>
              <p className="text-muted-foreground">
                Explicamos conceitos profundos em linguagem compreensível, removendo barreiras ao entendimento.
              </p>
            </div>
            
            <div className="bg-card rounded-xl shadow-sm border p-6 text-center">
              <Heart className="mx-auto text-primary mb-4" size={32} />
              <h3 className="text-lg font-semibold mb-2">Relevância Prática</h3>
              <p className="text-muted-foreground">
                Conectamos as verdades eternas da Bíblia aos desafios e questões da vida cotidiana moderna.
              </p>
            </div>
          </div>
        </section>
        
        <section className="mb-12">
          <div className="bg-primary/5 rounded-xl p-8 border border-primary/10">
            <h2 className="text-2xl font-bold text-primary mb-4">Nosso Compromisso</h2>
            <div className="prose prose-lg max-w-none">
              <p>
                O Verbum é um recurso gratuito e acessível a todos que buscam entender melhor as Escrituras.
                Nos comprometemos com:
              </p>
              
              <ul>
                <li>Atualização constante de nosso conteúdo com as melhores pesquisas teológicas</li>
                <li>Respeito à diversidade de tradições e interpretações cristãs</li>
                <li>Transparência em nossos métodos e fontes</li>
                <li>Abertura ao diálogo e ao feedback dos usuários</li>
              </ul>
              
              <p>
                Acreditamos que um entendimento mais profundo das Escrituras transforma vidas,
                fortalece a fé e equipa os cristãos para viverem conforme os propósitos de Deus.
              </p>
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </>
  );
}
