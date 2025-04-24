/**
 * Serviço para gerenciar slugs dos livros da Bíblia
 */

// Mapeamento de nomes de livros para slugs
const livrosParaSlugs: Record<string, string> = {
  // Velho Testamento
  'Gênesis': 'genesis',
  'Êxodo': 'exodo',
  'Levítico': 'levitico',
  'Números': 'numeros',
  'Deuteronômio': 'deuteronomio',
  'Josué': 'josue',
  'Juízes': 'juizes',
  'Rute': 'rute',
  '1 Samuel': '1-samuel',
  '2 Samuel': '2-samuel',
  '1 Reis': '1-reis',
  '2 Reis': '2-reis',
  '1 Crônicas': '1-cronicas',
  '2 Crônicas': '2-cronicas',
  'Esdras': 'esdras',
  'Neemias': 'neemias',
  'Ester': 'ester',
  'Jó': 'jo',
  'Salmos': 'salmos',
  'Provérbios': 'proverbios',
  'Eclesiastes': 'eclesiastes',
  'Cânticos': 'canticos',
  'Isaías': 'isaias',
  'Jeremias': 'jeremias',
  'Lamentações': 'lamentacoes',
  'Ezequiel': 'ezequiel',
  'Daniel': 'daniel',
  'Oséias': 'oseias',
  'Joel': 'joel',
  'Amós': 'amos',
  'Obadias': 'obadias',
  'Jonas': 'jonas',
  'Miquéias': 'miqueias',
  'Naum': 'naum',
  'Habacuque': 'habacuque',
  'Sofonias': 'sofonias',
  'Ageu': 'ageu',
  'Zacarias': 'zacarias',
  'Malaquias': 'malaquias',
  
  // Novo Testamento
  'Mateus': 'mateus',
  'Marcos': 'marcos',
  'Lucas': 'lucas',
  'João': 'joao',
  'Atos': 'atos',
  'Romanos': 'romanos',
  '1 Coríntios': '1-corintios',
  '2 Coríntios': '2-corintios',
  'Gálatas': 'galatas',
  'Efésios': 'efesios',
  'Filipenses': 'filipenses',
  'Colossenses': 'colossenses',
  '1 Tessalonicenses': '1-tessalonicenses',
  '2 Tessalonicenses': '2-tessalonicenses',
  '1 Timóteo': '1-timoteo',
  '2 Timóteo': '2-timoteo',
  'Tito': 'tito',
  'Filemom': 'filemom',
  'Hebreus': 'hebreus',
  'Tiago': 'tiago',
  '1 Pedro': '1-pedro',
  '2 Pedro': '2-pedro',
  '1 João': '1-joao',
  '2 João': '2-joao',
  '3 João': '3-joao',
  'Judas': 'judas',
  'Apocalipse': 'apocalipse'
};

// Mapeamento inverso de slugs para nomes de livros
const slugsParaLivros: Record<string, string> = {};

// Preencher o mapeamento inverso
Object.entries(livrosParaSlugs).forEach(([livro, slug]) => {
  slugsParaLivros[slug] = livro;
});

/**
 * Classe SlugService para gerenciar slugs dos livros da Bíblia
 */
class SlugService {
  private static instance: SlugService;

  private constructor() {}

  /**
   * Obtém a única instância do serviço (padrão Singleton)
   */
  public static getInstance(): SlugService {
    if (!SlugService.instance) {
      SlugService.instance = new SlugService();
    }
    return SlugService.instance;
  }

  /**
   * Converte um nome de livro para slug
   */
  public livroParaSlug(livro: string): string {
    return livrosParaSlugs[livro] || this.gerarSlug(livro);
  }

  /**
   * Converte um slug para nome de livro
   */
  public slugParaLivro(slug: string): string {
    return slugsParaLivros[slug] || this.formatarSlug(slug);
  }

  /**
   * Gera um slug a partir de um texto
   * Usado como fallback caso o livro não esteja no mapeamento
   */
  private gerarSlug(texto: string): string {
    return texto
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '') // Remove acentos
      .replace(/[^\w\s-]/g, '') // Remove caracteres especiais
      .replace(/\s+/g, '-') // Substitui espaços por hífens
      .replace(/--+/g, '-') // Remove hífens duplicados
      .trim();
  }

  /**
   * Formata um slug para exibição
   * Usado como fallback caso o slug não esteja no mapeamento inverso
   */
  private formatarSlug(slug: string): string {
    return slug
      .replace(/-/g, ' ') // Substitui hífens por espaços
      .replace(/\b\w/g, (l) => l.toUpperCase()); // Capitaliza primeira letra de cada palavra
  }
}

export default SlugService;
