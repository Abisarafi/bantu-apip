<!DOCTYPE html>
<html lang="en">


@include('components.head')

<body class="g-sidenav-show  bg-gray-100">
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 "
        id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href=" /" target="">
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
                        <div class="card-header pb-0">
                            <h6>Edit Employee</h6>
                        </div>
                        <div class="card-body px-5 pt-0 pb-2">
                            <form action="{{ route('employees.update', ['employee' => encrypt($employee->id)]) }}"
                                method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Nama Lengkap -->
                                <div class="mb-3">
                                    <label for="full_name" class="form-label text-xs font-weight-bold">Nama
                                        Lengkap</label>
                                    <input type="text" class="form-control form-control-sm" id="full_name"
                                        name="full_name" value="{{ $employee->nama_lengkap }}">
                                </div>

                                <!-- Email Asana -->
                                <div class="mb-3">
                                    <label for="email_asana" class="form-label text-xs font-weight-bold">Email
                                        Asana</label>
                                    <input type="email" class="form-control form-control-sm" id="email_asana"
                                        name="email_asana" value="{{ $employee->email_asana }}">
                                </div>

                                <!-- Jibble ID -->
                                <div class="mb-3">
                                    <label for="id_jibble" class="form-label text-xs font-weight-bold">Jibble ID</label>
                                    <input type="text" class="form-control form-control-sm" id="id_jibble"
                                        name="id_jibble" value="{{ $employee->id_jibble }}">
                                </div>

                                <!-- Duration  -->
                                <div class="mb-3">
                                    <label for="working_hour_target" class="form-label text-xs font-weight-bold">Working
                                        Hour
                                        Target Monthly (Minutes)</label>
                                    <input type="number" class="form-control form-control-sm" id="working_hour_target"
                                        name="working_hour_target" value="{{ $employee->working_hour_target }}">
                                </div>

                                <!-- Story Point-->
                                <div class="mb-3">
                                    <label for="story_point_target" class="form-label text-xs font-weight-bold">Story
                                        Point
                                        Target Monthly</label>
                                    <input type="number" class="form-control form-control-sm" id="story_point_target"
                                        name="story_point_target" value="{{ $employee->story_point_target }}">
                                </div>

                                <!-- No Telepon -->
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label text-xs font-weight-bold">No
                                        Telepon</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+62</span>
                                        <input type="text" class="form-control form-control-sm" id="phone_number"
                                            name="phone_number" value="{{ $employee->no_telepon }}">
                                    </div>

                                </div>

                                <!-- Username Github -->
                                <div class="mb-3">
                                    <label for="github_username" class="form-label text-xs font-weight-bold">Username
                                        Github</label>
                                    <input type="text" class="form-control form-control-sm" id="github_username"
                                        name="github_username" value="{{ $employee->username_github }}">
                                </div>

                                <!-- Department -->
                                <div class="mb-3">
                                    <label for="department"
                                        class="form-label text-xs font-weight-bold">Department</label>
                                    <select class="form-control form-control-sm" id="department" name="department">
                                        <option value="" disabled selected>Pilih Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ $employee->department_id == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tombol Update -->
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
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
