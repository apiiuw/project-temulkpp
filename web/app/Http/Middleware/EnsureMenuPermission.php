<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\RoleMenu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class EnsureMenuPermission
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $routeName = $request->route()?->getName();

        $allowed = $routeName !== null && RoleMenu::query()
            ->where('role', $role)
            ->with('menu:id,route')
            ->get()
            ->contains(function (RoleMenu $permission) use ($routeName): bool {
                if ($permission->menu === null) {
                    return false;
                }

                $menuRoute = $permission->menu->route;
                $menuPrefix = Str::beforeLast($menuRoute, '.');

                return $menuRoute === $routeName
                    || ($menuPrefix !== $menuRoute && Str::startsWith($routeName, $menuPrefix . '.'));
            });

        if (! $allowed) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
