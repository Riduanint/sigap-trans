<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna sistem (RBAC)
     */
    public function index(Request $request): View
    {
        $regencies = Regency::orderBy('id')->get();

        $query = User::with('regency');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%");
            });
        }

        $users = $query->orderByRaw("
            CASE 
                WHEN role = 'super_admin' THEN 1
                WHEN role = 'eksekutif' THEN 2
                WHEN role = 'operator_kabupaten' THEN 3
                WHEN role = 'mitra_bpn' THEN 4
                ELSE 5
            END
        ")->orderBy('name')->paginate(15)->withQueryString();

        $counts = [
            'total' => User::count(),
            'super_admin' => User::where('role', 'super_admin')->count(),
            'operator' => User::where('role', 'operator_kabupaten')->count(),
            'eksekutif' => User::where('role', 'eksekutif')->count(),
            'bpn' => User::where('role', 'mitra_bpn')->count(),
        ];

        return view('admin.users.users', compact('users', 'regencies', 'counts'));
    }

    /**
     * Tampilkan formulir tambah akun pengguna baru
     */
    public function create(): View
    {
        $regencies = Regency::orderBy('id')->get();
        return view('admin.users.create', compact('regencies'));
    }

    /**
     * Simpan akun pengguna baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,operator_kabupaten,eksekutif,mitra_bpn',
            'regency_id' => 'nullable|required_if:role,operator_kabupaten|exists:regencies,id',
            'nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
            'position' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ], [
            'regency_id.required_if' => 'Wilayah kabupaten wajib dipilih untuk peran Operator Kabupaten.',
            'email.unique' => 'Email ini telah terdaftar.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'regency_id' => $validated['role'] === 'operator_kabupaten' ? $validated['regency_id'] : null,
            'nip' => $validated['nip'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::log('CREATE_USER', 'users', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'regency_id' => $user->regency_id,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun pengguna '{$user->name}' berhasil ditambahkan ke dalam sistem!");
    }

    /**
     * Tampilkan formulir edit akun pengguna
     */
    public function edit(string $id): View
    {
        $user = User::with('regency')->findOrFail($id);
        $regencies = Regency::orderBy('id')->get();

        return view('admin.users.edit', compact('user', 'regencies'));
    }

    /**
     * Perbarui akun pengguna
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:super_admin,operator_kabupaten,eksekutif,mitra_bpn',
            'regency_id' => 'nullable|required_if:role,operator_kabupaten|exists:regencies,id',
            'nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
            'position' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'regency_id' => $validated['role'] === 'operator_kabupaten' ? $validated['regency_id'] : null,
            'nip' => $validated['nip'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        AuditLog::log('UPDATE_USER', 'users', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Data akun pengguna '{$user->name}' berhasil diperbarui!");
    }

    /**
     * Ubah status aktif/nonaktif pengguna secara cepat
     */
    public function toggleStatus(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::log('TOGGLE_USER_STATUS', 'users', $user->id, [
            'name' => $user->name,
            'new_status' => $user->is_active,
        ]);

        return back()->with('success', "Akun {$user->name} berhasil {$statusText}.");
    }

    /**
     * Hapus akun pengguna
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        AuditLog::log('DELETE_USER', 'users', $id, [
            'name' => $userName,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun pengguna '{$userName}' berhasil dihapus dari sistem.");
    }
}
