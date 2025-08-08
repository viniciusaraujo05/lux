import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import Footer from '@/components/footer';
import { Copy, CheckCircle2, Shield, Coffee, Server, Zap, Brain } from 'lucide-react';

const PIX_CODE = '00020126330014BR.GOV.BCB.PIX0111477980468015204000053039865802BR5924Vinicius Araujo da Silva6009SAO PAULO62140510Jl6JnGgkj06304DA02';

export default function Ofertar() {
  const [copied, setCopied] = useState(false);

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(PIX_CODE);
      setCopied(true);
      setTimeout(() => setCopied(false), 2500);
    } catch (e) {
      // Fallback simples
      const ok = window.prompt('Copie o código PIX (Ctrl+C):', PIX_CODE);
      if (ok !== null) setCopied(true);
    }
  };


  return (
    <>
      <Head>
        <title>Ofertar | Verso a Verso - Apoie o Projeto</title>
        <meta
          name="description"
          content="Ajude o Verso a Verso a continuar gratuito e em evolução. Oferecemos opções de oferta via Buy Me a Coffee e PIX. Sua contribuição viabiliza servidores, IA e melhorias."
        />
        <meta name="keywords" content="oferta, doação, apoio, buy me a coffee, pix, biblia explicada, verso a verso" />
      </Head>

      <main className="container max-w-5xl mx-auto px-4 py-8 relative">
        <Link
          href="/"
          className="absolute right-4 top-4 text-muted-foreground hover:text-primary"
          aria-label="Início"
          title="Início"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="lucide lucide-home">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
          </svg>
        </Link>
        {/* Top */}
        <section className="mb-10">
          <div className="flex justify-center mb-6">
            <img src="/logo.svg" alt="Verso a Verso" className="h-16 w-auto" />
          </div>
          <h1 className="text-3xl font-bold text-primary mb-3 text-center">Apoie o Verso a Verso</h1>
          <p className="text-center text-muted-foreground mb-8 max-w-2xl mx-auto">
            Sua oferta mantém o projeto gratuito, melhora as respostas e ajuda a sustentar servidores e os modelos de inteligência artificial
            que usamos para entregar explicações bíblicas de qualidade.
          </p>
        </section>

        {/* Options */}
        <section className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
          {/* Buy Me a Coffee */}
          <div className="border rounded-xl p-6 bg-card">
            <div className="flex items-center mb-4">
              <Coffee className="text-primary mr-2" />
              <h2 className="text-xl font-semibold">Ofertar via Buy Me a Coffee</h2>
            </div>
            <p className="text-sm text-muted-foreground mb-4">
              O Buy Me a Coffee é uma plataforma reconhecida e segura para apoiar criadores. O pagamento acontece no ambiente deles,
              com criptografia e proteção ao usuário. Nós não armazenamos dados de pagamento. É rápido, transparente e você pode
              contribuir uma única vez ou de forma recorrente.
            </p>
            <a
              href="https://buymeacoffee.com/viniciusaraujo"
              target="_blank"
              rel="noopener noreferrer"
              className="w-full inline-flex items-center justify-center bg-blue-700 hover:bg-blue-800 text-white rounded-md transition-colors font-semibold px-4 py-2.5"
            >
              <Coffee className="h-4 w-4 mr-2" />
              Ofertar via Buy Me a Coffee
            </a>
            <div className="mt-3 flex items-start text-xs text-muted-foreground">
              <Shield className="h-4 w-4 mr-2 mt-0.5" />
              <span>Transação segura e processada pela plataforma. Você será redirecionado para finalizar a oferta.</span>
            </div>
          </div>

          {/* Pix */}
          <div className="border rounded-xl p-6 bg-card">
            <div className="flex items-center mb-4">
              <img src="/icon-pix.svg" alt="PIX" className="h-5 w-5 mr-2" />
              <h2 className="text-xl font-semibold">PIX (Copia e Cola)</h2>
            </div>

            {/* QR Code do PIX */}
            <div className="rounded-lg border bg-white mb-3 overflow-hidden flex items-center justify-center">
              <img src="/pix.png" alt="QR Code PIX" className="h-56 w-auto object-contain p-2" />
            </div>
            <div className="mb-2 text-sm font-medium">Código PIX para copiar:</div>
            <div className="flex items-stretch gap-2">
              <div className="flex-1 bg-muted/40 rounded-md px-3 py-2 text-xs font-mono break-all border">
                {PIX_CODE}
              </div>
              <button
                onClick={handleCopy}
                className="inline-flex items-center gap-2 px-3 py-2 text-sm bg-primary text-white rounded-md hover:bg-primary/90"
                aria-label="Copiar código PIX"
              >
                {copied ? <CheckCircle2 className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
                {copied ? 'Copiado' : 'Copiar'}
              </button>
            </div>
            <p className="text-xs text-muted-foreground mt-2">Use o app do seu banco para pagar via PIX. Você também poderá escanear o QR Code quando estiver disponível.</p>
          </div>
        </section>

        {/* Why help */}
        <section className="mt-6 bg-primary/5 rounded-xl p-6 border border-primary/10">
          <h3 className="text-xl font-semibold mb-4 text-center">Por que sua ajuda importa</h3>
          <ul className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <li className="flex items-start">
              <Zap className="h-4 w-4 text-primary mr-2 mt-0.5" />
              <span>Melhorar a qualidade e a velocidade das respostas com modelos de IA mais avançados.</span>
            </li>
            <li className="flex items-start">
              <Server className="h-4 w-4 text-primary mr-2 mt-0.5" />
              <span>Custear servidores, armazenamento e CDN para manter o site rápido e estável.</span>
            </li>
            <li className="flex items-start">
              <Brain className="h-4 w-4 text-primary mr-2 mt-0.5" />
              <span>Aprimorar o processo de geração e revisão das explicações bíblicas.</span>
            </li>
            <li className="flex items-start">
              <Shield className="h-4 w-4 text-primary mr-2 mt-0.5" />
              <span>Manter a experiência sem anúncios invasivos e com privacidade respeitada.</span>
            </li>
          </ul>
          <p className="text-center text-sm text-muted-foreground mt-4">Obrigado por caminhar conosco e tornar este projeto possível para mais pessoas.</p>
        </section>
      </main>
      <Footer />
    </>
  );
}
