import React, { useState, useEffect, FC, ReactNode } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import {
  ChevronLeft, ArrowRight, ArrowLeft, ThumbsUp, ThumbsDown, Heart,
  BookOpen, Scale, Landmark, Users, Microscope, Cross, Target, Link, Gem,
  AlertTriangle, FileText, CheckCircle, User, Key, Book, HelpCircle
} from 'lucide-react';
import { useMediaQuery } from '@/hooks/useMediaQuery';
import Footer from '@/components/footer';
import DonateButton from '@/components/donate-button';

// --- TypeScript Interfaces ---
interface BibleExplanationProps {
  testamento: string;
  livro: string;
  capitulo: string;
  versos?: string;
}

interface VerseExplanation {
  titulo_principal_e_texto_biblico: { titulo: string; texto: string };
  contexto_detalhado: { [key: string]: string };
  analise_exegenetica: { introducao: string; analises: { verso: string; analise: string }[] };
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

type ExplanationData = VerseExplanation | ChapterSummary | ErrorExplanation;

// --- Reusable UI Components ---
const Section: FC<{ title: string; children: ReactNode; icon?: ReactNode; defaultOpen?: boolean }> = ({ title, children, icon, defaultOpen = true }) => {
  const [isOpen, setIsOpen] = useState(defaultOpen);
  return (
    <section className="mb-6 bg-white dark:bg-slate-800/50 rounded-lg shadow-md border border-slate-200 dark:border-slate-700 transition-all duration-300 hover:shadow-lg overflow-hidden">
      <button
        className="w-full flex items-center justify-between p-4 text-left"
        onClick={() => setIsOpen(!isOpen)}
        aria-expanded={isOpen}
      >
        <h2 className="flex items-center gap-3 text-lg sm:text-xl font-bold text-indigo-700 dark:text-indigo-400">
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
    <span className="text-indigo-500 dark:text-indigo-400 mt-1">{icon || <CheckCircle size={18} />}</span>
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
const VerseExplanationComponent: FC<{ explanation: VerseExplanation }> = ({ explanation }) => (
  <div style={{ animation: 'fadeIn 0.8s ease-in-out' }}>
    <header className="mb-8 text-center p-6 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-slate-800 dark:to-slate-900 rounded-lg shadow-inner">
      <h1 className="text-2xl sm:text-3xl font-extrabold text-indigo-800 dark:text-indigo-300 mb-2">{explanation.titulo_principal_e_texto_biblico.titulo}</h1>
      <blockquote className="text-base sm:text-lg italic text-slate-700 dark:text-slate-300 border-l-4 border-indigo-300 dark:border-indigo-600 pl-4">
        {explanation.titulo_principal_e_texto_biblico.texto}
      </blockquote>
    </header>

    <Section title="Contexto Detalhado" icon={<BookOpen size={22} />} defaultOpen={false}>
      <p>{explanation.contexto_detalhado.introducao}</p>
      <ul>
        {Object.entries(explanation.contexto_detalhado).map(([key, value]) => {
          if (key === 'introducao') return null;
          return <ListItem key={key}><strong>{key.replace(/_/g, ' ').replace(/(?:^|\s)\S/g, a => a.toUpperCase())}:</strong> {value}</ListItem>
        })}
      </ul>
    </Section>

    <Section title="Análise do versículo" icon={<Microscope size={22} />}>
      <p>{explanation.analise_exegenetica.introducao}</p>
      {explanation.analise_exegenetica.analises.map((item, index) => (
        <div key={index} className="mt-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-md">
          <h4 className="font-bold text-indigo-600 dark:text-indigo-400">Versículo {item.verso}</h4>
          <p>{item.analise}</p>
        </div>
      ))}
    </Section>

    <Section title="Teologia da Passagem (Doutrinas)" icon={<Landmark size={22} />} defaultOpen={false}>
      <p>{explanation.teologia_da_passagem.introducao}</p>
      <ul>{explanation.teologia_da_passagem.doutrinas.map((item, i) => <ListItem key={i}>{item}</ListItem>)}</ul>
    </Section>

    <Section title="Temas Principais" icon={<Key size={22} />}>
      <p>{explanation.temas_principais.introducao}</p>
      <ul>{explanation.temas_principais.temas.map((item, i) => <ListItem key={i}><strong>{item.tema}:</strong> {item.descricao}</ListItem>)}</ul>
    </Section>

    <Section title="Explicação do Versículo" icon={<Book size={22} />}>
      <p>{explanation.explicacao_do_versiculo.introducao}</p>
      <div className="mt-4 space-y-4">
        <div className="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-md">
          <h4 className="font-semibold text-indigo-700 dark:text-indigo-300">Significado Profundo</h4>
          <p className="mt-2">{explanation.explicacao_do_versiculo.significado_profundo}</p>
        </div>
        <div className="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-md">
          <h4 className="font-semibold text-blue-700 dark:text-blue-300">Contexto Original</h4>
          <p className="mt-2">{explanation.explicacao_do_versiculo.contexto_original}</p>
        </div>
        <div>
          <h4 className="font-semibold text-slate-700 dark:text-slate-300">Palavras-Chave</h4>
          <ul className="mt-2 flex flex-wrap gap-2">
            {explanation.explicacao_do_versiculo.palavras_chave.map((palavra, i) => (
              <li key={i} className="px-3 py-1 bg-slate-100 dark:bg-slate-700 rounded-full text-sm">{palavra}</li>
            ))}
          </ul>
        </div>
        <div className="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-md">
          <h4 className="font-semibold text-purple-700 dark:text-purple-300">Interpretação Teológica</h4>
          <p className="mt-2">{explanation.explicacao_do_versiculo.interpretacao_teologica}</p>
        </div>
      </div>
    </Section>

    {explanation.personagens_principais.personagens.length > 0 && (
      <Section title="Personagens Principais" icon={<Users size={22} />} defaultOpen={false}>
        <p>{explanation.personagens_principais.introducao}</p>
        <ul>{explanation.personagens_principais.personagens.map((item, i) => <ListItem key={i} icon={<User size={18} />}><strong>{item.nome}:</strong> {item.descricao}</ListItem>)}</ul>
      </Section>
    )}

    <Section title="Aplicação Contemporânea" icon={<Target size={22} />}>
      <p>{explanation.aplicacao_contemporanea.introducao}</p>
      <h4 className="font-semibold mt-4">Pontos de Aplicação</h4>
      <ul>{explanation.aplicacao_contemporanea.pontos_aplicacao.map((item, i) => <ListItem key={i}>{item}</ListItem>)}</ul>
      <h4 className="font-semibold mt-4">Perguntas para Reflexão</h4>
      <ul>{explanation.aplicacao_contemporanea.perguntas_reflexao.map((item, i) => <ListItem key={i} icon={<HelpCircle size={18} />}>{item}</ListItem>)}</ul>
    </Section>

    <Section title="Referências Cruzadas Relevantes" icon={<Link size={22} />} defaultOpen={false}>
      <p>{explanation.referencias_cruzadas.introducao}</p>
      <ul>{explanation.referencias_cruzadas.referencias.map((item, i) => <ListItem key={i}><strong>{item.passagem}:</strong> {item.explicacao}</ListItem>)}</ul>
    </Section>

    {explanation.simbologia_biblica.simbolos.length > 0 && (
      <Section title="Simbologia Bíblica" icon={<Gem size={22} />} defaultOpen={false}>
        <p>{explanation.simbologia_biblica.introducao}</p>
        <ul>{explanation.simbologia_biblica.simbolos.map((item, i) => <ListItem key={i}><strong>{item.simbolo}:</strong> {item.significado}</ListItem>)}</ul>
      </Section>
    )}

    <Section title="Interprete à Luz de Cristo" icon={<Cross size={22} />}>
      <p>{explanation.interprete_luz_de_cristo.introducao}</p>
      <p className="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-400 rounded-r-md">{explanation.interprete_luz_de_cristo.conexao}</p>
    </Section>
  </div>
);

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

  const [loading, setLoading] = useState(true);
  const [explanation, setExplanation] = useState<ExplanationData | string | null>(null);
  const [source, setSource] = useState('');
  const [explanationId, setExplanationId] = useState<number | null>(null);
  const [showFeedbackForm, setShowFeedbackForm] = useState(false);
  const [feedbackComment, setFeedbackComment] = useState('');
  const [feedbackSubmitted, setFeedbackSubmitted] = useState(false);
  const [feedbackType, setFeedbackType] = useState<'positive' | 'negative' | null>(null);
  const [showCelebration, setShowCelebration] = useState(false);
  const [seoMetadata, setSeoMetadata] = useState({ title: '', description: '', keywords: '' });
  const [relatedLinks, setRelatedLinks] = useState([]);
  const [feedbackStats, setFeedbackStats] = useState<{ positive_count: number; negative_count: number; total_count: number; positive_percentage: number; negative_percentage: number; } | null>(null);

  const isMobile = useMediaQuery('(max-width: 640px)');
  const { testamento, livro: book, capitulo: chapter } = props;

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
  const verses = extractVerses();
  const isChapterMode = !verses;

  const maxVerses = 50;

  useEffect(() => {
    async function fetchExplanation() {
      setLoading(true);
      try {
        let apiUrl = `/api/explanation/${testamento}/${book}/${chapter}`;
        if (verses) {
          apiUrl += `?verses=${verses}`;
        }
        const response = await fetch(apiUrl);
        if (!response.ok) throw new Error(`API responded with status: ${response.status}`);

        const data = await response.json();

        // The backend now sends the explanation inside the 'explanation' key
        const explanationContent = data.explanation;

        if (typeof explanationContent === 'string') {
          // It might be a JSON string or legacy HTML. Try parsing.
          try {
            const parsedJson = JSON.parse(explanationContent);
            setExplanation(parsedJson);
          } catch (e) {
            // If parsing fails, it's legacy HTML
            setExplanation(explanationContent);
          }
        } else if (typeof explanationContent === 'object' && explanationContent !== null) {
          // It's already a JSON object
          setExplanation(explanationContent);
        } else {
          throw new Error('No explanation was returned.');
        }

        setSource(data.origin || 'unknown');
        setExplanationId(data.id || null);
        if (data.id) fetchFeedbackStats(data.id);

        if (verses) {
          const slugifiedBook = encodeURIComponent(book);
          const slug = verses + '-explicacao-biblica';
          const expectedPath = `/explicacao/${testamento}/${slugifiedBook}/${chapter}/${slug}`;
          if (window.location.pathname !== expectedPath) {
            window.history.replaceState({}, '', expectedPath);
          }
        }
      } catch (error) {
        console.error('Error fetching explanation:', error);
        setExplanation({ error: 'fetch_failed', message: 'Erro ao buscar a explicação. Por favor, verifique sua conexão e tente novamente.' });
      } finally {
        setLoading(false);
      }
    }
    fetchExplanation();
  }, [testamento, book, chapter, verses]);

  // SEO and other side effects
  useEffect(() => {
    // ... (fetchSeoMetadata, fetchRelatedLinks can be called here)
  }, [testamento, book, chapter, verses]);

  const navigateToVerse = (verseNumber: number) => {
    if (verseNumber < 1 || verseNumber > maxVerses) return;
    window.location.href = `/explicacao/${testamento}/${book}/${chapter}?versiculos=${verseNumber}`;
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
      <Head>
        <title>{seoMetadata.title || `${book} ${chapter}${verses ? ':' + verses : ''} - Explicação Bíblica`}</title>
        <meta name="description" content={seoMetadata.description || `Explicação detalhada de ${book} ${chapter}${verses ? ':' + verses : ''}.`} />
        {/* ... other meta tags ... */}
      </Head>
      <AppLayout>
        <div className="container max-w-4xl mx-auto p-2 sm:p-4">
          <header className="mb-3 sm:mb-6 flex items-center justify-between bg-white dark:bg-slate-900 shadow-sm rounded-lg p-2 sm:p-4 sticky top-2 z-10">
            <div className="flex items-center gap-4">
              <button onClick={() => window.location.href = `/biblia/${testamento}/${book}/${chapter}`} className="text-indigo-600 hover:text-indigo-800" aria-label="Voltar">
                <ChevronLeft size={isMobile ? 18 : 22} />
              </button>
              <h1 className="text-base sm:text-xl font-bold text-gray-800 dark:text-gray-200 truncate max-w-[150px] sm:max-w-full">
                {book} {chapter}{verses && <span className="ml-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400 hidden sm:inline">(Versículos: {verses})</span>}
              </h1>
            </div>
            {verses && (
              <div className="flex items-center space-x-2">
                <button onClick={navigateToPreviousVerse} className="p-1 sm:p-2 rounded-full bg-indigo-50 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-slate-700" aria-label="Versículo anterior">
                  <ArrowLeft size={isMobile ? 16 : 18} />
                </button>
                <button onClick={navigateToNextVerse} className="p-1 sm:p-2 rounded-full bg-indigo-50 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-slate-700" aria-label="Próximo versículo">
                  <ArrowRight size={isMobile ? 16 : 18} />
                </button>
              </div>
            )}
          </header>
          {loading ? (
            <div className="flex flex-col justify-center items-center p-6 bg-white dark:bg-slate-900 rounded-xl shadow-md border border-slate-100 dark:border-slate-800 mt-6">
              <div className="relative mb-4">
                <div className="absolute inset-0 flex items-center justify-center"><Heart className="h-8 w-8 text-blue-600 dark:text-blue-400 animate-pulse" /></div>
                <div className="h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/30 animate-ping opacity-75"></div>
              </div>
              <p className="text-lg font-medium text-slate-800 dark:text-slate-200 mb-2">Preparando explicação...</p>
              <div className="w-16 h-1 bg-gradient-to-r from-blue-500 to-blue-700 rounded mb-4"></div>
              <p className="text-sm text-center text-slate-600 dark:text-slate-400 italic max-w-xs">"A tua palavra é lâmpada para os meus pés e luz para o meu caminho." - Salmos 119:105</p>
            </div>
          ) : (
            <div className="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 sm:p-6 mt-2 sm:mt-4 shadow-sm border border-slate-200 dark:border-slate-800">
              <ExplanationRenderer data={explanation} isChapterMode={isChapterMode} />
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

              {/* Navigation */}
              <div className="mt-8 pt-6 border-t border-border flex justify-between items-center">
                <button
                  onClick={navigateToPreviousVerse}
                  className="flex items-center gap-2 px-3 sm:px-4 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-colors text-xs sm:text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                  disabled={!verses || parseInt(verses.split(',')[0]) <= 1}
                >
                  <ArrowLeft size={isMobile ? 14 : 16} />
                  <span className="hidden xs:inline">Anterior</span>
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
                  <span className="hidden xs:inline">Próximo</span>
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