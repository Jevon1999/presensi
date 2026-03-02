<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMember
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->session()->get('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Admins should not access member routes
        if (($user['role'] ?? '') === 'admin') {
            return redirect('/dashboard');
        }

        $member = $request->session()->get('member');

        // No member record or rejected → redirect to apply
        if (!$member || $member['status'] === 'rejected') {
            return redirect('/member/apply');
        }

        // Pending → redirect to pending page
        if ($member['status'] === 'pending') {
            return redirect('/member/pending');
        }

        // Only approved members can access member dashboard
        return $next($request);
    }
}
