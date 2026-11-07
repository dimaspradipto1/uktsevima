<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Models\Institusi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class UserController extends Controller
{
    /**
     * Tampilkan daftar data pengguna menggunakan Yajra DataTables.
     */
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('pages.users.index');
    }

    /**
     * Form tambah pengguna baru.
     */
    public function create()
    {
        $institusis = Institusi::where('status', true)->get();
        return view('pages.users.create', compact('institusis'));
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'institusi_id' => 'nullable|exists:institusis,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,mahasiswa',
        ], [
            'institusi_id.exists' => 'Institusi yang dipilih tidak valid.',
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak valid.',
        ]);

        User::create([
            'institusi_id' => $validated['institusi_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        Alert::success('Berhasil', 'Pengguna baru berhasil ditambahkan.');
        return redirect()->route('users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Form edit pengguna (Profil, Nama, Email, Role).
     */
    public function edit(User $user)
    {
        $institusis = Institusi::all();
        return view('pages.users.edit', compact('user', 'institusis'));
    }

    /**
     * Update data profil pengguna (Nama, Email, Role).
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'institusi_id' => 'nullable|exists:institusis,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,mahasiswa',
        ], [
            'institusi_id.exists' => 'Institusi yang dipilih tidak valid.',
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak valid.',
        ]);

        $user->update([
            'institusi_id' => $validated['institusi_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        Alert::success('Berhasil', 'Data pengguna berhasil diperbarui.');
        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Halaman terpisah ubah password pengguna.
     */
    public function editPassword(User $user)
    {
        return view('pages.users.password', compact('user'));
    }

    /**
     * Proses update password pengguna secara terpisah.
     */
    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        Alert::success('Berhasil', 'Password pengguna ' . $user->name . ' berhasil diperbarui.');
        return redirect()->route('users.index')->with('success', 'Password pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user)
    {
        // Mencegah user menghapus akunnya sendiri saat sedang login
        if (Auth::id() === $user->id) {
            Alert::error('Gagal', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        $user->delete();

        Alert::success('Berhasil', 'Pengguna berhasil dihapus.');
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
