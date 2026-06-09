<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * ==========================================
     * MANAJEMEN USER
     * ==========================================
     */

    /**
     * Menampilkan daftar semua user, role, dan permission
     */
    public function index()
    {
        $users = User::with(['roles', 'sekolah'])->paginate(10);

        // Memuat role beserta permission yang dimilikinya
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $sekolahs = \App\Models\Sekolah::all();

        return view('admin.users.index', compact('users', 'roles', 'permissions', 'sekolahs'));
    }

    public function create()
    {
        $roles = Role::all();
        $sekolahs = \App\Models\Sekolah::all();

        return view('admin.users.tambah', compact('roles', 'sekolahs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'sekolah_id' => 'nullable|exists:sekolahs,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sekolah_id' => $request->sekolah_id,
        ]);

        $user->assignRole($request->role);

        return back()->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
            'sekolah_id' => 'nullable|exists:sekolahs,id',
        ]);

        // 1. Update data sekolah_id di table users
        $user->update([
            'sekolah_id' => $request->sekolah_id,
        ]);

        // 2. Spatie: Sinkronisasi role
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data user '.$user->name.' (Role & Sekolah) berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Keamanan Sistem: Anda tidak bisa menghapus akun yang sedang aktif digunakan.');
        }

        try {
            $name = $user->name;

            if (config('database.default') === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            }

            $user->delete();

            if (config('database.default') === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }

            return redirect()->route('admin.users.index')
                ->with('success', "Akun $name berhasil dihapus dari sistem.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user: '.$e->getMessage());
        }
    }

    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('12345678'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password untuk user $user->name telah berhasil direset menjadi 12345678.");
    }

    /**
     * ==========================================
     * MANAJEMEN ROLE & SINKRONISASI PERMISSION
     * ==========================================
     */
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        // Jika saat membuat role langsung mencentang permissions
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return back()->with('success', 'Role baru berhasil dibuat.');
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Sinkronisasi permission (otomatis hapus yang tidak dicentang dan tambah yang baru)
        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', 'Hak akses (Permission) untuk Role '.$role->name.' berhasil diperbarui.');
    }

    public function destroyRole(Role $role)
    {
        // Proteksi agar role utama sistem tidak terhapus
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Keamanan Sistem: Role Super Admin tidak boleh dihapus.');
        }

        $role->delete();

        return back()->with('success', 'Role berhasil dihapus.');
    }

    /**
     * ==========================================
     * MANAJEMEN MASTER PERMISSION (OPSIONAL)
     * ==========================================
     */
    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        Permission::create(['name' => $request->name]);

        return back()->with('success', 'Data Permission baru berhasil ditambahkan.');
    }

    public function destroyPermission(Permission $permission)
    {
        $permission->delete();

        return back()->with('success', 'Permission berhasil dihapus.');
    }
}
