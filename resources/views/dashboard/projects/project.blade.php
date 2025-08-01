@extends('app')
@section('content')

    <body class="g-sidenav-show  bg-gray-100">
        <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 "
            id="sidenav-main">
            <div class="sidenav-header">
                <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                    aria-hidden="true" id="iconSidenav"></i>
                <a class="navbar-brand m-0" href=" /" target="s">
                    <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img h-100" alt="main_logo">
                    <span class="ms-1 font-weight-bold">Manaje Wesclic Automation Tools</span>
                </a>
            </div>
            <hr class="horizontal dark mt-0">
            @include('components.sidebar')

        </aside>
        <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
            <!-- Navbar -->
            @include('components.header')
            <!-- End Navbar -->
            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                                <h6>Projects Table</h6>
                                <!-- Button Add Data -->
                                <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary"
                                    style="transition: background-color 0.3s, box-shadow 0.3s;">
                                    <i class="fas fa-plus me-2"></i>Tambahkan Data Project
                                </a>
                            </div>
                            <div class="card-body px-0 pt-0 pb-2">
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0">
                                        <thead>

                                            <tr>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    No</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Nama Project</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Link Asana</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Repo Github</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Repo Github FE</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Repo Github Mobile</th>

                                                <th class="text-secondary opacity-7"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($projects as $project)
                                                <tr>
                                                    <td>
                                                        <h6 class="text-sm mx-3">
                                                            {{ $loop->iteration + ($projects->currentPage() - 1) * $projects->perPage() }}
                                                        </h6>
                                                    </td>
                                                    <td>
                                                        <h6 class="text-sm mx-3">{{ $project->project_name }}</h6>

                                                    </td>
                                                    <td>
                                                        <a href="{{ $project->asana_link }}" target="_blank"
                                                            class="text-xs font-weight-bold mb-0">
                                                            {{ $project->asana_link }}</a>
                                                    </td>

                                                    <td>
                                                        <a href="{{ $project->github_repo_link }} " target="_blank"
                                                            class="text-xs font-weight-bold mb-0">
                                                            {{ $project->github_repo_link }}</a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ $project->github_repo_link }} " target="_blank"
                                                            class="text-xs font-weight-bold mb-0">
                                                            {{ $project->github_repo_link_fe }}</a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ $project->github_repo_link }} " target="_blank"
                                                            class="text-xs font-weight-bold mb-0">
                                                            {{ $project->github_repo_link_mobile }}</a>
                                                    </td>


                                                    <td class="align-middle">
                                                        <div class="ms-auto text-start d-flex flex-column">
                                                            <!-- Tombol Edit -->
                                                            <a class="btn btn-link text-dark mb-0 py-0"
                                                                href="{{ route('projects.edit', ['project' => $project->id]) }}">
                                                                <i class="fas
                                                          fa-pencil-alt text-dark me-2"
                                                                    aria-hidden="true"></i>Edit
                                                            </a>
                                                            <!-- Tombol Delete -->
                                                            <form id="deleteForm-{{ $project->id }}"
                                                                action="{{ route('projects.destroy', ['project' => $project->id]) }}"
                                                                method="POST" style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                            <a class="btn btn-link text-danger text-gradient mb-0 py-2"
                                                                href="javascript:void(0);"
                                                                onclick="confirmDelete('{{ $project->id }}', '{{ addslashes($project->nama_lengkap) }}')">
                                                                <i class="far fa-trash-alt me-2"></i>Delete
                                                            </a>

                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination Links -->
                                <div class="d-flex justify-content-end">
                                    <nav aria-label="Page navigation example">
                                        <ul class="pagination">
                                            {{ $projects->links('pagination::bootstrap-5') }}
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="footer pt-3  ">
                    <div class="container-fluid">
                        <div class="row align-items-center justify-content-lg-between">
                            <div class="col-lg-6 mb-lg-0 mb-4">
                                <div class="copyright text-center text-sm text-muted text-lg-start">
                                    ©
                                    <script>
                                        document.write(new Date().getFullYear())
                                    </script>,
                                    made with <i class="fa fa-heart"></i> by
                                    <a href="https://www.creative-tim.com" class="font-weight-bold" target="_blank">Creative
                                        Tim</a>
                                    for a better web.
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                                    <li class="nav-item">
                                        <a href="https://www.creative-tim.com" class="nav-link text-muted"
                                            target="_blank">Creative Tim</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="https://www.creative-tim.com/presentation" class="nav-link text-muted"
                                            target="_blank">About Us</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="https://www.creative-tim.com/blog" class="nav-link text-muted"
                                            target="_blank">Blog</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="https://www.creative-tim.com/license" class="nav-link pe-0 text-muted"
                                            target="_blank">License</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </main>


        @include('components.script')
        <script>
            function confirmDelete(projectID, projectName) {
                // Konfirmasi SweetAlert
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data project " + projectName + " akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika tombol "Hapus" diklik, kirim formulir untuk menghapus data
                        document.getElementById('deleteForm-' + projectID).submit();
                    }
                });
            }
        </script>
    </body>
@endsection
