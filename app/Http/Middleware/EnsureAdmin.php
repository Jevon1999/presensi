<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->session()->get('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (($user['role'] ?? '') !== 'admin') {
            // Redirect non-admin users to their appropriate page
            $member = $request->session()->get('member');
            if ($member && $member['status'] === 'approved') {
                return redirect('/member/dashboard');
            }
            if ($member && $member['status'] === 'pending') {
                return redirect('/member/pending');
            }
            return redirect('/member/apply');
        }

        return $next($request);
    }
}
