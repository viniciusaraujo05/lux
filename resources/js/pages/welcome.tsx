import React, { Suspense } from 'react';
import { SITE_NAME, SITE_TAGLINE } from '@/lib/siteMetadata';
import { Head } from '@inertiajs/react';
import { motion, useReducedMotion } from 'framer-motion';
import ThemeToggleButton from '@/components/ThemeToggleButton';
import Footer from '@/components/footer';
import DonateButton from '@/components/donate-button';
import { Heart } from 'lucide-react';

// Lazy-load the heavy grid to reduce initial bundle size
const BibleBooksGrid = React.lazy(() => import('@/components/BibleBooksGrid'));

// Lightweight skeleton while the grid loads
const GridSkeleton: React.FC = () => (
  <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
    {Array.from({ length: 12 }).map((_, i) => (
      <div key={i} className="h-10 rounded-md bg-muted/60 animate-pulse" />
    ))}
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

interface WelcomeProps {
  testamento?: string;
  livro?: string;
  capitulo?: string;
}

export default function Welcome(props: WelcomeProps) {
  const reduce = useReducedMotion();
  return (
    <>
      <Head title={`${SITE_NAME} - ${SITE_TAGLINE}`}>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link rel="preconnect" href="https://fonts.bunny.net" crossOrigin="anonymous" />
        <meta name="description" content="Estudos bíblicos claros e profundos. Explore livros, capítulos e versículos com explicações organizadas e práticas." />
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
      </Head>
      <ThemeToggleButton />
      <div className="min-h-screen bg-background text-foreground flex flex-col justify-between">
        <div className="mx-auto max-w-7xl px-4 py-8 md:px-6 md:py-12 lg:px-8 w-full">
          <header className="mb-8 md:mb-12">
            <motion.h1
              className="text-3xl md:text-4xl font-bold tracking-tight mb-2 text-center"
              initial={reduce ? false : { opacity: 0, y: -30 }}
              animate={reduce ? { opacity: 1 } : { opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
            >
              {SITE_NAME} - {SITE_TAGLINE}
            </motion.h1>
            <motion.p
              className="text-lg text-muted-foreground text-center"
              initial={reduce ? false : { opacity: 0, y: -10 }}
              animate={reduce ? { opacity: 1 } : { opacity: 1, y: 0 }}
              transition={{ delay: 0.3, duration: 0.6 }}
            >
            </motion.p>
            
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
          <CTA />
          <motion.main
            className="rounded-lg border bg-card text-card-foreground shadow-sm p-4 md:p-6 lg:p-8"
            initial={reduce ? false : { opacity: 0, scale: 0.97 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 1, duration: 0.7 }}
          >
            <Suspense fallback={<GridSkeleton />}>
              <BibleBooksGrid 
                initialTestament={props.testamento as 'velho' | 'novo' | undefined}
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

