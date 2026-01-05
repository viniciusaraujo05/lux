<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectSearchBot
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->userAgent() ?? '';

        $bots = [
            'Googlebot',
            'Bingbot',
            'Slurp',              // Yahoo
            'DuckDuckBot',
            'Baiduspider',
            'YandexBot',
            'Sogou',
            'Exabot',
            'facebookexternalhit',
            'LinkedInBot',
            'WhatsApp',
            'Twitterbot',
            'Applebot',
            'Discordbot',
            'TelegramBot',
            'ia_archiver',        // Alexa
            'AhrefsBot',
            'SemrushBot',
            'MJ12bot',            // Majestic
            'DotBot',
            'Screaming Frog',
        ];

        $isBot = false;
        foreach ($bots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                $isBot = true;
                break;
            }
        }

        // Adiciona flag ao request para uso posterior
        $request->attributes->set('is_bot', $isBot);
        $request->attributes->set('bot_name', $isBot ? $this->getBotName($userAgent) : null);

        return $next($request);
    }

    /**
     * Get bot name from user agent
     */
    private function getBotName(string $userAgent): string
    {
        if (stripos($userAgent, 'Googlebot') !== false) {
            return 'Googlebot';
        }
        if (stripos($userAgent, 'Bingbot') !== false) {
            return 'Bingbot';
        }
        if (stripos($userAgent, 'facebookexternalhit') !== false) {
            return 'Facebook';
        }

        return 'Unknown Bot';
    }
}
