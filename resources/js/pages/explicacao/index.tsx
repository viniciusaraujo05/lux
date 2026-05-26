import React, { useState, useEffect, FC, ReactNode, useRef } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import {
  ChevronLeft, ArrowRight, ArrowLeft, ThumbsUp, ThumbsDown, Heart, User, Loader2,
  BookOpen, Scale, Landmark, Users, Microscope, Cross, Target, Link, Gem,
  AlertTriangle, FileText, CheckCircle, Key, Book, HelpCircle, Share2, Sparkles, RefreshCw, ExternalLink
} from 'lucide-react';
import { useMediaQuery } from '@/hooks/useMediaQuery';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import Footer from '@/components/footer';
import DonateButton from '@/components/donate-button';
import AdSense from '@/components/AdSense';
// Removed Lottie Player to use Lucide Bird icon
// import { Player } from '@lottiefiles/react-lottie-player';
import SlugService from '@/services/SlugService';

const DynamicLoading: FC<{ statusMessage?: string; statusProgress?: number | null }> = ({ statusMessage, statusProgress }) => {
  const messages = [
    'Abrindo o texto com calma...',
    'Reunindo o contexto da passagem...',
    'Conectando referências bíblicas...',
    'Organizando os pontos principais...',
    'Preparando uma leitura clara...',
    'Quase pronto para estudar...'
  ];
  const steps = [
    'Lendo a passagem',
    'Situando no capítulo',
    'Ligando referências',
    'Trazendo sentido e aplicação',
    'Revisando com cuidado'
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
  const animatedProgress = Math.min(100, Math.round((effectiveStep / steps.length) * 100));
  const progress = typeof statusProgress === 'number'
    ? Math.max(0, Math.min(100, Math.round(statusProgress)))
    : animatedProgress;

  return (
    <Card className="w-full max-w-4xl mt-10 sm:mt-16 items-center overflow-hidden border-primary/10 bg-gradient-to-br from-amber-50 via-card to-sky-50 py-8 shadow-sm dark:from-amber-950/20 dark:via-card dark:to-sky-950/20 sm:py-10">
      <CardContent className="flex flex-col items-center gap-4 sm:gap-5 px-6 sm:px-8 w-full">
        <div className="relative">
          <div className="absolute inset-0 rounded-full bg-amber-300/30 blur-xl animate-pulse" />
          <div className="relative h-16 w-16 rounded-full bg-gradient-to-br from-amber-500 to-stone-700 flex items-center justify-center shadow-lg ring-4 ring-white/70 dark:ring-white/10">
            <BookOpen className="h-8 w-8 text-white" />
          </div>
        </div>
        <div className="space-y-2 text-center">
          <p className="text-xs font-semibold uppercase tracking-[0.22em] text-amber-700 dark:text-amber-300">Momento de estudo</p>
          <h2 className="text-xl sm:text-2xl font-semibold text-foreground">Preparando uma leitura mais profunda</h2>
          <p className="text-sm text-muted-foreground">{statusMessage || messages[msgIndex]}</p>
        </div>

        <div className="w-full max-w-md mt-1">
          <div className="h-2.5 bg-white/70 dark:bg-muted rounded-full overflow-hidden shadow-inner">
            <div className="h-full bg-gradient-to-r from-amber-500 via-orange-400 to-sky-500 transition-all duration-500" style={{ width: `${progress}%` }} />
          </div>
        </div>
        <p className="text-xs text-center text-muted-foreground mt-1">{progress}% do caminho — {progress >= 80 ? 'a leitura está quase pronta...' : 'seguindo com cuidado, verso por verso...'}</p>

        <ul className="w-full max-w-md text-sm text-muted-foreground space-y-2 mt-2 rounded-xl border border-border/60 bg-background/70 p-4 backdrop-blur">
          {steps.map((label, i) => (
            <li key={i} className="flex items-center gap-2">
              <div className={`h-5 w-5 rounded-full border flex items-center justify-center ${i < step ? 'bg-emerald-500 border-emerald-500 text-white' : i === step ? 'border-amber-500 text-amber-600 bg-amber-50 dark:bg-amber-950/30 animate-pulse' : 'border-muted-foreground/30 text-muted-foreground/40'}`}>
                {i < step ? <CheckCircle size={14} /> : <BookOpen size={12} />}
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
            Esta passagem ainda está sendo preparada pela primeira vez. Nas próximas visitas, ela abre bem mais rápido 😊
          </p>
        )}
      </CardContent>
    </Card>
  );
};

// --- TypeScript Interfaces ---
interface BibleExplanationProps {
  testamento: string;
  livro: string;
  capitulo: string;
  versos?: string;
  version?: string;
  // SSR initial props
  initialExplanation?: any;
  initialSource?: string;
  initialExplanationId?: number;
  initialChapterVerses?: ChapterVerseText[];
}

interface ChapterVerseText {
  number: number;
  text: string;
}

type ExplanationTarget = 'chapter' | 'verse';

interface VerseExplanation {
  titulo_principal_e_texto_biblico: { titulo: string; texto: string };
  contexto_detalhado: { [key: string]: string };
  analise_exegetica?: { introducao: string; analises: { verso: string; analise: string }[] };
  analise_exegenetica?: { introducao: string; analises: { verso: string; analise: string }[] }; // backward compatibility
  teologia_da_passagem: { introducao: string; doutrinas: string[] };
  temas_principais: { introducao: string; temas: { tema: string; descricao: string }[] };
  explicacao_do_versiculo: { introducao: string; significado_profundo: string; contexto_original: string; palavras_chave: string[]; interpretacao_teologica: string };
  personagens_principais: { introducao: string; personagens: { nome: string; descricao: string }[] };
  aplicacao_contemporanea: { introducao: string; pontos_aplicacao: string[]; perguntas_reflexao: string[] };
  referencias_cruzadas: { introducao: string; referencias: { passagem: string; explicacao: string }[] };
  simbologia_biblica: { introducao: string; simbolos: { simbolo: string; significado: string }[] };
  interprete_luz_de_cristo: { introducao: string; conexao: string };
}

interface ChapterSummary {
  contexto_geral?: {
    contexto_do_livro: {
      autor_e_data: string;
      audiencia_original: string;
      proposito_do_livro: string;
      contexto_historico_cultural: string;
    };
    contexto_do_capitulo_no_livro: string;
  };
  resumo_do_capitulo: string;
  temas_principais: string[];
  personagens_importantes: string[];
  versiculos_chave: string[];
  aplicacao_pratica: string;
}

interface ErrorExplanation {
  error: string;
  message: string;
}

interface FallbackExplanation {
  type: 'error';
  requestDetails?: { book?: string; chapter?: number; verses?: string | null };
  errorDetails: { title: string; message: string; suggestion?: string };
}

type ExplanationData = VerseExplanation | ChapterSummary | ErrorExplanation | FallbackExplanation;

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
      {isOpen && <div className="px-4 pb-4 prose prose-slate dark:prose-invert max-w-none text-[16px] sm:text-[17px] leading-8">{children}</div>}
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

// --- Explanation Renderer Components ---
const VerseExplanationComponent: FC<{ explanation: VerseExplanation }> = ({ explanation }) => {
  // Supports old cached key (analise_exegenetica) and new canonical key (analise_exegetica)
  const analysis = explanation.analise_exegetica ?? explanation.analise_exegenetica;

  return (
    <div style={{ animation: 'fadeIn 0.8s ease-in-out' }}>
    <header className="mb-8">
      <Card className="text-center">
        <CardHeader className="space-y-2">
          <CardTitle className="text-3xl sm:text-4xl">
            {explanation.titulo_principal_e_texto_biblico.titulo}
          </CardTitle>
          <CardDescription>
            <blockquote className="italic text-muted-foreground">
              {explanation.titulo_principal_e_texto_biblico.texto}
            </blockquote>
          </CardDescription>
        </CardHeader>
      </Card>
    </header>

    <AdSense className="mb-6" />

    <Section title="Contexto Detalhado" icon={<BookOpen size={22} />} defaultOpen={false}>
      {explanation.contexto_detalhado?.introducao && <p>{explanation.contexto_detalhado.introducao}</p>}
      <ul>
        {explanation.contexto_detalhado && Object.entries(explanation.contexto_detalhado).map(([key, value]) => {
          if (key === 'introducao') return null;
          return <ListItem key={key}><strong>{key.replace(/_/g, ' ').replace(/(?:^|\s)\S/g, a => a.toUpperCase())}:</strong> {value}</ListItem>
        })}
      </ul>
    </Section>

    <Section title="Análise do versículo" icon={<Microscope size={22} />}>
      {analysis?.introducao && <p>{analysis.introducao}</p>}
      {analysis?.analises?.map((item, index) => (
        <div key={index} className="mt-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-md">
          <h4 className="font-bold text-indigo-600 dark:text-indigo-400">Versículo {item.verso}</h4>
          <p>{item.analise}</p>
        </div>
      ))}
    </Section>

    <Section title="Teologia da Passagem (Doutrinas)" icon={<Landmark size={22} />} defaultOpen={false}>
      {explanation.teologia_da_passagem?.introducao && <p>{explanation.teologia_da_passagem.introducao}</p>}
      <ul>{explanation.teologia_da_passagem?.doutrinas?.map((item, i) => <ListItem key={i}>{item}</ListItem>)}</ul>
    </Section>

    <AdSense className="my-8" />

    <Section title="Temas Principais" icon={<Key size={22} />}>
      {explanation.temas_principais?.introducao && <p>{explanation.temas_principais.introducao}</p>}
      <ul>{explanation.temas_principais?.temas?.map((item, i) => <ListItem key={i}><strong>{item.tema}:</strong> {item.descricao}</ListItem>)}</ul>
    </Section>

    <Section title="Explicação do Versículo" icon={<Book size={22} />}>
      {explanation.explicacao_do_versiculo?.introducao && <p>{explanation.explicacao_do_versiculo.introducao}</p>}
      <div className="mt-4 space-y-4">
        {explanation.explicacao_do_versiculo?.significado_profundo && (
          <div className="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-md">
            <h4 className="font-semibold text-indigo-700 dark:text-indigo-300">Significado Profundo</h4>
            <p className="mt-2">{explanation.explicacao_do_versiculo.significado_profundo}</p>
          </div>
        )}
        {explanation.explicacao_do_versiculo?.contexto_original && (
          <div className="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-md">
            <h4 className="font-semibold text-blue-700 dark:text-blue-300">Contexto Original</h4>
            <p className="mt-2">{explanation.explicacao_do_versiculo.contexto_original}</p>
          </div>
        )}
        {explanation.explicacao_do_versiculo?.palavras_chave?.length > 0 && (
          <div>
            <h4 className="font-semibold text-slate-700 dark:text-slate-300">Palavras-Chave</h4>
            <ul className="mt-2 flex flex-wrap gap-2">
              {explanation.explicacao_do_versiculo.palavras_chave.map((palavra, i) => (
                <li key={i} className="px-3 py-1 bg-slate-100 dark:bg-slate-700 rounded-full text-sm">{palavra}</li>
              ))}
            </ul>
          </div>
        )}
        {explanation.explicacao_do_versiculo?.interpretacao_teologica && (
          <div className="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-md">
            <h4 className="font-semibold text-purple-700 dark:text-purple-300">Interpretação Teológica</h4>
            <p className="mt-2">{explanation.explicacao_do_versiculo.interpretacao_teologica}</p>
          </div>
        )}
      </div>
    </Section>

    {explanation.personagens_principais?.personagens?.length > 0 && (
      <Section title="Personagens Principais" icon={<Users size={22} />} defaultOpen={false}>
        {explanation.personagens_principais?.introducao && <p>{explanation.personagens_principais.introducao}</p>}
        <ul>{explanation.personagens_principais.personagens.map((item, i) => <ListItem key={i} icon={<User size={18} />}><strong>{item.nome}:</strong> {item.descricao}</ListItem>)}</ul>
      </Section>
    )}

    <Section title="Aplicação Contemporânea" icon={<Target size={22} />}>
      {explanation.aplicacao_contemporanea?.introducao && <p>{explanation.aplicacao_contemporanea.introducao}</p>}
      <h4 className="font-semibold mt-4">Pontos de Aplicação</h4>
      <ul>{explanation.aplicacao_contemporanea?.pontos_aplicacao?.map((item, i) => <ListItem key={i}>{item}</ListItem>)}</ul>
      <h4 className="font-semibold mt-4">Perguntas para Reflexão</h4>
      <ul>{explanation.aplicacao_contemporanea?.perguntas_reflexao?.map((item, i) => <ListItem key={i} icon={<HelpCircle size={18} />}>{item}</ListItem>)}</ul>
    </Section>

    <Section title="Referências Cruzadas Relevantes" icon={<Link size={22} />} defaultOpen={false}>
      {explanation.referencias_cruzadas?.introducao && <p>{explanation.referencias_cruzadas.introducao}</p>}
      <ul>{explanation.referencias_cruzadas?.referencias?.map((item, i) => <ListItem key={i}><strong>{item.passagem}:</strong> {item.explicacao}</ListItem>)}</ul>
    </Section>

    {explanation.simbologia_biblica?.simbolos?.length > 0 && (
      <Section title="Simbologia Bíblica" icon={<Gem size={22} />} defaultOpen={false}>
        {explanation.simbologia_biblica?.introducao && <p>{explanation.simbologia_biblica.introducao}</p>}
        <ul>{explanation.simbologia_biblica.simbolos.map((item, i) => <ListItem key={i}><strong>{item.simbolo}:</strong> {item.significado}</ListItem>)}</ul>
      </Section>
    )}

    <Section title="Interprete à Luz de Cristo" icon={<Cross size={22} />}>
      {explanation.interprete_luz_de_cristo?.introducao && <p>{explanation.interprete_luz_de_cristo.introducao}</p>}
      {explanation.interprete_luz_de_cristo?.conexao && <p className="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-400 rounded-r-md">{explanation.interprete_luz_de_cristo.conexao}</p>}
    </Section>
    </div>
  );
};

const ChapterSummaryComponent: FC<{ summary: ChapterSummary }> = ({ summary }) => (
  <div style={{ animation: 'fadeIn 0.8s ease-in-out' }}>
    {summary.contexto_geral && (
      <Section title="Contexto Geral do Capítulo" icon={<BookOpen size={22} />}>
        <h3 className="font-semibold text-lg mt-2 mb-2 text-slate-800 dark:text-slate-200">Contexto do Livro</h3>
        <ul className="list-none p-0 m-0">
          <ListItem icon={<User size={18} />}><strong>Autor e Data:</strong> {summary.contexto_geral.contexto_do_livro.autor_e_data}</ListItem>
          <ListItem icon={<Users size={18} />}><strong>Audiência Original:</strong> {summary.contexto_geral.contexto_do_livro.audiencia_original}</ListItem>
          <ListItem icon={<Target size={18} />}><strong>Propósito do Livro:</strong> {summary.contexto_geral.contexto_do_livro.proposito_do_livro}</ListItem>
          <ListItem icon={<Landmark size={18} />}><strong>Cenário Histórico-Cultural:</strong> {summary.contexto_geral.contexto_do_livro.contexto_historico_cultural}</ListItem>
        </ul>
        <h3 className="font-semibold text-lg mt-4 mb-2 text-slate-800 dark:text-slate-200">O Capítulo no Livro</h3>
        <p className="text-base leading-relaxed">{summary.contexto_geral.contexto_do_capitulo_no_livro}</p>
      </Section>
    )}
    <Section title="Resumo do Capítulo" icon={<FileText size={22} />}>
      <p>{summary.resumo_do_capitulo}</p>
    </Section>

    <AdSense className="my-8" />

    <Section title="Temas Principais" icon={<Key size={22} />}>
      <ul>{summary.temas_principais.map((item, i) => <ListItem key={i}>{item}</ListItem>)}</ul>
    </Section>
    <Section title="Personagens Importantes" icon={<Users size={22} />}>
      <ul>{summary.personagens_importantes.map((item, i) => <ListItem key={i}>{item}</ListItem>)}</ul>
    </Section>
    <Section title="Versículos Chave" icon={<Book size={22} />}>
      <ul>{summary.versiculos_chave.map((item, i) => <ListItem key={i}>{item}</ListItem>)}</ul>
    </Section>
    <Section title="Aplicação Prática" icon={<Target size={22} />}>
      <p>{summary.aplicacao_pratica}</p>
    </Section>
  </div>
);

function isVerseExplanation(data: any): data is VerseExplanation {
  return data && typeof data === 'object' && 'titulo_principal_e_texto_biblico' in data;
}

function isChapterSummary(data: any): data is ChapterSummary {
  // Check for new or old structure to support cached explanations
  return data && typeof data === 'object' && ('resumo_do_capitulo' in data || 'contexto_geral' in data);
}

function isErrorExplanation(data: any): data is ErrorExplanation {
  return data && typeof data === 'object' && 'error' in data;
}

function isFallbackExplanation(data: any): data is FallbackExplanation {
  return data && typeof data === 'object' && data.type === 'error' && !!data.errorDetails;
}

const ExplanationRenderer: FC<{ data: ExplanationData | string | null; isChapterMode: boolean }> = ({ data, isChapterMode }) => {
  if (typeof data === 'string') {
    // Fallback for old HTML data
    return <div className="prose max-w-none" dangerouslySetInnerHTML={{ __html: data }} />;
  }

  if (isErrorExplanation(data)) {
    return <ErrorComponent message={data.message} />;
  }
  if (isChapterMode && isChapterSummary(data)) {
    return <ChapterSummaryComponent summary={data} />;
  }
  if (!isChapterMode && isVerseExplanation(data)) {
    return <VerseExplanationComponent explanation={data} />;
  }

  return <ErrorComponent message="O formato dos dados da explicação é inválido ou não corresponde ao modo de visualização (capítulo/versículo)." />;
};

// --- Main Component ---
export default function BibleExplanation(props: BibleExplanationProps) {
  return <BibleExplanationContent {...props} />;
}

function BibleExplanationContent(props: BibleExplanationProps) {
  useEffect(() => {
    if (typeof window !== 'undefined') {
      try {
        const theme = localStorage.getItem('theme');
        if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      } catch (e) { }
    }
  }, []);

  useEffect(() => {
    if (typeof window === 'undefined' || !('scrollRestoration' in window.history)) return;
    const prev = window.history.scrollRestoration;
    window.history.scrollRestoration = 'manual';
    return () => {
      window.history.scrollRestoration = prev;
    };
  }, []);

  function extractVersion() {
    if (typeof window === 'undefined') return (props.version || 'nvi').toLowerCase();
    const params = new URLSearchParams(window.location.search);
    return (params.get('version') || props.version || 'nvi').toLowerCase();
  }

  // Normalize possible stringified JSON or legacy HTML
  const normalizeExplanation = (value: any): ExplanationData | string | null => {
    if (!value) return null;
    if (typeof value === 'string') {
      try {
        return JSON.parse(value);
      } catch {
        return value; // legacy HTML
      }
    }
    if (typeof value === 'object') return value as ExplanationData;
    return null;
  };

  const initialVerses = (() => {
    if (typeof window === 'undefined') return props.versos || null;
    const params = new URLSearchParams(window.location.search);
    const queryVerses = params.get('versiculos') || params.get('verses');
    if (queryVerses) return queryVerses;
    const pathParts = window.location.pathname.split('/');
    if (pathParts.length > 5) {
      const slug = pathParts[pathParts.length - 1];
      const match = slug.match(/^(\d+(?:,\d+)*)-explicacao-biblica/);
      if (match) return match[1];
    }
    return props.versos || null;
  })();
  const [loading, setLoading] = useState(!props.initialExplanation);
  const [explanation, setExplanation] = useState<ExplanationData | string | null>(normalizeExplanation(props.initialExplanation));
  const [source, setSource] = useState(props.initialSource || '');
  const [explanationId, setExplanationId] = useState<number | null>(props.initialExplanationId || null);
  const [showFeedbackForm, setShowFeedbackForm] = useState(false);
  const [feedbackComment, setFeedbackComment] = useState('');
  const [feedbackSubmitted, setFeedbackSubmitted] = useState(false);
  const [feedbackType, setFeedbackType] = useState<'positive' | 'negative' | null>(null);
  const [showCelebration, setShowCelebration] = useState(false);
  const [feedbackStats, setFeedbackStats] = useState<{ positive_count: number; negative_count: number; total_count: number; positive_percentage: number; negative_percentage: number; } | null>(null);
  const [streamStatus, setStreamStatus] = useState('Conectando ao serviço...');
  const [streamProgress, setStreamProgress] = useState<number | null>(null);
  const [version, setVersion] = useState<string>(extractVersion());
  const [chapterVerses, setChapterVerses] = useState<ChapterVerseText[]>(props.initialChapterVerses || []);
  const [chapterTextLoading, setChapterTextLoading] = useState<boolean>(!props.initialChapterVerses?.length);
  const [shouldGenerateExplanation, setShouldGenerateExplanation] = useState<boolean>(false);
  const [explanationTarget, setExplanationTarget] = useState<ExplanationTarget>(initialVerses ? 'verse' : 'chapter');
  const [lookupLoading, setLookupLoading] = useState<boolean>(false);
  const [readingWidthPercent, setReadingWidthPercent] = useState<number>(66);
  const [isResizingPanels, setIsResizingPanels] = useState<boolean>(false);
  const splitPaneRef = useRef<HTMLDivElement | null>(null);

  const isMobile = useMediaQuery('(max-width: 640px)');
  const { testamento, livro: book, capitulo: chapter } = props;
  const slugService = SlugService.getInstance();
  const bookSlug = slugService.livroParaSlug(book);

  // Helper: rolar para o topo ao trocar de versículo/capítulo/param
  const scrollToTop = (smooth: boolean = true) => {
    const behavior: ScrollBehavior = smooth ? 'smooth' : 'auto';
    try {
      window.scrollTo({ top: 0, left: 0, behavior });
      requestAnimationFrame(() => window.scrollTo({ top: 0, left: 0, behavior: 'auto' }));
      setTimeout(() => window.scrollTo({ top: 0, left: 0, behavior: 'auto' },), 0);
    } catch {
      window.scrollTo(0, 0);
    }
  };

  function extractVerses() {
    if (typeof window === 'undefined') return props.versos || null;
    const params = new URLSearchParams(window.location.search);
    let v = params.get('versiculos') || params.get('verses');
    if (v) return v;
    const pathParts = window.location.pathname.split('/');
    if (pathParts.length > 5) {
      const slug = pathParts[pathParts.length - 1];
      const match = slug.match(/^(\d+(?:,\d+)*)-explicacao-biblica/);
      if (match) return match[1];
    }
    return props.versos || null;
  }
  const [versesState, setVersesState] = useState<string | null>(initialVerses);
  const verses = versesState;
  const isChapterMode = !verses;
  const isStandaloneVersePage = typeof window !== 'undefined'
    ? window.location.pathname.split('/').filter(Boolean).length > 4
    : Boolean(props.versos);
  const isInlineReadingMode = !isStandaloneVersePage;
  const shouldLoadExplanation = shouldGenerateExplanation;
  const selectedVerseText = verses
    ? chapterVerses.find((verse) => verse.number === Number(verses))?.text
    : null;

  const maxVerses = chapterVerses.length || 50;

  const withVersionParam = (path: string) => {
    if (!version || version === 'nvi') return path;
    return `${path}?version=${encodeURIComponent(version)}`;
  };

  const buildChapterUrl = () => withVersionParam(`/explicacao/${testamento}/${bookSlug}/${chapter}`);

  const buildChapterVerseUrl = (verseNumber: number | string) => {
    const params = new URLSearchParams();
    params.set('verses', String(verseNumber));
    if (version && version !== 'nvi') {
      params.set('version', version);
    }

    return `/explicacao/${testamento}/${bookSlug}/${chapter}?${params.toString()}`;
  };

  const fullExplanationUrl = verses
    ? withVersionParam(`/explicacao/${testamento}/${bookSlug}/${chapter}/${verses}-explicacao-biblica`)
    : buildChapterUrl();

  useEffect(() => {
    if (!isResizingPanels) return;

    const handlePointerMove = (event: PointerEvent) => {
      const bounds = splitPaneRef.current?.getBoundingClientRect();
      if (!bounds) return;

      const nextPercent = ((event.clientX - bounds.left) / bounds.width) * 100;
      setReadingWidthPercent(Math.min(76, Math.max(46, Math.round(nextPercent))));
    };

    const handlePointerUp = () => {
      setIsResizingPanels(false);
      document.body.style.cursor = '';
      document.body.style.userSelect = '';
    };

    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
    window.addEventListener('pointermove', handlePointerMove);
    window.addEventListener('pointerup', handlePointerUp);

    return () => {
      document.body.style.cursor = '';
      document.body.style.userSelect = '';
      window.removeEventListener('pointermove', handlePointerMove);
      window.removeEventListener('pointerup', handlePointerUp);
    };
  }, [isResizingPanels]);

  const buildExplanationApiUrl = (stream = false, cacheBust = false): string => {
    const basePath = stream ? '/api/explanation-stream' : '/api/explanation';
    let apiUrl = `${basePath}/${testamento}/${bookSlug}/${chapter}`;
    const params = new URLSearchParams();
    if (verses) {
      params.set('verses', verses);
    }
    if (cacheBust) {
      params.set('_ts', String(Date.now()));
    }
    const query = params.toString();
    if (query) {
      apiUrl += `?${query}`;
    }

    return apiUrl;
  };

  const buildLookupApiUrl = (): string => {
    let apiUrl = `/api/explanation/${testamento}/${bookSlug}/${chapter}`;
    const params = new URLSearchParams();
    if (verses) {
      params.set('verses', verses);
    }
    params.set('version', version);
    params.set('lookup', '1');
    const query = params.toString();
    if (query) {
      apiUrl += `?${query}`;
    }

    return apiUrl;
  };

  const applyExplanationPayload = (data: any) => {
    const explanationContent = data?.explanation;
    setExplanation(normalizeExplanation(explanationContent));
    setSource(data?.origin || 'unknown');
    setExplanationId(data?.id || null);
    if (data?.id) fetchFeedbackStats(data.id);
  };

  // Sync verses state when server provides initial props (SSR or Inertia navigation)
  useEffect(() => {
    if (props.initialExplanation !== undefined) {
      setExplanation(normalizeExplanation(props.initialExplanation));
      setSource(props.initialSource || 'unknown');
      setExplanationId(props.initialExplanationId || null);
      if (props.initialExplanationId) fetchFeedbackStats(props.initialExplanationId);
      setLoading(!props.initialExplanation);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [props.initialExplanation, props.initialSource, props.initialExplanationId]);

  useEffect(() => {
    // Keep verses in sync when SSR prop changes
    if (props.versos !== undefined) {
      setVersesState(props.versos || null);
      setShouldGenerateExplanation(false);
      setExplanationTarget(props.versos ? 'verse' : 'chapter');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [props.versos]);

  useEffect(() => {
    const controller = new AbortController();

    async function lookupExistingExplanation() {
      if (shouldGenerateExplanation || isStandaloneVersePage) {
        return;
      }

      setLookupLoading(true);
      try {
        const response = await fetch(buildLookupApiUrl(), {
          signal: controller.signal,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error(`Lookup responded with status: ${response.status}`);
        }

        const data = await response.json();
        if (data?.explanation) {
          applyExplanationPayload(data);
        } else {
          setExplanation(null);
          setExplanationId(null);
          setSource('');
        }
      } catch (error: any) {
        if (error?.name !== 'AbortError') {
          console.error('Error looking up existing explanation:', error);
        }
      } finally {
        if (!controller.signal.aborted) {
          setLookupLoading(false);
        }
      }
    }

    lookupExistingExplanation();

    return () => controller.abort();
  }, [testamento, bookSlug, chapter, verses, version, shouldGenerateExplanation, isStandaloneVersePage]);

  // Client fetch with SSR-skip only on first hydration
  const didUseSSR = useRef(false);
  useEffect(() => {
    const controller = new AbortController();
    const signal = controller.signal;
    let streamConnection: EventSource | null = null;

    const closeStream = () => {
      if (streamConnection) {
        streamConnection.close();
        streamConnection = null;
      }
    };

    const fetchViaSSE = async (): Promise<void> => {
      if (typeof window === 'undefined' || typeof window.EventSource === 'undefined') {
        throw new Error('SSE unavailable');
      }

      setStreamStatus('Conectando ao streaming...');
      setStreamProgress(null);
      const streamUrl = buildExplanationApiUrl(true);

      return new Promise((resolve, reject) => {
        let settled = false;
        const timeoutId = window.setTimeout(() => {
          if (settled) return;
          settled = true;
          closeStream();
          reject(new Error('stream_timeout'));
        }, 65000);

        const finish = () => {
          clearTimeout(timeoutId);
          closeStream();
        };

        const fail = (error: Error) => {
          if (settled) return;
          settled = true;
          finish();
          reject(error);
        };

        streamConnection = new EventSource(streamUrl);

        streamConnection.addEventListener('status', (event: MessageEvent) => {
          try {
            const payload = JSON.parse(event.data);
            if (payload?.message) {
              setStreamStatus(payload.message);
            }
            if (typeof payload?.progress === 'number') {
              setStreamProgress(payload.progress);
            }
          } catch {
            // ignore malformed status event
          }
        });

        streamConnection.addEventListener('complete', (event: MessageEvent) => {
          if (settled) return;
          try {
            const payload = JSON.parse(event.data);
            applyExplanationPayload(payload);
            setStreamStatus('Concluído');
            setStreamProgress(100);
            settled = true;
            finish();
            resolve();
          } catch {
            fail(new Error('stream_invalid_payload'));
          }
        });

        streamConnection.addEventListener('error', (event: MessageEvent) => {
          let message = 'stream_error';
          try {
            const payload = JSON.parse(event.data);
            if (payload?.message) {
              message = payload.message;
            }
          } catch {
            // ignore malformed error payload
          }
          fail(new Error(message));
        });

        streamConnection.onerror = () => {
          fail(new Error('stream_connection_error'));
        };
      });
    };

    const fetchViaHttp = async (cacheBust = false) => {
      const apiUrl = buildExplanationApiUrl(false, cacheBust);
      const response = await fetch(apiUrl, {
        signal,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      });

      if (!response.ok) {
        throw new Error(`API responded with status: ${response.status}`);
      }

      const data = await response.json();
      applyExplanationPayload(data);
    };

    async function fetchExplanation() {
      setLoading(true);
      try {
        await fetchViaSSE();
      } catch (error: any) {
        if (error?.name === 'AbortError') return;

        console.warn('SSE failed, falling back to HTTP:', error);
        setStreamStatus('Conexão em tempo real indisponível, usando modo padrão...');
        setStreamProgress(null);

        try {
          await fetchViaHttp();
        } catch (httpError: any) {
          if (httpError?.name === 'AbortError') return; // ignore aborted requests
          console.error('Error fetching explanation:', httpError);

          const isConnectionError = httpError?.message?.includes('Failed to fetch') ||
            httpError?.message?.includes('ERR_CONNECTION_CLOSED') ||
            httpError?.message?.includes('net::ERR_CONNECTION_CLOSED');

          if (isConnectionError) {
            setTimeout(async () => {
              try {
                const retryResponse = await fetch(buildExplanationApiUrl(false), {
                  headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                  }
                });

                if (retryResponse.ok) {
                  const retryData = await retryResponse.json();
                  applyExplanationPayload(retryData);
                  setLoading(false);

                  return;
                }
              } catch (retryError) {
                console.error('Retry failed:', retryError);
              }

              setExplanation({
                error: 'connection_failed',
                message: 'A explicação pode ter sido gerada, mas houve um problema de conexão. Clique em "Tentar novamente" ou atualize a página.'
              });
              setLoading(false);
            }, 3000);

            return;
          }

          let errorMessage = 'Erro ao buscar a explicação. Por favor, tente novamente.';
          if (httpError?.message?.includes('timeout')) {
            errorMessage = 'A requisição demorou muito. Tente novamente em alguns instantes.';
          }

          setExplanation({
            error: 'fetch_failed',
            message: errorMessage
          });
        }
      } finally {
        if (!signal.aborted) setLoading(false);
        closeStream();
      }
    }
    if (!shouldLoadExplanation) {
      setLoading(false);
      return () => {
        closeStream();
        controller.abort();
      };
    }

    if (!didUseSSR.current && props.initialExplanation) {
      // First render with SSR data: use it and skip fetching
      setLoading(false);
      didUseSSR.current = true;
      return () => {
        closeStream();
        controller.abort();
      };
    }
    fetchExplanation();
    return () => {
      closeStream();
      controller.abort();
    };
  }, [testamento, bookSlug, chapter, verses, shouldLoadExplanation]);

  // Manual refetch for Retry action (adds cache-busting param)
  const retryRefetch = async () => {
    setLoading(true);
    setExplanation(null);
    setStreamStatus('Tentando novamente...');
    setStreamProgress(null);
    try {
      const response = await fetch(buildExplanationApiUrl(false, true), {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      });
      if (!response.ok) throw new Error(`API responded with status: ${response.status}`);
      const data = await response.json();
      applyExplanationPayload(data);
      scrollToTop();
    } catch (error) {
      console.error('Retry fetch failed:', error);
      setExplanation({ error: 'fetch_failed', message: 'Ainda não foi possível gerar a explicação. Tente novamente em alguns instantes.' });
    } finally {
      setLoading(false);
    }
  };

  // Canonical URL rewrite for verse mode
  useEffect(() => {
    if (typeof window === 'undefined') return;
    try {
      const slugifiedBook = bookSlug;
      const params = new URLSearchParams(window.location.search);
      params.delete('verses');
      params.delete('versiculos');
      if (version && version !== 'nvi') {
        params.set('version', version);
      } else {
        params.delete('version');
      }
      const query = params.toString();
      if (verses && isStandaloneVersePage) {
        const slug = verses + '-explicacao-biblica';
        const expectedPath = `/explicacao/${testamento}/${slugifiedBook}/${chapter}/${slug}${query ? `?${query}` : ''}`;
        const current = `${window.location.pathname}${window.location.search}`;
        if (current !== expectedPath) {
          window.history.replaceState({}, '', expectedPath);
        }
      } else {
        const expectedPath = `/explicacao/${testamento}/${slugifiedBook}/${chapter}${query ? `?${query}` : ''}`;
        const current = `${window.location.pathname}${window.location.search}`;
        if (current !== expectedPath) {
          window.history.replaceState({}, '', expectedPath);
        }
      }
    } catch { /* noop */ }
  }, [testamento, bookSlug, chapter, verses, version, isStandaloneVersePage]);

  useEffect(() => {
    const loadChapterText = async () => {
      setChapterTextLoading(true);
      try {
        const response = await fetch(`/api/bible-text/${version}/${testamento}/${bookSlug}/${chapter}`, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        });
        if (!response.ok) throw new Error('Failed to load chapter text');
        const data = await response.json();
        setChapterVerses(Array.isArray(data?.verses) ? data.verses : []);
      } catch (error) {
        console.error('Error loading chapter text:', error);
        setChapterVerses((current) => current.length > 0 ? current : []);
      } finally {
        setChapterTextLoading(false);
      }
    };

    loadChapterText();
  }, [version, testamento, bookSlug, chapter]);

  // Sync with browser back/forward navigation
  useEffect(() => {
    const onPop = () => {
      try {
        const v = extractVerses();
        const nextVersion = extractVersion();
        setVersesState(v);
        setVersion(nextVersion);
        setShouldGenerateExplanation(false);
        setExplanationTarget(v ? 'verse' : 'chapter');
        setExplanation(null);
        setExplanationId(null);
        setLoading(false);
        // Volta para o topo ao navegar pelo histórico
        scrollToTop(false);
      } catch { /* noop */ }
    };
    window.addEventListener('popstate', onPop);
    return () => window.removeEventListener('popstate', onPop);
  }, []);

  // SEO and other side effects
  useEffect(() => {
    // ... (fetchSeoMetadata, fetchRelatedLinks can be called here)
  }, [testamento, bookSlug, chapter, verses]);

  // Sempre rolar para o topo quando os parâmetros de navegação mudarem
  useEffect(() => {
    scrollToTop(false);
  }, [testamento, bookSlug, chapter, verses]);

  const navigateToVerse = (verseNumber: number) => {
    if (verseNumber < 1 || verseNumber > maxVerses) return;
    const url = buildChapterVerseUrl(verseNumber);
    try {
      window.history.pushState({}, '', url);
    } catch { /* noop */ }
    if (!isInlineReadingMode) {
      scrollToTop();
    }
    setVersesState(String(verseNumber));
    setShouldGenerateExplanation(false);
    setExplanationTarget('verse');
    setExplanation(null);
    setExplanationId(null);
    setLoading(false);
  };

  const closeInlineVerse = () => {
    const url = buildChapterUrl();
    try {
      window.history.pushState({}, '', url);
    } catch { /* noop */ }
    setVersesState(null);
    setShouldGenerateExplanation(false);
    setExplanationTarget('chapter');
    setExplanation(null);
    setExplanationId(null);
    setLoading(false);
  };

  const triggerExplanationGeneration = (target: ExplanationTarget) => {
    setExplanationTarget(target);
    if (target === 'chapter') {
      setVersesState(null);
    }
    setShouldGenerateExplanation(true);
    setLoading(true);
  };

  const renderExplanationPanelContent = () => {
    if (!verses) {
      return (
        <div className="space-y-3">
          {shouldGenerateExplanation && loading ? (
            <DynamicLoading statusMessage={streamStatus} statusProgress={streamProgress} />
          ) : shouldGenerateExplanation && explanation ? (
            <ExplanationRenderer data={explanation} isChapterMode={true} />
          ) : lookupLoading ? (
            <p className="text-sm text-muted-foreground">Procurando explicação já existente...</p>
          ) : explanation ? (
            <ExplanationRenderer data={explanation} isChapterMode={true} />
          ) : (
            <p className="text-sm text-muted-foreground">Clique em um verso para abrir a explicação aqui, ou use o botão no topo para entender o capítulo inteiro.</p>
          )}
        </div>
      );
    }

    if (!shouldGenerateExplanation) {
      return (
        <div className="space-y-3">
          {lookupLoading ? (
            <p className="text-sm text-muted-foreground">Procurando explicação já existente...</p>
          ) : explanation ? (
            <ExplanationRenderer data={explanation} isChapterMode={false} />
          ) : (
            <>
              <p className="text-sm text-muted-foreground">Versículo selecionado: {book} {chapter}:{verses}. Clique abaixo para abrir a explicação.</p>
              <button
                onClick={() => triggerExplanationGeneration('verse')}
                className="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors text-sm font-medium"
              >
                <BookOpen size={16} />
                Entender este verso
              </button>
              <a
                href={`/explicacao/${testamento}/${bookSlug}/${chapter}/${verses}-explicacao-biblica?version=${version}`}
                className="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-border hover:bg-muted transition-colors text-sm font-medium"
              >
                Abrir página completa
              </a>
            </>
          )}
        </div>
      );
    }

    if (loading) {
      return <DynamicLoading statusMessage={streamStatus} statusProgress={streamProgress} />;
    }

    if (isFallbackExplanation(explanation)) {
      return (
        <div className="p-4 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 rounded-r-lg">
          <p className="font-medium">{explanation.errorDetails.title}</p>
          <p className="text-sm mt-1">{explanation.errorDetails.message}</p>
        </div>
      );
    }

    return <ExplanationRenderer data={explanation} isChapterMode={explanationTarget === 'chapter'} />;
  };

  const navigateToNextVerse = () => {
    if (!verses) return;
    const versesList = verses.split(',');
    const lastVerse = parseInt(versesList[versesList.length - 1], 10);
    if (lastVerse < maxVerses) navigateToVerse(lastVerse + 1);
  };

  const navigateToPreviousVerse = () => {
    if (!verses) return;
    const versesList = verses.split(',');
    const firstVerse = parseInt(versesList[0], 10);
    if (firstVerse > 1) navigateToVerse(firstVerse - 1);
  };

  const fetchFeedbackStats = async (id: number) => {
    try {
      const response = await fetch(`/api/feedback/stats/${id}`);
      if (response.ok) setFeedbackStats(await response.json());
    } catch (error) {
      console.error('Error fetching feedback stats:', error);
    }
  };

  const submitFeedback = async (isPositive: boolean) => {
    if (!explanationId) return;
    setFeedbackType(isPositive ? 'positive' : 'negative');
    if (isPositive) {
      try {
        const response = await fetch('/api/feedback', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
          body: JSON.stringify({ bible_explanation_id: explanationId, is_positive: true, comment: '', testamento, book, chapter, verses }),
        });
        if (response.ok) {
          setFeedbackSubmitted(true);
          setShowCelebration(true);
          setTimeout(() => setShowCelebration(false), 8000);
          fetchFeedbackStats(explanationId);
        }
      } catch (error) { console.error('Error submitting positive feedback:', error); }
    } else {
      setShowFeedbackForm(true);
    }
  };

  const submitFeedbackWithComment = async () => {
    if (!explanationId || !feedbackType) return;
    try {
      const response = await fetch('/api/feedback', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
        body: JSON.stringify({ bible_explanation_id: explanationId, is_positive: feedbackType === 'positive', comment: feedbackComment, testamento, book, chapter, verses }),
      });
      if (response.ok) {
        setFeedbackSubmitted(true);
        setShowFeedbackForm(false);
        fetchFeedbackStats(explanationId);
      }
    } catch (error) { console.error('Error submitting feedback with comment:', error); }
  };

  return (
    <>
      <AppLayout>
        <div className="container max-w-7xl mx-auto px-2 py-2 sm:p-4">
          <header className="mb-2 sm:mb-5 grid grid-cols-[auto_1fr_auto] items-center gap-2 bg-gradient-to-r from-card via-card to-primary/5 text-card-foreground shadow-sm rounded-xl p-2 sm:p-4 sticky top-1 sm:top-2 z-10 border border-border/70 backdrop-blur">
              <button onClick={() => {
                try {
                  // Sempre voltar para a visão de versículos do capítulo atual
                  // No fluxo novo, voltamos para a lista de capítulos do livro.
                  const gridTestament = (props.testamento === 'antigo' ? 'velho' : 'novo');
                  const currentBookSlug = bookSlug;
                  const versiculosUrl = `/biblia/${gridTestament}/${currentBookSlug}`;
                  
                  // Usar router.visit para navegação SPA consistente
                  router.visit(versiculosUrl, {
                    preserveScroll: false,
                    replace: false
                  });
                } catch (error) {
                  console.error('Erro na navegação:', error);
                  // Fallback para navegação direta
                  const gridTestament = (props.testamento === 'antigo' ? 'velho' : 'novo');
                  const currentBookSlug = bookSlug;
                  window.location.href = `/biblia/${gridTestament}/${currentBookSlug}`;
                }
              }} className="inline-flex h-9 w-9 sm:w-auto items-center justify-center gap-2 rounded-lg bg-primary/10 hover:bg-primary/20 text-primary hover:text-primary/80 transition-colors font-medium sm:px-3" aria-label="Voltar para capítulos">
                <ChevronLeft size={isMobile ? 18 : 22} />
                <span className="hidden sm:inline">Capítulos</span>
              </button>
              <div className="min-w-0 text-center sm:text-left">
                <h1 className="truncate text-base sm:text-xl font-bold text-gray-800 dark:text-gray-200">
                  {book} {chapter}{verses && <span className="ml-1 text-xs sm:text-sm text-gray-600 dark:text-gray-400">:{verses}</span>}
                </h1>
                <p className="hidden sm:block text-xs text-muted-foreground">{isInlineReadingMode ? 'Leia o capítulo e toque em um verso para estudar.' : 'Página completa da explicação.'}</p>
              </div>
            <div className="flex items-center justify-end gap-1.5 sm:gap-3">
              <button
                onClick={() => triggerExplanationGeneration('chapter')}
                className="hidden sm:inline-flex h-9 items-center gap-2 rounded-md border border-primary/30 bg-primary/10 px-3 text-sm font-medium text-primary hover:bg-primary/15 transition-colors"
              >
                <BookOpen size={16} />
                Entender capítulo
              </button>
              <select
                className="h-9 rounded-md border border-border bg-background px-2 text-xs sm:text-sm shadow-sm"
                value={version}
                onChange={(event) => {
                  const nextVersion = event.target.value;
                  setVersion(nextVersion);
                  setShouldGenerateExplanation(false);
                  setExplanation(null);
                  setExplanationId(null);
                }}
              >
                <option value="acf">ACF</option>
                <option value="nvi">NVI</option>
                <option value="aa">AA</option>
              </select>
              {verses && (
              <div className="flex items-center gap-1">
                <button onClick={navigateToPreviousVerse} className="inline-flex h-9 w-9 items-center justify-center rounded-md bg-indigo-50 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-slate-700" aria-label="Versículo anterior">
                  <ArrowLeft size={isMobile ? 16 : 18} />
                </button>
                <button onClick={navigateToNextVerse} className="inline-flex h-9 w-9 items-center justify-center rounded-md bg-indigo-50 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-slate-700" aria-label="Próximo versículo">
                  <ArrowRight size={isMobile ? 16 : 18} />
                </button>
              </div>
              )}
            </div>
            <div className="col-span-3 flex sm:hidden">
              <button
                onClick={() => triggerExplanationGeneration('chapter')}
                className="inline-flex h-9 w-full items-center justify-center gap-2 rounded-md border border-primary/30 bg-primary/10 px-3 text-xs font-medium text-primary hover:bg-primary/15 transition-colors"
              >
                <BookOpen size={15} />
                Entender capítulo
              </button>
            </div>
          </header>
          {isInlineReadingMode ? (
            <div
              ref={splitPaneRef}
              className="flex flex-col lg:flex-row gap-3 sm:gap-4 mt-2 sm:mt-4"
            >
              <div
                className="bg-card text-card-foreground rounded-xl p-3 sm:p-6 shadow-sm border border-border/80"
                style={!isMobile ? { flexBasis: `${readingWidthPercent}%` } : undefined}
              >
                <div className="mb-3 sm:mb-4 flex items-center justify-between gap-3">
                  <h2 className="text-base sm:text-lg font-semibold">Texto bíblico ({version.toUpperCase()})</h2>
                  {chapterTextLoading && chapterVerses.length > 0 && (
                    <span className="text-xs text-muted-foreground">Atualizando...</span>
                  )}
                </div>
                {chapterTextLoading && chapterVerses.length === 0 ? (
                  <p className="text-sm text-muted-foreground">Carregando capítulo...</p>
                ) : (
                  <div className={`space-y-2.5 sm:space-y-3 transition-opacity duration-200 ${chapterTextLoading ? 'opacity-60' : 'opacity-100'}`}>
                    {chapterVerses.map((verse) => {
                      const isSelectedVerse = Number(verses) === verse.number;

                      return (
                        <div key={verse.number} className="space-y-2">
                          <button
                            className={`w-full text-left rounded-lg p-3 sm:p-3.5 border transition-colors leading-7 text-[15px] sm:text-base ${isSelectedVerse ? 'border-primary bg-primary/10' : 'border-border hover:border-primary/50 hover:bg-primary/5'}`}
                            onClick={() => navigateToVerse(verse.number)}
                          >
                            <span className="font-semibold mr-2">{verse.number}</span>
                            <span>{verse.text}</span>
                          </button>
                          {isMobile && isSelectedVerse && (
                            <div className="rounded-lg border border-primary/20 bg-background p-3 shadow-sm">
                              <div className="mb-3 flex items-center justify-between gap-3">
                                <h3 className="text-sm font-semibold">Explicação do verso</h3>
                                <div className="flex items-center gap-2">
                                  <a
                                    href={fullExplanationUrl}
                                    aria-label="Abrir página completa da explicação"
                                    title="Abrir página completa"
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border hover:bg-muted transition-colors"
                                  >
                                    <ExternalLink size={15} />
                                  </a>
                                  <button
                                    onClick={closeInlineVerse}
                                    className="text-xs px-3 py-1.5 rounded-md border border-border hover:bg-muted transition-colors"
                                  >
                                    Fechar
                                  </button>
                                </div>
                              </div>
                              {renderExplanationPanelContent()}
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
              {!isMobile && (
                <button
                  type="button"
                  aria-label="Redimensionar leitura e explicação"
                  onPointerDown={(event) => {
                    event.preventDefault();
                    setIsResizingPanels(true);
                  }}
                  className="hidden lg:flex w-2 shrink-0 cursor-col-resize items-stretch justify-center rounded-full bg-transparent hover:bg-primary/10 focus:outline-none focus:ring-2 focus:ring-primary/30"
                >
                  <span className="my-8 w-1 rounded-full bg-border" />
                </button>
              )}
              <aside
                className={`${isMobile && verses ? 'hidden' : ''} bg-card text-card-foreground rounded-xl p-3 sm:p-6 shadow-sm border border-border/80`}
                style={!isMobile ? { flexBasis: `${100 - readingWidthPercent}%` } : undefined}
              >
                <div className="flex items-center justify-between gap-3 mb-4">
                  <h2 className="text-base sm:text-lg font-semibold">
                    {explanationTarget === 'chapter' ? 'Explicação do capítulo' : 'Explicação do verso'}
                  </h2>
                  <a
                    href={fullExplanationUrl}
                    aria-label="Abrir página completa da explicação"
                    title="Abrir página completa"
                    className="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border hover:bg-muted transition-colors"
                  >
                    <ExternalLink size={16} />
                  </a>
                </div>
                {renderExplanationPanelContent()}
              </aside>
            </div>
          ) : (
            <div className="bg-card text-card-foreground rounded-xl p-3 sm:p-6 mt-2 sm:mt-4 shadow-sm border border-border/80 sm:text-[16.5px] leading-8">
              {verses && selectedVerseText && (
                <div className="mb-6 rounded-lg border border-primary/15 bg-primary/5 p-4">
                  <p className="text-xs font-semibold uppercase tracking-wide text-primary mb-2">Texto bíblico</p>
                  <h2 className="text-lg font-semibold mb-2">{book} {chapter}:{verses}</h2>
                  <p className="text-base leading-8 text-foreground">{selectedVerseText}</p>
                </div>
              )}
              {!shouldLoadExplanation && !explanation ? (
                <div className="space-y-6">
                  <section className="rounded-xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-card to-sky-50 p-4 shadow-sm dark:border-amber-800/40 dark:from-amber-950/20 dark:via-card dark:to-sky-950/20 sm:p-5">
                    <p className="text-xs font-semibold uppercase tracking-[0.22em] text-amber-700 dark:text-amber-300">Estudo bíblico</p>
                    <h2 className="mt-2 text-xl font-semibold text-foreground">
                      {book} {chapter}:{verses} explicado
                    </h2>
                    <p className="mt-3 text-sm leading-7 text-muted-foreground">
                      Esta página reúne o texto de {book} {chapter}:{verses} dentro do capítulo completo. A explicação detalhada ainda não foi aberta nesta visita; você pode ler o contexto, navegar pelos versículos e gerar uma análise verso a verso quando quiser.
                    </p>
                    <div className="mt-4 flex flex-col gap-2 sm:flex-row">
                      <button
                        onClick={() => triggerExplanationGeneration('verse')}
                        className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors text-sm font-medium"
                      >
                        <BookOpen size={16} />
                        Gerar explicação deste verso
                      </button>
                      <a
                        href={buildChapterUrl()}
                        className="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md border border-border hover:bg-muted transition-colors text-sm font-medium"
                      >
                        Ler capítulo completo
                        <ArrowRight size={16} />
                      </a>
                    </div>
                  </section>

                  {chapterVerses.length > 0 && (
                    <section className="rounded-xl border border-border/80 bg-card p-4 sm:p-5">
                      <div className="mb-4">
                        <h2 className="text-lg font-semibold">Contexto de {book} {chapter}</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                          Leia os versículos ao redor para entender melhor a passagem antes de gerar a explicação.
                        </p>
                      </div>
                      <div className="space-y-2.5">
                        {chapterVerses.map((verse) => {
                          const isSelectedVerse = Number(verses) === verse.number;

                          return (
                            <a
                              key={verse.number}
                              href={buildChapterVerseUrl(verse.number)}
                              className={`block rounded-lg border p-3 leading-7 transition-colors ${isSelectedVerse ? 'border-primary bg-primary/10' : 'border-border hover:border-primary/50 hover:bg-primary/5'}`}
                            >
                              <span className="font-semibold mr-2">{verse.number}</span>
                              <span>{verse.text}</span>
                            </a>
                          );
                        })}
                      </div>
                    </section>
                  )}
                </div>
              ) : loading ? (
                <DynamicLoading statusMessage={streamStatus} statusProgress={streamProgress} />
              ) : isFallbackExplanation(explanation) ? (
                <div className="p-5 sm:p-6 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 dark:border-amber-400 rounded-r-lg">
                  <div className="flex items-start gap-3">
                    <AlertTriangle className="h-6 w-6 text-amber-600 dark:text-amber-300 mt-0.5" />
                    <div className="flex-1">
                      <h3 className="font-semibold text-amber-800 dark:text-amber-200">{explanation.errorDetails.title}</h3>
                      <p className="text-amber-700 dark:text-amber-300 mt-1">{explanation.errorDetails.message}</p>
                      {explanation.errorDetails.suggestion && (
                        <p className="text-amber-700/90 dark:text-amber-300/90 text-sm mt-2">{explanation.errorDetails.suggestion}</p>
                      )}
                      <div className="mt-4">
                        <button
                          onClick={retryRefetch}
                          className="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors text-sm font-medium"
                        >
                          <RefreshCw size={16} />
                          Tentar novamente
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              ) : (
                <ExplanationRenderer data={explanation} isChapterMode={isChapterMode} />
              )}
            </div>
          )}

          {/* Action Area */}
          {!loading && explanationId && (
            <div className="mt-8 pt-6 border-t border-border">
              {/* Feedback & Support Card */}
              <div className="bg-card text-card-foreground border border-border rounded-lg shadow-sm transition-all duration-300 p-5 sm:p-6">
                {/* Support Section */}
                <div className="flex flex-col items-center text-center">
                  <div className="inline-flex items-center justify-center bg-muted/50 p-3 rounded-full mb-3 shadow-inner">
                    <Heart className="h-5 w-5 text-destructive" />
                  </div>
                  <h3 className="font-semibold text-lg text-foreground mb-2">Nos ajude a continuar!</h3>
                  <p className="text-sm text-muted-foreground mb-4 max-w-md mx-auto">Seu apoio é fundamental para mantermos e melhorarmos esta ferramenta de estudo da Bíblia.</p>
                  <DonateButton size="md" className="w-full sm:w-auto max-w-xs shadow-sm hover:shadow transition-shadow" />
                </div>

                <div className="mt-6 pt-6 border-t border-border">
                  <h3 className="text-base sm:text-lg font-semibold mb-4 text-center text-foreground">Esta explicação foi útil?</h3>
                  {feedbackSubmitted ? (
                    <div
                      className={`p-3 sm:p-4 rounded-md border text-center ${feedbackType === 'positive'
                        ? 'bg-green-50 text-green-800 border-green-200 dark:bg-green-950/50 dark:text-green-300 dark:border-green-800/50' + (showCelebration ? ' celebration' : '')
                        : 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/50'}`}
                    >
                      <p className="text-sm sm:text-base">
                        {feedbackType === 'positive'
                          ? 'Obrigado pelo seu feedback positivo!'
                          : 'Obrigado! Usaremos seu feedback para melhorar.'}
                      </p>
                    </div>
                  ) : (
                    <div className="flex flex-col space-y-4">
                      <div className="flex space-x-3 sm:space-x-4">
                        <button
                          onClick={() => submitFeedback(true)}
                          className="flex w-full items-center justify-center space-x-2 px-3 sm:px-4 py-2 bg-emerald-50 text-emerald-700 rounded-md border border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/50 dark:hover:bg-emerald-900/60 transition-colors"
                          disabled={!explanationId}
                        >
                          <ThumbsUp size={isMobile ? 16 : 18} />
                          <span className="text-sm font-medium">Sim</span>
                        </button>
                        <button
                          onClick={() => submitFeedback(false)}
                          className="flex w-full items-center justify-center space-x-2 px-3 sm:px-4 py-2 bg-amber-50 text-amber-700 rounded-md border border-amber-200 hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/50 dark:hover:bg-amber-900/60 transition-colors"
                          disabled={!explanationId}
                        >
                          <ThumbsDown size={isMobile ? 16 : 18} />
                          <span className="text-sm font-medium">Não</span>
                        </button>
                      </div>
                      {showFeedbackForm && (
                        <div className="bg-muted/50 p-3 sm:p-4 rounded-md border border-border">
                          <h4 className="font-medium mb-2 text-sm sm:text-base text-foreground">Como podemos melhorar?</h4>
                          <textarea
                            className="w-full p-2 sm:p-3 bg-background border border-border rounded-md focus:ring-2 focus:ring-ring outline-none mb-3 text-sm text-foreground placeholder:text-muted-foreground"
                            rows={isMobile ? 3 : 4}
                            placeholder="Compartilhe suas sugestões..."
                            value={feedbackComment}
                            onChange={(e) => setFeedbackComment(e.target.value)}
                          />
                          <div className="flex justify-end space-x-3">
                            <button
                              onClick={() => setShowFeedbackForm(false)}
                              className="px-4 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-colors text-xs sm:text-sm font-medium"
                            >
                              Cancelar
                            </button>
                            <button
                              onClick={submitFeedbackWithComment}
                              className="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors text-xs sm:text-sm font-medium"
                              disabled={!feedbackComment.trim()}
                            >
                              Enviar
                            </button>
                          </div>
                        </div>
                      )}
                    </div>
                  )}
                  {feedbackStats && feedbackStats.total_count > 0 && (
                    <div className="mt-4 text-xs sm:text-sm text-muted-foreground">
                      <p className="mb-1.5 text-center">{feedbackStats.positive_percentage}% de {feedbackStats.total_count} pessoas acharam esta explicação útil.</p>
                      <div className="w-full bg-muted rounded-full h-2">
                        <div
                          className="bg-emerald-500 h-2 rounded-full"
                          style={{ width: `${feedbackStats.positive_percentage}%` }}
                        ></div>
                      </div>
                    </div>
                  )}
                </div>
              </div>

              {/* Share & PDF */}
              <div className="mt-8 flex flex-col sm:flex-row sm:justify-center gap-3">
                <button
                  onClick={() => {
                    const shareData = {
                      title: `${book} ${chapter}${verses ? ':' + verses : ''} | Verso a Verso`,
                      text: 'Confira esta explicação bíblica!',
                      url: window.location.href,
                    } as ShareData;
                    if (navigator.share) {
                      navigator.share(shareData).catch(() => {/* cancelled */});
                    } else {
                      navigator.clipboard.writeText(window.location.href).then(() => {
                        alert('Link copiado para a área de transferência!');
                      });
                    }
                  }}
                  className="flex items-center gap-2 px-4 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-colors text-sm font-medium shadow-sm"
                >
                  <Share2 size={18} />
                  Compartilhar
                </button>

                <button
                  onClick={async () => {
                    // 1. Primeiro expandimos todas as seções
                    const sections = document.querySelectorAll('[aria-expanded]');
                    sections.forEach(btn => {
                      if (btn.getAttribute('aria-expanded') === 'false') {
                        (btn as HTMLElement).click();
                      }
                    });

                    // 2. Aguardamos um momento para as seções expandirem
                    await new Promise(resolve => setTimeout(resolve, 100));

                    // 3. Capturamos o conteúdo da explicação
                    const explanation = document.querySelector('.bg-card.rounded-lg.shadow-sm.border');
                    if (!explanation) return;

                    // 4. Criamos uma nova janela para impressão
                    const printWindow = window.open('', '_blank');
                    if (!printWindow) return;

                    // 5. Preparamos o HTML para impressão
                    const printContent = `
                      <!DOCTYPE html>
                      <html>
                      <head>
                        <title>Explicação Bíblica - PDF</title>
                        <meta charset="utf-8">
                        <style>
                          @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
                          
                          body {
                            font-family: 'Inter', system-ui, -apple-system, sans-serif;
                            line-height: 1.5;
                            max-width: 800px;
                            margin: 0 auto;
                            padding: 40px 20px;
                            color: #1a1a1a;
                          }

                          /* Estilos gerais */
                          .section {
                            margin-bottom: 24px;
                            padding: 20px;
                            border: 1px solid #e5e7eb;
                            border-radius: 8px;
                            background-color: white;
                          }

                          .section-content {
                            display: block !important;
                            max-height: none !important;
                            opacity: 1 !important;
                          }

                          /* Tipografia */
                          h1, h2, h3 { color: #111827; }
                          h2 { 
                            font-size: 1.4rem; 
                            margin: 1.5rem 0 1rem;
                            font-weight: 600;
                            color: #1e293b;
                          }
                          p { margin: 0.8em 0; }

                          /* Listas */
                          ul, ol { padding-left: 1.5em; margin: 0.8em 0; }
                          li { margin: 0.5em 0; }

                          /* Citações */
                          blockquote {
                            margin: 1.5em 0;
                            padding: 1em;
                            border-left: 4px solid #e0e7ef;
                            color: #64748b;
                            font-style: italic;
                            background-color: #f8fafc;
                          }

                          /* Cores específicas da explicação */
                          .bg-slate-50 { background-color: #f8fafc !important; }
                          .bg-indigo-50 { background-color: #eef2ff !important; }
                          .bg-blue-50 { background-color: #eff6ff !important; }
                          .bg-purple-50 { background-color: #f5f3ff !important; }
                          
                          .text-indigo-600 { color: #4f46e5 !important; }
                          .text-blue-600 { color: #2563eb !important; }
                          .text-purple-600 { color: #9333ea !important; }
                          
                          .dark\:bg-slate-700\/50 { background-color: #f8fafc !important; }
                          .dark\:bg-indigo-900\/30 { background-color: #eef2ff !important; }
                          .dark\:bg-blue-900\/30 { background-color: #eff6ff !important; }
                          .dark\:bg-purple-900\/30 { background-color: #f5f3ff !important; }
                          
                          .font-bold { font-weight: 700 !important; }
                          .font-semibold { font-weight: 600 !important; }
                          
                          /* Remover elementos desnecessários */
                          button, .feedback, .DonateButton, [aria-expanded], .lucide { display: none !important; }

                          /* Ajustes para impressão */
                          @media print {
                            body { padding: 0; }
                            .section { page-break-inside: avoid; }
                            a { text-decoration: none; }
                            @page { margin: 2cm; }
                          }
                        </style>
                      </head>
                      <body>
                        ${explanation.innerHTML}
                      </body>
                      </html>
                    `;

                    // 6. Escrevemos o conteúdo na nova janela
                    printWindow.document.write(printContent);
                    printWindow.document.close();

                    // 7. Aguardamos o carregamento e imprimimos
                    printWindow.onload = () => {
                      setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                      }, 500);
                    };
                  }}
                  className="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors text-sm font-medium shadow-sm"
                >
                  <FileText size={18} />
                  Salvar PDF
                </button>
              </div>

              {/* Navigation */}
              <div className="mt-8 pt-6 border-t border-border flex justify-between items-center">
                <button
                  onClick={navigateToPreviousVerse}
                  className="flex items-center gap-2 px-3 sm:px-4 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-colors text-xs sm:text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                  disabled={!verses || parseInt(verses.split(',')[0]) <= 1}
                >
                  <ArrowLeft size={isMobile ? 14 : 16} />
                  <span className="hidden sm:inline">Anterior</span>
                </button>
                <button
                  onClick={() => window.history.back()}
                  className="flex items-center gap-2 px-4 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-colors text-sm font-medium"
                >
                  <ChevronLeft size={16} />
                  Voltar
                </button>
                <button
                  onClick={navigateToNextVerse}
                  className="flex items-center gap-2 px-3 sm:px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors text-xs sm:text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                  disabled={!verses}
                >
                  <span className="hidden sm:inline">Próximo</span>
                  <ArrowRight size={isMobile ? 14 : 16} />
                </button>
              </div>
            </div>
          )}
        </div>
        <Footer />
      </AppLayout >
    </>
  );
}
