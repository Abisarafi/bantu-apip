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
        $users = Employee::all(); // ⬅️ ambil semua employee
        return view('dashboard.project-manager.edit-project-manager', compact('pm', 'projects', 'users'));
    }

    // Menyimpan update PM
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:employees,id',
            'email' => 'nullable|email',
            'projects' => 'nullable|array',
            'projects.*.id' => 'nullable|exists:projects,id',
            'projects.*.is_notified' => 'nullable|boolean'
        ]);

        
        $user = Employee::find($validated['user_id']);
        Log::info('Updating PM with user: ', ['user' => $user]);

        $pm = ProyekManajer::findOrFail($id);
        $pm->update([
            'nama_pm' => $user->nama_lengkap,
            'email' => $validated['email'],
        ]);

        // Update proyek terkait
        if ($request->has('projects')) {
            $pivotData = [];
            foreach ($request->projects as $project) {
                if (isset($project['id']) && !empty($project['id'])) {
                    $pivotData[$project['id']] = [
                        'is_notified' => isset($project['is_notified']) ? 1 : 0
                    ];
                }
            }
            $pm->projects()->sync($pivotData);
        }


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
