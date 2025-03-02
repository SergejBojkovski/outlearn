<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (Auth::check() && Auth::user()->role && Auth::user()->role->name === 'admin') {
            return $next($request);
        }
        
        // Redirect to home page with an error message
        return redirect()->route('dashboard')
            ->with('error', 'You do not have permission to access the admin area.');
    }
} 