<?php

namespace App\Support;

use App\Models\User;

class AdminAccess
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function modules(): array
    {
        return config('admin-modules.modules', []);
    }

    public static function moduleExists(string $module): bool
    {
        return array_key_exists($module, self::modules());
    }

    public static function isValid(string $module, string $action): bool
    {
        $actions = self::modules()[$module]['actions'] ?? [];

        return in_array($action, $actions, true);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function allPermissions(): array
    {
        $granted = [];

        foreach (self::modules() as $key => $module) {
            $granted[$key] = $module['actions'];
        }

        return $granted;
    }

    /**
     * @return array<int, array{label: string, items: list<array<string, string>>}>
     */
    public static function navGroups(?User $user): array
    {
        if (! $user?->canAccessAdmin()) {
            return [];
        }

        $groups = [];

        foreach (config('admin-modules.groups', []) as $label => $moduleKeys) {
            $items = [];

            foreach ($moduleKeys as $key) {
                if (! $user->hasPermission($key, 'view')) {
                    continue;
                }

                foreach (self::modules()[$key]['nav'] ?? [] as $link) {
                    $items[] = $link;
                }
            }

            if ($items !== []) {
                $groups[] = [
                    'label' => (string) $label,
                    'items' => $items,
                ];
            }
        }

        return $groups;
    }

    public static function firstAccessibleRoute(User $user): ?string
    {
        foreach (self::navGroups($user) as $group) {
            foreach ($group['items'] as $item) {
                return $item['route'];
            }
        }

        return null;
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    public static function resolve(?string $routeName, string $method = 'GET'): ?array
    {
        if (! is_string($routeName) || $routeName === '') {
            return null;
        }

        $module = self::moduleForRoute($routeName);

        if ($module === null) {
            return null;
        }

        $overrides = config('admin-modules.route_actions', []);
        $action = $overrides[$routeName] ?? self::actionFromRoute($routeName, $method);

        if (! self::isValid($module, $action)) {
            $action = 'view';
        }

        return [$module, $action];
    }

    public static function moduleForRoute(string $routeName): ?string
    {
        $map = config('admin-modules.route_modules', []);

        uksort($map, fn (string $a, string $b) => strlen($b) <=> strlen($a));

        foreach ($map as $prefix => $module) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                return $module;
            }
        }

        return null;
    }

    public static function actionFromRoute(string $routeName, string $method = 'GET'): string
    {
        $last = last(explode('.', $routeName));

        return match ($last) {
            'create', 'store', 'preview' => 'create',
            'edit', 'update', 'add', 'order', 'status' => 'edit',
            'destroy', 'destroy-all', 'remove' => 'delete',
            default => match (strtoupper($method)) {
                'POST' => 'create',
                'PUT', 'PATCH' => 'edit',
                'DELETE' => 'delete',
                default => 'view',
            },
        };
    }

    /**
     * @param  iterable<int, object>  $rows
     * @return array<string, list<string>>
     */
    public static function mapPermissions(iterable $rows): array
    {
        $granted = [];

        foreach ($rows as $row) {
            $module = (string) $row->module;
            $action = (string) $row->action;

            if (! self::isValid($module, $action)) {
                continue;
            }

            $granted[$module] ??= [];

            if (! in_array($action, $granted[$module], true)) {
                $granted[$module][] = $action;
            }
        }

        return $granted;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, list<string>>
     */
    public static function sanitize(array $input): array
    {
        $granted = [];

        foreach ($input as $module => $actions) {
            $module = (string) $module;
            $actions = is_array($actions) ? $actions : [$actions];

            foreach ($actions as $action) {
                $action = (string) $action;

                if (! self::isValid($module, $action)) {
                    continue;
                }

                $granted[$module] ??= [];

                if (! in_array($action, $granted[$module], true)) {
                    $granted[$module][] = $action;
                }
            }

            if (isset($granted[$module]) && ! in_array('view', $granted[$module], true) && in_array('view', self::modules()[$module]['actions'], true)) {
                array_unshift($granted[$module], 'view');
            }
        }

        return $granted;
    }
}
