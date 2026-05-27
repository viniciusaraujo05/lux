const BIBLICAL_THEME_LINKS = [
  ['fe', 'Fé'],
  ['confianca', 'Confiança'],
  ['ofertas', 'Ofertas'],
  ['amor', 'Amor'],
  ['oracao', 'Oração'],
  ['ansiedade', 'Ansiedade'],
  ['perdao', 'Perdão'],
  ['gratidao', 'Gratidão'],
  ['familia', 'Família'],
  ['casamento', 'Casamento'],
  ['sabedoria', 'Sabedoria'],
  ['forca', 'Força'],
  ['esperanca', 'Esperança'],
  ['paz', 'Paz'],
  ['cura', 'Cura'],
  ['provisao', 'Provisão'],
  ['obediencia', 'Obediência'],
  ['arrependimento', 'Arrependimento'],
  ['salvacao', 'Salvação'],
  ['louvor', 'Louvor'],
] as const;

export default function ThematicVerseLinks() {
  return (
    <section className="mt-6 rounded-2xl border border-border bg-card p-4 text-card-foreground shadow-sm sm:p-6">
      <div className="mb-4">
        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Estudos por tema</p>
        <h2 className="mt-1 text-2xl font-bold tracking-tight">Versículos sobre...</h2>
        <p className="mt-2 text-sm leading-6 text-muted-foreground">
          Explore temas bíblicos com passagens selecionadas e explicações verso a verso.
        </p>
      </div>
      <div className="flex flex-wrap gap-2">
        {BIBLICAL_THEME_LINKS.map(([slug, label]) => (
          <a
            key={slug}
            href={`/temas/${slug}?gerar=1`}
            className="rounded-full border border-border bg-background px-3.5 py-2 text-sm font-semibold shadow-sm transition hover:border-foreground/30 hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black"
          >
            {label}
          </a>
        ))}
      </div>
    </section>
  );
}
