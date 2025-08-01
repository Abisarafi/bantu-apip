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
                                <h6>Employees table</h6>


                                <div>
                                    <!-- Button Sync Jibble -->
                                    <button id="sync-jibble-btn" class="btn btn-sm btn-primary">
                                        <i class="fas fa-sync me-2"></i>Sinkronkan ID Jibble
                                        <span id="sync-spinner" class="spinner-border spinner-border-sm d-none"
                                            role="status" aria-hidden="true"></span>
                                    </button>

                                    <div id="sync-result" class="mt-3 d-none"></div>

                                    <!-- Button Add Data -->
                                    <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary"
                                        style="transition: background-color 0.3s, box-shadow 0.3s;">
                                        <i class="fas fa-plus me-2"></i>Tambahkan Data Karyawan
                                    </a>
                                </div>

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
                                                    Nama Lengkap</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Sync JIbble</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Email Asana</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    No Telepon</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Username Github</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Department</th>
                                                <th class="text-secondary opacity-7"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($employees as $employee)
                                                <tr>
                                                    <td>
                                                        <h6 class="text-sm mx-3">
                                                            {{ $loop->iteration + ($employees->currentPage() - 1) * $employees->perPage() }}
                                                        </h6>
                                                    </td>
                                                    <td>
                                                        <h6 class="text-sm mx-3">{{ $employee->nama_lengkap }}</h6>

                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <h6
                                                            class="badge badge-sm {{ $employee->id_jibble ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                            {{ $employee->id_jibble ? 'True' : 'False' }}
                                                        </h6>

                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">
                                                            {{ $employee->email_asana }}</p>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $employee->no_telepon) }}"
                                                            class="badge badge-sm bg-gradient-success" target="_blank">
                                                            {{ $employee->no_telepon }}
                                                        </a>
                                                    </td>
                                                    <td
                                                        class="align-middle
                                                    text-center">
                                                        <p class="text-xs font-weight-bold mb-0">
                                                            {{ $employee->username_github ?? '-' }}</p>
                                                    </td>
                                                    <td
                                                        class="align-middle
                                                            text-center">
                                                        <p class="text-xs font-weight-bold mb-0">
                                                            {{ $employee->department->name }}</p>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="ms-auto text-start d-flex flex-column">
                                                            <!-- Tombol Edit -->
                                                            <a class="btn btn-link text-dark mb-0 py-0"
                                                                href="{{ route('employees.edit', ['employee' => encrypt($employee->id)]) }}">
                                                                <i class="fas
                                                            fa-pencil-alt text-dark me-2"
                                                                    aria-hidden="true"></i>Edit
                                                            </a>
                                                            <!-- Tombol Delete -->
                                                            <form id="deleteForm-{{ $employee->id }}"
                                                                action="{{ route('employees.destroy', ['employee' => encrypt($employee->id)]) }}"
                                                                method="POST" style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                            <a class="btn btn-link text-danger text-gradient mb-0 py-2"
                                                                href="javascript:void(0);"
                                                                onclick="confirmDelete({{ $employee->id }}, '{{ addslashes($employee->nama_lengkap) }}')">
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
                                            {{ $employees->links('pagination::bootstrap-5') }}
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
            function confirmDelete(employeeID, employeeName) {
                // Konfirmasi SweetAlert
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data karyawan " + employeeName + " akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika tombol "Hapus" diklik, kirim formulir untuk menghapus data
                        document.getElementById('deleteForm-' + employeeID).submit();
                    }
                });
            }
        </script>

        <script>
            $('#sync-jibble-btn').click(function() {
                const btn = $(this);
                const spinner = $('#sync-spinner');
                const resultBox = $('#sync-result');

                // Reset
                resultBox.removeClass('alert alert-success alert-danger').addClass('d-none').text('');

                // Show loading spinner
                spinner.removeClass('d-none');
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('employees.sync-jibble') }}", // Pastikan route-nya benar
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        spinner.addClass('d-none');
                        btn.prop('disabled', false);
                        resultBox
                            .removeClass('d-none')
                            .addClass('alert alert-success')
                            .html(
                                `<strong>Berhasil sinkron:</strong> ${res.jibble_synced} ID Jibble dan ${res.asana_gid_synced} GID Asana data diperbarui.`
                            );
                    },
                    error: function(xhr) {
                        spinner.addClass('d-none');
                        btn.prop('disabled', false);
                        resultBox
                            .removeClass('d-none')
                            .addClass('alert alert-danger')
                            .text(xhr.responseJSON?.message || 'Terjadi kesalahan saat sinkronisasi.');
                    }
                });
            });
        </script>
    </body>
@endsection
