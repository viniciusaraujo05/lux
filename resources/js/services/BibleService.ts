/**
 * Serviço para acessar os dados da estrutura da Bíblia
 */

// Interface para a estrutura de versículos
interface VersiculosMap {
    [capitulo: string]: number;
}

// Interface para a estrutura de um livro
interface Livro {
    numCapitulos: number;
    versiculos: VersiculosMap;
}

// Interface para a estrutura de um testamento
interface Testamento {
    [livro: string]: Livro;
}

// Interface para a estrutura completa da Bíblia
interface EstruturaBiblia {
    velho: Testamento;
    novo: Testamento;
}

/**
 * Classe BibleService para gerenciar o acesso aos dados da Bíblia
 */
class BibleService {
    private estruturaBiblia: EstruturaBiblia | null = null;
    private static instance: BibleService;

    private constructor() {}

    /**
     * Obtém a única instância do serviço (padrão Singleton)
     */
    public static getInstance(): BibleService {
        if (!BibleService.instance) {
            BibleService.instance = new BibleService();
        }
        return BibleService.instance;
    }

    /**
     * Carrega os dados da estrutura da Bíblia do arquivo JSON
     */
    public async loadBibleData(): Promise<EstruturaBiblia> {
        if (this.estruturaBiblia) {
            return this.estruturaBiblia;
        }

        try {
            const response = await fetch('/data/bible-structure.json');
            const data = await response.json() as EstruturaBiblia;
            this.estruturaBiblia = data;
            return data;
        } catch (error) {
            console.error('Erro ao carregar os dados da Bíblia:', error);
            throw error;
        }
    }

    /**
     * Obtém a lista de livros do Velho Testamento
     */
    public async getLivrosVelhoTestamento(): Promise<string[]> {
        const data = await this.loadBibleData();
        return Object.keys(data.velho);
    }

    /**
     * Obtém a lista de livros do Novo Testamento
     */
    public async getLivrosNovoTestamento(): Promise<string[]> {
        const data = await this.loadBibleData();
        return Object.keys(data.novo);
    }

    /**
     * Obtém o número de capítulos de um livro específico
     */
    public async getNumeroCapitulos(livro: string, testamento: 'velho' | 'novo'): Promise<number> {
        const data = await this.loadBibleData();
        return data[testamento][livro]?.numCapitulos || 0;
    }

    /**
     * Obtém o número de versículos de um capítulo específico
     */
    public async getNumeroVersiculos(livro: string, capitulo: number, testamento: 'velho' | 'novo'): Promise<number> {
        const data = await this.loadBibleData();
        return data[testamento][livro]?.versiculos[capitulo.toString()] || 0;
    }

    /**
     * Obtém a lista de capítulos de um livro específico
     */
    public async getCapitulos(livro: string, testamento: 'velho' | 'novo'): Promise<number[]> {
        const numCapitulos = await this.getNumeroCapitulos(livro, testamento);
        return Array.from({ length: numCapitulos }, (_, i) => i + 1);
    }

    /**
     * Obtém a lista de versículos de um capítulo específico
     */
    public async getVersiculos(livro: string, capitulo: number, testamento: 'velho' | 'novo'): Promise<number[]> {
        const numVersiculos = await this.getNumeroVersiculos(livro, capitulo, testamento);
        return Array.from({ length: numVersiculos }, (_, i) => i + 1);
    }
}

export default BibleService;
