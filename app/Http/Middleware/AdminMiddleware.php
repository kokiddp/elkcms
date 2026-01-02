<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to access the admin panel.');
        }

        // Check if user has admin role or super-admin role
        if (! Auth::user()->hasAnyRole(['admin', 'super-admin', 'editor'])) {
            Log::warning('Unauthorized admin access attempt', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            
            abort(403, 'Unauthorized access to admin panel.');
        }

        return $next($request);
    }
}
