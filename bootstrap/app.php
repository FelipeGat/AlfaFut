<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\ExigeEmailConfirmado;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'verified' => ExigeEmailConfirmado::class,
        ]);

        // Na API, quem não entrou recebe 401, nunca um redirecionamento para a tela de login.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->map(AuthorizationException::class, fn (AuthorizationException $e) => new AccessDeniedHttpException(
            'Você não tem permissão para esta ação.',
            $e,
        ));

        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        // O texto padrão do 404 traz o nome da classe do modelo.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') || $e instanceof ValidationException || $e instanceof HttpResponseException) {
                return null;
            }

            return match (true) {
                $e instanceof AuthenticationException => response()->json(['message' => 'Faça login para continuar.'], 401),
                $e instanceof NotFoundHttpException => response()->json(['message' => 'Não encontrado.'], 404),
                $e instanceof MethodNotAllowedHttpException => response()->json(['message' => 'Método não permitido nesta rota.'], 405, $e->getHeaders()),
                ! $e instanceof HttpExceptionInterface && ! config('app.debug') => response()->json(['message' => 'Erro interno. Tente de novo em instantes.'], 500),
                default => null,
            };
        });
    })->create();
