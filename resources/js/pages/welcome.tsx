import { Head } from '@inertiajs/react';
import BibleBooksGrid from '@/components/BibleBooksGrid';
import { motion } from 'framer-motion';

// Frase de orientação
const CTA = () => (
  <motion.p
    className="text-base md:text-lg text-primary font-medium text-center mb-6"
    initial={{ opacity: 0, y: 10 }}
    animate={{ opacity: 1, y: 0 }}
    transition={{ delay: 0.7, duration: 0.6 }}
  >
    Escolha um livro para começar sua leitura
  </motion.p>
);

export default function Welcome() {
  return (
    <>
      <Head title="Verbum - Bíblia Sagrada">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
      </Head>
      <div className="min-h-screen bg-background text-foreground flex flex-col justify-center">
        <div className="mx-auto max-w-7xl px-4 py-8 md:px-6 md:py-12 lg:px-8 w-full">
          <header className="mb-8 md:mb-12">
            <motion.h1
              className="text-3xl md:text-4xl font-bold tracking-tight mb-2 text-center"
              initial={{ opacity: 0, y: -30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
            >
              Verbum
            </motion.h1>
            <motion.p
              className="text-lg text-muted-foreground text-center"
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3, duration: 0.6 }}
            >
              Bíblia Sagrada
            </motion.p>
          </header>
          <CTA />
          <motion.main
            className="rounded-lg border bg-card text-card-foreground shadow-sm p-4 md:p-6 lg:p-8"
            initial={{ opacity: 0, scale: 0.97 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 1, duration: 0.7 }}
          >
            <BibleBooksGrid />
          </motion.main>
        </div>
      </div>
    </>
  );
}

