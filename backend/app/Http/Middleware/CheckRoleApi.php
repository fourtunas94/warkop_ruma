<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRoleApi
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, $roles)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        return $next($request);
    }
}
