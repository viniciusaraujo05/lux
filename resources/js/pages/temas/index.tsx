import AppLayout from '@/layouts/app-layout';
import Footer from '@/components/footer';
import { BookOpen, Search } from 'lucide-react';

interface Topic {
  slug: string;
  label: string;
  title: string;
  term: string;
  description: string;
}

export default function ThemesIndex({ topics }: { topics: Topic[] }) {
  return (
    <AppLayout>
      <main className="min-h-screen bg-[radial-gradient(circle_at_top,_hsl(var(--primary)/0.12),_transparent_42%),hsl(var(--background))] px-4 py-8 text-foreground sm:px-6 lg:px-8">
        <div className="mx-auto max-w-6xl">
          <section className="rounded-3xl border border-border/70 bg-card p-6 shadow-sm sm:p-10">
            <div className="max-w-3xl">
              <p className="mb-3 inline-flex items-center gap-2 rounded-full border border-border bg-background px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                <Search className="h-3.5 w-3.5" /> Temas bíblicos
              </p>
              <h1 className="text-3xl font-bold tracking-tight sm:text-5xl">Versículos sobre temas da Bíblia</h1>
              <p className="mt-4 text-base leading-8 text-muted-foreground sm:text-lg">
                Escolha um tema para ler passagens bíblicas relacionadas, entender o significado na Bíblia e abrir a explicação verso a verso de cada texto.
              </p>
            </div>
          </section>

          <section className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {topics.map((topic) => (
              <a
                key={topic.slug}
                href={`/temas/${topic.slug}`}
                className="group rounded-2xl border border-border/70 bg-card p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-foreground/20 hover:shadow-md"
              >
                <div className="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-black text-white dark:bg-white dark:text-black">
                  <BookOpen className="h-5 w-5" />
                </div>
                <h2 className="text-xl font-semibold">{topic.title}</h2>
                <p className="mt-2 text-sm leading-6 text-muted-foreground">{topic.description}</p>
                <span className="mt-4 inline-flex text-sm font-semibold text-foreground">Ver versículos</span>
              </a>
            ))}
          </section>
        </div>
      </main>
      <Footer />
    </AppLayout>
  );
}
