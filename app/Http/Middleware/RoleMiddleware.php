<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware phân quyền theo role.
 * Cách dùng trong route: ->middleware('role:admin') hoặc 'role:admin,customer'
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return ApiResponse::error('Bạn không có quyền truy cập chức năng này', 403);
        }

        return $next($request);
    }
}
