<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::orderBy('updated_at', 'desc')->paginate(10);
        return view('dashboard.projects.project', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.projects.add-project');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
            // Validasi input
            $validated = $request->validate([
                'project_name' => 'required|string|max:255',
                'asana_link' => [
                    'required',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/app\.asana\.com\/\d+\/\d+\/.+$/'
                ],
                'github_repo_link' => [
                    'required',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/github\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/'
                ],
                'github_repo_link_fe' => [
                    'nullable',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/github\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/'
                ],
                'github_repo_link_mobile' => [
                    'nullable',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/github\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/'
                ],
                'repo_titles.*' => [
                    'nullable',
                    'string',
                    'max:2083',

                ],
                'repo_links.*' => [
                    'nullable',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/github\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/'
                ],
            ], [
                'project_name.required' => 'Nama proyek harus diisi.',
                'project_name.string' => 'Nama proyek harus berupa teks.',
                'project_name.max' => 'Nama proyek maksimal 255 karakter.',

                'asana_link.required' => 'Link Asana harus diisi.',
                'asana_link.string' => 'Link Asana harus berupa teks.',
                'asana_link.max' => 'Link Asana maksimal 2083 karakter.',
                'asana_link.regex' => 'Link Asana harus berupa URL valid yang dimulai dengan "https://app.asana.com/".',

                'github_repo_link.required' => 'Link GitHub harus diisi.',
                'github_repo_link.string' => 'Link GitHub harus berupa teks.',
                'github_repo_link.max' => 'Link GitHub maksimal 2083 karakter.',
                'github_repo_link.regex' => 'Link GitHub harus berupa URL valid yang dimulai dengan "https://github.com/".',

                'github_repo_link_fe.nullable' => 'Link GitHub FE dapat dikosongkan.',
                'github_repo_link_fe.string' => 'Link GitHub FE harus berupa teks.',
                'github_repo_link_fe.max' => 'Link GitHub FE maksimal 2083 karakter.',
                'github_repo_link_fe.regex' => 'Link GitHub FE harus berupa URL valid yang dimulai dengan "https://github.com/".',

                'github_repo_link_mobile.nullable' => 'Link GitHub Mobile dapat dikosongkan.',
                'github_repo_link_mobile.string' => 'Link GitHub Mobile harus berupa teks.',
                'github_repo_link_mobile.max' => 'Link GitHub Mobile maksimal 2083 karakter.',
                'github_repo_link_mobile.regex' => 'Link GitHub Mobile harus berupa URL valid yang dimulai dengan "https://github.com/".',
            ]);

            $gid_project = "";
            if (preg_match('/\/0\/(\d+)\//', $validated['asana_link'], $matches)) {
                // Mendapatkan gid_project
                $gid_project = $matches[1];
            }
            // Simpan data karyawan baru
            $project = Project::create([
                'project_name' => $validated['project_name'],
                'asana_link' => $validated['asana_link'],
                'gid_project' => $gid_project,
                'github_repo_link' => $validated['github_repo_link'],
                'github_repo_link_fe' => $validated['github_repo_link_fe'],
                'github_repo_link_mobile' => $validated['github_repo_link_mobile'],
            ]);

            if (!empty($validated['repo_titles']) && !empty($validated['repo_links'])) {
                foreach ($validated['repo_titles'] as $index => $title) {
                    $link = $validated['repo_links'][$index] ?? null;

                    if ($title || $link) {
                        $project->project_links()->create([
                            'title' => $title,
                            'link' => $link,
                        ]);
                    }
                }
            }
            DB::commit();
            if (!$project) {
                return back()->withErrors(['error' => 'Data Project gagal disimpan.']);
            }
            // Redirect dengan pesan sukses
            return redirect()->route('projects.index')->with('success', 'Data projek berhasil ditambahkan.');
        } catch (\Exception $e) {
            // Tangani exception dan beri pesan error
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $project = Project::with('project_links')->findOrFail($id);



        // $employee = Employee::findOrFail($employeeId);
        return view('dashboard.projects.edit-project', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        try {
            $validated = $request->validate([
                'project_name' => 'required|string|max:255',
                'asana_link' => [
                    'required',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/app\.asana\.com\/\d+\/\d+\/.+$/'
                ],
                'github_repo_link' => [
                    'required',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/github\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/'
                ],
                'github_repo_link_fe' => [
                    'nullable',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/github\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/'
                ],
                'github_repo_link_mobile' => [
                    'nullable',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/github\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/'
                ],
                'repo_titles.*' => [
                    'nullable',
                    'string',
                    'max:2083',

                ],
                'repo_links.*' => [
                    'nullable',
                    'string',
                    'max:2083',
                    'regex:/^https:\/\/github\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/'
                ],
            ], [
                'project_name.required' => 'Nama proyek harus diisi.',
                'project_name.string' => 'Nama proyek harus berupa teks.',
                'project_name.max' => 'Nama proyek maksimal 255 karakter.',

                'asana_link.required' => 'Link Asana harus diisi.',
                'asana_link.string' => 'Link Asana harus berupa teks.',
                'asana_link.max' => 'Link Asana maksimal 2083 karakter.',
                'asana_link.regex' => 'Link Asana harus berupa URL valid yang dimulai dengan "https://app.asana.com/".',

                'github_repo_link.required' => 'Link GitHub harus diisi.',
                'github_repo_link.string' => 'Link GitHub harus berupa teks.',
                'github_repo_link.max' => 'Link GitHub maksimal 2083 karakter.',
                'github_repo_link.regex' => 'Link GitHub harus berupa URL valid yang dimulai dengan "https://github.com/".',

                'github_repo_link_fe.nullable' => 'Link GitHub FE dapat dikosongkan.',
                'github_repo_link_fe.string' => 'Link GitHub FE harus berupa teks.',
                'github_repo_link_fe.max' => 'Link GitHub FE maksimal 2083 karakter.',
                'github_repo_link_fe.regex' => 'Link GitHub FE harus berupa URL valid yang dimulai dengan "https://github.com/".',

                'github_repo_link_mobile.nullable' => 'Link GitHub Mobile dapat dikosongkan.',
                'github_repo_link_mobile.string' => 'Link GitHub Mobile harus berupa teks.',
                'github_repo_link_mobile.max' => 'Link GitHub Mobile maksimal 2083 karakter.',
                'github_repo_link_mobile.regex' => 'Link GitHub Mobile harus berupa URL valid yang dimulai dengan "https://github.com/".',
            ]);

            $gid_project = "";
            if (preg_match('/\/0\/(\d+)\//', $validated['asana_link'], $matches)) {
                // Mendapatkan gid_project
                $gid_project = $matches[1];
            }


            $project = Project::findOrFail($id);

            // Update data project
            $updated_project = $project->update([
                'project_name' => $request->project_name,
                'asana_link' => $request->asana_link,
                'gid_project' => $gid_project,
                'github_repo_link' => $request->github_repo_link,
                'github_repo_link_fe' => $request->github_repo_link_fe,
                'github_repo_link_mobile' => $request->github_repo_link_mobile,

            ]);

            $project->project_links()->delete();

            // Simpan repositories baru jika ada
            if (!empty($validated['repo_titles']) && !empty($validated['repo_links'])) {
                foreach ($validated['repo_titles'] as $index => $title) {
                    $link = $validated['repo_links'][$index] ?? null;

                    if ($title || $link) {
                        $project->project_links()->create([
                            'title' => $title,
                            'link' => $link,
                        ]);
                    }
                }
            }

            if (!$updated_project) {
                return back()->withErrors(['error' => 'Data Project gagal disimpan.']);
            }

            return redirect()->route('projects.index')->with('success', 'Project updated successfully');
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
            $project = Project::findOrFail($id);

            $project->delete();

            // Redirect dengan pesan sukses
            return redirect()->route('projects.index')->with('success', 'Project berhasil dihapus.');
        } catch (\Exception $e) {
            // Tangani error jika terjadi
            return redirect()->route('projects.index')->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}
