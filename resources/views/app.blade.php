<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
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

        <title inertia>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'Verbum - Bíblia Explicada') }}</title>
        
        <!-- Meta tags para SEO -->
        <meta name="description" content="{{ isset($description) ? $description : 'Verbum - Bíblia Explicada oferece explicações detalhadas sobre passagens bíblicas, estudo versículo por versículo, contexto histórico e aplicações para a vida atual.' }}">
        <meta name="keywords" content="{{ isset($keywords) ? $keywords : 'bíblia, explicação bíblica, estudo bíblico, versículos da bíblia, verbum, comentário bíblico' }}">
        <meta name="author" content="Verbum">
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'Verbum - Bíblia Explicada') }}">
        <meta property="og:description" content="{{ isset($description) ? $description : 'Verbum - Bíblia Explicada oferece explicações detalhadas sobre passagens bíblicas, estudo versículo por versículo, contexto histórico e aplicações para a vida atual.' }}">
        <meta property="og:image" content="{{ asset('images/verbum-og-image.jpg') }}">
        
        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta property="twitter:title" content="{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'Verbum - Bíblia Explicada') }}">
        <meta property="twitter:description" content="{{ isset($description) ? $description : 'Verbum - Bíblia Explicada oferece explicações detalhadas sobre passagens bíblicas, estudo versículo por versículo, contexto histórico e aplicações para a vida atual.' }}">
        <meta property="twitter:image" content="{{ asset('images/verbum-og-image.jpg') }}">
        
        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}">
        
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
