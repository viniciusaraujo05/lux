import React, { useState, useEffect, useRef, FC, ReactNode } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { 
  ArrowLeft, 
  Home, 
  Share2, 
  Download, 
  ThumbsUp, 
  ThumbsDown, 
  RefreshCw, 
  AlertTriangle,
  ChevronLeft,
  CheckCircle,
  Book,
  Users,
  FileText,
  Cross,
  Target,
  Gem,
  Key,
  BookOpen,
  Copy,
  Loader2,
  Sparkles
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import AdSense from '@/components/AdSense';
import SlugService from '@/services/SlugService';
import BibleService from '@/services/BibleService';
import { useMediaQuery } from '@/hooks/useMediaQuery';
import Footer from '@/components/footer';
import DonateButton from '@/components/donate-button';

const DynamicLoading: FC = () => {
  const messages = [
    'Preparando contexto do livro...',
    'Analisando estrutura bíblica...',
    'Pesquisando contexto histórico...',
    'Gerando análise teológica...',
    'Formatando o conteúdo...',
    'Quase pronto...'
  ];
  const steps = [
    'Contexto histórico e literário',
    'Análise do gênero literário',
    'Autoria e intenção',
    'Mensagem de fé',
    'Revisão geral'
  ];
  const [msgIndex, setMsgIndex] = useState(0);
  const [step, setStep] = useState(0);
  const [showFirstUserMsg, setShowFirstUserMsg] = useState(false);
  
  useEffect(() => {
    const interval = setInterval(() => setMsgIndex((i) => (i + 1) % messages.length), 2800);
    const stepper = setInterval(() => setStep((s) => Math.min(s + 1, steps.length - 1)), 2600);
    const timeout = setTimeout(() => setShowFirstUserMsg(true), 20000);
    return () => {
      clearInterval(interval);
      clearInterval(stepper);
      clearTimeout(timeout);
    };
  }, []);
  
  const effectiveStep = step >= steps.length - 1 ? steps.length : (step === 0 ? 1 : step);
  const progress = Math.min(100, Math.round((effectiveStep / steps.length) * 100));

  return (
    <Card className="w-full max-w-4xl mt-10 sm:mt-16 items-center py-8 sm:py-10">
      <CardContent className="flex flex-col items-center gap-4 sm:gap-5 px-6 sm:px-8 w-full">
        <div className="h-14 w-14 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center shadow-lg">
          <Sparkles className="h-7 w-7 text-white animate-pulse" />
        </div>
        <h2 className="text-lg sm:text-xl font-semibold text-foreground">Preparando contexto do livro...</h2>
        <p className="text-sm text-center text-muted-foreground">{messages[msgIndex]}</p>

        <div className="w-full max-w-md mt-1">
          <div className="h-2 bg-muted rounded-full overflow-hidden">
            <div className="h-full bg-primary transition-all duration-500" style={{ width: `${progress}%` }} />
          </div>
        </div>
        <p className="text-xs text-center text-muted-foreground mt-1">{progress}% concluído — {progress >= 80 ? 'já está quase pronto...' : 'estamos preparando com carinho...'}</p>

        <ul className="w-full max-w-md text-sm text-muted-foreground space-y-2 mt-2">
          {steps.map((label, i) => (
            <li key={i} className="flex items-center gap-2">
              <div className={`h-5 w-5 rounded-full border flex items-center justify-center ${i < step ? 'bg-emerald-500 border-emerald-500 text-white' : i === step ? 'border-primary text-primary animate-pulse' : 'border-muted-foreground/30 text-muted-foreground/40'}`}>
                {i < step ? <CheckCircle size={14} /> : <Loader2 size={12} className={i === step ? 'animate-spin' : ''} />}
              </div>
              <span className={`${i <= step ? 'text-foreground' : ''}`}>{label}</span>
            </li>
          ))}
        </ul>

        <p className="text-xs text-center text-muted-foreground italic max-w-md">
          "A tua palavra é lâmpada para os meus pés e luz para o meu caminho." - Salmos 119:105
        </p>
        {showFirstUserMsg && (
          <p className="text-xs text-center text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/40 rounded-md px-3 py-2 mt-1">
            Você pode ser a primeira pessoa a acessar o contexto deste livro — a geração inicial pode levar um pouco mais. Nas próximas visitas, tudo será instantâneo 😊
          </p>
        )}
      </CardContent>
    </Card>
  );
};

interface BookContextProps {
  testamento: 'antigo' | 'novo';
  livro: string;
  initialContext?: any;
}

interface BookContextData {
  sections: Array<{
    id: string;
    title: string;
    content?: string;
    subsections?: Array<{
      title: string;
      content: string;
    }>;
  }>;
}

interface BookContext {
  id?: number;
  content: string;
  origin?: string;
}

interface ContextError {
  error: string;
  message: string;
}

type ContextData = BookContextData | ContextError;

// --- Reusable UI Components ---
const Section: FC<{ title: string; children: ReactNode; icon?: ReactNode; defaultOpen?: boolean }> = ({ title, children, icon, defaultOpen = true }) => {
  const [isOpen, setIsOpen] = useState(defaultOpen);
  return (
    <section className="mb-6 bg-card text-card-foreground rounded-lg shadow-sm border border-border transition-all duration-300 hover:shadow-lg overflow-hidden">
      <button
        className="w-full flex items-center justify-between p-4 text-left"
        onClick={() => setIsOpen(!isOpen)}
        aria-expanded={isOpen}
      >
        <h2 className="flex items-center gap-3 text-lg sm:text-xl font-bold text-foreground">
          {icon}{title}
        </h2>
        <ChevronLeft className={`transform transition-transform duration-300 ${isOpen ? '-rotate-90' : 'rotate-0'}`} size={20} />
      </button>
      {isOpen && <div className="px-4 pb-4 prose prose-slate dark:prose-invert max-w-none text-base leading-relaxed">{children}</div>}
    </section>
  );
};

const ListItem: FC<{ children: ReactNode; icon?: ReactNode }> = ({ children, icon }) => (
  <li className="flex items-start gap-3 my-2">
    <span className="text-primary mt-1">{icon || <CheckCircle size={18} />}</span>
    <div>{children}</div>
  </li>
);

const ErrorComponent: FC<{ message: string }> = ({ message }) => (
  <div className="p-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400 rounded-r-lg shadow-md">
    <div className="flex items-center gap-3">
      <AlertTriangle className="h-8 w-8 text-red-600 dark:text-red-400" />
      <div>
        <h3 className="font-bold text-red-800 dark:text-red-200">Ocorreu um Erro</h3>
        <p className="text-red-700 dark:text-red-300">{message}</p>
      </div>
    </div>
  </div>
);

// --- Context Renderer Component ---
const BookContextRenderer: FC<{ context: BookContextData; bookName: string }> = ({ context, bookName }) => {
  const getSectionIcon = (id: string) => {
    switch (id) {
      case 'identificacao-genero': return <Book className="h-5 w-5" />;
      case 'contexto-historico': return <Users className="h-5 w-5" />;
      case 'autoria-intencao': return <FileText className="h-5 w-5" />;
      case 'mensagem-fe': return <Cross className="h-5 w-5" />;
      case 'relacao-biblia': return <Target className="h-5 w-5" />;
      case 'doutrinas-presentes': return <Gem className="h-5 w-5" />;
      case 'leitura-canonica': return <Key className="h-5 w-5" />;
      default: return <BookOpen className="h-5 w-5" />;
    }
  };

  return (
    <div className="space-y-6">
      {/* Header Card */}
      <Card>
        <CardHeader className="text-center pb-6">
          <CardTitle className="text-2xl md:text-3xl font-bold">
            Contexto do Livro de {bookName}
          </CardTitle>
          <CardDescription className="text-base">
            Análise teológica e histórica abrangente
          </CardDescription>
        </CardHeader>
      </Card>

      <AdSense className="my-6" />

      {/* Context Sections */}
      {context.sections?.map((section, index) => (
        <Section
          key={section.id}
          title={section.title}
          icon={getSectionIcon(section.id)}
          defaultOpen={index < 2}
        >
          {section.content && (
            <div className="prose prose-gray dark:prose-invert max-w-none mb-4">
              <p>{section.content}</p>
            </div>
          )}
          
          {section.subsections && (
            <div className="space-y-3">
              {section.subsections.map((subsection, subIndex) => (
                <Card key={subIndex} className="border-l-4 border-l-primary/30">
                  <CardContent className="pt-4">
                    <h4 className="font-semibold text-foreground mb-2 flex items-center gap-2">
                      <div className="w-2 h-2 bg-primary rounded-full flex-shrink-0"></div>
                      {subsection.title}
                    </h4>
                    <div className="prose prose-sm prose-gray dark:prose-invert max-w-none">
                      <p className="text-muted-foreground leading-relaxed m-0">
                        {subsection.content}
                      </p>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          )}
        </Section>
      ))}
      <AdSense className="my-8" />
    </div>
  );
};

function isBookContext(data: any): data is BookContextData {
  return data && typeof data === 'object' && 'sections' in data;
}

function isErrorContext(data: any): data is ContextError {
  return data && typeof data === 'object' && 'error' in data;
}

const ContextRenderer: FC<{ data: ContextData | string | null; bookName: string }> = ({ data, bookName }) => {
  if (typeof data === 'string') {
    try {
      const parsedData = JSON.parse(data);
      if (isBookContext(parsedData)) {
        return <BookContextRenderer context={parsedData} bookName={bookName} />;
      }
    } catch (error) {
      return <ErrorComponent message="Não foi possível processar o contexto do livro." />;
    }
  }

  if (isErrorContext(data)) {
    return <ErrorComponent message={data.message} />;
  }

  if (isBookContext(data)) {
    return <BookContextRenderer context={data} bookName={bookName} />;
  }

  return <ErrorComponent message="Formato de dados não reconhecido." />;
};

const slugService = SlugService.getInstance();

const BookContextPage: React.FC<BookContextProps> = (props) => {
  const [contextData, setContextData] = useState<ContextData | string | null>(props.initialContext?.context || null);
  const [source, setSource] = useState<string>(props.initialContext?.origin || '');
  const [contextId, setContextId] = useState<number | null>(props.initialContext?.id || null);
  const [loading, setLoading] = useState<boolean>(!props.initialContext);
  const [error, setError] = useState<string | null>(null);
  const [retryCount, setRetryCount] = useState(0);
  const [feedback, setFeedback] = useState<'like' | 'dislike' | null>(null);
  const [showShareMenu, setShowShareMenu] = useState(false);
  const isMobile = useMediaQuery('(max-width: 768px)');
  
  const testamento = props.testamento;
  const book = props.livro;
  const bookSlug = slugService.livroParaSlug(book);
  const testamentSlug = testamento === 'antigo' ? 'vt' : 'nt';
  const shareUrl = `${window.location.origin}/contexto/${testamentSlug}/${bookSlug}`;

  const didUseSSR = useRef(false);

  const fetchContext = async (isRetry = false) => {
    if (isRetry) {
      setRetryCount(prev => prev + 1);
    }
    
    try {
      setLoading(true);
      setError(null);
      
      const response = await fetch(`/api/book-context/${testamento}/${bookSlug}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      });
      
      if (!response.ok) {
        throw new Error(`Erro ${response.status}: ${response.statusText}`);
      }
      
      const result = await response.json();
      
      if (result.context) {
        setContextData(result.context);
        setSource(result.origin || 'ai_generated');
        setContextId(result.id || null);
      } else {
        throw new Error('Resposta inválida do servidor');
      }
    } catch (err: any) {
      console.error('Erro ao buscar contexto:', err);
      
      if (err.name === 'AbortError') {
        return;
      }
      
      if (err.message.includes('ERR_CONNECTION_CLOSED') && retryCount < 2) {
        setTimeout(() => {
          fetchContext(true);
        }, 3000);
        return;
      }
      
      setError(err.message || 'Erro desconhecido ao carregar contexto');
    } finally {
      setLoading(false);
    }
  };

  const handleShare = async (platform: string) => {
    const title = `Contexto do Livro de ${book} - Verbum`;
    const text = `Confira esta análise teológica completa do livro de ${book}!`;
    
    switch (platform) {
      case 'whatsapp':
        window.open(`https://wa.me/?text=${encodeURIComponent(`${text} ${shareUrl}`)}`);
        break;
      case 'telegram':
        window.open(`https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(text)}`);
        break;
      case 'copy':
        await navigator.clipboard.writeText(shareUrl);
        alert('Link copiado para a área de transferência!');
        break;
    }
    setShowShareMenu(false);
  };

  const handleExportPDF = () => {
    window.print();
  };

  const handleFeedback = async (type: 'like' | 'dislike') => {
    if (!contextId) return;
    
    try {
      await fetch('/api/feedback', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          explanation_id: contextId,
          feedback_type: type,
        }),
      });
      setFeedback(type);
    } catch (error) {
      console.error('Erro ao enviar feedback:', error);
    }
  };

  const handleBackNavigation = () => {
    const gridTestament = (testamento === 'antigo' ? 'velho' : 'novo');
    router.visit(`/biblia/${gridTestament}/${bookSlug}`);
  };

  const handleHomeNavigation = () => {
    router.visit('/');
  };

  useEffect(() => {
    if (!props.initialContext && !didUseSSR.current) {
      fetchContext();
      didUseSSR.current = true;
    } else if (props.initialContext) {
      setContextData(props.initialContext.context);
      setSource(props.initialContext.origin || 'ssr');
      setContextId(props.initialContext.id || null);
      setLoading(false);
    }
  }, [testamento, bookSlug, props.initialContext]);

  const bookName = book;

  if (loading) {
    return (
      <AppLayout>
        <div className="container max-w-4xl mx-auto p-2 sm:p-4">
          <DynamicLoading />
        </div>
      </AppLayout>
    );
  }

  if (error) {
    return (
      <AppLayout>
        <div className="container max-w-4xl mx-auto p-2 sm:p-4">
          <header className="mb-3 sm:mb-6 flex items-center justify-between bg-card text-card-foreground shadow-sm rounded-lg p-2 sm:p-4 sticky top-2 z-10">
            <div className="flex items-center gap-4">
              <button onClick={handleBackNavigation} className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors rounded-md hover:bg-muted">
                <ArrowLeft size={16} />
                <span className="hidden sm:inline">Voltar</span>
              </button>
              <button onClick={handleHomeNavigation} className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors rounded-md hover:bg-muted">
                <Home size={16} />
                <span className="hidden sm:inline">Início</span>
              </button>
            </div>
          </header>
          
          <Card className="p-6">
            <div className="text-center py-8">
              <AlertTriangle className="h-16 w-16 text-red-500 mx-auto mb-4" />
              <h2 className="text-xl font-bold mb-2">Erro ao Carregar Contexto</h2>
              <p className="text-muted-foreground mb-4">{error}</p>
              <button onClick={() => fetchContext()} className="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors mx-auto">
                <RefreshCw className="h-4 w-4" />
                Tentar Novamente
              </button>
            </div>
          </Card>
        </div>
      </AppLayout>
    );
  }

  return (
    <>
      <Head>
        <title>{`Contexto do Livro de ${bookName} - Explicação Bíblica`}</title>
        <meta name="description" content={`Contexto detalhado do livro de ${bookName} com análise teológica e histórica abrangente.`} />
      </Head>
      <AppLayout>
        <div className="container max-w-4xl mx-auto p-2 sm:p-4">
          <header className="mb-3 sm:mb-6 flex items-center justify-between bg-card text-card-foreground shadow-sm rounded-lg p-2 sm:p-4 sticky top-2 z-10">
            <div className="flex items-center gap-4">
              <button onClick={handleBackNavigation} className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors rounded-md hover:bg-muted">
                <ArrowLeft size={16} />
                <span className="hidden sm:inline">Voltar</span>
              </button>
              <button onClick={handleHomeNavigation} className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors rounded-md hover:bg-muted">
                <Home size={16} />
                <span className="hidden sm:inline">Início</span>
              </button>
            </div>
            
            <div className="flex items-center gap-2">
              <button onClick={handleExportPDF} className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors rounded-md hover:bg-muted">
                <Download size={16} />
                <span className="hidden sm:inline">PDF</span>
              </button>
              <button onClick={() => setShowShareMenu(!showShareMenu)} className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors rounded-md hover:bg-muted">
                <Share2 size={16} />
                <span className="hidden sm:inline">Compartilhar</span>
              </button>
            </div>
          </header>

          {/* Share Menu */}
          {showShareMenu && (
            <div className="mb-6 p-4 bg-muted/50 rounded-lg border">
              <div className="flex flex-wrap items-center gap-4">
                <span className="text-sm font-medium">Compartilhar:</span>
                <div className="flex gap-2">
                  <button onClick={() => handleShare('whatsapp')} className="px-3 py-1 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                    WhatsApp
                  </button>
                  <button onClick={() => handleShare('telegram')} className="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Telegram
                  </button>
                  <button onClick={() => handleShare('copy')} className="flex items-center gap-1 px-3 py-1 text-sm bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    <Copy size={14} />
                    Copiar Link
                  </button>
                </div>
              </div>
            </div>
          )}

          {/* Context Content */}
          <ContextRenderer data={contextData} bookName={bookName} />
          
          {/* Feedback Section */}
          <div className="mt-8 p-4 bg-card rounded-lg border border-border">
            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div>
                <h3 className="font-semibold mb-1">Esta explicação foi útil?</h3>
                <p className="text-sm text-muted-foreground">Seu feedback nos ajuda a melhorar</p>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => handleFeedback('like')}
                  className={`flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition-colors ${
                    feedback === 'like' 
                      ? 'bg-green-600 text-white' 
                      : 'bg-muted text-muted-foreground hover:bg-green-50 hover:text-green-700'
                  }`}
                >
                  <ThumbsUp size={16} />
                  Útil
                </button>
                <button
                  onClick={() => handleFeedback('dislike')}
                  className={`flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition-colors ${
                    feedback === 'dislike' 
                      ? 'bg-red-600 text-white' 
                      : 'bg-muted text-muted-foreground hover:bg-red-50 hover:text-red-700'
                  }`}
                >
                  <ThumbsDown size={16} />
                  Não útil
                </button>
              </div>
            </div>
          </div>
        </div>
        <Footer />
        <DonateButton />
      </AppLayout>
    </>
  );
};

export default BookContextPage;
