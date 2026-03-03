import React, { Suspense, useEffect, useMemo, useState } from 'react';
import { SITE_NAME, SITE_TAGLINE } from '@/lib/siteMetadata';
import { Head } from '@inertiajs/react';
import { motion, useReducedMotion } from 'framer-motion';
import ThemeToggleButton from '@/components/ThemeToggleButton';
import Footer from '@/components/footer';
import DonateButton from '@/components/donate-button';
import { Heart } from 'lucide-react';
import AdSense from '@/components/AdSense';

// Lazy-load the heavy grid to reduce initial bundle size
const BibleBooksGrid = React.lazy(() => import('@/components/BibleBooksGrid'));

// Enhanced skeleton while the grid loads
const GridSkeleton: React.FC = () => (
  <div className="space-y-8">
    {/* Search skeleton */}
    <div className="flex justify-center">
      <div className="w-full max-w-md h-10 rounded-lg bg-muted/60 animate-pulse" />
    </div>
    
    {/* Velho Testamento skeleton */}
    <div>
      <div className="h-6 w-40 bg-muted/60 animate-pulse rounded mb-4" />
      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
        {Array.from({ length: 39 }).map((_, i) => (
          <div key={`vt-${i}`} className="h-12 rounded-md bg-muted/60 animate-pulse" />
        ))}
      </div>
    </div>
    
    {/* Novo Testamento skeleton */}
    <div>
      <div className="h-6 w-40 bg-muted/60 animate-pulse rounded mb-4" />
      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
        {Array.from({ length: 27 }).map((_, i) => (
          <div key={`nt-${i}`} className="h-12 rounded-md bg-muted/60 animate-pulse" />
        ))}
      </div>
    </div>
  </div>
);

// Frase de orientação
const CTA: React.FC = () => {
  const reduce = useReducedMotion();
  return (
    <motion.p
      className="text-base md:text-lg text-primary font-medium text-center mb-6"
      initial={reduce ? false : { opacity: 0, y: 10 }}
      animate={reduce ? { opacity: 1 } : { opacity: 1, y: 0 }}
      transition={{ delay: 0.7, duration: 0.6 }}
    >
      Escolha um livro para começar os seus estudos
    </motion.p>
  );
};

const DAILY_VERSES = [
  'Lâmpada para os meus pés é a tua palavra. — Salmos 119:105',
  'Confia no Senhor de todo o teu coração. — Provérbios 3:5',
  'Vinde a mim, todos os que estais cansados. — Mateus 11:28',
  'A alegria do Senhor é a nossa força. — Neemias 8:10',
];

interface WelcomeProps {
  testamento?: string;
  livro?: string;
  capitulo?: string;
}

export default function Welcome(props: WelcomeProps) {
  const reduce = useReducedMotion();
  const [verseIndex, setVerseIndex] = useState(() => Math.floor(Date.now() / (1000 * 60 * 60 * 24)) % DAILY_VERSES.length);
  const verseOfDay = useMemo(() => DAILY_VERSES[verseIndex % DAILY_VERSES.length], [verseIndex]);
  const normalizedInitialTestament: 'velho' | 'novo' | undefined =
    props.testamento === 'antigo' ? 'velho' : props.testamento === 'novo' || props.testamento === 'velho' ? (props.testamento as 'velho' | 'novo') : undefined;

  useEffect(() => {
    const timer = setInterval(() => {
      setVerseIndex((prev) => (prev + 1) % DAILY_VERSES.length);
    }, 9000);
    return () => clearInterval(timer);
  }, []);

  return (
    <>
      <Head title={`${SITE_NAME} - ${SITE_TAGLINE}`}>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link rel="preconnect" href="https://fonts.bunny.net" crossOrigin="anonymous" />
        <meta name="description" content="Estudos bíblicos claros e profundos. Explore livros, capítulos e versículos com explicações organizadas e práticas." />
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|playfair-display:500,700" rel="stylesheet" />
      </Head>
      <ThemeToggleButton />
      <div className="min-h-screen bg-[radial-gradient(circle_at_top,_hsl(var(--primary)/0.15),_transparent_45%),radial-gradient(circle_at_85%_15%,_hsl(var(--secondary)/0.12),_transparent_35%),hsl(var(--background))] text-foreground flex flex-col justify-between">
        <div className="mx-auto max-w-7xl px-4 py-8 md:px-6 md:py-12 lg:px-8 w-full">
          <header className="mb-8 md:mb-12 rounded-2xl border border-border/70 bg-card/80 backdrop-blur-sm shadow-sm p-5 sm:p-6">
            <motion.h1
              className="text-3xl md:text-4xl font-bold tracking-tight mb-2 text-center"
              style={{ fontFamily: '"Playfair Display", serif' }}
              initial={reduce ? false : { opacity: 0, y: -30 }}
              animate={reduce ? { opacity: 1 } : { opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
            >
              {SITE_NAME} - {SITE_TAGLINE}
            </motion.h1>
            <motion.p
              className="text-base sm:text-lg text-muted-foreground text-center max-w-3xl mx-auto"
              initial={reduce ? false : { opacity: 0, y: -10 }}
              animate={reduce ? { opacity: 1 } : { opacity: 1, y: 0 }}
              transition={{ delay: 0.3, duration: 0.6 }}
            >
              Aprofunde-se na Palavra com uma experiência clara, bonita e focada em estudo verso por verso.
            </motion.p>

            <motion.div
              className="mt-4 mx-auto max-w-2xl rounded-xl border border-blue-300/40 dark:border-blue-500/30 bg-blue-50/70 dark:bg-blue-950/30 px-4 py-3 text-center"
              initial={reduce ? false : { opacity: 0, y: 8 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.45, duration: 0.5 }}
            >
              <p className="text-sm sm:text-[15px] text-blue-900 dark:text-blue-200 font-medium">{verseOfDay}</p>
            </motion.div>
            
            <motion.div
              className="flex justify-center mt-4"
              initial={reduce ? false : { opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.9, duration: 0.6 }}
            >
              <div className="flex flex-col sm:flex-row items-center gap-3 sm:gap-2 bg-blue-100 dark:bg-blue-900/30 p-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-full border border-blue-300 dark:border-blue-500/40 shadow-sm">
                <div className="flex items-center">
                  <Heart className="h-4 w-4 text-blue-600 dark:text-blue-400 mr-2" />
                  <span className="text-sm text-blue-900 dark:text-blue-200">Oferte e nos ajude a continuar</span>
                </div>
                <DonateButton size="sm" className="w-full sm:w-auto" />
              </div>
            </motion.div>
          </header>
          <div className="mt-6">
            <CTA />
          </div>
          
          <motion.div
            initial={reduce ? false : { opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.8 }}
          >
            <AdSense className="mb-8" />
          </motion.div>

          <motion.main
            className="rounded-lg border bg-card text-card-foreground shadow-sm p-4 md:p-6 lg:p-8"
            initial={reduce ? false : { opacity: 0, scale: 0.97 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 1, duration: 0.7 }}
          >
            <Suspense fallback={<GridSkeleton />}>
              <BibleBooksGrid 
                initialTestament={normalizedInitialTestament}
                initialBook={props.livro}
                initialChapter={props.capitulo ? parseInt(props.capitulo) : undefined}
              />
            </Suspense>
          </motion.main>
        </div>
        <Footer />
      </div>
    </>
  );
}
