<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KaryawanMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isKaryawan()) {
            abort(403, 'Akses ditolak! Hanya karyawan yang bisa mengakses.');
        }
        return $next($request);
    }
}