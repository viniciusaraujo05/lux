<!doctype html>
<html amp lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    @php
        // Normalize inputs early to avoid Array to string conversion anywhere in the template
        $versesStr = isset($verses) ? (is_array($verses) ? implode(',', $verses) : $verses) : null;
        if (isset($verseTexts) && is_array($verseTexts)) {
            $verseTexts = implode(' ', $verseTexts);
        }
        if (isset($keywords) && is_array($keywords)) {
            $keywords = implode(', ', $keywords);
        }
        if (isset($title) && is_array($title)) {
            $title = implode(' ', $title);
        }
        if (isset($description) && is_array($description)) {
            $description = implode(' ', $description);
        }
        // Ensure relatedLinks is an array
        $relatedLinks = isset($relatedLinks) && is_array($relatedLinks) ? $relatedLinks : [];
        // Ensure explanation is a string; if array (JSON structure), stringify safely for AMP
        if (isset($explanation) && is_array($explanation)) {
            // Prefer a minimal textual rendering for AMP; fallback to compact JSON
            $explanation = '<pre style="white-space:pre-wrap">'
                . e(json_encode($explanation, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                . '</pre>';
        }
    @endphp
    <title>{{ $title ?? 'Verso a verso - Bíblia Explicada' }}</title>
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <meta name="description" content="{{ $description ?? 'Verso a verso - Bíblia Explicada oferece explicações detalhadas sobre passagens bíblicas, estudo versículo por versículo, contexto histórico e aplicações para a vida atual.' }}">
    <meta name="keywords" content="{{ $keywords ?? 'bíblia, explicação bíblica, estudo bíblico, versículos da bíblia, verso a verso, comentário bíblico' }}">
    
    <!-- Schema.org markup para Google -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "{{ $title ?? 'Verso a verso - Bíblia Explicada' }}",
        "description": "{{ $description ?? 'Verso a verso - Bíblia Explicada oferece explicações detalhadas sobre passagens bíblicas' }}",
        "author": {
            "@type": "Organization",
            "name": "Verso a verso - Bíblia Explicada"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Verso a verso - Bíblia Explicada",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ url('/images/logo.png') }}"
            }   
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ $canonicalUrl }}"
        }
    }
    </script>
    
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    
    <style amp-custom>
        /* Cores e variáveis */
        :root {
            --primary-color: #3563E9;
            --text-color: #1F2937;
            --background-color: #F9FAFB;
            --card-background: #FFFFFF;
            --muted-color: #6B7280;
            --border-color: #E5E7EB;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-color);
            background-color: var(--background-color);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 16px;
        }
        
        header {
            background-color: var(--card-background);
            border-bottom: 1px solid var(--border-color);
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        header h1 {
            font-size: 18px;
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            color: var(--text-color);
            text-decoration: none;
            margin-right: 12px;
        }
        
        .section {
            background-color: var(--card-background);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 16px;
            padding: 20px;
            border: 1px solid var(--border-color);
        }
        
        h2 {
            color: var(--primary-color);
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }
        
        h3 {
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        p {
            margin-top: 0;
            margin-bottom: 16px;
        }
        
        .bible-text {
            font-style: italic;
            color: #4B5563;
            margin-bottom: 20px;
            padding: 12px;
            background-color: #F3F4F6;
            border-radius: 4px;
            position: relative;
        }
        
        .bible-text::before {
            content: '"';
            font-size: 24px;
            color: #9CA3AF;
            position: absolute;
            left: 4px;
            top: 0;
        }
        
        .bible-text::after {
            content: '"';
            font-size: 24px;
            color: #9CA3AF;
            position: absolute;
            right: 4px;
            bottom: 0;
        }
        
        ul, ol {
            padding-left: 24px;
            margin-bottom: 16px;
        }
        
        li {
            margin-bottom: 8px;
        }
        
        .related-content {
            background-color: #EFF6FF;
            border: 1px solid #DBEAFE;
            border-radius: 8px;
            padding: 16px;
            margin-top: 24px;
        }
        
        .related-content h3 {
            color: var(--primary-color);
            margin-top: 0;
        }
        
        .related-links {
            list-style: none;
            padding-left: 0;
        }
        
        .related-links li {
            margin-bottom: 12px;
        }
        
        .related-links a {
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .related-links a::before {
            content: "→";
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        footer {
            text-align: center;
            margin-top: 32px;
            padding: 16px;
            font-size: 14px;
            color: var(--muted-color);
        }
        
        .non-amp-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 12px;
            background-color: #F3F4F6;
            border-radius: 4px;
            color: var(--muted-color);
        }
    </style>
 </head>
 <body>
     <div class="container">
         <header>
             <h1>
                 <a href="{{ url('/') }}" class="back-button">
                     ←
                 </a>
                 {{ $book }} {{ $chapter }}{{ !empty($versesStr) ? ':'.$versesStr : '' }}
             </h1>
             <div>AMP</div>
         </header>
 
         <main>
             <div class="section">
                 <h2>Explicação Bíblica</h2>
                 
                 @if(!empty($versesStr))
                     <div class="bible-text">
                         {{ $verseTexts ?? ('Versículo ' . $versesStr . ' do capítulo ' . $chapter . ' de ' . $book) }}
                     </div>
                 @endif
                 
                 {!! $explanation ?? 'Carregando explicação...' !!}
             </div>
            
            <!-- Seção de links relacionados -->
            @if(count($relatedLinks) > 0)
            <div class="related-content">
                <h3>Leituras Relacionadas</h3>
                <ul class="related-links">
                    @foreach($relatedLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}">{{ $link['title'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <div class="non-amp-link">
                <a href="{{ $canonicalUrl }}">Ver versão completa deste conteúdo</a>
            </div>
        </main>
        
        <footer>
            &copy; {{ date('Y') }} Verso a Verso - Bíblia Explicada. Todos os direitos reservados.
        </footer>
    </div>
</body>
</html>
