@extends('app')

@section('content')

    <body class="g-sidenav-show bg-gray-100">
        <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3"
            id="sidenav-main">
            @include('components.sidebar')
        </aside>

        <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
            @include('components.header')

            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                                <h6>Tabel KPI</h6>

                                <!-- Filter Form -->
                                <form id="filter-form" class="d-flex align-items-center">
                                    <input type="text" id="date-range" class="form-control" style="max-width: 250px;" />

                                    <input type="hidden" name="start_date" id="start_date">
                                    <input type="hidden" name="end_date" id="end_date">

                                    <button type="submit" class="btn btn-primary ml-3">Filter</button>
                                </form>

                            </div>

                            <div class="card-body px-0 pt-0 pb-2">
                                <div id="employee-loading" class="d-flex justify-content-center align-items-center my-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>

                                <div class="table-responsive p-0 d-none" id="employee-table-wrapper">
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
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Tracking Time</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Story Point Selesai</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Target Working Hour</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Target Story Point</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Duration (%)</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Story Point (%)</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Productivity</th>
                                                <th class="text-secondary opacity-7"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="employee-table-body">
                                            <!-- Data dimuat melalui AJAX -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <div id="pagination-links" class="pagination"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        @include('components.script')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Simulasi ambil data dari API
                const filterForm = document.getElementById("filter-form");

                // Function to load employee data with filters
                function loadEmployeeData() {
                    const startDate = document.getElementById("start_date").value;
                    const endDate = document.getElementById("end_date").value;

                    const tableBody = document.getElementById("employee-table-body");
                    const tableWrapper = document.getElementById("employee-table-wrapper");
                    const loadingSpinner = document.getElementById("employee-loading");

                    // Tampilkan loading, sembunyikan table
                    loadingSpinner.classList.remove("d-none");
                    tableWrapper.classList.add("d-none");
                    tableBody.innerHTML = ""; // Kosongkan isi awal

                    fetch(`/kpi/async?start_date=${startDate}&end_date=${endDate}`)
                        .then(response => response.json())
                        .then(data => {
                            let no = 1;
                            data.data.forEach((item) => {
                                const row = document.createElement("tr");
                                row.innerHTML = `
                    <td><h6 class="text-sm mx-3">${no++}</h6></td>
                    <td><h6 class="text-sm mx-3">${item.name}</h6></td>
                    <td><p class="text-sm mb-0 mx-3">${item.time_tracking}</p></td>
                    <td><p class="text-sm mb-0 mx-3">${item.story_points}</p></td>
                    <td><p class="text-sm mb-0 mx-3">${item.working_hour_target}</p></td>
                    <td><p class="text-sm mb-0 mx-3">${item.story_point_target}</p></td>
                    <td><p class="text-sm mb-0 mx-3">${item.duration}%</p></td>
                    <td><p class="text-sm mb-0 mx-3">${item.story}%</p></td>
                    <td><p class="text-sm mb-0 mx-3">${item.productivity}%</p></td>
                `;
                                tableBody.appendChild(row);
                            });

                            // Sembunyikan loading, tampilkan tabel
                            loadingSpinner.classList.add("d-none");
                            tableWrapper.classList.remove("d-none");
                        })
                        .catch((error) => {
                            console.error("Gagal memuat data:", error);
                            loadingSpinner.classList.add("d-none");
                        });
                }


                // Trigger load on page load
                loadEmployeeData();

                // Event listener for the filter form submission
                filterForm.addEventListener("submit", function(e) {
                    e.preventDefault(); // Prevent default form submission
                    loadEmployeeData(); // Load the filtered data
                });
            });
        </script>
        <script>
            $(function() {
                const start = moment().subtract(29, 'days');
                const end = moment();

                function updateHiddenInputs(start, end) {
                    $('#date-range span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                    $('#start_date').val(start.format('YYYY-MM-DD'));
                    $('#end_date').val(end.format('YYYY-MM-DD'));
                }

                $('#date-range').daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')],
                        'This Year': [moment().startOf('year'), moment()],
                        'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year')
                            .endOf('year')
                        ],
                    }
                }, updateHiddenInputs);

                // Set initial value
                updateHiddenInputs(start, end);
            });
        </script>

        <script>
            function confirmDelete(id, name) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data karyawan " + name + " akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('deleteForm-' + id).submit();
                    }
                });
            }
        </script>
    </body>
@endsection
