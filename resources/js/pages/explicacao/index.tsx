import React, { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, BookOpen, ArrowRight, ArrowLeft } from 'lucide-react';

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
  
  // Extrair os parâmetros da URL
  const testament = props.testamento;
  const book = props.livro;
  const chapter = props.capitulo;
  const verses = new URLSearchParams(window.location.search).get('versiculos');
  
  // Determinar o número máximo de versículos para este capítulo
  // Isso é um placeholder - idealmente viria de uma API ou dados da Bíblia
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
        setLoading(false);
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

  return (
    <div className="container max-w-4xl mx-auto p-4">
      <header className="mb-6 flex items-center justify-between bg-white shadow-sm rounded-lg p-4">
        <div className="flex items-center">
          <Link href="/" className="mr-4 text-indigo-600 hover:text-indigo-800 transition-colors">
            <ChevronLeft size={22} />
          </Link>
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
          <div className="flex justify-center py-8">
            <div className="animate-spin h-8 w-8 border-4 border-blue-500 rounded-full border-t-transparent"></div>
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
              <div className="explanation-container bg-slate-50 rounded-lg p-6 mt-4 shadow-sm border border-slate-200">
                {explanation && (
                  <div
                    className="explanation-text text-slate-800 text-lg leading-relaxed"
                    dangerouslySetInnerHTML={{ __html: explanation }}
                  />
                )}
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
