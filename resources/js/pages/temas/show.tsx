import AppLayout from '@/layouts/app-layout';
import Footer from '@/components/footer';
import { BookOpen, Loader2, Sparkles, ArrowRight } from 'lucide-react';
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
      <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,_hsl(var(--primary)/0.12),_transparent_38%),hsl(var(--background))] px-4 py-6 text-foreground sm:px-6 lg:px-8">
        <div className="mx-auto max-w-6xl">
          <a href="/temas" className="mb-4 inline-flex text-sm font-semibold text-muted-foreground hover:text-foreground">Todos os temas</a>

          <section className="overflow-hidden rounded-3xl border border-border/70 bg-card shadow-sm">
            <div className="bg-zinc-950 p-6 text-white dark:bg-zinc-900 sm:p-10">
              <p className="mb-3 text-xs font-semibold uppercase tracking-[0.22em] text-zinc-300">Versículos sobre</p>
              <h1 className="text-3xl font-bold tracking-tight sm:text-5xl">{study.titulo || topic.title}</h1>
              <p className="mt-4 max-w-3xl text-base leading-8 text-zinc-200 sm:text-lg">{study.subtitulo || topic.description}</p>
            </div>
            <div className="grid gap-4 p-5 sm:grid-cols-3 sm:p-6">
              <div className="rounded-2xl border border-border bg-background p-4 sm:col-span-2">
                <h2 className="text-lg font-semibold">O que a Bíblia ensina?</h2>
                <p className="mt-2 leading-7 text-muted-foreground">{study.introducao}</p>
              </div>
              <div className="rounded-2xl border border-border bg-background p-4">
                <h2 className="text-lg font-semibold">Significado bíblico</h2>
                <p className="mt-2 text-sm leading-7 text-muted-foreground">{study.significado_biblico}</p>
              </div>
            </div>
          </section>

          {currentOrigin === 'fallback' && (
            <section className="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/30 sm:flex sm:items-center sm:justify-between sm:gap-4">
              <div>
                <h2 className="font-semibold text-amber-950 dark:text-amber-100">Estudo inicial pronto para leitura</h2>
                <p className="mt-1 text-sm text-amber-800 dark:text-amber-200">Você já pode navegar pelos versículos. Se quiser, gere uma explicação temática mais completa com IA.</p>
              </div>
              <button
                onClick={generateStudy}
                disabled={loading}
                className="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-full bg-black px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800 disabled:opacity-60 dark:bg-white dark:text-black dark:hover:bg-zinc-200 sm:mt-0 sm:w-auto"
              >
                {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Sparkles className="h-4 w-4" />}
                Gerar estudo completo
              </button>
            </section>
          )}

          {error && <p className="mt-4 rounded-xl border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive">{error}</p>}

          <section className="mt-6">
            <div className="mb-4 flex items-end justify-between gap-4">
              <div>
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Passagens selecionadas</p>
                <h2 className="mt-1 text-2xl font-bold">Versículos sobre {topic.term}</h2>
              </div>
            </div>

            <div className="grid gap-4 lg:grid-cols-2">
              {study.versiculos.map((verse) => (
                <article key={`${verse.referencia}-${verse.livro_slug}`} className="rounded-2xl border border-border/70 bg-card p-5 shadow-sm">
                  <div className="mb-3 flex items-start justify-between gap-3">
                    <div>
                      <p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">{verse.referencia}</p>
                      <h3 className="mt-1 text-lg font-semibold">{verse.referencia}</h3>
                    </div>
                    <BookOpen className="h-5 w-5 text-muted-foreground" />
                  </div>
                  <p className="leading-8 text-foreground">{verse.texto}</p>
                  <p className="mt-3 text-sm leading-6 text-muted-foreground">{verse.motivo}</p>
                  <a
                    href={explanationUrl(verse)}
                    className="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full bg-black px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200 sm:w-auto"
                  >
                    Ver explicação do verso
                    <ArrowRight className="h-4 w-4" />
                  </a>
                </article>
              ))}
            </div>
          </section>

          <section className="mt-8 rounded-2xl border border-border/70 bg-card p-5 shadow-sm">
            <h2 className="text-xl font-semibold">Outros temas bíblicos</h2>
            <div className="mt-4 flex flex-wrap gap-2">
              {topics.filter((item) => item.slug !== topic.slug).slice(0, 12).map((item) => (
                <a key={item.slug} href={`/temas/${item.slug}`} className="rounded-full border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted">
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
