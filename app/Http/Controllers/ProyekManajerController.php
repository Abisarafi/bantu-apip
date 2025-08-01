<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProyekManajer;
use App\Models\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProyekManajerController extends Controller
{
    // Menampilkan semua PM
    public function index()
    {
        $managers = ProyekManajer::with('projects')->get();
        return view('dashboard.project-manager.project-manager', compact('managers'));
    }

    // Menampilkan form tambah PM
    public function create()
    {
        $projects = Project::all();
        $users = Employee::all();
        return view('dashboard.project-manager.add-project-manager', compact('projects', 'users'));
    }

    // Menyimpan PM baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:employees,id',
            'email' => 'required|email',
        ]);

        $user = Employee::find($validated['user_id']);

        $pm = ProyekManajer::create([
            'user_id' => $user->id,
            'nama_pm' => $user->nama_lengkap, // Nama diambil dari user
            'email' => $validated['email'],
        ]);

        // Simpan relasi proyek
        if ($request->has('projects')) {
            $pivotData = [];
            Log::info('tes tes ');
            Log::info('Projects to sync: ', $request->projects);
            foreach ($request->projects as $projectId) {
                $pivotData[$projectId] = [
                    'id' => Str::uuid(),        // generate UUID
                    'is_notified' => true,
                ];
            }
            $pm->projects()->syncWithoutDetaching($pivotData);
        }

        return redirect()->route('project-managers.index')->with('success', 'PM berhasil ditambahkan.');
    }

    // Menampilkan form edit PM
    public function edit($id)
    {
        $pm = ProyekManajer::with('projects')->findOrFail($id);
        $projects = Project::all();
        return view('project_managers.edit', compact('pm', 'projects'));
    }

    // Menyimpan update PM
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pm' => 'required|string|max:255',
            'email' => 'nullable|email',
            'projects' => 'nullable|array',
            'projects.*.id' => 'required|exists:projects,id',
            'projects.*.is_notified' => 'required|boolean'
        ]);

        $pm = ProyekManajer::findOrFail($id);
        $pm->update([
            'nama_pm' => $request->nama_pm,
            'email' => $request->email,
        ]);

        // Update proyek terkait
        $pivotData = [];
        foreach ($request->projects as $p) {
            $pivotData[$p['id']] = ['is_notified' => $p['is_notified']];
        }
        $pm->projects()->sync($pivotData);

        return redirect()->route('project-managers.index')->with('success', 'PM berhasil diperbarui.');
    }

    // Menghapus PM
    public function destroy($id)
    {
        $pm = ProyekManajer::findOrFail($id);
        $pm->delete();

        return redirect()->route('project-managers.index')->with('success', 'PM berhasil dihapus.');
    }
}
