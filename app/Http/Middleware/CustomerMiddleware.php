<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            $request->session()->put(
                'url.intended',
                $request->isMethod('GET') ? $request->fullUrl() : route('menu'),
            );

            return redirect()
                ->route('login')
                ->with('status', 'Please login to continue your order.');
        }

        abort_unless(
            $request->user()?->role === 'customer' && $request->user()?->is_active,
            403
        );

        return $next($request);
    }
}
