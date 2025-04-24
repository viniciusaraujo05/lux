import { useState, useEffect } from 'react';

/**
 * Hook para detectar se um media query está ativo
 * @param query Media query string (ex: '(max-width: 640px)')
 * @returns boolean indicando se o media query está ativo
 */
export function useMediaQuery(query: string): boolean {
  const [matches, setMatches] = useState(false);

  useEffect(() => {
    // Verificar se estamos no browser (não no SSR)
    if (typeof window !== 'undefined') {
      const media = window.matchMedia(query);
      
      // Definir o estado inicial
      setMatches(media.matches);
      
      // Função para atualizar o estado quando o media query mudar
      const listener = () => {
        setMatches(media.matches);
      };
      
      // Adicionar listener
      media.addEventListener('change', listener);
      
      // Remover listener ao desmontar o componente
      return () => {
        media.removeEventListener('change', listener);
      };
    }
    
    // Fallback para SSR
    return undefined;
  }, [query]);

  return matches;
}
