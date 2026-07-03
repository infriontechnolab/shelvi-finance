<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * One source of truth for the access hierarchy.
 *
 * The `superadmin` role is the owner account: it holds every permission but is
 * kept out of every management surface (users list, roles list, role pickers,
 * direct edit URLs) so the client never learns it exists. User & role
 * administration is superadmin-only, so those permission groups are also hidden
 * from the visible role matrix — no admin/custom role can be granted them.
 */
final class Access
{
    /** The hidden owner role. Never surfaced in the UI. */
    public const SUPERADMIN = 'superadmin';

    /** Permission prefixes only the superadmin may hold — hidden from the matrix. */
    public const HIDDEN_GROUPS = ['users', 'roles', 'trash'];

    /** Role names that must never appear in, or be reachable through, the UI. */
    public static function hiddenRoles(): array
    {
        return [self::SUPERADMIN];
    }

    /** Is this user the hidden owner? (superadmin-only surfaces gate on this.) */
    public static function isSuperAdmin(?User $user): bool
    {
        return $user !== null && $user->hasRole(self::SUPERADMIN);
    }

    /** Is this a role the UI must pretend does not exist? */
    public static function isHiddenRole(?Role $role): bool
    {
        return $role !== null && in_array($role->name, self::hiddenRoles(), true);
    }

    /** Permission names offered in the role matrix (excludes users.*, roles.*). */
    public static function assignablePermissionNames(): array
    {
        return Permission::query()->orderBy('name')->pluck('name')
            ->reject(fn (string $name) => in_array(explode('.', $name)[0], self::HIDDEN_GROUPS, true))
            ->values()->all();
    }
}
