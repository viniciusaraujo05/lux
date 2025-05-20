<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CachingMiddleware
{
    /**
     * Middleware para gerenciar cache de páginas estáticas ou semi-estáticas
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Bypass para usuários logados ou rotas dinâmicas específicas
        if ($request->user() || 
            $request->is('api/*') || 
            $request->method() !== 'GET') {
            return $next($request);
        }
        
        // Gerar chave de cache única para esta URL
        $cacheKey = 'page_cache_' . sha1($request->fullUrl());
        
        // Verificar se temos versão em cache
        if (Cache::has($cacheKey)) {
            $cachedContent = Cache::get($cacheKey);
            
            // Adiciona cabeçalhos úteis para SEO e cache do navegador
            return response($cachedContent)
                ->header('X-Cache', 'HIT')
                ->header('Cache-Control', 'public, max-age=300'); // 5 minutos de cache no navegador
        }
        
        // Se não temos cache, processa a requisição
        $response = $next($request);
        
        // Salvar apenas respostas bem-sucedidas
        if ($response->status() === 200) {
            $content = $response->getContent();
            
            // Cache por 60 minutos (ajuste conforme necessidade)
            Cache::put($cacheKey, $content, now()->addMinutes(60));
            
            $response->header('X-Cache', 'MISS');
            $response->header('Cache-Control', 'public, max-age=300');
        }
        
        return $response;
    }
}
