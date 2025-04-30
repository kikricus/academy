<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class CheckTokenPresence
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The token expired.'
            ], 401);
        }

        return $next($request);
    }

    /**
     * Попытка аутентификации с Sanctum.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate(Request $request)
    {
        if (auth('sanctum')->guest()) {
            throw new AuthenticationException('Unauthenticated.');
        }
    }
}
