<article class="featured-verses" itemscope itemtype="https://schema.org/Article">
    <meta itemprop="headline" content="Versículos em Destaque - {{ $titulo ?? 'Verso a Verso' }}">
    <meta itemprop="author" content="Verso a Verso - Bíblia Explicada">
    <meta itemprop="datePublished" content="{{ now()->toIso8601String() }}">
    
    <h2 class="section-title">Versículos em Destaque</h2>
    
    <div class="verse-carousel">
        @foreach($versiculos ?? [] as $versiculo)
            <div class="verse-card" itemprop="hasPart" itemscope itemtype="https://schema.org/CreativeWork">
                <div class="verse-reference">
                    <span itemprop="name">{{ $versiculo['referencia'] }}</span>
                </div>
                <blockquote class="verse-text" itemprop="text">
                    "{{ $versiculo['texto'] }}"
                </blockquote>
                <div class="verse-actions">
                    <a href="{{ $versiculo['url'] }}" class="btn btn-primary">Ler explicação</a>
                    <button class="btn btn-secondary share-btn" data-reference="{{ $versiculo['referencia'] }}" data-text="{{ $versiculo['texto'] }}">
                        Compartilhar
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</article>
