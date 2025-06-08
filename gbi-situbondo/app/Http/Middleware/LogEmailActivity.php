<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogEmailActivity
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('notifikasi.*')) {
            Log::channel('notifications')->info('Notification action accessed', [
                'user' => auth()->user()->email ?? 'guest',
                'route' => $request->route()->getName(),
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);
        }

        return $next($request);
    }
}