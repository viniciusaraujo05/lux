import React, { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Link, Head } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, BookOpen, ArrowRight, ArrowLeft, ThumbsUp, ThumbsDown, MessageSquare, Gift, Heart } from 'lucide-react';
import { useMediaQuery } from '@/hooks/useMediaQuery';
import Footer from '@/components/footer';
import DonateButton from '@/components/donate-button';

interface BibleExplanationProps {
  testamento: string;
  livro: string;
  capitulo: string;
  versos?: string;
}

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
      } catch (e) {}
    }
  }, []);

  const [loading, setLoading] = useState(true);
  const [explanation, setExplanation] = useState('');
  const [source, setSource] = useState('');
  const [currentVerse, setCurrentVerse] = useState<number | null>(null);
  const [explanationId, setExplanationId] = useState<number | null>(null);
  const [showFeedbackForm, setShowFeedbackForm] = useState(false);
  const [feedbackComment, setFeedbackComment] = useState('');
  const [feedbackSubmitted, setFeedbackSubmitted] = useState(false);
  const [feedbackType, setFeedbackType] = useState<'positive' | 'negative' | null>(null);
  const [showCelebration, setShowCelebration] = useState(false);
  const [seoMetadata, setSeoMetadata] = useState({
    title: '',
    description: '',
    keywords: ''
  });
  const [relatedLinks, setRelatedLinks] = useState([]);
  const [feedbackStats, setFeedbackStats] = useState<{
    positive_count: number;
    negative_count: number;
    total_count: number;
    positive_percentage: number;
    negative_percentage: number;
  } | null>(null);

  const isMobile = useMediaQuery('(max-width: 640px)');
  const testament = props.testamento;
  const book = props.livro;
  const chapter = props.capitulo;

  // Função robusta para extrair versículos da URL amigável ou query
  function extractVerses() {
    // 1. Query param
    const params = new URLSearchParams(window.location.search);
    let v = params.get('versiculos') || params.get('verses');
    if (v) return v;
    // 2. Slug amigável na URL: /explicacao/{testamento}/{livro}/{capitulo}/{slug}
    const pathParts = window.location.pathname.split('/');
    if (pathParts.length > 5) {
      // slug é o último
      const slug = pathParts[pathParts.length - 1];
      // Exemplo: "3-explicacao-biblica" => pega antes do primeiro hifen
      const match = slug.match(/^(\d+(?:,\d+)*)-explicacao-biblica/);
      if (match) return match[1];
    }
    // 3. Props (fallback)
    if (props.versos) return props.versos;
    return null;
  }
  const verses = extractVerses();
  const isChapterMode = !verses;
  // Debug temporário
  console.log('[explicacao] verses extraído:', verses, 'pathname:', window.location.pathname, 'search:', window.location.search);

  const maxVerses = 50;

  useEffect(() => {
    async function fetchSeoMetadata() {
      try {
        let seoUrl = `/api/seo/${testament}/${book}/${chapter}`;
        if (verses) {
          seoUrl += `?verses=${verses}`;
        }
        const response = await fetch(seoUrl);
        if (response.ok) {
          const seoData = await response.json();
          setSeoMetadata({
            title: seoData.title || `${book} ${chapter} - Explicação Bíblica | Verbum`,
            description: seoData.description || `Explicação detalhada de ${book} ${chapter} com contexto histórico e aplicações práticas.`,
            keywords: seoData.keywords || `Bíblia, ${book}, ${chapter}, explicação bíblica, estudo bíblico`
          });
        }
      } catch (error) {
        console.error('Error fetching SEO metadata:', error);
      }
    }
    fetchSeoMetadata();
  }, [testament, book, chapter, verses]);

  useEffect(() => {
    async function fetchRelatedLinks() {
      try {
        let relatedUrl = `/api/related/${testament}/${book}/${chapter}`;
        if (verses) {
          relatedUrl += `?verses=${verses}`;
        }
        const response = await fetch(relatedUrl);
        if (response.ok) {
          const data = await response.json();
          setRelatedLinks(data.relatedLinks || []);
        }
      } catch (error) {
        console.error('Error fetching related links:', error);
      }
    }
    fetchRelatedLinks();
  }, [testament, book, chapter, verses]);

  useEffect(() => {
    async function fetchExplanation() {
      try {
        let apiUrl = `/api/explanation/${testament}/${book}/${chapter}`;
        if (verses) {
          apiUrl += `?verses=${verses}`;
        }
        const response = await fetch(apiUrl);
        if (!response.ok) {
          throw new Error(`API responded with status: ${response.status}`);
        }
        const data = await response.json();
        setExplanation(data.explanation || 'No explanation was returned.');
        setSource(data.origin || 'unknown');
        setExplanationId(data.id || null);
        setLoading(false);
        if (data.id) {
          fetchFeedbackStats(data.id);
        }
        if (verses) {
          const slugifiedBook = encodeURIComponent(book);
          const slug = verses + '-explicacao-biblica';
          const expectedPath = `/explicacao/${testament}/${slugifiedBook}/${chapter}/${slug}`;
          if (window.location.pathname !== expectedPath) {
            window.history.replaceState({}, '', expectedPath);
          }
        }
      } catch (error) {
        console.error('Error fetching explanation:', error);
        setExplanation('Erro ao buscar explicação. Por favor, tente novamente.');
        setLoading(false);
      }
    }
    fetchExplanation();
  }, [testament, book, chapter, verses]);

  const navigateToVerse = (verseNumber: number) => {
    if (verseNumber < 1 || verseNumber > maxVerses) return;
    window.location.href = `/explicacao/${testament}/${book}/${chapter}?versiculos=${verseNumber}`;
  };

  const startVerseByVerseAnalysis = () => {
    navigateToVerse(1);
  };

  const navigateToNextVerse = () => {
    if (!verses) return;
    const versesList = verses.split(',');
    const lastVerse = parseInt(versesList[versesList.length - 1], 10);
    if (lastVerse < maxVerses) {
      navigateToVerse(lastVerse + 1);
    }
  };

  const navigateToPreviousVerse = () => {
    if (!verses) return;
    const versesList = verses.split(',');
    const firstVerse = parseInt(versesList[0], 10);
    if (firstVerse > 1) {
      navigateToVerse(firstVerse - 1);
    }
  };

  const fetchFeedbackStats = async (id: number) => {
    try {
      const response = await fetch(`/api/feedback/stats/${id}`);
      if (response.ok) {
        const stats = await response.json();
        setFeedbackStats(stats);
      }
    } catch (error) {
      console.error('Error fetching feedback stats:', error);
    }
  };

  const submitFeedback = async (isPositive: boolean) => {
    if (!explanationId) {
      console.error('Erro: ID da explicação não encontrado');
      return;
    }
    setFeedbackType(isPositive ? 'positive' : 'negative');
    if (isPositive) {
      try {
        const response = await fetch('/api/feedback', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
          body: JSON.stringify({
            bible_explanation_id: explanationId,
            is_positive: true,
            comment: '',
            testament,
            book,
            chapter,
            verses,
          }),
        });
        if (response.ok) {
          setFeedbackSubmitted(true);
          setShowCelebration(true);
          setTimeout(() => {
            setShowCelebration(false);
          }, 8000);
          fetchFeedbackStats(explanationId);
        } else {
          console.error('Erro na resposta do servidor:', await response.text());
        }
      } catch (error) {
        console.error('Erro ao enviar feedback positivo:', error);
      }
    } else {
      setShowFeedbackForm(true);
    }
  };

  const submitFeedbackWithComment = async () => {
    if (!explanationId || !feedbackType) {
      console.error('Erro: ID da explicação ou tipo de feedback não encontrado');
      return;
    }
    try {
      const response = await fetch('/api/feedback', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          bible_explanation_id: explanationId,
          is_positive: feedbackType === 'positive',
          comment: feedbackComment,
          testament,
          book,
          chapter,
          verses,
        }),
      });
      if (response.ok) {
        setFeedbackSubmitted(true);
        setShowFeedbackForm(false);
        fetchFeedbackStats(explanationId);
      } else {
        console.error('Erro na resposta do servidor:', await response.text());
      }
    } catch (error) {
      console.error('Erro ao enviar feedback com comentário:', error);
    }
  };

  return (
    <>
      <Head>
        <title>{seoMetadata.title}</title>
        <meta name="description" content={seoMetadata.description} />
        <meta name="keywords" content={seoMetadata.keywords} />
        <meta property="og:type" content="article" />
        <meta property="og:url" content={window.location.href} />
        <meta property="og:title" content={seoMetadata.title} />
        <meta property="og:description" content={seoMetadata.description} />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:url" content={window.location.href} />
        <meta property="twitter:title" content={seoMetadata.title} />
        <meta property="twitter:description" content={seoMetadata.description} />
        <link rel="canonical" href={window.location.href} />
        <link rel="amphtml" href={`/amp/explicacao/${testament}/${book}/${chapter}${verses ? '?verses=' + verses : ''}`} />
        <script type="application/ld+json">{`
          {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": "${book} ${chapter}${verses ? ':' + verses : ''} - Explicação Bíblica",
            "description": "${seoMetadata.description}",
            "author": {
              "@type": "Organization",
              "name": "Verbum - Bíblia Explicada"
            },
            "publisher": {
              "@type": "Organization",
              "name": "Verbum - Bíblia Explicada",
              "logo": {
                "@type": "ImageObject",
                "url": "${window.location.origin}/images/logo.png"
              }
            },
            "mainEntityOfPage": {
              "@type": "WebPage",
              "@id": "${window.location.href}"
            }
          }
        `}</script>
      </Head>
      <AppLayout>
        <div className="container max-w-4xl mx-auto p-2 sm:p-4">
          <header className="mb-3 sm:mb-6 flex items-center justify-between bg-white shadow-sm rounded-lg p-2 sm:p-4">
            <div className="flex items-center gap-4">
              <button 
                onClick={() => {
                  window.location.href = `/biblia/${testament}/${book}/${chapter}`;
                }}
                className="text-indigo-600 hover:text-indigo-800 transition-colors flex items-center justify-center"
                aria-label="Voltar"
              >
                <ChevronLeft size={isMobile ? 18 : 22} />
              </button>
              <h1 className="text-base sm:text-xl font-bold text-gray-800 truncate max-w-[150px] sm:max-w-full">
                {book} {chapter}
                {verses && <span className="ml-2 text-xs sm:text-sm text-gray-600 hidden sm:inline">(Versículos: {verses})</span>}
              </h1>
            </div>
            {verses && (
              <div className="flex items-center space-x-2">
                <button 
                  onClick={navigateToPreviousVerse}
                  className="p-1 sm:p-2 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
                  aria-label="Versículo anterior"
                >
                  <ArrowLeft size={isMobile ? 16 : 18} />
                </button>
                <button 
                  onClick={navigateToNextVerse}
                  className="p-1 sm:p-2 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
                  aria-label="Próximo versículo"
                >
                  <ArrowRight size={isMobile ? 16 : 18} />
                </button>
              </div>
            )}
          </header>
          {loading ? (
            <div className="flex flex-col justify-center items-center p-6 bg-white dark:bg-slate-900 rounded-xl shadow-md border border-slate-100 dark:border-slate-800 mt-6">
              <div className="relative mb-4">
                <div className="absolute inset-0 flex items-center justify-center">
                  <Heart className="h-8 w-8 text-blue-600 dark:text-blue-400 animate-pulse" />
                </div>
                <div className="h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/30 animate-ping opacity-75"></div>
              </div>
              <p className="text-lg font-medium text-slate-800 dark:text-slate-200 mb-2">
                Preparando explicação...
              </p>
              <div className="w-16 h-1 bg-gradient-to-r from-blue-500 to-blue-700 rounded mb-4"></div>
              <p className="text-sm text-center text-slate-600 dark:text-slate-400 italic max-w-xs">
                "A tua palavra é lâmpada para os meus pés e luz para o meu caminho." - Salmos 119:105
              </p>
            </div>
          ) : (
            <div className="prose max-w-none">
              <div 
                className="explanation-container bg-slate-50 rounded-lg p-3 sm:p-6 mt-2 sm:mt-4 shadow-sm border border-slate-200"
                style={{ animation: 'fadeIn 0.8s ease-in-out' }}
              >
                {verses ? (
                  <>
                    <h2 className="font-semibold text-base sm:text-lg mb-2 text-indigo-700">Explicação do(s) versículo(s): {verses}</h2>
                    <div
                      className="explanation-text text-slate-800 text-base sm:text-lg leading-relaxed"
                      dangerouslySetInnerHTML={{ __html: explanation }}
                    />
                  </>
                ) : (
                  <>
                    <h2 className="font-semibold text-base sm:text-lg mb-2 text-indigo-700">Explicação do capítulo {chapter}</h2>
                    <div
                      className="explanation-text text-slate-800 text-base sm:text-lg leading-relaxed"
                      dangerouslySetInnerHTML={{ __html: explanation }}
                    />
                  </>
                )}
                <div className="my-8 p-5 bg-blue-50 dark:bg-blue-950/30 border border-blue-300 dark:border-blue-700/40 rounded-lg shadow-md transition-all duration-300 hover:shadow-lg">
                  <div className="flex flex-col items-center text-center mb-4">
                    <div className="inline-flex items-center justify-center bg-blue-100 dark:bg-blue-800/50 p-3 rounded-full mb-3 shadow-inner">
                      <Heart className="h-5 w-5 text-blue-600 dark:text-blue-300" />
                    </div>
                    <h3 className="font-semibold text-lg text-blue-900 dark:text-blue-50 mb-2">Nos ajude a continuar!</h3>
                  </div>
                  <div className="flex justify-center">
                    <DonateButton size="md" className="w-full sm:w-auto max-w-xs shadow-sm hover:shadow transition-shadow" />
                  </div>
                </div>
                <div className="mt-4 sm:mt-8 pt-3 sm:pt-6 border-t border-slate-200">
                  <h3 className="text-base sm:text-lg font-semibold mb-2 sm:mb-4">Esta explicação foi útil?</h3>
                  {feedbackSubmitted ? (
                    <div 
                      className={`p-2 sm:p-4 rounded-md border ${feedbackType === 'positive' 
                        ? 'bg-green-50 text-green-700 border-green-200' + (showCelebration ? ' celebration' : '')
                        : 'bg-amber-50 text-amber-700 border-amber-200'}`}
                    >
                      {feedbackType === 'positive' ? (
                        <p className="text-center text-sm sm:text-base text-green-700">
                          Obrigado pelo seu feedback positivo! Sua opinião nos ajuda a melhorar.
                        </p>
                      ) : (
                        <p>Obrigado pelo seu feedback! Sua opinião é muito importante para melhorarmos nosso conteúdo.</p>
                      )}
                    </div>
                  ) : (
                    <div className="flex flex-col space-y-4">
                      <div className="flex space-x-2 sm:space-x-4">
                        <button 
                          onClick={() => submitFeedback(true)}
                          className="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-4 py-1.5 sm:py-2 bg-emerald-50 text-emerald-700 rounded-md border border-emerald-200 hover:bg-emerald-100 transition-colors"
                          disabled={!explanationId}
                        >
                          <ThumbsUp size={isMobile ? 14 : 18} />
                          <span className="text-xs sm:text-sm">Sim, foi útil</span>
                        </button>
                        <button 
                          onClick={() => submitFeedback(false)}
                          className="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-4 py-1.5 sm:py-2 bg-amber-50 text-amber-700 rounded-md border border-amber-200 hover:bg-amber-100 transition-colors"
                          disabled={!explanationId}
                        >
                          <ThumbsDown size={isMobile ? 14 : 18} />
                          <span className="text-xs sm:text-sm.">Não foi útil</span>
                        </button>
                      </div>
                      {showFeedbackForm && (
                        <div className="bg-slate-100 p-2 sm:p-4 rounded-md border border-slate-200">
                          <h4 className="font-medium mb-1 sm:mb-2 text-sm sm:text-base text-slate-700">Como podemos melhorar esta explicação?</h4>
                          <textarea 
                            className="w-full p-2 text-black sm:p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none mb-2 sm:mb-3 text-sm"
                            rows={isMobile ? 3 : 4}
                            placeholder="Compartilhe suas sugestões para melhorarmos o conteúdo..."
                            value={feedbackComment}
                            onChange={(e) => setFeedbackComment(e.target.value)}
                          />
                          <div className="flex justify-end space-x-2 sm:space-x-3">
                            <button 
                              onClick={() => setShowFeedbackForm(false)}
                              className="px-3 sm:px-4 py-1.5 sm:py-2 text-slate-700 bg-slate-200 rounded-md hover:bg-slate-300 transition-colors text-xs sm:text-sm"
                            >
                              Cancelar
                            </button>
                            <button 
                              onClick={submitFeedbackWithComment}
                              className="px-3 sm:px-4 py-1.5 sm:py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition-colors text-xs sm:text-sm"
                              disabled={!feedbackComment.trim()}
                            >
                              Enviar feedback
                            </button>
                          </div>
                        </div>
                      )}
                    </div>
                  )}
                  {feedbackStats && feedbackStats.total_count > 0 && (
                    <div className="mt-3 sm:mt-4 text-xs sm:text-sm text-slate-600">
                      <p className="mb-1">{feedbackStats.positive_count} de {feedbackStats.total_count} pessoas acharam esta explicação útil ({feedbackStats.positive_percentage}%)</p>
                      <div className="w-full bg-slate-200 rounded-full h-1.5 sm:h-2.5">
                        <div 
                          className="bg-emerald-500 h-1.5 sm:h-2.5 rounded-full" 
                          style={{ width: `${feedbackStats.positive_percentage}%` }}
                        ></div>
                      </div>
                    </div>
                  )}
                </div>
                {verses && (
                  <div className="mt-4 sm:mt-8 flex justify-between items-center">
                    <button 
                      onClick={navigateToPreviousVerse}
                      className="flex items-center gap-1 sm:gap-2 px-2 sm:px-4 py-1.5 sm:py-2 bg-slate-100 text-slate-700 rounded-md hover:bg-slate-200 transition-colors text-xs sm:text-sm"
                      disabled={parseInt(verses.split(',')[0]) <= 1}
                    >
                      <ArrowLeft size={isMobile ? 14 : 16} />
                      <span className="hidden xs:inline">Versículo anterior</span>
                      <span className="xs:hidden">Anterior</span>
                    </button>
                    <button 
                      onClick={navigateToNextVerse}
                      className="flex items-center gap-1 sm:gap-2 px-2 sm:px-4 py-1.5 sm:py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors text-xs sm:text-sm"
                    >
                      <span className="hidden xs:inline">Próximo versículo</span>
                      <span className="xs:hidden">Próximo</span>
                      <ArrowRight size={isMobile ? 14 : 16} />
                    </button>
                  </div>
                )}
                <style>{`
                  @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                  }
                  @keyframes celebrate {
                    0% { transform: scale(1); box-shadow: 0 0 0 rgba(72, 187, 120, 0); }
                    25% { transform: scale(1.03); }
                    50% { transform: scale(1.05); box-shadow: 0 0 20px rgba(72, 187, 120, 0.4); }
                    75% { transform: scale(1.03); }
                    100% { transform: scale(1); box-shadow: 0 0 0 rgba(72, 187, 120, 0); }
                  }
                  .celebration {
                    animation: celebrate 1.2s ease-in-out infinite;
                    background-color: rgba(236, 253, 245, 0.9);
                    transition: all 0.3s ease;
                    border-color: rgba(72, 187, 120, 0.6) !important;
                  }
                  .bible-explanation {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                    color: hsl(240 10% 3.9%);
                    line-height: 1.8;
                    max-width: 100%;
                    overflow-wrap: break-word;
                  }
                  .main-title {
                    color: hsl(240 5.9% 10%);
                    font-size: 1.875rem;
                    margin-bottom: 1.75rem;
                    text-align: center;
                    border-bottom: 1px solid hsl(240 5.9% 90%);
                    padding-bottom: 1rem;
                    font-weight: 700;
                    letter-spacing: -0.025em;
                  }
                  .section-title {
                    color: hsl(240 6% 10%);
                    font-size: 1.25rem;
                    margin-top: 2.5rem;
                    margin-bottom: 1rem;
                    border-left: 3px solid hsl(221.2 83.2% 53.3%);
                    padding-left: 0.75rem;
                    font-weight: 600;
                    letter-spacing: -0.025em;
                  }
                  .bible-text {
                    font-size: 1.125rem;
                    background-color: hsl(220 14.3% 95.9%);
                    padding: 1.25rem;
                    border-radius: 0.5rem;
                    margin: 1.25rem 0;
                    font-style: italic;
                    color: hsl(240 5.9% 26%);
                    border-left: 3px solid hsl(142.1 76.2% 36.3%);
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                  }
                  .original-translation {
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                    margin: 1.75rem 0;
                    background-color: hsl(210 40% 98%);
                    border-radius: 0.5rem;
                    padding: 1.5rem;
                    border-left: 3px solid hsl(262.1 83.3% 57.8%);
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                  }
                  .original-text {
                    font-family: 'SBL Hebrew', 'Times New Roman', Times, serif;
                    font-size: 1.25rem;
                    line-height: 1.7;
                    direction: rtl;
                    text-align: right;
                    padding-bottom: 1rem;
                    border-bottom: 1px solid hsl(240 5.9% 90%);
                    color: hsl(240 5.9% 26%);
                    font-weight: 500;
                  }
                  .transliteration {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                    font-style: italic;
                    color: hsl(240 3.8% 46.1%);
                    padding-bottom: 1rem;
                    border-bottom: 1px solid hsl(240 5.9% 90%);
                  }
                  .translation {
                    font-size: 1.125rem;
                    padding-bottom: 1rem;
                    color: hsl(240 5.9% 10%);
                    font-weight: 500;
                    border-bottom: 1px solid hsl(240 5.9% 90%);
                  }
                  .word-analysis {
                    font-size: 0.95rem;
                    color: hsl(240 3.8% 46.1%);
                    line-height: 1.7;
                    background-color: hsl(240 5.9% 96%);
                    padding: 1rem;
                    border-radius: 0.375rem;
                    margin-top: 0.75rem;
                  }
                  .applications, .connections, .references {
                    margin-left: 1.5rem;
                    margin-top: 1.25rem;
                    list-style-type: none;
                  }
                  .applications li, .connections li, .references li {
                    margin-bottom: 1rem;
                    position: relative;
                    padding-left: 0.75rem;
                    line-height: 1.7;
                  }
                  .applications li:before {
                    content: '•';
                    color: hsl(142.1 76.2% 36.3%);
                    font-weight: bold;
                    display: inline-block;
                    width: 1em;
                    margin-left: -1em;
                  }
                  .connections li:before {
                    content: '•';
                    color: hsl(221.2 83.2% 53.3%);
                    font-weight: bold;
                    display: inline-block;
                    width: 1em;
                    margin-left: -1em;
                  }
                  .references li:before {
                    content: '•';
                    color: hsl(262.1 83.3% 57.8%);
                    font-weight: bold;
                    display: inline-block;
                    width: 1em;
                    margin-left: -1em;
                  }
                  .references {
                    font-size: 0.95rem;
                    color: hsl(240 3.8% 46.1%);
                    font-style: italic;
                  }
                  .reflection {
                    margin: 2.5rem 0;
                  }
                  .reflection blockquote {
                    background-color: hsl(210 40% 98%);
                    border-left: 3px solid hsl(221.2 83.2% 53.3%);
                    margin: 1.75rem 0;
                    padding: 1.5rem;
                    font-style: italic;
                    border-radius: 0 0.5rem 0.5rem 0;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                  }
                  .reflection blockquote p {
                    margin: 0;
                    color: hsl(221.2 83.2% 53.3%);
                    font-size: 1.125rem;
                    line-height: 1.7;
                    font-weight: 500;
                  }
                  .bible-explanation-fallback h2, .bible-explanation-fallback h3 {
                    color: #3b3b7c;
                    margin-top: 1.2em;
                  }
                  .bible-explanation-fallback ul {
                    margin-left: 1.5em;
                    list-style: disc;
                  }
                  .bible-explanation-fallback blockquote {
                    background: #eef2fa;
                    border-left: 4px solid #3b3b7c;
                    margin: 1em 0;
                    padding: 0.7em 1em;
                    color: #444;
                  }
                  .bible-explanation-note {
                    color: #777;
                    font-size: 0.95em;
                    margin-top: 2em;
                  }
                  @media (max-width: 768px) {
                    .main-title {
                      font-size: 1.5rem;
                      padding-bottom: 0.75rem;
                      margin-bottom: 1.5rem;
                    }
                    .section-title {
                      font-size: 1.125rem;
                      margin-top: 2rem;
                    }
                    .bible-text {
                      padding: 1rem;
                      font-size: 1rem;
                    }
                    .original-text {
                      font-size: 1.125rem;
                    }
                    .translation {
                      font-size: 1rem;
                    }
                    .reflection blockquote {
                      padding: 1.25rem;
                    }
                    .reflection blockquote p {
                      font-size: 1rem;
                    }
                  }
                  .bible-explanation * {
                    transition: all 0.2s ease-in-out;
                  }
                  .bible-explanation a {
                    color: hsl(221.2 83.2% 53.3%);
                    text-decoration: underline;
                    text-underline-offset: 0.2em;
                  }
                  .bible-explanation a:hover {
                    color: hsl(221.2 83.2% 43.3%);
                  }
                `}</style>
              </div>
            </div>
          )}
        </div>
        <Footer />
      </AppLayout>
    </>
  );
}