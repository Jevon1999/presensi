<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->session()->get('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        if ($user['role'] !== $role) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
