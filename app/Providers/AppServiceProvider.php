<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->confiarNosProxies();
        $this->definirLimites();
    }

    private function confiarNosProxies(): void
    {
        $proxies = config('app.proxies_confiaveis');

        if (filled($proxies)) {
            TrustProxies::at($proxies);
        }
    }

    private function definirLimites(): void
    {
        $limite = fn (Limit $limite, ?string $chave) => $limite
            ->by($chave)
            ->response(fn (Request $request, array $headers) => $this->recusarPorLimite($request, $headers));

        RateLimiter::for('login', function (Request $request) use ($limite) {
            $email = $request->input('email');

            return $limite(Limit::perMinute(5), (is_string($email) ? mb_strtolower($email) : '').'|'.$request->ip());
        });

        RateLimiter::for('cadastro', fn (Request $request) => $limite(Limit::perHour(30), $request->ip()));
        RateLimiter::for('recuperacao', fn (Request $request) => $limite(Limit::perMinute(3), $request->ip()));
        RateLimiter::for('reenvio-confirmacao', fn (Request $request) => $limite(Limit::perMinute(6), $request->user()?->id ?: $request->ip()));
        RateLimiter::for('meus-dados', fn (Request $request) => $limite(Limit::perMinute(6), $request->user()?->id ?: $request->ip()));
        RateLimiter::for('entrar-codigo', fn (Request $request) => $limite(Limit::perMinute(10), $request->user()?->id ?: $request->ip()));
    }

    private function recusarPorLimite(Request $request, array $headers): Response
    {
        $segundos = (int) ($headers['Retry-After'] ?? 60);
        $mensagem = "Muitas tentativas. Tente de novo em {$segundos} segundos.";

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $mensagem], 429, $headers);
        }

        return back()
            ->withHeaders($headers)
            ->withInput($request->except(['password', 'password_confirmation']))
            ->withErrors(['email' => $mensagem])
            ->with('erro', $mensagem);
    }
}
