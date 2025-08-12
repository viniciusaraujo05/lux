<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WarmPages extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'warm:pages {--base-url= : Base URL, fallback to APP_URL}';

    /**
     * The console command description.
     */
    protected $description = 'Prime (warm) the page cache by issuing GET requests to key pages.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $base = rtrim($this->option('base-url') ?: config('app.url', 'http://localhost'), '/');

        $paths = [
            '/',
            '/biblia',
            '/sitemap.xml',
        ];

        foreach ($paths as $path) {
            $url = $base.$path;
            try {
                $start = microtime(true);
                $resp = Http::timeout(30)->get($url);
                $ms = (microtime(true) - $start) * 1000;
                $this->line(sprintf('%s => %d (%0.1f ms)', $url, $resp->status(), $ms));
            } catch (\Throwable $e) {
                $this->error("Failed warming {$url}: ".$e->getMessage());
                Log::warning('warm:pages error', ['url' => $url, 'message' => $e->getMessage()]);
            }
        }

        $this->info('Warmup complete.');

        return self::SUCCESS;
    }
}
