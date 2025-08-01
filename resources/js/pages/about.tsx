import React from 'react';
import { Head } from '@inertiajs/react';
import Footer from '@/components/footer';
import { Book, Users, Award, MessageCircle, Heart } from 'lucide-react';

export default function About() {
  return (
    <>
      <Head>
        <title>Sobre o Verso a verso | Nossa Missão e Valores</title>
        <meta name="description" content="Conheça a história e a missão do Verso a verso. Criamos explicações bíblicas detalhadas e acessíveis para aproximar as pessoas da Palavra de Deus." />
        <meta name="keywords" content="Verso a verso, Bíblia explicada, projeto bíblico, estudo bíblico, explicações bíblicas, análise teológica" />
        
        {/* Open Graph / Facebook */}
        <meta property="og:type" content="website" />
        <meta property="og:url" content={window.location.href} />
        <meta property="og:title" content="Sobre o Verso a verso | Nossa Missão e Valores" />
        <meta property="og:description" content="Conheça a história e a missão do Verso a verso. Criamos explicações bíblicas detalhadas e acessíveis para aproximar as pessoas da Palavra de Deus." />
        
        {/* Twitter */}
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:url" content={window.location.href} />
        <meta property="twitter:title" content="Sobre o Verso a verso | Nossa Missão e Valores" />
        <meta property="twitter:description" content="Conheça a história e a missão do Verso a verso. Criamos explicações bíblicas detalhadas e acessíveis para aproximar as pessoas da Palavra de Deus." />
      </Head>
      
      <main className="container max-w-4xl mx-auto px-4 py-8">
        <section className="mb-12">
          <div className="text-center mb-8">
            <h1 className="text-3xl font-bold text-primary mb-4">Sobre o Verso a verso</h1>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              Tornando a Bíblia acessível, compreensível e aplicável para todos
            </p>
          </div>
          
          <div className="prose prose-lg max-w-none">
            <p>
              <strong>Verso a verso</strong> nasceu da visão de utilizar a tecnologia moderna com inteligência 
              artificial para tornar as Escrituras acessíveis a todos. Nossa missão é oferecer explicações 
              bíblicas detalhadas e bem fundamentadas, geradas com o auxílio de IA avançada, mas sempre com 
              supervisão teológica adequada.
            </p>
            
            <p>
              Combinamos o poder da inteligência artificial com rigor teológico, proporcionando explicações 
              que conectam o contexto histórico com aplicações contemporâneas. Nosso objetivo é usar a tecnologia 
              para criar uma ponte entre o texto bíblico original e a vida diária dos leitores, tornando o estudo 
              bíblico mais acessível e enriquecedor.
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
                O Verso a verso foi criado para servir a diversos perfis de leitores:
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
                O Verso a verso é um recurso gratuito e acessível a todos que buscam entender melhor as Escrituras.
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
