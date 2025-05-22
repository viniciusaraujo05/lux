import React from 'react';
import { Gift, Heart } from 'lucide-react';

interface DonateButtonProps {
  size?: 'sm' | 'md' | 'lg';
  variant?: 'text' | 'icon' | 'full';
  className?: string;
}

const DonateButton: React.FC<DonateButtonProps> = ({ 
  size = 'md', 
  variant = 'full',
  className = ''
}) => {
  // Tamanhos para os diferentes componentes
  const buttonSizes = {
    sm: 'text-xs px-3 py-1.5',
    md: 'text-sm px-3 py-2',
    lg: 'text-base px-4 py-2.5'
  };
  
  const iconSizes = {
    sm: 'h-3 w-3',
    md: 'h-4 w-4',
    lg: 'h-5 w-5'
  };
  
  // Renderizar botão baseado na variante
  if (variant === 'icon') {
    return (
      <a 
        href="https://buymeacoffee.com/viniciusaraujo" 
        target="_blank"
        rel="noopener noreferrer"
        className={`text-blue-400 hover:text-blue-300 transition-colors ${className}`}
        aria-label="Ofertar"
        title="Ofertar e Contribuir com a Obra"
      >
        <Gift className={iconSizes[size]} />
      </a>
    );
  }
  
  if (variant === 'text') {
    return (
      <a 
        href="https://buymeacoffee.com/viniciusaraujo" 
        target="_blank"
        rel="noopener noreferrer"
        className={`text-blue-400 hover:text-blue-300 font-medium transition-colors ${className}`}
        aria-label="Ofertar"
      >
        Ofertar
      </a>
    );
  }
  
  // Variante padrão: full
  return (
    <a 
      href="https://buymeacoffee.com/viniciusaraujo" 
      target="_blank"
      rel="noopener noreferrer"
      className={`flex items-center justify-center bg-blue-700 hover:bg-blue-800 text-white rounded-md transition-colors font-semibold ${buttonSizes[size]} ${className}`}
    >
      <Gift className={`${iconSizes[size]} mr-2`} />
      Ofertar Agora
    </a>
  );
};

export default DonateButton;
