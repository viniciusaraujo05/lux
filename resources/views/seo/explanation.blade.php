<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $keywords }}">
    
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-N5X2XHKS');</script>
    <!-- End Google Tag Manager -->
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-QEEJB2L42W"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-QEEJB2L42W');
    </script>
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1406842788891515"
         crossorigin="anonymous"></script>
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Verso a verso - Bíblia Explicada">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    
    <!-- Canonical -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "{{ $title }}",
        "description": "{{ $description }}",
        "author": {
            "@type": "Organization",
            "name": "Verso a verso"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Verso a verso",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ url('/logo.svg') }}"
            }
        },
        "datePublished": "{{ now()->toIso8601String() }}",
        "dateModified": "{{ now()->toIso8601String() }}",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        }
    }
    </script>
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        h1 {
            color: #1a1a1a;
            font-size: 2em;
            margin: 0;
        }
        h2 {
            color: #2c3e50;
            font-size: 1.5em;
            margin-top: 30px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .verse-text {
            background: #f8f9fa;
            border-left: 4px solid #4a90e2;
            padding: 15px 20px;
            margin: 20px 0;
            font-style: italic;
            font-size: 1.1em;
        }
        section {
            margin: 30px 0;
        }
        aside {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 40px;
        }
        aside h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        aside ul {
            list-style: none;
            padding: 0;
        }
        aside li {
            margin: 10px 0;
        }
        aside a {
            color: #4a90e2;
            text-decoration: none;
        }
        aside a:hover {
            text-decoration: underline;
        }
        footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N5X2XHKS"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <header>
        <h1>{{ $title }}</h1>
        @if(isset($book) && isset($chapter))
            <p><strong>{{ $book }} {{ $chapter }}@if(isset($verses)):{{ $verses }}@endif</strong></p>
        @endif
    </header>

    <!-- AdSense Top -->
    <div style="margin: 20px 0; text-align: center;">
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="ca-pub-1406842788891515"
             data-ad-slot="auto"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
    
    <main>
        <article>
            @if(isset($intro))
                <div style="background: #f0f7ff; padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #4a90e2;">
                    <p style="margin: 0; line-height: 1.8;">{{ $intro }}</p>
                </div>
            @endif
            
            @if(isset($verse_text))
                <blockquote class="verse-text">
                    {{ $verse_text }}
                </blockquote>
            @endif
            
            @if(isset($content))
                <p style="font-size: 1.1em; line-height: 1.8;">{{ $content }}</p>
            @endif
            
            @if(isset($sections))
                @php $sectionCount = 0; @endphp
                @foreach($sections as $sectionKey => $section)
                    @php $sectionCount++; @endphp
                    <section>
                        <h2>{{ $section['title'] ?? ucfirst(str_replace('_', ' ', $sectionKey)) }}</h2>
                        <p style="line-height: 1.8; text-align: justify;">{{ $section['content'] ?? $section }}</p>
                    </section>

                    @if($sectionCount === 2)
                        <!-- AdSense Middle -->
                        <div style="margin: 30px 0; text-align: center;">
                            <ins class="adsbygoogle"
                                 style="display:block"
                                 data-ad-client="ca-pub-1406842788891515"
                                 data-ad-slot="auto"
                                 data-ad-format="auto"
                                 data-full-width-responsive="true"></ins>
                            <script>
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            </script>
                        </div>
                    @endif
                @endforeach
            @endif
            
            @if(isset($book) && isset($chapter))
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 40px;">
                    <h3 style="margin-top: 0;">Por que estudar {{ $book }} {{ $chapter }}@if(isset($verses)):{{ $verses }}@endif?</h3>
                    <p style="line-height: 1.8;">
                        O estudo profundo da Bíblia é essencial para o crescimento espiritual e para uma vida cristã madura. 
                        {{ $book }} {{ $chapter }}@if(isset($verses)):{{ $verses }}@endif oferece insights valiosos sobre a natureza de Deus, 
                        Seu plano de salvação, e como devemos viver de acordo com Sua vontade. Ao dedicar tempo para compreender 
                        este texto em seu contexto original e aplicá-lo à nossa vida hoje, somos transformados pela renovação 
                        da nossa mente (Romanos 12:2) e equipados para toda boa obra (2 Timóteo 3:16-17). Este estudo não é 
                        apenas acadêmico, mas profundamente prático e transformador.
                    </p>
                </div>
            @endif
        </article>
        
        @if(isset($related_links) && count($related_links) > 0)
            <aside>
                <h3>Links Relacionados</h3>
                <ul>
                    @foreach($related_links as $link)
                        <li><a href="{{ $link['url'] }}">{{ $link['title'] }}</a></li>
                    @endforeach
                </ul>
            </aside>
        @endif

        <!-- AdSense Bottom -->
        <div style="margin: 30px 0; text-align: center;">
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="ca-pub-1406842788891515"
                 data-ad-slot="auto"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
            <script>
                 (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>
    </main>
    
    <footer>
        <p>&copy; {{ date('Y') }} Verso a verso - Bíblia Explicada</p>
        <p><a href="/">Página Inicial</a> | <a href="/biblia">Explorar Bíblia</a></p>
    </footer>
</body>
</html>
