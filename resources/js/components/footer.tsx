import React from 'react';
import { Link } from '@inertiajs/react';
import { Github, Gift, Heart } from 'lucide-react';
import DonateButton from './donate-button';

const Footer: React.FC = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-white dark:bg-black border-t-2 border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white mt-auto py-6">
      <div className="container max-w-6xl mx-auto px-4">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* Sobre o Verbum */}
          <div className="bg-white dark:bg-black p-4 rounded-lg shadow-sm">
            <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Verbum</h3>
            <p className="text-sm text-gray-600 dark:text-gray-300 mb-4">
              Uma plataforma para estudar a Bíblia com explicações detalhadas e contextuais
              para ajudar na compreensão das Escrituras.
            </p>
          </div>

          {/* Links Rápidos */}
          <div className="bg-white dark:bg-black p-4 rounded-lg shadow-sm">
            <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Links Rápidos</h3>
            <ul className="space-y-2">
              <li>
                <Link 
                  href="/biblia" 
                  className="text-sm text-gray-600 dark:text-gray-300 hover:text-primary transition-colors"
                >
                  Bíblia
                </Link>
              </li>
              <li>
                <Link 
                  href="/sobre" 
                  className="text-sm text-gray-600 dark:text-gray-300 hover:text-primary transition-colors"
                >
                  Sobre o Projeto
                </Link>
              </li>
              <li>
                <Link 
                  href="/faq" 
                  className="text-sm text-gray-600 dark:text-gray-300 hover:text-primary transition-colors"
                >
                  Perguntas Frequentes
                </Link>
              </li>
            </ul>
          </div>

          {/* Suporte */}
          <div className="bg-white dark:bg-black p-4 rounded-lg shadow-sm">
            <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Suporte</h3>
            <p className="text-sm text-gray-600 dark:text-gray-300 mb-4">
              Encontrou algum problema ou tem alguma sugestão? Entre em contato conosco.
            </p>
            <a 
              href="mailto:contato@verbumbiblia.com" 
              className="text-sm text-gray-600 dark:text-gray-300 hover:text-primary transition-colors"
            >
              contato@verbumbiblia.com
            </a>
          </div>
        </div>

        <div className="border-t border-gray-300 dark:border-gray-700 mt-8 pt-6">
          <div className="flex flex-col items-center justify-center mb-6 bg-purple-100 dark:bg-purple-900/30 rounded-lg p-4 border border-purple-300 dark:border-purple-500/40 shadow-md">
            <div className="flex items-center mb-2">
              <Gift className="h-5 w-5 text-purple-600 dark:text-purple-400 mr-2" />
              <h4 className="font-semibold text-purple-900 dark:text-white">Nos ajude a manter esse site vivo!</h4>
            </div>
            <p className="text-sm text-purple-800 dark:text-gray-300 mb-4 text-center">
              Sua oferta ajuda a manter este site de estudos bíblicos disponível gratuitamente.
            </p>
            <DonateButton size="md" />
          </div>
          
          <div className="flex flex-col md:flex-row justify-between items-center bg-white dark:bg-black p-3 rounded-lg shadow-sm mt-2">
            <p className="text-sm text-gray-600 dark:text-gray-300 mb-4 md:mb-0">
              &copy; {currentYear} Verbum - Bíblia Explicada. Todos os direitos reservados.
            </p>
            <div className="flex space-x-4">
              <DonateButton variant="icon" size="md" className="text-purple-400 hover:text-purple-300" />
              <a 
                href="https://github.com/viniciusaraujo05" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-gray-600 dark:text-gray-300 hover:text-primary transition-colors"
                aria-label="GitHub"
              >
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
