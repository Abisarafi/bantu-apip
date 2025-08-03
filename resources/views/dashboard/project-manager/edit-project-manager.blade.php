<!DOCTYPE html>
<html lang="en">
@include('components.head')

<body class="g-sidenav-show bg-gray-100">
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3"
        id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href="/">
                <img src="../../assets/img/logo-ct-dark.png" class="navbar-brand-img h-100" alt="main_logo">
                <span class="ms-1 font-weight-bold">Manaje Wesclic Automation Tools</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0">
        @include('components.sidebar')
    </aside>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('components.header')

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Edit Project Manager</h6>
                        </div>
                        <div class="card-body px-5 pt-0 pb-2">
                            <form action="{{ route('project-managers.update', $pm->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="user_id" class="form-label text-xs font-weight-bold">Pilih User</label>
                                    <select class="form-select form-select-sm select2" id="user_id" name="user_id"
                                        required>
                                        <option value="" disabled>-- Pilih User --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ $pm->user_id == $user->id ? 'selected' : '' }}>
                                                {{ $user->nama_lengkap }} ({{ $user->email_asana }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label text-xs font-weight-bold">Email</label>
                                    <input type="email" class="form-control form-control-sm" id="email"
                                        name="email" value="{{ $pm->email }}" required>
                                </div>

                                <!-- Pilihan Proyek -->
                                <div class="mb-3">
                                    <label class="form-label text-xs font-weight-bold">Proyek yang Dikelola</label>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Nama Proyek</th>
                                                    <th>Notifikasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($projects as $project)
                                                    @php
                                                        $isChecked = $pm->projects->contains($project->id);
                                                        $notified = $pm->projects->firstWhere('id', $project->id)
                                                            ?->pivot?->notified;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox"
                                                                name="projects[{{ $project->id }}][id]"
                                                                value="{{ $project->id }}"
                                                                {{ $isChecked ? 'checked' : '' }}>
                                                            {{ $project->project_name }}
                                                        </td>
                                                        <td>
                                                            <input type="hidden"
                                                                name="projects[{{ $project->id }}][is_notified]"
                                                                value="0">
                                                            <input type="checkbox"
                                                                name="projects[{{ $project->id }}][is_notified]"
                                                                value="1" {{ $notified ? 'checked' : '' }}>
                                                            Kirim Notifikasi
                                                        </td>
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Tombol Submit -->
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-sm btn-success">Update Data</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('components.script')
</body>

</html>
