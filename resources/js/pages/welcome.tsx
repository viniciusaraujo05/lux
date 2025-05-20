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
              <div className="inline-flex items-center bg-purple-100 dark:bg-purple-900/30 px-4 py-2 rounded-full border border-purple-300 dark:border-purple-500/40">
                <Heart className="h-4 w-4 text-purple-600 dark:text-purple-400 mr-2" />
                <span className="text-sm text-purple-900 dark:text-purple-200 mr-3">Oferte e ajude e nos ajude a continuar</span>
                <DonateButton size="sm" />
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

