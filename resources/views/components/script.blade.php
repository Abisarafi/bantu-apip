<!--   Core JS Files   -->
<script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Github buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

<script>
    $(document).ready(function() {
        $('#user_id').select2({
            placeholder: "-- Pilih User --",
            allowClear: true,
            width: '100%' // biar responsif
        });
    });
</script>

<!-- Moment.js -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<!-- Date Range Picker JS -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.all.min.js"></script>
@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sukses',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
@endif
@if ($errors->any())
    <script>
        // Gabungkan semua error dalam satu pesan
        let errorMessages = '';
        @foreach ($errors->all() as $error)
            errorMessages += '• {{ $error }}\n';
        @endforeach

        // Tampilkan SweetAlert dengan semua pesan error
        Swal.fire({
            title: 'Terjadi Kesalahan!',
            text: errorMessages,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
@endif
