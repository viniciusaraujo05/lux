import React from 'react';
import { Head } from '@inertiajs/react';
import BibleBooksGrid from '@/components/BibleBooksGrid';
import { motion } from 'framer-motion';
import ThemeToggleButton from '@/components/ThemeToggleButton';
import Footer from '@/components/footer';
import DonateButton from '@/components/donate-button';
import { Gift, Heart } from 'lucide-react';

// Frase de orientação
const CTA = () => (
  <motion.p
    className="text-base md:text-lg text-primary font-medium text-center mb-6"
    initial={{ opacity: 0, y: 10 }}
    animate={{ opacity: 1, y: 0 }}
    transition={{ delay: 0.7, duration: 0.6 }}
  >
    Escolha um livro para começar os seus estudos
  </motion.p>
);

interface WelcomeProps {
  testamento?: string;
  livro?: string;
  capitulo?: string;
}

export default function Welcome(props: WelcomeProps) {
  return (
    <>
      <Head title="Verbum - Bíblia Explicada">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
      </Head>
      <ThemeToggleButton />
      <div className="min-h-screen bg-background text-foreground flex flex-col justify-between">
        <div className="mx-auto max-w-7xl px-4 py-8 md:px-6 md:py-12 lg:px-8 w-full">
          <header className="mb-8 md:mb-12">
            <motion.h1
              className="text-3xl md:text-4xl font-bold tracking-tight mb-2 text-center"
              initial={{ opacity: 0, y: -30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
            >
              Verbum - Bíblia Explicada
            </motion.h1>
            <motion.p
              className="text-lg text-muted-foreground text-center"
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3, duration: 0.6 }}
            >
            </motion.p>
            
            <motion.div
              className="flex justify-center mt-4"
              initial={{ opacity: 0 }}
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
            initial={{ opacity: 0, scale: 0.97 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 1, duration: 0.7 }}
          >
            <BibleBooksGrid 
              initialTestament={props.testamento as 'velho' | 'novo' | undefined}
              initialBook={props.livro}
              initialChapter={props.capitulo ? parseInt(props.capitulo) : undefined}
            />
          </motion.main>
        </div>
        <Footer />
      </div>
    </>
  );
}

