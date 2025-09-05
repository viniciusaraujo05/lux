import React from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { BookOpen, Heart, Users, Target } from 'lucide-react';

export default function About() {
  return (
    <>
      <Head>
        <title>Sobre - Verso a verso</title>
        <meta name="description" content="Conheça a missão do Verso a verso: tornar o estudo da Bíblia acessível e profundo para todos." />
      </Head>
      <AppLayout>
        <div className="container max-w-4xl mx-auto p-4 sm:p-6">
          <div className="space-y-8">
            {/* Header */}
            <div className="text-center">
              <h1 className="text-3xl sm:text-4xl font-bold text-foreground mb-4">
                Sobre o Verso a verso
              </h1>
              <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
                Uma plataforma dedicada a tornar o estudo da Bíblia mais acessível e profundo para todos.
              </p>
            </div>

            {/* Mission */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Target className="h-5 w-5 text-primary" />
                  Nossa Missão
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground leading-relaxed">
                  O Verso a verso foi criado com o objetivo de democratizar o acesso ao conhecimento bíblico. 
                  Oferecemos explicações detalhadas, contexto histórico e aplicações práticas para cada 
                  capítulo e versículo da Bíblia, ajudando pessoas de todos os níveis de conhecimento a 
                  compreenderem melhor as Escrituras Sagradas.
                </p>
              </CardContent>
            </Card>

            {/* Features */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <BookOpen className="h-5 w-5 text-primary" />
                  O que oferecemos
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid gap-4 sm:grid-cols-2">
                  <div>
                    <h3 className="font-semibold text-foreground mb-2">Explicações Detalhadas</h3>
                    <p className="text-sm text-muted-foreground">
                      Análises verso por verso com contexto histórico, cultural e teológico.
                    </p>
                  </div>
                  <div>
                    <h3 className="font-semibold text-foreground mb-2">Aplicação Prática</h3>
                    <p className="text-sm text-muted-foreground">
                      Conexões relevantes para a vida contemporânea e crescimento espiritual.
                    </p>
                  </div>
                  <div>
                    <h3 className="font-semibold text-foreground mb-2">Referências Cruzadas</h3>
                    <p className="text-sm text-muted-foreground">
                      Ligações entre diferentes passagens bíblicas para maior compreensão.
                    </p>
                  </div>
                  <div>
                    <h3 className="font-semibold text-foreground mb-2">Interface Intuitiva</h3>
                    <p className="text-sm text-muted-foreground">
                      Navegação simples e organizada para facilitar seus estudos.
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Community */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Users className="h-5 w-5 text-primary" />
                  Comunidade
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground leading-relaxed mb-4">
                  Acreditamos que o estudo da Bíblia é uma jornada compartilhada. Nossa plataforma 
                  é construída com amor e dedicação para servir a comunidade cristã mundial, 
                  oferecendo recursos que enriquecem a compreensão das Escrituras.
                </p>
                <p className="text-muted-foreground leading-relaxed">
                  Se você tem sugestões, feedback ou gostaria de contribuir com nosso projeto, 
                  ficaremos felizes em ouvir você. Juntos, podemos tornar o estudo da Palavra 
                  de Deus ainda mais acessível e transformador.
                </p>
              </CardContent>
            </Card>

            {/* Support */}
            <Card className="border-primary/20 bg-primary/5">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Heart className="h-5 w-5 text-primary" />
                  Apoie nosso trabalho
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground leading-relaxed mb-4">
                  O Verso a verso é mantido por uma equipe dedicada que trabalha para oferecer 
                  conteúdo de qualidade gratuitamente. Seu apoio nos ajuda a continuar 
                  desenvolvendo e melhorando esta ferramenta de estudo bíblico.
                </p>
                <p className="text-sm text-muted-foreground">
                  "Cada um contribua segundo propôs no seu coração, não com tristeza ou por 
                  necessidade; porque Deus ama ao que dá com alegria." - 2 Coríntios 9:7
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </AppLayout>
    </>
  );
}