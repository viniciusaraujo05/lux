import React, { useState, useEffect, useRef, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ChevronLeft, ChevronRight, BookOpen, Home, Search, Menu, X, Sparkles, Book, Users, Scale, Cross, Target, Gem, FileText, Key, Loader2, Bookmark } from 'lucide-react';
import SlugService from '@/services/SlugService';
import BibleService from '@/services/BibleService';
import { router } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';

// Definindo os estados de navegação possíveis
type NavigationState = 'testamentos' | 'livros' | 'capitulos' | 'versiculos' | 'contexto';

// Interface para o contexto do livro
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

// Componente para renderizar o contexto do livro
const BookContextRenderer: React.FC<{ context: string; bookName: string | null }> = ({ context, bookName }) => {
  let contextData: BookContextData;
  
  try {
    contextData = JSON.parse(context);
  } catch (error) {
    return (
      <div className="p-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400 rounded-r-lg">
        <h3 className="font-semibold text-red-800 dark:text-red-200">Erro ao processar contexto</h3>
        <p className="text-red-700 dark:text-red-300 mt-1">Não foi possível processar o contexto do livro.</p>
      </div>
    );
  }

  const getSectionIcon = (id: string) => {
    switch (id) {
      case 'identificacao-genero': return <Book className="h-5 w-5" />;
      case 'contexto-historico': return <Users className="h-5 w-5" />;
      case 'autoria-intencao': return <FileText className="h-5 w-5" />;
      case 'mensagem-fe': return <Cross className="h-5 w-5" />;
      case 'relacao-biblia': return <Target className="h-5 w-5" />;
      case 'tradicao-interpretativa': return <Scale className="h-5 w-5" />;
      case 'doutrinas-presentes': return <Gem className="h-5 w-5" />;
      case 'leitura-canonica': return <Key className="h-5 w-5" />;
      default: return <BookOpen className="h-5 w-5" />;
    }
  };

  return (
    <div className="space-y-6">
      {/* Cabeçalho */}
      <div className="text-center py-6 bg-gradient-to-r from-primary/5 to-secondary/5 rounded-lg border border-border">
        <h1 className="text-2xl font-bold text-foreground mb-2">
          Contexto do Livro de {bookName}
        </h1>
        <p className="text-muted-foreground">
          Análise teológica e histórica abrangente
        </p>
      </div>

      {/* Seções */}
      {contextData.sections?.map((section, index) => (
        <motion.div
          key={section.id}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: index * 0.1 }}
          className="bg-card text-card-foreground rounded-lg border border-border shadow-sm overflow-hidden"
        >
          {/* Cabeçalho da seção */}
          <div className="bg-gradient-to-r from-primary/10 to-secondary/10 px-6 py-4 border-b border-border">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-primary/20 rounded-lg text-primary">
                {getSectionIcon(section.id)}
              </div>
              <h2 className="text-xl font-semibold text-foreground">
                {section.title}
              </h2>
            </div>
          </div>

          <div className="p-6">
            {/* Conteúdo principal da seção */}
            {section.content && (
              <div className="prose prose-gray dark:prose-invert max-w-none mb-6">
                <p className="text-muted-foreground leading-relaxed">
                  {section.content}
                </p>
              </div>
            )}

            {/* Subseções */}
            {section.subsections && (
              <div className="space-y-4">
                {section.subsections.map((subsection, subIndex) => (
                  <div key={subIndex} className="bg-muted/30 rounded-lg p-4 border border-border/50">
                    <h3 className="font-semibold text-foreground mb-2 flex items-center gap-2">
                      <div className="w-2 h-2 bg-primary rounded-full"></div>
                      {subsection.title}
                    </h3>
                    <p className="text-muted-foreground leading-relaxed">
                      {subsection.content}
                    </p>
                  </div>
                ))}
              </div>
            )}
          </div>
        </motion.div>
      ))}

      {/* Rodapé */}
      <div className="text-center py-4 text-sm text-muted-foreground border-t border-border">
        <p>Análise gerada com base em fontes teológicas e exegéticas confiáveis</p>
      </div>
    </div>
  );
};

interface BibleBooksGridProps {
  initialTestament?: 'velho' | 'novo';
  initialBook?: string;
  initialChapter?: number;
}

const NAV_STATE_KEY = 'verbum:bible_nav_state';
type SavedNavState = {
  testament: 'velho' | 'novo';
  book: string | null;
  chapter: number | null;
  selectedVerses: number[];
};

export default function BibleBooksGrid({ initialTestament, initialBook, initialChapter }: BibleBooksGridProps) {
  // Estados para armazenar os dados da Bíblia
  const [livrosVelhoTestamento, setLivrosVelhoTestamento] = useState<string[]>([]);
  const [livrosNovoTestamento, setLivrosNovoTestamento] = useState<string[]>([]);
  const [selectedBook, setSelectedBook] = useState<string | null>(null);
  const [activeTestament, setActiveTestament] = useState<'velho' | 'novo'>('velho'); // Mantido para compatibilidade
  const [selectedChapter, setSelectedChapter] = useState<number | null>(null);
  const [chapters, setChapters] = useState<number[]>([]);
  const [verses, setVerses] = useState<number[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [navState, setNavState] = useState<NavigationState>('livros'); // Começa direto na visão de livros
  const [searchTerm, setSearchTerm] = useState<string>(''); // Estado global para pesquisa
  const [selectedVerses, setSelectedVerses] = useState<number[]>([]); // Versículos selecionados para explicação
  const [selectionMode, setSelectionMode] = useState<boolean>(false); // Modo de seleção de versículos
  const [navigating, setNavigating] = useState<boolean>(false); // Feedback imediato ao navegar para explicação
  const [bookContext, setBookContext] = useState<any>(null);
  const [contextLoading, setContextLoading] = useState(false);

  // Instâncias dos serviços
  const bibleService = BibleService.getInstance();
  const slugService = SlugService.getInstance();
  
  // Helper: rolar para o topo em transições
  const scrollToTop = () => {
    try {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch {
      window.scrollTo(0, 0);
    }
  };

  // Fetch book context
  const fetchBookContext = async () => {
    if (!selectedBook) return;
    
    try {
      const bookSlug = slugService.livroParaSlug(selectedBook);
      const expTestament = toExplanationTestament(activeTestament);
      const response = await fetch(`/api/book-context/${expTestament}/${bookSlug}`, {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      });
      
      if (!response.ok) throw new Error(`API responded with status: ${response.status}`);
      
      const data = await response.json();
      setBookContext(data);
    } catch (error) {
      console.error('Error fetching book context:', error);
      setBookContext({
        error: 'fetch_failed',
        message: 'Erro ao buscar o contexto do livro. Tente novamente.'
      });
    } finally {
      setContextLoading(false);
    }
  };
  
  // Normaliza o testamento para as rotas de explicação (backend usa 'antigo' | 'novo')
  const toExplanationTestament = (t: 'velho' | 'novo'): 'antigo' | 'novo' => (t === 'velho' ? 'antigo' : 'novo');

  // Helper para persistir o estado atual no localStorage (com possibilidade de override)
  const persistNavState = (overrides?: Partial<SavedNavState>) => {
    try {
      const payload: SavedNavState = {
        testament: activeTestament,
        book: selectedBook,
        chapter: selectedChapter,
        selectedVerses,
        ...(overrides || {}),
      } as SavedNavState;
      localStorage.setItem(NAV_STATE_KEY, JSON.stringify(payload));
    } catch (e) {
      void e;
    }
  };

  // Carrega os livros quando o componente é montado
  useEffect(() => {
    const loadBooks = async () => {
      try {
        setLoading(true);
        const velhoTestamento = await bibleService.getLivrosVelhoTestamento();
        const novoTestamento = await bibleService.getLivrosNovoTestamento();
        
        setLivrosVelhoTestamento(velhoTestamento);
        setLivrosNovoTestamento(novoTestamento);
        
        // Inicializar com os parâmetros da URL, se disponíveis
        if (initialTestament && initialBook) {
          setActiveTestament(initialTestament);
          setSelectedBook(initialBook);
          
          const bookChapters = await bibleService.getCapitulos(initialBook, initialTestament);
          setChapters(bookChapters);
          
          if (initialChapter) {
            setSelectedChapter(initialChapter);
            
            // Carregar versículos para o capítulo selecionado
            const chapterVerses = await bibleService.getVersiculos(initialBook, initialChapter, initialTestament);
            setVerses(chapterVerses);
            
            // Definir o estado de navegação como 'versiculos'
            setNavState('versiculos');
          } else {
            // Definir o estado de navegação como 'capitulos'
            setNavState('capitulos');
          }
        } else {
          // Restaurar estado salvo do localStorage, se existir
          try {
            const savedRaw = localStorage.getItem(NAV_STATE_KEY);
            if (savedRaw) {
              const saved: SavedNavState = JSON.parse(savedRaw);
              if (saved.testament) setActiveTestament(saved.testament);
              if (saved.book) {
                setSelectedBook(saved.book);
                const chs = await bibleService.getCapitulos(saved.book, saved.testament);
                setChapters(chs);
                if (saved.chapter !== null && saved.chapter !== undefined) {
                  setSelectedChapter(saved.chapter);
                  const vs = await bibleService.getVersiculos(saved.book, saved.chapter, saved.testament);
                  setVerses(vs);
                  setNavState('versiculos');
                } else {
                  setNavState('capitulos');
                }
              } else {
                setNavState('livros');
              }
              // Sincroniza URL com o estado salvo
              const bookSlug = saved.book ? slugService.livroParaSlug(saved.book) : '';
              if (saved.book && saved.chapter !== null && saved.chapter !== undefined) {
                window.history.replaceState(
                  { testament: saved.testament, book: saved.book, chapter: saved.chapter },
                  '',
                  `/biblia/${saved.testament}/${bookSlug}/${saved.chapter}`
                );
              } else if (saved.book) {
                window.history.replaceState(
                  { testament: saved.testament, book: saved.book },
                  '',
                  `/biblia/${saved.testament}/${bookSlug}`
                );
              } else {
                window.history.replaceState({}, '', `/biblia`);
              }
            }
          } catch (e) {
            void e; // Silencia erros de localStorage
          }
        }
        
        setLoading(false);
      } catch (err) {
        console.error('Erro ao carregar livros:', err);
        setError('Não foi possível carregar os livros da Bíblia. Por favor, tente novamente mais tarde.');
        setLoading(false);
      }
    };

    loadBooks();
  }, [initialTestament, initialBook, initialChapter, bibleService, slugService]);

  // Carrega os capítulos quando um livro é selecionado
  useEffect(() => {
    const loadChapters = async () => {
      if (selectedBook) {
        try {
          setLoading(true);
          // Garantir que o testamento seja do tipo correto
          const testament = activeTestament as 'velho' | 'novo';
          const chaptersData = await bibleService.getCapitulos(selectedBook, testament);
          setChapters(chaptersData);
          setLoading(false);
        } catch (err) {
          console.error('Erro ao carregar capítulos:', err);
          setError('Não foi possível carregar os capítulos. Por favor, tente novamente mais tarde.');
          setLoading(false);
        }
      } else {
        setChapters([]);
      }
    };

    loadChapters();
  }, [selectedBook, activeTestament, bibleService]);

  // Carrega os versículos quando um capítulo é selecionado
  useEffect(() => {
    const loadVerses = async () => {
      if (selectedBook && selectedChapter !== null) {
        try {
          setLoading(true);
          const versesData = await bibleService.getVersiculos(selectedBook, selectedChapter, activeTestament);
          setVerses(versesData);
          setLoading(false);
        } catch (err) {
          console.error('Erro ao carregar versículos:', err);
          setError('Não foi possível carregar os versículos. Por favor, tente novamente mais tarde.');
          setLoading(false);
        }
      } else {
        setVerses([]);
      }
    };

    loadVerses();
  }, [selectedBook, selectedChapter, activeTestament, bibleService]);

  // Persiste o estado de navegação no localStorage
  useEffect(() => {
    const saved: SavedNavState = {
      testament: activeTestament,
      book: selectedBook,
      chapter: selectedChapter,
      selectedVerses,
    };
    try {
      localStorage.setItem(NAV_STATE_KEY, JSON.stringify(saved));
    } catch (e) {
      void e; // ignore quota or serialization errors
    }
  }, [activeTestament, selectedBook, selectedChapter, selectedVerses]);

  // Sincroniza com o botão de voltar/avançar do navegador
  useEffect(() => {
    const applyRoute = async (path: string) => {
      if (!path.startsWith('/biblia')) return;
      const parts = path.split('/').filter(Boolean);
      // '/biblia'
      if (parts.length === 1) {
        setSelectedBook(null);
        setSelectedChapter(null);
        setChapters([]);
        setVerses([]);
        setNavState('livros');
        scrollToTop();
        return;
      }
      // '/biblia/{testamento}/{livro}' ou '/biblia/{testamento}/{livro}/{capitulo}'
      if (parts.length >= 3) {
        const testament = (parts[1] === 'velho' ? 'velho' : 'novo') as 'velho' | 'novo';
        const bookSlug = parts[2];
        const book = slugService.slugParaLivro(bookSlug);
        setActiveTestament(testament);
        setSelectedBook(book);
        try {
          setLoading(true);
          const chs = await bibleService.getCapitulos(book, testament);
          setChapters(chs);
          if (parts.length >= 4) {
            const chapter = parseInt(parts[3], 10);
            setSelectedChapter(chapter);
            const vs = await bibleService.getVersiculos(book, chapter, testament);
            setVerses(vs);
            setNavState('versiculos');
            scrollToTop();
          } else {
            setSelectedChapter(null);
            setVerses([]);
            setNavState('capitulos');
            scrollToTop();
          }
        } catch (e) {
          console.error('Erro ao sincronizar URL:', e);
          setError('Não foi possível sincronizar a navegação.');
        } finally {
          setLoading(false);
        }
      }
    };

    const onPop = () => applyRoute(window.location.pathname);
    window.addEventListener('popstate', onPop);
    return () => window.removeEventListener('popstate', onPop);
  }, [bibleService, slugService]);

  // Funções de navegação (simplificadas após redesign)
  // (removido) navigateToBooks não é necessário na UI atual

  // Função para navegar para os capítulos de um livro
  const navigateToChapters = async (book: string) => {
    setLoading(true);
    setSelectedBook(book);
    setSelectedChapter(null);
    setVerses([]);
    setSelectedVerses([]);
    setSelectionMode(false);
    
    try {
      // Determinar o testamento com base no livro selecionado
      const testament = livrosVelhoTestamento.includes(book) ? 'velho' as const : 'novo' as const;
      setActiveTestament(testament);
      
      // Converter o nome do livro para slug para URL limpa
      const bookSlug = slugService.livroParaSlug(book);
      
      // Atualizar a URL do navegador
      window.history.pushState(
        { testament, book }, 
        '', 
        `/biblia/${testament}/${bookSlug}`
      );
      // Garantir que a visão comece pelo topo
      scrollToTop();
      
      const bookChapters = await bibleService.getCapitulos(book, testament);
      setChapters(bookChapters);
      setLoading(false);
    } catch (err) {
      console.error('Erro ao carregar capítulos:', err);
      setError('Não foi possível carregar os capítulos. Por favor, tente novamente mais tarde.');
      setLoading(false);
    }
  };

  // Função para navegar para os versículos de um capítulo
  const navigateToVerses = async (chapter: number) => {
    setLoading(true);
    setSelectedChapter(chapter);
    setSelectedVerses([]);
    setSelectionMode(false);
    
    try {
      if (!selectedBook) throw new Error('Nenhum livro selecionado');
      
      // Converter o nome do livro para slug para URL limpa
      const bookSlug = selectedBook ? slugService.livroParaSlug(selectedBook) : '';
      
      // Atualizar a URL do navegador
      window.history.pushState(
        { testament: activeTestament, book: selectedBook, chapter },
        '',
        `/biblia/${activeTestament}/${bookSlug}/${chapter}`
      );
      // Garantir que a visão comece pelo topo
      scrollToTop();
      
      // Garantir que o testamento seja do tipo correto
      const testament = activeTestament as 'velho' | 'novo';
      const chapterVerses = await bibleService.getVersiculos(selectedBook, chapter, testament);
      setVerses(chapterVerses);
      setLoading(false);
    } catch (err) {
      console.error('Erro ao carregar versículos:', err);
      setError('Não foi possível carregar os versículos. Por favor, tente novamente mais tarde.');
      setLoading(false);
    }
  };

  // Função para voltar para a visualização anterior
  const navigateBack = () => {
    if (navState === 'contexto') {
      setNavState('capitulos');
      setBookContext(null);
    } else if (selectedChapter !== null) {
      const backBookSlug = selectedBook ? slugService.livroParaSlug(selectedBook) : '';
      setSelectedChapter(null);
      setVerses([]);
      setSelectedVerses([]);
      setSelectionMode(false);
      window.history.pushState(
        { testament: activeTestament, book: selectedBook },
        '',
        `/biblia/${activeTestament}/${backBookSlug}`
      );
      setNavState('capitulos');
    } else if (selectedBook !== null) {
      setSelectedBook(null);
      setChapters([]);
      window.history.pushState({}, '', `/biblia`);
      setNavState('livros');
    }
    scrollToTop();
  };

  // Atualiza o estado de navegação quando algo é selecionado
  useEffect(() => {
    if (selectedChapter !== null) {
      setNavState('versiculos');
    } else if (selectedBook !== null) {
      setNavState('capitulos');
    } else {
      setNavState('livros');
    }
    // Sempre voltamos para 'livros' como estado base
  }, [selectedBook, selectedChapter]);

  // Memoizações básicas para filtrar livros
  const allBooks = useMemo(() => [...livrosVelhoTestamento, ...livrosNovoTestamento], [livrosVelhoTestamento, livrosNovoTestamento]);
  const filteredBooks = useMemo(() => allBooks.filter(book => book.toLowerCase().includes(searchTerm.toLowerCase())), [allBooks, searchTerm]);
  const filteredVT = useMemo(() => filteredBooks.filter(book => livrosVelhoTestamento.includes(book)), [filteredBooks, livrosVelhoTestamento]);
  const filteredNT = useMemo(() => filteredBooks.filter(book => livrosNovoTestamento.includes(book)), [filteredBooks, livrosNovoTestamento]);

  // Renderiza todos os livros com barra de pesquisa e separados por testamento
  const renderAllBooks = () => {
    // Determina qual testamento contém o livro
    const getTestament = (book: string) => {
      return livrosVelhoTestamento.includes(book) ? 'velho' : 'novo';
    };
    
    // Renderiza um card de livro
    const renderBookCard = (book: string) => (
      <motion.div
        key={book}
        whileHover={{ scale: 1.06, boxShadow: "0 4px 24px rgba(80,80,120,0.12)" }}
        whileTap={{ scale: 0.97 }}
        transition={{ type: 'spring', stiffness: 300 }}
      >
        <Card
          className={`bible-card overflow-hidden transition-all cursor-pointer ${selectedBook === book ? 'border-2 border-primary' : 'border border-border'}`}
          onClick={() => {
            // Define o testamento correto ao navegar para os capítulos
            setActiveTestament(getTestament(book));
            navigateToChapters(book);
          }}
        >
          <CardContent className="p-0">
            <button className="w-full h-full p-4 text-center font-medium text-sm focus:outline-none always-visible">
              {book}
            </button>
          </CardContent>
        </Card>
      </motion.div>
    );

    return (
      <div>
        <div className="mb-6 flex justify-center">
          <div className="relative w-full max-w-md">
            <input
              type="text"
              className="w-full px-4 py-2 pl-10 rounded-lg border border-border focus:ring-2 focus:ring-primary outline-none text-sm shadow-sm"
              placeholder="Pesquisar livro da Bíblia..."
              value={searchTerm}
              onChange={e => setSearchTerm(e.target.value)}
              aria-label="Pesquisar livro"
            />
            <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
          </div>
        </div>
        
        {filteredBooks.length === 0 ? (
          <div className="text-center text-muted-foreground py-8">Nenhum livro encontrado.</div>
        ) : (
          <div className="space-y-8">
            {/* Velho Testamento */}
            {filteredVT.length > 0 && (
              <div>
                <h3 className="text-lg font-semibold mb-4 text-primary">Velho Testamento</h3>
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                  {filteredVT.map(renderBookCard)}
                </div>
              </div>
            )}
            
            {/* Novo Testamento */}
            {filteredNT.length > 0 && (
              <div>
                <h3 className="text-lg font-semibold mb-4 text-primary">Novo Testamento</h3>
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                  {filteredNT.map(renderBookCard)}
                </div>
              </div>
            )}
          </div>
        )}
      </div>
    );
  };

  // Componente de loading melhorado
  const LoadingIndicator = () => {
    const getLoadingMessage = () => {
      switch (navState) {
        case 'livros': return 'Carregando livros da Bíblia...';
        case 'capitulos': return `Carregando capítulos de ${selectedBook}...`;
        case 'versiculos': return `Carregando versículos do capítulo ${selectedChapter}...`;
        default: return 'Carregando...';
      }
    };

    return (
      <div className="flex justify-center items-center h-32 sm:h-40">
        <div className="flex flex-col items-center space-y-3">
          <div className="relative">
            <div className="animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-4 border-primary/20 border-t-primary"></div>
            <div className="absolute inset-0 rounded-full border-4 border-transparent border-t-primary/40 animate-pulse"></div>
          </div>
          <div className="text-center">
            <p className="text-sm sm:text-base font-medium text-foreground">{getLoadingMessage()}</p>
            <p className="text-xs text-muted-foreground mt-1">Aguarde um momento...</p>
          </div>
        </div>
      </div>
    );
  };

  // Componente de erro melhorado
  const ErrorMessage = () => (
    <div className="p-6 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
      <div className="flex items-start gap-3">
        <div className="flex-shrink-0">
          <svg className="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <div className="flex-1">
          <h3 className="text-sm font-medium text-red-800 dark:text-red-200 mb-1">
            Erro ao carregar conteúdo
          </h3>
          <p className="text-sm text-red-700 dark:text-red-300 mb-3">
            {error}
          </p>
          <button
            onClick={() => {
              setError(null);
              setLoading(true);
              // Recarregar a página para tentar novamente
              window.location.reload();
            }}
            className="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-700 dark:text-red-200 bg-red-100 dark:bg-red-800/30 hover:bg-red-200 dark:hover:bg-red-800/50 rounded-md transition-colors"
          >
            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Tentar novamente
          </button>
        </div>
      </div>
    </div>
  );

  // Removidos componentes não utilizados (NavigationHeader, TestamentosView)
  
  // Renderiza a view com base no estado atual
  const renderCurrentView = () => {
    if (error) {
      return <ErrorMessage />;
    }

    if (loading && navState !== 'testamentos') {
      return <LoadingIndicator />;
    }

    switch (navState) {
      case 'testamentos':
        // Redirecionamos para livros, mas mantemos esse caso para compatibilidade
        return <div className="always-visible">{renderAllBooks()}</div>;
      
      case 'livros':
        // Agora mostra todos os livros organizados por testamento
        return <div className="always-visible">{renderAllBooks()}</div>;
      
      case 'capitulos':
        return (
          <div className="always-visible space-y-6">
            {/* Botão para explicar contexto do livro */}
            <div className="flex justify-center">
              <motion.button
                className="px-6 py-3 rounded-lg font-semibold shadow-md flex items-center justify-center gap-2 border border-secondary bg-secondary text-secondary-foreground hover:bg-secondary/90 focus:outline-none focus:ring-2 focus:ring-secondary/50 transition-all"
                whileHover={{ scale: 1.03, boxShadow: "0 4px 20px rgba(0,0,0,0.1)" }}
                whileTap={{ scale: 0.97 }}
                transition={{ type: 'spring', stiffness: 400 }}
                disabled={navigating || !selectedBook}
                onClick={() => {
                  if (!selectedBook) return;
                  setNavigating(true);
                  
                  // Navegar para a página de contexto
                  const bookSlug = slugService.livroParaSlug(selectedBook);
                  const expTestament = toExplanationTestament(activeTestament);
                  
                  router.visit(`/contexto/${expTestament}/${bookSlug}`, {
                    onFinish: () => setNavigating(false),
                    onError: () => setNavigating(false)
                  });
                }}
              >
                {navigating ? (
                  <>
                    <Loader2 className="w-5 h-5 animate-spin" />
                    <span>Carregando...</span>
                  </>
                ) : (
                  <>
                    <BookOpen className="w-5 h-5" />
                    <span>Explicar contexto do livro</span>
                  </>
                )}
              </motion.button>
            </div>

            {/* Grid de capítulos */}
            <div className="grid grid-cols-4 xs:grid-cols-5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 xl:grid-cols-12 gap-2 sm:gap-3">
              {chapters.map((chapter) => {
                const isSelected = selectedChapter === chapter;
                return (
                  <motion.button
                    key={chapter}
                    className={`relative p-3 sm:p-4 rounded-lg border text-sm sm:text-base transition-all cursor-pointer font-semibold text-center focus:outline-none focus:ring-2 focus:ring-primary/50 ${isSelected ? 'bg-primary text-primary-foreground border-primary shadow-md' : 'bg-card hover:bg-accent border-border text-foreground hover:border-primary/30'}`}
                    whileHover={{ scale: 1.05, boxShadow: "0 4px 12px rgba(0,0,0,0.1)" }}
                    whileTap={{ scale: 0.98 }}
                    transition={{ type: 'spring', stiffness: 400, damping: 25 }}
                    onClick={() => navigateToVerses(chapter)}
                  >
                    {chapter}
                    {isSelected && (
                      <span className="absolute -top-1 -right-1 bg-primary-foreground text-primary w-5 h-5 flex items-center justify-center rounded-full text-xs border-2 border-primary shadow-sm">✓</span>
                    )}
                  </motion.button>
                );
              })}
            </div>
          </div>
        );
      
      case 'contexto':
        return (
          <div className="always-visible space-y-6">
            {contextLoading ? (
              <div className="flex justify-center items-center py-12">
                <div className="flex flex-col items-center space-y-4">
                  <div className="relative">
                    <div className="animate-spin rounded-full h-12 w-12 border-4 border-primary/20 border-t-primary"></div>
                    <div className="absolute inset-0 rounded-full border-4 border-transparent border-t-primary/40 animate-pulse"></div>
                  </div>
                  <p className="text-sm text-muted-foreground animate-pulse">Gerando contexto do livro...</p>
                </div>
              </div>
            ) : bookContext ? (
              <div className="space-y-6">
                {bookContext.error ? (
                  <div className="p-6 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 dark:border-amber-400 rounded-r-lg">
                    <div className="flex items-start gap-3">
                      <div className="flex-1">
                        <h3 className="font-semibold text-amber-800 dark:text-amber-200">Erro ao carregar contexto</h3>
                        <p className="text-amber-700 dark:text-amber-300 mt-1">{bookContext.message}</p>
                        <div className="mt-4">
                          <button
                            onClick={() => {
                              setContextLoading(true);
                              fetchBookContext();
                            }}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors text-sm font-medium"
                          >
                            Tentar novamente
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                ) : (
                  <BookContextRenderer context={bookContext.context} bookName={selectedBook} />
                )}
              </div>
            ) : null}
          </div>
        );
      
      case 'versiculos':
        return (
          <div className="always-visible space-y-6">
            {/* Botões de ação */}
            <div className="flex flex-col sm:flex-row gap-3 justify-center items-center">
              <motion.button
                className="w-full sm:w-auto px-6 py-3 rounded-lg font-semibold shadow-md flex items-center justify-center gap-2 border border-primary bg-primary text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all dark:bg-primary dark:text-primary-foreground dark:border-primary"
                whileHover={{ scale: 1.03, boxShadow: "0 4px 20px rgba(0,0,0,0.1)" }}
                whileTap={{ scale: 0.97 }}
                transition={{ type: 'spring', stiffness: 400 }}
                disabled={navigating || !selectedBook || !selectedChapter}
                onClick={() => {
                  const bookSlug = selectedBook ? slugService.livroParaSlug(selectedBook) : '';
                  const expTestament = toExplanationTestament(activeTestament);
                  // Persistir estado antes de navegar para explicação
                  persistNavState();
                  setNavigating(true);
                  // Subir para o topo para evitar ficar no rodapé
                  scrollToTop();
                  router.visit(`/explicacao/${expTestament}/${bookSlug}/${selectedChapter}`, {
                    preserveScroll: false,
                    onFinish: () => setNavigating(false),
                  });
                }}
              >
                {navigating ? (
                  <>
                    <Loader2 className="w-5 h-5 animate-spin" />
                    <span>Indo para explicação...</span>
                  </>
                ) : (
                  <>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-5 h-5">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Explicar Capítulo
                  </>
                )}
              </motion.button>
              
              {/* <motion.button
                className={`w-full sm:w-auto px-6 py-3 rounded-lg font-semibold shadow-md flex items-center justify-center gap-2 border transition-all
                  ${selectionMode
                    ? 'bg-amber-500 text-white border-amber-500 hover:bg-amber-600'
                    : 'bg-black text-primary border-primary hover:bg-accent/40'}
                `}
                style={{ color: selectionMode ? '#fff' : 'var(--primary)' }}
                whileHover={{ scale: 1.03, boxShadow: "0 4px 20px rgba(0,0,0,0.1)" }}
                whileTap={{ scale: 0.97 }}
                transition={{ type: 'spring', stiffness: 400 }}
                onClick={() => setSelectionMode(!selectionMode)}
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-5 h-5">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                {selectionMode ? 'Finalizar Seleção' : 'Selecionar Versículos'}
              </motion.button> */}
            </div>

            {/* Informações de seleção */}
            {/* {selectionMode && (
              <div className="bg-muted/30 p-2 sm:p-4 rounded-lg border border-border text-center">
                <p className="text-xs sm:text-sm">Selecione os versículos que deseja explicar {selectedVerses.length > 0 && <span className="font-bold">({selectedVerses.length} selecionados)</span>}</p>
                {selectedVerses.length > 0 && (
                  <motion.button
                    className="mt-3 sm:mt-4 px-3 sm:px-4 py-1.5 sm:py-2 bg-primary text-primary-foreground rounded-md text-xs sm:text-sm font-medium shadow-sm inline-flex items-center gap-1 hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-primary/50"
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    transition={{ type: 'spring', stiffness: 400 }}
                    onClick={() => {
                      const versiculosParam = selectedVerses.join(',');
                      
                      const bookSlug = selectedBook ? slugService.livroParaSlug(selectedBook) : '';
                      const expTestament = toExplanationTestament(activeTestament);
                      window.location.href = `/explicacao/${expTestament}/${bookSlug}/${selectedChapter}?verses=${versiculosParam}`;
                    }}
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                    Explicar Seleção
                  </motion.button>
                )}
              </div>
            )} */}

            {/* Grid de versículos - melhorado para mobile */}
            <div className="grid grid-cols-5 xs:grid-cols-6 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 xl:grid-cols-15 gap-2 sm:gap-3">
              {verses.map((verse) => {
                const isSelected = selectedVerses.includes(verse);
                return (
                  <motion.button
                    key={verse}
                    className={`relative p-3 sm:p-4 rounded-lg border text-sm sm:text-base font-semibold transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-primary/50 ${isSelected ? 'bg-primary text-primary-foreground border-primary shadow-md' : 'bg-card hover:bg-accent border-border hover:border-primary/30'}`}
                    disabled={navigating}
                    whileHover={{ scale: 1.05, boxShadow: "0 4px 12px rgba(0,0,0,0.1)" }}
                    whileTap={{ scale: 0.98 }}
                    transition={{ type: 'spring', stiffness: 400, damping: 25 }}
                    onClick={() => {
                      if (selectionMode) {
                        // Modo de seleção: toggle de versículos selecionados
                        setSelectedVerses(prev =>
                          isSelected
                            ? prev.filter(v => v !== verse)
                            : [...prev, verse].sort((a, b) => a - b)
                        );
                      } else {
                        // Modo normal: navegar para explicação de versículo único (SPA)
                        const bookSlug = selectedBook ? slugService.livroParaSlug(selectedBook) : '';
                        const expTestament = toExplanationTestament(activeTestament);
                        // Persistir estado com o versículo selecionado
                        persistNavState({ selectedVerses: [verse] });
                        const verseSlug = `${verse}-explicacao-biblica`;
                        setNavigating(true);
                        // Subir para o topo antes de navegar
                        scrollToTop();
                        router.visit(`/explicacao/${expTestament}/${bookSlug}/${selectedChapter}/${verseSlug}`, {
                          preserveScroll: false,
                          onFinish: () => setNavigating(false),
                        });
                      }
                    }}
                  >
                    {navigating ? <Loader2 className="w-4 h-4 animate-spin" /> : verse}
                  </motion.button>
                );
              })}
            </div>
          </div>
        );
        
      default:
        return <div className="always-visible">{renderAllBooks()}</div>;
    }
  };

  // Removido efeito que forçava navState('livros') no mount para evitar sobrescrever restauração

  return (
    <div className="flex flex-col space-y-4">
      {/* Renderiza o cabeçalho de navegação */}
      <div className="flex items-center justify-between bg-card shadow-sm rounded-md p-3 mb-4">
        {navState !== 'livros' ? (
          <button 
            onClick={navigateBack}
            className="p-2 rounded-full hover:bg-muted transition-all"
            aria-label="Voltar"
          >
            <ChevronLeft size={20} />
          </button>
        ) : (
          <div className="w-10">{/* Espaçador */}</div>
        )}
        
        <div className="flex items-center font-medium">
          {(() => {
            switch (navState) {
              case 'testamentos': return <Book className="mr-2" size={18} />;
              case 'livros': return <Book className="mr-2" size={18} />;
              case 'capitulos': return <Bookmark className="mr-2" size={18} />;
              case 'versiculos': return <Bookmark className="mr-2" size={18} />;
              default: return <Book className="mr-2" size={18} />;
            }
          })()}
          <span>
            {(() => {
              switch (navState) {
                case 'testamentos': return 'Bíblia Sagrada';
                case 'livros': return 'Bíblia Sagrada';
                case 'capitulos': return selectedBook || '';
                case 'versiculos': return selectedBook && selectedChapter ? `${selectedBook} ${selectedChapter}` : '';
                default: return 'Bíblia';
              }
            })()}
          </span>
        </div>
        
        <button 
          onClick={() => window.location.href = '/biblia'}
          className="flex items-center gap-2 px-3 py-2 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-all shadow-sm font-medium text-sm"
          aria-label="Início"
        >
          <Home size={18} />
          <span className="hidden sm:inline">Início</span>
        </button>
      </div>
      {renderCurrentView()}
    </div>
  );
}