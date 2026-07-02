<?php

namespace App\Http\Controllers;

use App\Support\Access;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->whereNotIn('name', Access::hiddenRoles())
            ->withCount(['permissions', 'users'])
            ->orderBy('name')->get();

        return view('pages.roles', ['roles' => $roles]);
    }

    public function create()
    {
        return view('pages.roles-form', [
            'role' => null,
            'groups' => $this->permissionGroups(),
            'assigned' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, creating: true);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles')->with('success', ucfirst($role->name).' role created.');
    }

    public function edit(Role $role)
    {
        abort_if(Access::isHiddenRole($role), 404);

        return view('pages.roles-form', [
            'role' => $role,
            'groups' => $this->permissionGroups(),
            'assigned' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if(Access::isHiddenRole($role), 404);

        $data = $this->validated($request, creating: false);

        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles')->with('success', ucfirst($role->name).' permissions updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if(Access::isHiddenRole($role), 404);

        // Built-in roles are structural — only custom roles can be deleted.
        if (in_array($role->name, ['admin', 'accountant'], true)) {
            return back()->with('error', 'Built-in roles cannot be deleted.');
        }
        if ($role->users()->exists()) {
            return back()->with('error', 'Reassign this role\'s users before deleting it.');
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles')->with('success', 'Role deleted.');
    }

    /**
     * Validate a role write. Permissions are restricted to the assignable set,
     * so no visible role can ever be granted the superadmin-only user/role
     * permissions — even via a crafted request.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $creating): array
    {
        $rules = [
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(Access::assignablePermissionNames())],
        ];

        if ($creating) {
            $rules['name'] = [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name'),
                Rule::notIn(Access::hiddenRoles()),
            ];
            $request->merge(['name' => Str::of($request->input('name', ''))->lower()->trim()->value()]);
        }

        return $request->validate($rules);
    }

    /**
     * Assignable permissions grouped by resource prefix (e.g. "parties" => [...]).
     * The superadmin-only groups (users, roles) are excluded entirely.
     *
     * @return array<string, Collection>
     */
    private function permissionGroups(): array
    {
        return Permission::query()->orderBy('name')->get()
            ->reject(fn (Permission $p) => in_array(explode('.', $p->name)[0], Access::HIDDEN_GROUPS, true))
            ->groupBy(fn (Permission $p) => explode('.', $p->name)[0])
            ->all();
    }
}
