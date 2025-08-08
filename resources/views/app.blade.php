<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-N5X2XHKS');</script>
    <!-- End Google Tag Manager -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'A Bíblia explicada, Verso a Verso') }}</title>
        
        <!-- Meta tags para SEO -->
        <meta name="description" content="{{ isset($description) ? $description : 'Verso a Verso oferece explicações detalhadas sobre passagens bíblicas, estudo versículo por versículo, contexto histórico e aplicações para a vida atual.' }}">
        <meta name="keywords" content="{{ isset($keywords) ? $keywords : 'bíblia, explicação bíblica, estudo bíblico, versículos da bíblia, verso a verso, comentário bíblico' }}">
        <meta name="robots" content="{{ $robots ?? 'index, follow' }}">
        <meta name="author" content="Verso a verso">
        <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}" />
        <link rel="alternate" hreflang="pt-br" href="{{ $canonicalUrl ?? url()->current() }}" />
        @if(isset($ampUrl))
            <link rel="amphtml" href="{{ $ampUrl }}" />
        @endif
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="{{ $ogType ?? 'website' }}">
        <meta property="og:locale" content="pt_BR">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'A Bíblia explicada, Verso a Verso') }}">
        <meta property="og:description" content="{{ isset($description) ? $description : 'Verso a verso oferece explicações detalhadas sobre passagens bíblicas, estudo versículo por versículo, contexto histórico e aplicações para a vida atual.' }}">
        <meta property="og:image" content="{{ asset('logo.svg') }}">
        @if(($ogType ?? null) === 'article')
            <meta property="article:published_time" content="{{ now()->toIso8601String() }}">
            <meta property="article:modified_time" content="{{ now()->toIso8601String() }}">
        @endif
        
        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta property="twitter:title" content="{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'A Bíblia explicada, Verso a Verso') }}">
        <meta property="twitter:description" content="{{ isset($description) ? $description : 'Verso a verso oferece explicações detalhadas sobre passagens bíblicas, estudo versículo por versículo, contexto histórico e aplicações para a vida atual.' }}">
        <meta property="twitter:image" content="{{ asset('logo.svg') }}">
        
        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg') }}">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
        
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @if(isset($jsonLd))
            <script type="application/ld+json">{!! $jsonLd !!}</script>
        @endif

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N5X2XHKS"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-QEEJB2L42W"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-QEEJB2L42W');
</script>
<body class="font-sans antialiased">
        @inertia
        @include('partials.cookie-consent')
    </body>
</html>
