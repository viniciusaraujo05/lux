import React from 'react';
import { Link } from '@inertiajs/react';
import { Github, Gift, Heart } from 'lucide-react';
import DonateButton from './donate-button';

const Footer: React.FC = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-dark-900 border-t border-gray-800 text-white mt-auto">
      <div className="container max-w-6xl mx-auto px-4 py-8">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* Sobre o Verbum */}
          <div>
            <h3 className="text-lg font-semibold mb-4 text-white">Verbum</h3>
            <p className="text-sm text-gray-300 mb-4">
              Uma plataforma para estudar a Bíblia com explicações detalhadas e contextuais
              para ajudar na compreensão das Escrituras.
            </p>
          </div>

          {/* Links Rápidos */}
          <div>
            <h3 className="text-lg font-semibold mb-4 text-white">Links Rápidos</h3>
            <ul className="space-y-2">
              <li>
                <Link 
                  href="/biblia" 
                  className="text-sm text-gray-300 hover:text-primary transition-colors"
                >
                  Bíblia
                </Link>
              </li>
              <li>
                <Link 
                  href="/sobre" 
                  className="text-sm text-gray-300 hover:text-primary transition-colors"
                >
                  Sobre o Projeto
                </Link>
              </li>
              <li>
                <Link 
                  href="/faq" 
                  className="text-sm text-gray-300 hover:text-primary transition-colors"
                >
                  Perguntas Frequentes
                </Link>
              </li>
            </ul>
          </div>

          {/* Suporte */}
          <div>
            <h3 className="text-lg font-semibold mb-4 text-white">Suporte</h3>
            <p className="text-sm text-gray-300 mb-4">
              Encontrou algum problema ou tem alguma sugestão? Entre em contato conosco.
            </p>
            <a 
              href="mailto:contato@verbumbiblia.com" 
              className="text-sm text-gray-300 hover:text-primary transition-colors"
            >
              contato@verbumbiblia.com
            </a>
          </div>
        </div>

        <div className="border-t border-gray-700 mt-8 pt-6">
          <div className="flex flex-col items-center justify-center mb-6 bg-purple-900/30 rounded-lg p-4 border border-purple-500/40">
            <div className="flex items-center mb-2">
              <Gift className="h-5 w-5 text-purple-400 mr-2" />
              <h4 className="font-semibold text-white">Nos ajude a manter esse site vivo!</h4>
            </div>
            <p className="text-sm text-gray-300 mb-4 text-center">
              Sua oferta ajuda a manter este site de estudos bíblicos disponível gratuitamente.
            </p>
            <DonateButton size="md" />
          </div>
          
          <div className="flex flex-col md:flex-row justify-between items-center">
            <p className="text-sm text-gray-300 mb-4 md:mb-0">
              &copy; {currentYear} Verbum - Bíblia Explicada. Todos os direitos reservados.
            </p>
            <div className="flex space-x-4">
              <DonateButton variant="icon" size="md" className="text-purple-400 hover:text-purple-300" />
              <a 
                href="#" 
                className="text-gray-300 hover:text-primary transition-colors"
                aria-label="GitHub"
              >
                <Github className="h-5 w-5" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
