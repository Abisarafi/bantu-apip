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
                <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img h-100" alt="main_logo">
                <span class="ms-1 font-weight-bold">Manaje Wesclic Automation Tools</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0">
        @include('components.sidebar')
    </aside>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        @include('components.header')
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Edit Project</h6>
                        </div>
                        <div class="card-body px-5 pt-0 pb-2">
                            <form action="{{ route('projects.update', ['project' => $project->id]) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Nama Project -->
                                <div class="mb-3">
                                    <label for="project_name" class="form-label text-xs font-weight-bold">Nama
                                        Project</label>
                                    <input type="text" class="form-control form-control-sm" id="project_name"
                                        name="project_name" value="{{ old('project_name', $project->project_name) }}">
                                </div>

                                <!-- Link Asana -->
                                <div class="mb-3">
                                    <label for="asana_link" class="form-label text-xs font-weight-bold">Link
                                        Asana</label>
                                    <input type="text" class="form-control form-control-sm" id="asana_link"
                                        name="asana_link" value="{{ old('asana_link', $project->asana_link) }}">
                                </div>

                                <!-- Link Github BE/Fullstack -->
                                <div class="mb-3">
                                    <label for="github_repo_link" class="form-label text-xs font-weight-bold">Link
                                        Github BE/Fullstack</label>
                                    <input type="text" class="form-control form-control-sm" id="github_repo_link"
                                        name="github_repo_link"
                                        value="{{ old('github_repo_link', $project->github_repo_link) }}">
                                </div>

                                <!-- Link Github FE -->
                                <div class="mb-3">
                                    <label for="github_repo_link_fe" class="form-label text-xs font-weight-bold">Link
                                        Github FE</label>
                                    <input type="text" class="form-control form-control-sm" id="github_repo_link_fe"
                                        name="github_repo_link_fe"
                                        value="{{ old('github_repo_link_fe', $project->github_repo_link_fe) }}">
                                </div>

                                <!-- Link Github Mobile -->
                                <div class="mb-3">
                                    <label for="github_repo_link_mobile"
                                        class="form-label text-xs font-weight-bold">Link
                                        Github Mobile</label>
                                    <input type="text" class="form-control form-control-sm"
                                        id="github_repo_link_mobile" name="github_repo_link_mobile"
                                        value="{{ old('github_repo_link_mobile', $project->github_repo_link_mobile) }}">
                                </div>

                                <!-- Repositories Tambahan -->
                                <div id="github-repos-container">
                                    @foreach ($project->project_links as $index => $repository)
                                        <div class="mb-3 repo-input">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="repo_title_{{ $index }}"
                                                        class="form-label text-xs font-weight-bold">Judul Repo</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="repo_title_{{ $index }}" name="repo_titles[]"
                                                        value="{{ old('repo_titles.' . $index, $repository->title) }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="repo_link_{{ $index }}"
                                                        class="form-label text-xs font-weight-bold">Link Repo</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control form-control-sm"
                                                            id="repo_link_{{ $index }}" name="repo_links[]"
                                                            value="{{ old('repo_links.' . $index, $repository->link) }}">
                                                        <button type="button"
                                                            class="btn btn-danger btn-sm remove-repo">Hapus</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-success mb-3" id="add-repo">Tambah Repo
                                    Tambahan</button>

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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const repoContainer = document.getElementById('github-repos-container');
            const addRepoButton = document.getElementById('add-repo');

            let repoCount = {{ $project->project_links->count() }};

            // Function to add a new repository input field
            addRepoButton.addEventListener('click', function() {
                const newRepoDiv = document.createElement('div');
                newRepoDiv.classList.add('mb-3', 'repo-input');

                newRepoDiv.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label for="repo_title_${repoCount}" class="form-label text-xs font-weight-bold">Judul Repo</label>
                            <input type="text" class="form-control form-control-sm" id="repo_title_${repoCount}" name="repo_titles[]">
                        </div>
                        <div class="col-md-6">
                            <label for="repo_link_${repoCount}" class="form-label text-xs font-weight-bold">Link Repo</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="repo_link_${repoCount}" name="repo_links[]">
                                <button type="button" class="btn btn-danger btn-sm remove-repo">Hapus</button>
                            </div>
                        </div>
                    </div>
                `;

                repoContainer.appendChild(newRepoDiv);
                repoCount++;
            });

            // Event delegation for removing repository inputs
            repoContainer.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-repo')) {
                    const repoInput = event.target.closest('.repo-input');
                    repoInput.remove();
                }
            });
        });
    </script>
</body>

</html>
