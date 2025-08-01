@extends('app')
@section('content')

    <body class="g-sidenav-show  bg-gray-100">
        <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3"
            id="sidenav-main">
            @include('components.sidebar')
        </aside>

        <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
            @include('components.header')

            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                                <h6>Daftar Project Manager</h6>
                                <a href="{{ route('project-managers.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-2"></i> Tambah PM
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
                                                    Nama PM</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Email</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Jumlah Proyek</th>
                                                <th class="text-secondary opacity-7"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($managers as $pm)
                                                <tr>
                                                    <td>
                                                        <h6 class="text-sm mx-3">{{ $loop->iteration }}</h6>
                                                    </td>
                                                    <td>
                                                        <h6 class="text-sm mx-3">{{ $pm->nama_pm }}</h6>
                                                    </td>
                                                    <td>
                                                        <h6 class="text-sm mx-3">{{ $pm->email }}</h6>
                                                    </td>
                                                    <td>
                                                        <h6 class="text-sm mx-3">{{ $pm->projects->count() }}</h6>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="ms-auto text-start d-flex flex-column">
                                                            <a class="btn btn-link text-dark mb-0 py-0"
                                                                href="{{ route('project-managers.edit', $pm->id) }}">
                                                                <i class="fas fa-pencil-alt text-dark me-2"></i>Edit
                                                            </a>
                                                            <form id="deleteForm-{{ $pm->id }}"
                                                                action="{{ route('project-managers.destroy', $pm->id) }}"
                                                                method="POST" style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                            <a class="btn btn-link text-danger text-gradient mb-0 py-2"
                                                                href="javascript:void(0);"
                                                                onclick="confirmDelete('{{ $pm->id }}', '{{ addslashes($pm->nama_pm) }}')">
                                                                <i class="far fa-trash-alt me-2"></i>Delete
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if ($managers->isEmpty())
                                                <tr>
                                                    <td colspan="5" class="text-center text-sm text-muted py-3">Belum ada
                                                        Project Manager.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                {{-- Optional Pagination --}}
                                {{-- <div class="d-flex justify-content-end">
                                {{ $managers->links('pagination::bootstrap-5') }}
                            </div> --}}
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
            function confirmDelete(pmID, pmName) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "PM " + pmName + " akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('deleteForm-' + pmID).submit();
                    }
                });
            }
        </script>
    </body>
@endsection
