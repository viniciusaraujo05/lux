import AppLayout from '@/layouts/app-layout';
import Footer from '@/components/footer';
import { ArrowRight, BookOpen, CheckCircle2, Loader2, ScrollText, Sparkles } from 'lucide-react';
import { useState } from 'react';

interface Topic {
  slug: string;
  label: string;
  title: string;
  term: string;
  description: string;
}

interface ThemeVerse {
  referencia: string;
  testamento: 'antigo' | 'novo';
  livro_slug: string;
  capitulo: number;
  versos: string;
  texto: string;
  motivo: string;
}

interface ThemeStudy {
  titulo: string;
  subtitulo: string;
  introducao: string;
  significado_biblico: string;
  aplicacao_pratica: string;
  historia_biblica?: {
    titulo: string;
    texto: string;
    referencia: string;
  };
  versiculos: ThemeVerse[];
}

interface Props {
  topic: Topic;
  study: ThemeStudy;
  origin: string;
  topics: Topic[];
}

const explanationUrl = (verse: ThemeVerse) => `/explicacao/${verse.testamento}/${verse.livro_slug}/${verse.capitulo}/${verse.versos}-explicacao-biblica`;

export default function ThemeShow({ topic, study: initialStudy, origin, topics }: Props) {
  const [study, setStudy] = useState(initialStudy);
  const [currentOrigin, setCurrentOrigin] = useState(origin);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const generateStudy = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`/api/temas/${topic.slug}/gerar`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });
      if (!response.ok) throw new Error('Não foi possível gerar o estudo agora.');
      const data = await response.json();
      setStudy(data.study);
      setCurrentOrigin(data.origin);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao gerar estudo.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <AppLayout>
      <main className="min-h-screen bg-background px-3 py-4 text-foreground sm:px-6 sm:py-8 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <a href="/temas" className="mb-4 inline-flex rounded-full border border-border bg-card px-3 py-2 text-sm font-semibold text-muted-foreground transition hover:bg-muted hover:text-foreground">
            Todos os temas
          </a>

          <section className="grid overflow-hidden rounded-3xl border border-border bg-card shadow-sm lg:grid-cols-[1.15fr_0.85fr]">
            <div className="bg-black p-6 text-white dark:bg-white dark:text-black sm:p-10 lg:p-12">
              <p className="mb-4 inline-flex rounded-full border border-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white/70 dark:border-black/20 dark:text-black/60">
                Versículos sobre
              </p>
              <h1 className="max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">{study.titulo || topic.title}</h1>
              <p className="mt-5 max-w-3xl text-base leading-8 text-white/75 dark:text-black/70 sm:text-lg">{study.subtitulo || topic.description}</p>
              <div className="mt-7 flex flex-col gap-3 sm:flex-row">
                <a href="#versiculos" className="inline-flex items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-semibold text-black transition hover:bg-zinc-200 dark:bg-black dark:text-white dark:hover:bg-zinc-800">
                  Ver lista de versículos
                  <ArrowRight className="h-4 w-4" />
                </a>
                <button
                  onClick={generateStudy}
                  disabled={loading}
                  className="inline-flex items-center justify-center gap-2 rounded-full border border-white/25 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10 disabled:opacity-60 dark:border-black/25 dark:text-black dark:hover:bg-black/10"
                >
                  {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Sparkles className="h-4 w-4" />}
                  {currentOrigin === 'database' ? 'Atualizar estudo' : 'Gerar estudo completo'}
                </button>
              </div>
            </div>

            <div className="flex flex-col justify-between gap-4 p-5 sm:p-7 lg:p-8">
              <div className="rounded-2xl border border-border bg-background p-5">
                <div className="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-black text-white dark:bg-white dark:text-black">
                  <BookOpen className="h-5 w-5" />
                </div>
                <h2 className="text-xl font-semibold">O que a Bíblia ensina?</h2>
                <p className="mt-3 leading-8 text-muted-foreground">{study.introducao}</p>
              </div>
              <div className="rounded-2xl border border-border bg-background p-5">
                <h2 className="text-xl font-semibold">Significado bíblico</h2>
                <p className="mt-3 leading-8 text-muted-foreground">{study.significado_biblico}</p>
              </div>
            </div>
          </section>

          {error && <p className="mt-4 rounded-xl border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive">{error}</p>}

          {study.historia_biblica && (
            <section className="mt-6 rounded-3xl border border-border bg-card p-5 shadow-sm sm:p-7 lg:p-8">
              <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div className="max-w-3xl">
                  <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">História bíblica</p>
                  <h2 className="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">{study.historia_biblica.titulo}</h2>
                  <p className="mt-4 leading-8 text-muted-foreground">{study.historia_biblica.texto}</p>
                </div>
                <div className="rounded-2xl border border-border bg-background p-4 text-sm font-semibold text-muted-foreground lg:min-w-56">
                  <ScrollText className="mb-2 h-5 w-5" />
                  {study.historia_biblica.referencia}
                </div>
              </div>
            </section>
          )}

          <section id="versiculos" className="mt-7 grid gap-6 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
            <aside className="rounded-3xl border border-border bg-card p-5 shadow-sm lg:sticky lg:top-24">
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Guia rápido</p>
              <h2 className="mt-2 text-2xl font-bold">Versículos sobre {topic.term}</h2>
              <p className="mt-3 leading-7 text-muted-foreground">Esta lista reúne passagens centrais para estudar {topic.term} no contexto bíblico. Abra cada explicação para ver sentido, contexto e aplicação.</p>
              <div className="mt-5 space-y-2">
                {study.versiculos.slice(0, 8).map((verse) => (
                  <a key={`nav-${verse.referencia}`} href={`#${verse.referencia.replace(/[^a-zA-Z0-9]/g, '-')}`} className="flex items-center gap-2 rounded-xl border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted">
                    <CheckCircle2 className="h-4 w-4 text-muted-foreground" />
                    {verse.referencia}
                  </a>
                ))}
              </div>
            </aside>

            <div className="space-y-4">
              {study.versiculos.map((verse, index) => (
                <article id={verse.referencia.replace(/[^a-zA-Z0-9]/g, '-')} key={`${verse.referencia}-${verse.livro_slug}`} className="rounded-3xl border border-border bg-card p-5 shadow-sm transition hover:shadow-md sm:p-6">
                  <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                      <p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Versículo {index + 1}</p>
                      <h3 className="mt-1 text-2xl font-bold tracking-tight">{verse.referencia}</h3>
                    </div>
                    <a
                      href={explanationUrl(verse)}
                      className="inline-flex items-center justify-center gap-2 rounded-full bg-black px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200"
                    >
                      Ver explicação
                      <ArrowRight className="h-4 w-4" />
                    </a>
                  </div>
                  <blockquote className="mt-5 border-l-4 border-black pl-4 text-lg leading-9 text-foreground dark:border-white">
                    {verse.texto}
                  </blockquote>
                  <p className="mt-4 rounded-2xl border border-border bg-background p-4 text-sm leading-7 text-muted-foreground">{verse.motivo}</p>
                </article>
              ))}
            </div>
          </section>

          <section className="mt-8 rounded-3xl border border-border bg-card p-5 shadow-sm sm:p-6">
            <h2 className="text-xl font-semibold">Outros temas bíblicos</h2>
            <div className="mt-4 flex flex-wrap gap-2">
              {topics.filter((item) => item.slug !== topic.slug).slice(0, 12).map((item) => (
                <a key={item.slug} href={`/temas/${item.slug}`} className="rounded-full border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black">
                  {item.label}
                </a>
              ))}
            </div>
          </section>
        </div>
      </main>
      <Footer />
    </AppLayout>
  );
}
