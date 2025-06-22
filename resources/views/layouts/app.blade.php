<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Pastikan ini ada! --}}
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        /* Gaya untuk tombol "Kirim Disposisi Via Email" saat di-hover */
        .btn-outline-info:hover {
            color: white !important; /* Mengubah warna teks menjadi putih */
            background-color: #17a2b8; /* Mengubah warna latar belakang menjadi warna info Bootstrap */
        }

        /* Pastikan ikon juga berubah warna jika ada */
        .btn-outline-info:hover .fas {
            color: white !important;
        }

        /* Gaya untuk tombol "Kirim Disposisi Via Email" ketika dropdown terbuka */
        .btn-outline-info.show {
            color: white !important; /* Pastikan teks putih saat dropdown terbuka */
            background-color: #17a2b8; /* Pastikan background juga tetap info color */
        }

        /* Pastikan ikon juga putih saat dropdown terbuka */
        .btn-outline-info.show .fas {
            color: white !important;
        }

        /* Gaya untuk item dropdown WhatsApp saat di-hover */
        .dropdown-item:hover {
            background-color: #e9ecef; /* Warna latar belakang abu-abu terang */
            color: #28a745; /* Warna teks biru Bootstrap primary */
        }

        /* Pastikan juga aktif saat diklik (focus) jika diperlukan */
        .dropdown-item:focus {
            background-color: #e9ecef;
            color: #007bff;
        }

        button[type="submit"] {
            padding: 8px 16px;
            color: #333;
            border: none;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #e9ecef;
            color: #007bff;
        }
    </style>
</head>

<body>
    @unless (request()->is('login'))
        <nav class="navbar navbar-expand-lg navbar-dark">
            </nav>
    @endunless

    <main>
        @yield('content')
    </main>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="liveToastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>

        <div id="liveToastError" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    {{-- SweetAlert2 dihapus karena tidak digunakan --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

    @stack('scripts') {{-- Pastikan ini tetap ada untuk script spesifik halaman --}}
</body>

</html>