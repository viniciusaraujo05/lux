import React, { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';

interface BibleExplanationProps {
  testamento: string;
  livro: string;
  capitulo: string;
}

export default function BibleExplanation(props: BibleExplanationProps) {
  const [loading, setLoading] = useState(true);
  const [explanation, setExplanation] = useState('');
  const [source, setSource] = useState('');
  
  // Extrair os parâmetros da URL
  const testament = props.testamento;
  const book = props.livro;
  const chapter = props.capitulo;
  const verses = new URLSearchParams(window.location.search).get('versiculos');
  
  useEffect(() => {
    async function fetchExplanation() {
      try {
        // Construir a URL da API com os parâmetros corretos
        let apiUrl = `/api/explanation/${testament}/${book}/${chapter}`;
        if (verses) {
          apiUrl += `?verses=${verses}`;
        }
        
        console.log('Fetching explanation from:', apiUrl);
        
        const response = await fetch(apiUrl);
        if (!response.ok) {
          throw new Error(`API responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('API response:', data);
        
        setExplanation(data.explanation || 'No explanation was returned.');
        setSource(data.origin || 'unknown');
        setLoading(false);
      } catch (error) {
        console.error('Error fetching explanation:', error);
        setExplanation('Erro ao buscar explicação. Por favor, tente novamente.');
        setLoading(false);
      }
    }
    
    fetchExplanation();
  }, [testament, book, chapter, verses]);
  
  return (
    <div className="container max-w-4xl mx-auto p-4">
      <header className="mb-6 flex items-center">
        <Link href="/" className="mr-4">
          <ChevronLeft size={22} />
        </Link>
        <h1 className="text-xl font-bold">
          {book} {chapter}
          {verses && <span className="ml-2 text-sm">(Versículos: {verses})</span>}
        </h1>
      </header>
      
      <div className="border rounded-lg p-6 bg-white shadow-sm">
        {loading ? (
          <div className="flex justify-center py-8">
            <div className="animate-spin h-8 w-8 border-4 border-blue-500 rounded-full border-t-transparent"></div>
          </div>
        ) : (
          <div>
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-lg font-semibold">Explicação</h2>
            </div>
            
            <div className="prose max-w-none">
              {explanation.split('\n').map((paragraph, idx) => (
                <p key={idx} className={paragraph.trim() ? 'mb-4' : 'mb-2'}>
                  {paragraph}
                </p>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
