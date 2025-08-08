import '../../../../../css/app.css';
import React, { useState, useEffect } from 'react';
import { useParams, useSearchParams } from 'react-router-dom';
import { ChevronLeft, Book, Bookmark, Copy, Share } from "lucide-react";
import { motion, AnimatePresence } from 'framer-motion';
import BibleService from "@/services/BibleService";
import SlugService from "@/services/SlugService";
import ThemeToggleButton from '@/components/ThemeToggleButton';

// Aplica o tema imediatamente ao carregar a página (evita flash/reset)
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

export default function BibleExplanation() {
  return (
    <>
      <ThemeToggleButton />
      <BibleExplanationContent />
    </>
  );
}

// Componente extraído para manter a lógica original
function BibleExplanationContent() {
  const { testamento: testament, livro: book, capitulo: chapter } = useParams<{
    testamento: string;
    livro: string;
    capitulo: string;
  }>();
  const [searchParams] = useSearchParams();

  // Normalize book input: accept either slug or encoded name, always derive slug and display name
  const slugService = SlugService.getInstance();
  const rawBook = book || '';
  const decodedBook = decodeURIComponent(rawBook);
  const bookSlug = slugService.livroParaSlug(decodedBook);
  const bookName = slugService.slugParaLivro(bookSlug);

  // Ensure URL uses canonical slug (avoid encoded names)
  useEffect(() => {
    try {
      if (!book || !bookSlug) return;
      if (book !== bookSlug) {
        const search = window.location.search || '';
        const newUrl = `/explicacao/${testament}/${bookSlug}/${chapter}${search}`;
        window.history.replaceState({}, '', newUrl);
      }
    } catch {}
  }, [book, bookSlug, testament, chapter]);

  // Extract verses from query string if they exist
  const versesParam = searchParams.get('verses');
  const selectedVerses = versesParam ? versesParam.split(',').map(Number) : [];
  
  const [chapterExplanation, setChapterExplanation] = useState<string>('');
  const [verseExplanations, setVerseExplanations] = useState<Record<number, string>>({});
  const [loading, setLoading] = useState<boolean>(true);
  const [copied, setCopied] = useState<boolean>(false);
  const [originalVerses, setOriginalVerses] = useState<Record<number, string>>({});
  const [explanationSource, setExplanationSource] = useState<'cache' | 'api' | null>(null);

  useEffect(() => {
    // Function to load original Bible verse texts
    const loadOriginalVerses = async () => {
      try {
        if (!testament || !bookSlug || !chapter) return;
        
        const bibleService = BibleService.getInstance();
        
        // Here you would implement a method in BibleService to get the verse texts
        // This is a placeholder - replace with the real implementation
        const verseTexts: Record<number, string> = {};
        
        // If we have specific verses selected
        if (selectedVerses.length > 0) {
          for (const verseNumber of selectedVerses) {
            // Simulation - replace with real API or service call
            verseTexts[verseNumber] = `Original text for verse ${verseNumber} of ${bookName} ${chapter}.`;
          }
        } else {
          // Simulate loading all verses from the chapter
          const verseCount = 30; // Placeholder - get real number from BibleService
          for (let i = 1; i <= verseCount; i++) {
            verseTexts[i] = `Original text for verse ${i} of ${bookName} ${chapter}.`;
          }
        }
        
        setOriginalVerses(verseTexts);
      } catch (error) {
        console.error('Error loading original verses:', error);
      }
    };

    // Function to generate explanations
    const generateExplanations = async () => {
      try {
        setLoading(true);
        
        // Parameters for the API call
        const params = new URLSearchParams();
        if (selectedVerses.length > 0) {
          params.append('verses', selectedVerses.join(','));
        }
        
        // API call to backend with correct parameter names for Laravel
        const response = await fetch(
          `/api/explanation/${testament}/${bookSlug}/${chapter}?${params.toString()}`
        );
        
        if (!response.ok) {
          throw new Error('Failed to fetch explanation');
        }
        
        const data = await response.json();
        
        // Store the origin of the explanation (cache or API)
        setExplanationSource(data.origin);
        console.log('Explanation origin:', data.origin);
        
        if (selectedVerses.length > 0) {
          // Explanation for specific verses
          const explanationsObj: Record<number, string> = {};
          
          // Each verse gets the same explanation for now
          // In a more advanced implementation, we might have individual explanations per verse
          selectedVerses.forEach(verse => {
            explanationsObj[verse] = data.explanation;
          });
          
          setVerseExplanations(explanationsObj);
          setChapterExplanation('');
        } else {
          // Explanation for the entire chapter
          setChapterExplanation(data.explanation);
          setVerseExplanations({});
        }
        
        setLoading(false);
      } catch (error) {
        console.error('Error generating explanations:', error);
        setLoading(false);
      }
    };

    loadOriginalVerses();
    generateExplanations();
  }, [testament, bookSlug, chapter, selectedVerses]);

  const copyContent = () => {
    let textToCopy = "";
    
    if (chapterExplanation) {
      textToCopy = `Explanation of ${bookName} ${chapter}\n\n${chapterExplanation}`;
    } else {
      for (const verse of selectedVerses) {
        textToCopy += `${bookName} ${chapter}:${verse}\n`;
        textToCopy += `${originalVerses[verse] || ''}\n\n`;
        textToCopy += `Explanation:\n${verseExplanations[verse] || ''}\n\n`;
      }
    }
    
    navigator.clipboard.writeText(textToCopy);
    setCopied(true);
    
    setTimeout(() => {
      setCopied(false);
    }, 2000);
  };

  const shareContent = async () => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: `Explanation of ${bookName} ${chapter}`,
          text: selectedVerses.length > 0 
            ? `Explanation of verses ${selectedVerses.join(', ')} from ${bookName} ${chapter}`
            : `Complete explanation of ${bookName} ${chapter}`,
          url: window.location.href,
        });
      } catch (error) {
        console.log('Error sharing', error);
      }
    } else {
      // Fallback - copy link to clipboard
      navigator.clipboard.writeText(window.location.href);
      alert('Link copied to clipboard!');
    }
  };

  return (
    <div className="container max-w-4xl mx-auto px-4 py-8">
      {/* Header with navigation */}
      <div className="flex items-center mb-6">
        <motion.button 
          onClick={() => window.history.back()}
          className="mr-4 p-2 rounded-full hover:bg-muted transition-all"
          whileHover={{ scale: 1.1 }}
          whileTap={{ scale: 0.9 }}
        >
          <ChevronLeft size={24} />
        </motion.button>
        
        <h1 className="text-2xl font-bold flex items-center">
          <Book className="mr-2 text-primary" size={24} />
          {bookName} {chapter}
          {selectedVerses.length > 0 && (
            <span className="ml-2 text-sm bg-primary/10 text-primary px-2 py-1 rounded-full">
              Verse{selectedVerses.length > 1 ? 's' : ''} {selectedVerses.join(', ')}
            </span>
          )}
        </h1>
        
        <div className="ml-auto flex gap-2">
          <motion.button
            className="p-2 rounded-full hover:bg-muted transition-all relative"
            whileHover={{ scale: 1.1 }}
            whileTap={{ scale: 0.9 }}
            onClick={copyContent}
            aria-label="Copy content"
          >
            <Copy size={20} />
            {copied && (
              <motion.span 
                className="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-foreground text-background text-xs py-1 px-2 rounded shadow-md"
                initial={{ opacity: 0, y: 5 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0 }}
              >
                Copied!
              </motion.span>
            )}
          </motion.button>
          
          <motion.button
            className="p-2 rounded-full hover:bg-muted transition-all"
            whileHover={{ scale: 1.1 }}
            whileTap={{ scale: 0.9 }}
            onClick={shareContent}
            aria-label="Share"
          >
            <Share size={20} />
          </motion.button>
        </div>
      </div>
      
      {/* Main content */}
      <div className="space-y-8">
        {loading ? (
          <div className="flex justify-center py-12">
            <div className="flex flex-col items-center">
              <div className="w-12 h-12 border-4 border-primary/30 border-t-primary rounded-full animate-spin"></div>
              <p className="mt-4 text-muted-foreground">Generating explanation...</p>
            </div>
          </div>
        ) : (
          <>
            {chapterExplanation ? (
              <FullChapterExplanation 
                book={bookName || ''} 
                chapter={chapter || ''} 
                explanation={chapterExplanation}
                explanationSource={explanationSource}
              />
            ) : (
              <VersesExplanations 
                verses={selectedVerses} 
                explanations={verseExplanations}
                originalVerses={originalVerses}
                book={bookName || ''}
                chapter={chapter || ''}
                explanationSource={explanationSource}
              />
            )}
          </>
        )}
      </div>
    </div>
  );
}

// Component to show complete chapter explanation
const FullChapterExplanation = ({ 
  book, 
  chapter, 
  explanation,
  explanationSource
}: { 
  book: string; 
  chapter: string; 
  explanation: string;
  explanationSource: 'cache' | 'api' | null;
}) => {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5 }}
      className="bg-card rounded-xl shadow-sm border p-6"
    >
      <div className="flex items-center mb-4 justify-between">
        <div className="flex items-center">
          <Bookmark className="text-primary mr-3" size={24} />
          <h2 className="text-xl font-semibold">Complete Explanation</h2>
        </div>
        {explanationSource && (
          <span className={`text-xs px-2 py-1 rounded-full ${explanationSource === 'cache' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}`}>
            Source: {explanationSource === 'cache' ? 'Database' : 'AI Generated'}
          </span>
        )}
      </div>
      
      <div className="prose prose-sm md:prose-base max-w-none">
        {explanation.split('\n').map((paragraph, index) => (
          <p key={index} className={paragraph.trim() ? '' : 'h-4'}>
            {paragraph}
          </p>
        ))}
      </div>
    </motion.div>
  );
};

// Component to show explanations for specific verses
const VersesExplanations = ({ 
  verses, 
  explanations, 
  originalVerses,
  book,
  chapter,
  explanationSource
}: { 
  verses: number[]; 
  explanations: Record<number, string>;
  originalVerses: Record<number, string>;
  book: string;
  chapter: string;
  explanationSource: 'cache' | 'api' | null;
}) => {
  return (
    <div className="space-y-6">
      {verses.map((verse) => (
        <motion.div
          key={verse}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: verse * 0.1 }}
          className="bg-card rounded-xl shadow-sm border overflow-hidden"
        >
          {/* Verse header */}
          <div className="bg-muted p-4 border-b">
            <div className="flex items-center justify-between">
              <div className="flex items-center">
                <span className="bg-primary text-primary-foreground w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3">
                  {verse}
                </span>
                <h3 className="font-semibold">{book} {chapter}:{verse}</h3>
              </div>
              {explanationSource && (
                <span className={`text-xs px-2 py-1 rounded-full ${explanationSource === 'cache' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}`}>
                  Source: {explanationSource === 'cache' ? 'Database' : 'AI Generated'}
                </span>
              )}
            </div>
          </div>
          
          {/* Original verse text */}
          <div className="p-4 border-b bg-primary/5">
            <p className="italic text-foreground/90 font-serif">
              "{originalVerses[verse] || 'Loading verse text...'}"
            </p>
          </div>
          
          {/* Verse explanation */}
          <div className="p-6">
            <div className="prose prose-sm md:prose-base max-w-none">
              {(explanations[verse] || '').split('\n').map((paragraph, index) => (
                <p key={index} className={paragraph.trim() ? '' : 'h-4'}>
                  {paragraph}
                </p>
              ))}
            </div>
          </div>
        </motion.div>
      ))}
    </div>
  );
};
