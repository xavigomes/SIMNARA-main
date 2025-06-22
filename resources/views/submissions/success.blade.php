@extends('layouts.app')

@section('content')


    <div class="container py-5"> {{-- Added vertical padding --}}
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-7"> {{-- Increased width for better content flow --}}
                <div class="card shadow-lg border-0 animate__animated animate__fadeInUp"> {{-- Added border-0, shadow-lg, and Animate.css class (if used) --}}
                    <div class="card-header bg-success text-white text-center py-3 rounded-top-1x"> {{-- Centered header text, added custom rounded class --}}
                        <h4 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2 fa-lg"></i>Pengajuan Berhasil Dibuat!</h4> {{-- Larger icon --}}
                    </div>
                    <div class="card-body text-center p-4 p-md-5"> {{-- Increased padding --}}
                        <div class="icon-box-large mb-4 mx-auto"> {{-- New larger icon box --}}
                            <i class="fas fa-file-invoice fa-3x"></i>
                        </div>
                        <p class="lead mb-4">Surat tugas Anda atas nama <strong class="text-primary">{{ $submission->nama }}</strong> telah berhasil diajukan.</p> {{-- Highlighted name, larger margin --}}
                        <p class="text-muted mb-4">Anda dapat mengirimkan detail disposisi ini ke pihak terkait melalui email atau WhatsApp untuk proses tindak lanjut.</p> {{-- Clarified instruction --}}

                        <div class="detail-section mb-5 p-3 rounded bg-light-subtle border border-light"> {{-- New section for details --}}
                            <h6 class="text-dark fw-bold mb-3">Rincian Pengajuan Anda:</h6>
                            <div class="row text-start justify-content-center">
                                <div class="col-sm-10 col-md-8">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-6 text-muted">Nama Pemohon:</dt>
                                        <dd class="col-sm-6 fw-semibold text-dark">{{ $submission->nama }}</dd>

                                        <dt class="col-sm-6 text-muted">Tanggal Pengajuan:</dt>
                                        <dd class="col-sm-6 fw-semibold text-dark">{{ \Carbon\Carbon::parse($submission->created_at)->format('d F Y, H:i') }}</dd>

                                        <dt class="col-sm-6 text-muted">Status:</dt>
                                        <dd class="col-sm-6">
                                        @php
                                            $statusClass = '';
                                            $statusTextClass = '';
                                            $iconClass = '';
                                            switch ($submission->status) {
                                                case 'approved':
                                                case 'Approved': // Pastikan menangani huruf besar/kecil jika ada
                                                    $statusClass = 'bg-success-subtle text-success';
                                                    $iconClass = 'fas fa-check-circle';
                                                    break;
                                                case 'pending':
                                                case 'Pending':
                                                    $statusClass = 'bg-warning-subtle text-warning';
                                                    $iconClass = 'fas fa-clock';
                                                    break;
                                                case 'rejected':
                                                case 'Rejected':
                                                    $statusClass = 'bg-danger-subtle text-danger';
                                                    $iconClass = 'fas fa-times-circle';
                                                    break;
                                                default: // Default case for other statuses
                                                    $statusClass = 'bg-secondary-subtle text-secondary';
                                                    $iconClass = 'fas fa-info-circle';
                                                    break;
                                            }
                                        @endphp
                                        <span class="badge {{ $statusClass }} fw-bold p-2 px-3 rounded-pill shadow-sm animate__animated animate__pulse animate__infinite">
                                            <i class="{{ $iconClass }} me-1"></i>{{ ucfirst($submission->status) }} {{-- Menggunakan ucfirst untuk kapitalisasi huruf pertama --}}
                                        </span>
                                        </dd>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-dark fw-bold mb-3">Bagikan Disposisi:</h6>
                        <div class="d-grid gap-3 d-md-flex justify-content-center mb-4"> {{-- Changed to d-grid for better stacking on small screens --}}
                            {{-- Dropdown untuk Kirim Disposisi Via Email (SMTP Server-Side) --}}
                            <div class="dropdown w-100 w-md-auto"> {{-- Made dropdown take full width on small screens --}}
                                <button class="btn btn-primary rounded-pill px-4 py-2 w-100 hover-lift transition-all dropdown-toggle"
                                        type="button" id="dropdownDisposisiEmail{{ $submission->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-envelope me-2"></i>Kirim Disposisi Via Email
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="dropdownDisposisiEmail{{ $submission->id }}"> {{-- Made dropdown menu full width --}}
                                    @php
                                        $emailRecipients = [
                                            ["name" => "IPDS", "email" => "ipds@example.com"],
                                            ["name" => "TU", "email" => "tu@example.com"],
                                            ["name" => "Kepala Kantor", "email" => "kepalakk@example.com"],
                                            ["name" => "Neraca", "email" => "neraca@example.com"],
                                            ["name" => "Sosial", "email" => "sosial@example.com"],
                                            ["name" => "Distribusi", "email" => "distribusi@example.com"],
                                            ["name" => "Produksi", "email" => "produksi@example.com"]
                                        ];
                                    @endphp

                                    @foreach($emailRecipients as $recipient)
                                        <li>
                                            {{-- Gunakan form untuk mengirim POST request --}}
                                            <form action="{{ route('submissions.sendDispositionEmail', $submission) }}" method="POST" class="d-inline-block send-email-form w-100">
                                                @csrf
                                                <input type="hidden" name="recipient_email" value="{{ $recipient['email'] }}">
                                                <input type="hidden" name="recipient_name" value="{{ $recipient['name'] }}">
                                                <button type="submit" class="dropdown-item text-start w-100"> {{-- Made dropdown item full width --}}
                                                    Kirim ke {{ $recipient['name'] }}
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Dropdown untuk Kirim Via WhatsApp (Ini tetap pakai link wa.me) --}}
                            <div class="dropdown w-100 w-md-auto"> {{-- Made dropdown take full width on small screens --}}
                                <button class="btn btn-outline-success rounded-pill px-4 py-2 w-100 hover-lift transition-all dropdown-toggle"
                                        type="button" id="dropdownWhatsApp{{ $submission->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fab fa-whatsapp me-2"></i>Kirim Via WhatsApp
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="dropdownWhatsApp{{ $submission->id }}"> {{-- Made dropdown menu full width --}}
                                    @php
                                        $whatsappRecipients = [
                                            ['name' => 'IPDS', 'phone' => '6285864850582'],
                                            ['name' => 'TU', 'phone' => '6285930429161'],
                                            ['name' => 'Kepala Kantor', 'phone' => '6285239356553'],
                                            ['name' => 'Neraca', 'phone' => '6285239356553'],
                                            ['name' => 'Sosial', 'phone' => '6285239356553'],
                                            ['name' => 'Distribusi', 'phone' => '6285239356553'],
                                            ['name' => 'Produksi', 'phone' => '6285239356553'],
                                        ];

                                        $whatsappMessageTemplate = "Yth. [NAMA_PENERIMA],\n\nAnda menerima disposisi untuk surat tugas dengan ID #{$submission->id} atas nama " . $submission->nama . ".\n\n" .
                                                                    "Detail submission dapat dilihat di: " . route('submissions.show', $submission) . "\n" .
                                                                    "Mohon untuk ditindaklanjuti.\n\nTerima kasih.";
                                    @endphp

                                    @foreach($whatsappRecipients as $recipient)
                                        @if(!empty($recipient['phone']))
                                            <li>
                                                <a class="dropdown-item text-start"
                                                   href="https://wa.me/{{ $recipient['phone'] }}?text={{ urlencode(str_replace('[NAMA_PENERIMA]', $recipient['name'], $whatsappMessageTemplate)) }}"
                                                   target="_blank">
                                                   Kirim ke {{ $recipient['name'] }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <a href="{{ route('user.dashboard') }}" class="btn btn-secondary mt-3 rounded-pill px-5 py-2 hover-lift transition-all">
                            <i class="fas fa-home me-1"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    /* Anda bisa ganti warna di atas sesuai keinginan Anda */
    /* Contoh lain: */
    /* background-color: #28a745; */ /* Warna hijau WhatsApp */
    /* color: white; */
}

/* Pastikan juga aktif saat diklik (focus) jika diperlukan */
.dropdown-item:focus {
    background-color: #e9ecef;
    color: #007bff;
}

/* Gaya untuk item dropdown yang disabled */
.dropdown-item.disabled,
.dropdown-item:disabled,
.dropdown-item[disabled] {
    pointer-events: none; /* Mencegah klik event */
    opacity: 0.6; /* Sedikit transparansi */
    background-color: transparent !important; /* Pastikan tidak ada highlight saat hover */
    color: var(--bs-secondary-color) !important; /* Warna teks abu-abu */
}

/* Override untuk tombol di dalam form yang sudah terkirim */
.send-email-form button[type="submit"].text-muted {
    pointer-events: none; /* Mencegah klik pada tombol itu sendiri */
    cursor: not-allowed;
}

/* Pastikan style untuk dropdown-item ini mengena */
.dropdown-menu .send-email-form button[type="submit"] {
    display: block; /* Agar mengambil seluruh lebar dropdown-item */
    width: 100%;
    text-align: left; /* Sesuaikan perataan teks */
    padding: var(--bs-dropdown-item-padding-y) var(--bs-dropdown-item-padding-x); /* Ikuti padding dropdown-item Bootstrap */
    clear: both; /* Untuk membersihkan float jika ada */
    font-weight: 400;
    color: var(--bs-dropdown-link-color);
    text-decoration: none;
    white-space: nowrap; /* Mencegah teks patah */
    background-color: transparent;
    border: 0; /* Hapus border tombol */
}

.dropdown-menu .send-email-form button[type="submit"]:hover {
    color:#007bff;
    background-color: #e9ecef;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.send-email-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            const originalButtonClass = submitButton.className; // Simpan kelas asli
            const dropdownItemParent = submitButton.closest('li'); // Dapatkan <li> parent
            const dropdownButton = submitButton.closest('.dropdown').querySelector('.dropdown-toggle'); // Dapatkan tombol dropdown utama

            const recipientName = this.querySelector('input[name="recipient_name"]').value;
            const recipientEmail = this.querySelector('input[name="recipient_email"]').value;
            const submissionId = "{{ $submission->id }}";
            const route = `/submissions/${submissionId}/send-disposition-email`;

            // Nonaktifkan tombol yang diklik
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...';
            // Opsional: tambahkan kelas untuk styling disabled jika diperlukan
            // submitButton.classList.add('disabled-sending');

            fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    recipient_email: recipientEmail,
                    recipient_name: recipientName
                })
            })
            .then(response => {
                // Aktifkan kembali tombol setelah respons diterima (baik sukses maupun error)
                // Ini penting agar tombol tidak terus dalam status "Mengirim..." jika ada error
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                // submitButton.classList.remove('disabled-sending');

                if (!response.ok) {
                    return response.text().then(text => { throw new Error(text) });
                }
                return response.json();
            })
            .then(data => {
                const toastBody = document.querySelector('#liveToastSuccess .toast-body');
                const toastElement = document.getElementById('liveToastSuccess');
                const toast = new bootstrap.Toast(toastElement);

                if (data.status === 'success') {
                    toastBody.textContent = data.message || `Email disposisi berhasil dikirim ke ${recipientName}!`;
                    toastElement.classList.remove('text-bg-danger');
                    toastElement.classList.add('text-bg-success');
                    toast.show();

                    // === BAGIAN INI YANG DITAMBAHKAN/DIMODIFIKASI ===
                    // Setelah sukses, nonaktifkan item dropdown yang bersangkutan
                    if (dropdownItemParent) {
                        dropdownItemParent.classList.add('disabled'); // Tambahkan kelas Bootstrap 'disabled' ke <li>
                        // Ubah tampilan tombol di dalam <li> agar terlihat disabled
                        submitButton.classList.remove(...originalButtonClass.split(' ')); // Hapus kelas asli
                        submitButton.classList.add('text-muted', 'cursor-not-allowed', 'opacity-75'); // Tambahkan kelas disabled
                        submitButton.innerHTML = `<i class="fas fa-check me-2"></i>Terkirim ke ${recipientName}`; // Ubah teks tombol
                        submitButton.disabled = true; // Nonaktifkan tombol lagi secara permanen (untuk sesi ini)
                    }

                    // Opsional: Cek apakah semua email sudah terkirim, lalu nonaktifkan tombol dropdown utama
                    let allEmailsSent = true;
                    document.querySelectorAll('.send-email-form').forEach(otherForm => {
                        const otherSubmitButton = otherForm.querySelector('button[type="submit"]');
                        if (!otherSubmitButton.disabled || !otherSubmitButton.classList.contains('text-muted')) {
                            allEmailsSent = false;
                        }
                    });

                    if (allEmailsSent) {
                        if (dropdownButton) {
                            dropdownButton.disabled = true;
                            dropdownButton.textContent = 'Semua Email Terkirim';
                            dropdownButton.classList.remove('btn-primary');
                            dropdownButton.classList.add('btn-secondary');
                        }
                    }

                } else {
                    const errorToastBody = document.querySelector('#liveToastError .toast-body');
                    const errorToastElement = document.getElementById('liveToastError');
                    const errorToast = new bootstrap.Toast(errorToastElement);

                    errorToastBody.textContent = data.message || 'Terjadi kesalahan saat mengirim email.';
                    errorToastElement.classList.remove('text-bg-success');
                    errorToastElement.classList.add('text-bg-danger');
                    errorToast.show();
                    // Jika ada error, tombol sudah diaktifkan kembali oleh .then(response => ...)
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                // submitButton.classList.remove('disabled-sending');

                const errorToastBody = document.querySelector('#liveToastError .toast-body');
                const errorToastElement = document.getElementById('liveToastError');
                const errorToast = new bootstrap.Toast(errorToastElement);

                errorToastBody.textContent = 'Gagal mengirim email: ' + error.message;
                errorToastElement.classList.remove('text-bg-success');
                errorToastElement.classList.add('text-bg-danger');
                errorToast.show();
            });
        });
    });
});
</script>
@endpush
@endsection