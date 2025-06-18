<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class KaryawanController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'karyawan');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id_karyawan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // HAPUS FILTER SHIFT - tidak diperlukan lagi
        // if ($request->filled('shift_id')) {
        //     $query->where('shift_id', $request->shift_id);
        // }

        $karyawan = $query->orderBy('created_at', 'desc')->paginate(10);

        // Statistics
        $stats = [
            'total' => User::where('role', 'karyawan')->count(),
            'aktif' => User::where('role', 'karyawan')->where('status', 'aktif')->count(),
            'nonaktif' => User::where('role', 'karyawan')->where('status', 'nonaktif')->count(),
        ];

        // HAPUS SHIFTS - tidak diperlukan untuk form
        // $shifts = Shift::where('aktif', true)->orderBy('nama')->get();

        // Generate next employee ID
        $nextEmployeeId = $this->getNextEmployeeId();

        return view('admin.karyawan', compact(
            'karyawan',
            'stats',
            // 'shifts',  // HAPUS INI
            'nextEmployeeId'
        ));
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|string|unique:users,id_karyawan',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'no_hp' => 'required|string',
            // 'shift_id' => 'required|exists:shifts,id', // HAPUS INI
            'tanggal_masuk' => 'required|date',
            'alamat' => 'required|string',
            'password' => 'required|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $userData = [
            'id_karyawan' => $request->id_karyawan,
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'role' => 'karyawan',
            'status' => 'aktif',
            // 'shift_id' => $request->shift_id, // HAPUS INI
            'tanggal_masuk' => $request->tanggal_masuk,
            'alamat' => $request->alamat,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $uploadPath = public_path('uploads/users');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }
            $fileName = time() . '_' . $request->id_karyawan . '.' . $foto->getClientOriginalExtension();
            $foto->move($uploadPath, $fileName);
            $userData['foto'] = 'uploads/users/' . $fileName;
        }

        User::create($userData);

        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan berhasil ditambahkan!');
    }

    /**
     * Update Karyawan
     */
    public function update(Request $request, User $karyawan)
    {
        if ($karyawan->role !== 'karyawan') {
            abort(404);
        }

        $request->validate([
            'id_karyawan' => 'required|string|unique:users,id_karyawan,' . $karyawan->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $karyawan->id,
            'no_hp' => 'required|string',
            // 'shift_id' => 'required|exists:shifts,id', // HAPUS INI
            'tanggal_masuk' => 'required|date',
            'alamat' => 'required|string',
            'status' => 'required|in:aktif,nonaktif',
            'password' => 'nullable|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $updateData = $request->only([
            'id_karyawan',
            'name',
            'email',
            'no_hp',
            // 'shift_id', // HAPUS INI
            'tanggal_masuk',
            'alamat',
            'status'
        ]);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('foto')) {
            if ($karyawan->foto && File::exists(public_path($karyawan->foto))) {
                File::delete(public_path($karyawan->foto));
            }

            $foto = $request->file('foto');
            $uploadPath = public_path('uploads/users');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }
            $fileName = time() . '_' . $request->id_karyawan . '.' . $foto->getClientOriginalExtension();
            $foto->move($uploadPath, $fileName);
            $updateData['foto'] = 'uploads/users/' . $fileName;
        }

        $karyawan->update($updateData);

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil diperbarui!');
    }

    /**
     * Hapus Karyawan
     */
    public function destroy(User $karyawan)
    {
        if ($karyawan->role !== 'karyawan') {
            return redirect()->back()->with('error', 'Tidak bisa menghapus user yang bukan karyawan!');
        }

        if ($karyawan->foto && File::exists(public_path($karyawan->foto))) {
            File::delete(public_path($karyawan->foto));
        }

        $karyawan->delete();
        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan berhasil dihapus!');
    }

    /**
     * Generate ID Karyawan otomatis untuk AJAX
     */
    public function generateIdKaryawan()
    {
        $newId = $this->getNextEmployeeId();
        return response()->json(['id_karyawan' => $newId]);
    }

    /**
     * Bulk actions for multiple employees
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,activate,deactivate',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:users,id'
        ]);

        $employees = User::whereIn('id', $request->employee_ids)->where('role', 'karyawan');

        switch ($request->action) {
            case 'delete':
                foreach ($employees->get() as $employee) {
                    if ($employee->foto && File::exists(public_path($employee->foto))) {
                        File::delete(public_path($employee->foto));
                    }
                }
                $count = $employees->count();
                $employees->delete();
                return redirect()->route('admin.karyawan.index')
                    ->with('success', "Berhasil menghapus {$count} karyawan!");

            case 'activate':
                $count = $employees->update(['status' => 'aktif']);
                return redirect()->route('admin.karyawan.index')
                    ->with('success', "Berhasil mengaktifkan {$count} karyawan!");

            case 'deactivate':
                $count = $employees->update(['status' => 'nonaktif']);
                return redirect()->route('admin.karyawan.index')
                    ->with('success', "Berhasil menonaktifkan {$count} karyawan!");
        }
    }

    /**
     * Export employees data
     */
    public function export(Request $request)
    {
        $query = User::where('role', 'karyawan');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id_karyawan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // HAPUS FILTER SHIFT
        // if ($request->filled('shift_id')) {
        //     $query->where('shift_id', $request->shift_id);
        // }

        $employees = $query->orderBy('created_at', 'desc')->get();

        return $this->exportToCsv($employees);
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($employees)
    {
        $filename = 'karyawan_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($employees) {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, [
                'ID Karyawan',
                'Nama',
                'Email',
                'No HP',
                'Status',
                'Tanggal Masuk',
                'Alamat'
                // 'Shift' // HAPUS INI
            ]);

            // Data
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->id_karyawan,
                    $employee->name,
                    $employee->email,
                    $employee->no_hp,
                    $employee->status,
                    $employee->tanggal_masuk->format('Y-m-d'),
                    $employee->alamat
                    // $employee->shift->nama ?? '-' // HAPUS INI
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate next employee ID
     */
    private function getNextEmployeeId()
    {
        $lastEmployee = User::where('role', 'karyawan')
            ->where('id_karyawan', 'like', 'EMP%')
            ->orderBy('id_karyawan', 'desc')
            ->first();

        if ($lastEmployee) {
            $lastNumber = intval(substr($lastEmployee->id_karyawan, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'EMP' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get employee performance stats
     */
    public function getEmployeeStats(User $employee)
    {
        if ($employee->role !== 'karyawan') {
            return response()->json(['error' => 'Invalid employee'], 404);
        }

        $currentMonth = now();

        $stats = [
            'total_working_days' => $employee->attendances()
                ->whereMonth('tanggal_absen', $currentMonth->month)
                ->whereYear('tanggal_absen', $currentMonth->year)
                ->count(),
            'present_days' => $employee->attendances()
                ->whereMonth('tanggal_absen', $currentMonth->month)
                ->whereYear('tanggal_absen', $currentMonth->year)
                ->whereIn('status_absen', ['hadir', 'terlambat'])
                ->count(),
            'late_days' => $employee->attendances()
                ->whereMonth('tanggal_absen', $currentMonth->month)
                ->whereYear('tanggal_absen', $currentMonth->year)
                ->where('status_absen', 'terlambat')
                ->count(),
            'absent_days' => $employee->attendances()
                ->whereMonth('tanggal_absen', $currentMonth->month)
                ->whereYear('tanggal_absen', $currentMonth->year)
                ->where('status_absen', 'tidak_hadir')
                ->count()
        ];

        return response()->json($stats);
    }
}