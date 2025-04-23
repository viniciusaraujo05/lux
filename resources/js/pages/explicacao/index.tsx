import React, { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, BookOpen, ArrowRight, ArrowLeft, ThumbsUp, ThumbsDown, MessageSquare } from 'lucide-react';

interface BibleExplanationProps {
  testamento: string;
  livro: string;
  capitulo: string;
}

export default function BibleExplanation(props: BibleExplanationProps) {
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
  const [feedbackStats, setFeedbackStats] = useState<{
    positive_count: number;
    negative_count: number;
    total_count: number;
    positive_percentage: number;
    negative_percentage: number;
  } | null>(null);
  
  // Extrair os parâmetros da URL
  const testament = props.testamento;
  const book = props.livro;
  const chapter = props.capitulo;
  // Obter versículos da URL (pode estar como 'versiculos' em português ou 'verses' em inglês)
  const versiculosParam = new URLSearchParams(window.location.search).get('versiculos');
  const versesParam = new URLSearchParams(window.location.search).get('verses');
  const verses = versiculosParam || versesParam;
  
  const maxVerses = 50; // Valor padrão alto para a maioria dos capítulos

  useEffect(() => {
    async function fetchExplanation() {
      try {
        // Construir a URL da API com os parâmetros corretos
        let apiUrl = `/api/explanation/${testament}/${book}/${chapter}`;
        if (verses) {
          apiUrl += `?verses=${verses}`;
        }
        
        console.log('Fetching explanation from:', apiUrl);
        
        const response = await fetch(apiUrl);
        if (!response.ok) {
          throw new Error(`API responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('API response:', data);
        
        setExplanation(data.explanation || 'No explanation was returned.');
        setSource(data.origin || 'unknown');
        setExplanationId(data.id || null);
        setLoading(false);
        
        // Se temos um ID de explicação, buscar estatísticas de feedback
        if (data.id) {
          fetchFeedbackStats(data.id);
        }
      } catch (error) {
        console.error('Error fetching explanation:', error);
        setExplanation('Erro ao buscar explicação. Por favor, tente novamente.');
        setLoading(false);
      }
    }
    
    fetchExplanation();
  }, [testament, book, chapter, verses]);



  // Função para navegar para um versículo específico
  const navigateToVerse = (verseNumber: number) => {
    if (verseNumber < 1 || verseNumber > maxVerses) return;
    window.location.href = `/explicacao/${testament}/${book}/${chapter}?versiculos=${verseNumber}`;
  };

  // Função para iniciar análise versículo por versículo
  const startVerseByVerseAnalysis = () => {
    navigateToVerse(1);
  };

  // Função para navegar para o próximo versículo
  const navigateToNextVerse = () => {
    if (!verses) return;
    
    // Se temos múltiplos versículos, pegamos o último
    const versesList = verses.split(',');
    const lastVerse = parseInt(versesList[versesList.length - 1], 10);
    
    if (lastVerse < maxVerses) {
      navigateToVerse(lastVerse + 1);
    }
  };

  // Função para navegar para o versículo anterior
  const navigateToPreviousVerse = () => {
    if (!verses) return;
    
    // Se temos múltiplos versículos, pegamos o primeiro
    const versesList = verses.split(',');
    const firstVerse = parseInt(versesList[0], 10);
    
    if (firstVerse > 1) {
      navigateToVerse(firstVerse - 1);
    }
  };

  // Função para buscar estatísticas de feedback
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

  // Função para enviar feedback
  const submitFeedback = async (isPositive: boolean) => {
    if (!explanationId) return;
    
    setFeedbackType(isPositive ? 'positive' : 'negative');
    
    if (isPositive) {
      // Se for positivo, envie imediatamente e mostre celebração
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
          
          // Atualizar estatísticas
          if (explanationId) {
            fetchFeedbackStats(explanationId);
          }
        }
      } catch (error) {
        console.error('Error submitting positive feedback:', error);
      }
    } else {
      // Se for negativo, mostre o formulário para comentários
      setShowFeedbackForm(true);
      setFeedbackType('negative');
    }
  };

  // Função para enviar feedback com comentário
  const submitFeedbackWithComment = async () => {
    if (!explanationId || !feedbackType) return;
    
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
        // Atualizar estatísticas
        if (explanationId) {
          fetchFeedbackStats(explanationId);
        }
      }
    } catch (error) {
      console.error('Error submitting feedback with comment:', error);
    }
  };

  return (
    <div className="container max-w-4xl mx-auto p-4">
      <header className="mb-6 flex items-center justify-between bg-white shadow-sm rounded-lg p-4">
        <div className="flex items-center">
          <button 
            onClick={() => {
              // Redirecionar para a página de versículos usando a nova estrutura de URLs
              window.location.href = `/biblia/${testament}/${book}/${chapter}`;
            }}
            className="mr-4 text-indigo-600 hover:text-indigo-800 transition-colors flex items-center justify-center"
            aria-label="Voltar"
          >
            <ChevronLeft size={22} />
          </button>
          <h1 className="text-xl font-bold text-gray-800">
            {book} {chapter}
            {verses && <span className="ml-2 text-sm text-gray-600">(Versículos: {verses})</span>}
          </h1>
        </div>
        
        {verses && (
          <div className="flex items-center space-x-2">
            <button 
              onClick={navigateToPreviousVerse}
              className="p-2 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
              aria-label="Versículo anterior"
            >
              <ArrowLeft size={18} />
            </button>
            <button 
              onClick={navigateToNextVerse}
              className="p-2 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
              aria-label="Próximo versículo"
            >
              <ArrowRight size={18} />
            </button>
          </div>
        )}
      </header>
      
      <div className="border rounded-lg p-6 bg-white shadow-sm">
        {!verses && (
          <div className="mb-6 flex justify-end">
            <button 
              onClick={startVerseByVerseAnalysis}
              className="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
            >
              <BookOpen size={18} />
              Analisar versículo por versículo
            </button>
          </div>
        )}
        {loading ? (
          <div className="flex flex-col items-center justify-center py-12 space-y-4">
            <div className="animate-spin h-12 w-12 border-4 border-primary rounded-full border-t-transparent"></div>
            <p className="text-lg text-center text-slate-600 animate-pulse">
              Preparando explicação...
            </p>
            <p className="text-sm text-center text-slate-500">
              "A tua palavra é lâmpada para os meus pés e luz para o meu caminho." - Salmos 119:105
            </p>
          </div>
        ) : (
          <div>
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-lg font-semibold text-gray-800">Explicação Bíblica</h2>
              {source && (
                <div className={`text-xs px-3 py-1 rounded-full font-medium ${source === 'cache' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700'}`}>
                  Fonte: {source === 'cache' ? 'Banco de Dados' : 'Inteligência Artificial'}
                </div>
              )}
            </div>
            
            <div className="prose max-w-none">
              <div 
                className="explanation-container bg-slate-50 rounded-lg p-6 mt-4 shadow-sm border border-slate-200"
                style={{
                  animation: 'fadeIn 0.8s ease-in-out',
                }}
              >
                {explanation && (
                  <div
                    className="explanation-text text-slate-800 text-lg leading-relaxed"
                    dangerouslySetInnerHTML={{ __html: explanation }}
                  />
                )}
                
                {/* Seção de Feedback */}
                <div className="mt-8 pt-6 border-t border-slate-200">
                  <h3 className="text-lg font-semibold mb-4">Esta explicação foi útil?</h3>
                  
                  {feedbackSubmitted ? (
                    <div 
                      className={`p-4 rounded-md border ${feedbackType === 'positive' 
                        ? 'bg-green-50 text-green-700 border-green-200' + (showCelebration ? ' celebration' : '')
                        : 'bg-amber-50 text-amber-700 border-amber-200'}`}
                    >
                      {feedbackType === 'positive' ? (
                        <div className="flex flex-col items-center text-center">
                          <div className="text-2xl mb-2">🎉 Que maravilha! 🙏</div>
                          <p>Obrigado pelo seu feedback positivo! Ficamos felizes que a explicação tenha sido útil para sua jornada espiritual.</p>
                          <p className="text-sm mt-2 text-green-600">"Pois a palavra de Deus é viva e eficaz..." - Hebreus 4:12</p>
                        </div>
                      ) : (
                        <p>Obrigado pelo seu feedback! Sua opinião é muito importante para melhorarmos nosso conteúdo.</p>
                      )}
                    </div>
                  ) : (
                    <div className="flex flex-col space-y-4">
                      <div className="flex space-x-4">
                        <button 
                          onClick={() => submitFeedback(true)}
                          className="flex items-center space-x-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-md border border-emerald-200 hover:bg-emerald-100 transition-colors"
                        >
                          <ThumbsUp size={18} />
                          <span>Sim, foi útil</span>
                        </button>
                        
                        <button 
                          onClick={() => submitFeedback(false)}
                          className="flex items-center space-x-2 px-4 py-2 bg-amber-50 text-amber-700 rounded-md border border-amber-200 hover:bg-amber-100 transition-colors"
                        >
                          <ThumbsDown size={18} />
                          <span>Não foi útil</span>
                        </button>
                      </div>
                      
                      {showFeedbackForm && (
                        <div className="bg-slate-100 p-4 rounded-md border border-slate-200">
                          <h4 className="font-medium mb-2">Como podemos melhorar esta explicação?</h4>
                          <textarea 
                            className="w-full p-3 border border-slate-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none mb-3"
                            rows={4}
                            placeholder="Compartilhe suas sugestões para melhorarmos o conteúdo..."
                            value={feedbackComment}
                            onChange={(e) => setFeedbackComment(e.target.value)}
                          />
                          <div className="flex justify-end space-x-3">
                            <button 
                              onClick={() => setShowFeedbackForm(false)}
                              className="px-4 py-2 text-slate-700 bg-slate-200 rounded-md hover:bg-slate-300 transition-colors"
                            >
                              Cancelar
                            </button>
                            <button 
                              onClick={submitFeedbackWithComment}
                              className="px-4 py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition-colors"
                            >
                              Enviar feedback
                            </button>
                          </div>
                        </div>
                      )}
                    </div>
                  )}
                  
                  {/* Estatísticas de Feedback */}
                  {feedbackStats && feedbackStats.total_count > 0 && (
                    <div className="mt-4 text-sm text-slate-600">
                      <p className="mb-1">{feedbackStats.positive_count} de {feedbackStats.total_count} pessoas acharam esta explicação útil ({feedbackStats.positive_percentage}%)</p>
                      <div className="w-full bg-slate-200 rounded-full h-2.5">
                        <div 
                          className="bg-emerald-500 h-2.5 rounded-full" 
                          style={{ width: `${feedbackStats.positive_percentage}%` }}
                        ></div>
                      </div>
                    </div>
                  )}
                </div>
              </div>
              
              {verses && (
                <div className="mt-8 flex justify-between items-center">
                  <button 
                    onClick={navigateToPreviousVerse}
                    className="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 rounded-md hover:bg-slate-200 transition-colors"
                    disabled={parseInt(verses.split(',')[0]) <= 1}
                  >
                    <ArrowLeft size={16} />
                    Versículo anterior
                  </button>
                  
                  <button 
                    onClick={navigateToNextVerse}
                    className="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
                  >
                    Próximo versículo
                    <ArrowRight size={16} />
                  </button>
                </div>
              )}
              {/* CSS para a explicação bíblica estruturada */}
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
                
                /* Estilos gerais para a explicação bíblica - Inspirado no Shadcn UI */
                .bible-explanation {
                  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                  color: hsl(240 10% 3.9%);
                  line-height: 1.8;
                  max-width: 100%;
                  overflow-wrap: break-word;
                }
                
                /* Título principal */
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
                
                /* Títulos de seção */
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
                
                /* Texto bíblico */
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
                
                /* Seção combinada de texto original e tradução */
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
                
                /* Texto original */
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
                
                /* Transliteração */
                .transliteration {
                  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                  font-style: italic;
                  color: hsl(240 3.8% 46.1%);
                  padding-bottom: 1rem;
                  border-bottom: 1px solid hsl(240 5.9% 90%);
                }
                
                /* Tradução */
                .translation {
                  font-size: 1.125rem;
                  padding-bottom: 1rem;
                  color: hsl(240 5.9% 10%);
                  font-weight: 500;
                  border-bottom: 1px solid hsl(240 5.9% 90%);
                }
                
                /* Análise de palavras */
                .word-analysis {
                  font-size: 0.95rem;
                  color: hsl(240 3.8% 46.1%);
                  line-height: 1.7;
                  background-color: hsl(240 5.9% 96%);
                  padding: 1rem;
                  border-radius: 0.375rem;
                  margin-top: 0.75rem;
                }
                
                /* Listas de aplicações, conexões e referências */
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
                
                /* Bloco de reflexão */
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
                
                /* Estilos para fallback */
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
                
                /* Responsividade para telas menores */
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
                
                /* Animações suaves */
                .bible-explanation * {
                  transition: all 0.2s ease-in-out;
                }
                
                /* Melhorias de acessibilidade */
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
    </div>
  );
}
