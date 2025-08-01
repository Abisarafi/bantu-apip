<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use Illuminate\Http\Request;


class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::orderBy('updated_at', 'desc')->paginate(10);
        return view('dashboard.employee', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        return view('dashboard.add-employee', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email_asana' => 'required|email|max:255',
                'id_jibble' => 'nullable|string|max:36',
                'phone_number' => 'required|numeric|digits_between:10,15',
                'github_username' => 'nullable|string|max:39|regex:/^[a-zA-Z0-9](?!.*--)[a-zA-Z0-9-]{0,37}[a-zA-Z0-9]$/',
                'department' => 'required|exists:departments,id',
                'story_point_target' => 'nullable|integer',
                'working_hour_target' => 'nullable|integer',
            ]);

            // Ubah nomor telepon jika dimulai dengan 08 menjadi 628
            $phoneNumber = $validated['phone_number'];
            if (str_starts_with($phoneNumber, '0')) {
                $phoneNumber = '62' . substr($phoneNumber, 1);
            } else {
                $phoneNumber = '62' . $phoneNumber;
            }
            // Simpan data karyawan baru
            $employee = Employee::create([
                'nama_lengkap' => $validated['full_name'],
                'email_asana' => $validated['email_asana'],
                'no_telepon' => $phoneNumber,
                'username_github' => $validated['github_username'],
                'department_id' => $validated['department'],
                'id_jibble' => $validated['id_jibble'],
                'story_point_target' => $validated['story_point_target'] ?? 0,
                'working_hour_target' => $validated['working_hour_target'] ?? 0
            ]);

            if (!$employee) {
                return back()->withErrors(['error' => 'Data karyawan gagal disimpan.']);
            }
            // Redirect dengan pesan sukses
            return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil ditambahkan.');
        } catch (\Exception $e) {
            // Tangani exception dan beri pesan error
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Employee $employee) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_encrypt)
    {


        $employeeId = decrypt($id_encrypt);

        $employee = Employee::findOrFail($employeeId);
        $departments = Department::all();

        $phoneNumber = $employee->no_telepon;
        if (substr($phoneNumber, 0, 2) == '62') {
            $phoneNumber = substr($phoneNumber, 2); // Hapus '62' dari awal nomor
        }

        $employee->no_telepon = $phoneNumber;

        // $employee = Employee::findOrFail($employeeId);
        return view('dashboard.edit-employee', compact('employee', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'id_jibble' => 'nullable|string|max:36',
                'email_asana' => 'required|email|max:255',
                'phone_number' => 'required|numeric|digits_between:10,15',
                'github_username' => 'nullable|string|max:39|regex:/^[a-zA-Z0-9](?!.*--)[a-zA-Z0-9-]{0,37}[a-zA-Z0-9]$/',
                'department' => 'required|exists:departments,id',
                'story_point_target' => 'nullable|integer',
                'working_hour_target' => 'nullable|integer',
            ]);

            $phoneNumber = $validated['phone_number'];
            if (str_starts_with($phoneNumber, '0')) {
                $phoneNumber = '62' . substr($phoneNumber, 1);
            } else {
                $phoneNumber = '62' . $phoneNumber;
            }

            // Dekripsi ID
            $employeeId = decrypt($id);

            $employee = Employee::findOrFail($employeeId);

            // Update data employee
            $updated_employee = $employee->update([
                'nama_lengkap' => $request->full_name,
                'email_asana' => $request->email_asana,
                'no_telepon' => $phoneNumber,
                'username_github' => $validated['github_username'],
                'department_id' => $request->department, // Menyimpan ID department yang dipilih
                'id_jibble' => $validated['id_jibble'],
                'story_point_target' => $validated['story_point_target'] ?? 0,
                'working_hour_target' => $validated['working_hour_target'] ?? 0
            ]);

            if (!$updated_employee) {
                return back()->withErrors(['error' => 'Data karyawan gagal disimpan.']);
            }

            return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
        } catch (\Exception $e) {
            // Tangani exception dan beri pesan error
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $employeeId = decrypt($id);
            // Temukan karyawan berdasarkan ID dan hapus
            $employee = Employee::findOrFail($employeeId);

            $employee->delete();

            // Redirect dengan pesan sukses
            return redirect()->route('employees.index')->with('success', 'Karyawan berhasil dihapus.');
        } catch (\Exception $e) {
            // Tangani error jika terjadi
            return redirect()->route('employees.index')->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}
