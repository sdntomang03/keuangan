<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua user dan role mereka
     */
    public function index()
    {
        $users = User::with(['roles', 'sekolah'])->paginate(10);
        $roles = \Spatie\Permission\Models\Role::all();
        $sekolahs = \App\Models\Sekolah::all(); // Tambahkan ini

        return view('admin.users.index', compact('users', 'roles', 'sekolahs'));
    }

    /**
     * Menampilkan form edit user & role
     */
    public function edit(User $user)
    {
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update Role User
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
            'sekolah_id' => 'nullable|exists:sekolahs,id', // Tambahkan validasi sekolah
        ]);

        // 1. Update data sekolah_id di table users
        $user->update([
            'sekolah_id' => $request->sekolah_id,
        ]);

        // 2. Spatie: Sinkronisasi role
        $user->syncRoles($request->role);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data user '.$user->name.' (Role & Sekolah) berhasil diperbarui.');
    }

    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::all();
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

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'sekolah_id' => $request->sekolah_id,
        ]);

        $user->assignRole($request->role);

        return back()->with('success', 'User berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        // Cari user berdasarkan ID
        $user = \App\Models\User::findOrFail($id);

        // Proteksi agar tidak menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Keamanan Sistem: Anda tidak bisa menghapus akun yang sedang aktif digunakan.');
        }

        try {
            $name = $user->name;

            // Penanganan Khusus SQLite untuk "Foreign Key Mismatch"
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
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password untuk user $user->name telah berhasil direset menjadi 12345678.");
    }
}
