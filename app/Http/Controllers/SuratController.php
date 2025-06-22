<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller; // Pastikan ini di-import
use App\Mail\DisposisiSurat;        // Pastikan ini di-import
use App\Models\Submission;          // Pastikan model Submission di-import
use Illuminate\Http\Request;         // Pastikan ini di-import
use Illuminate\Support\Facades\Mail; // Pastikan ini di-import
use Illuminate\Support\Facades\Log;  // Opsional, tapi bagus untuk debugging

class SuratController extends Controller // Nama kelas: SuratController
{
    /**
     * Mengirim email disposisi untuk sebuah submission ke penerima yang dipilih.
     * Metode ini dipanggil via POST request dari form di Blade.
     *
     * @param  \Illuminate\Http\Request  $request  Data dari form (termasuk 'disposisi_pilihan').
     * @param  \App\Models\Submission  $submission Objek Submission yang otomatis dibind dari URL.
     * @return \Illuminate\Http\RedirectResponse Redirect kembali dengan pesan sukses/error.
     */
    public function kirimDisposisi(Request $request, Submission $submission)
    {
        // --- Debugging Awal (Opsional, hapus di production) ---
        // Log semua input dari request untuk memastikan data diterima
        Log::info('SuratController@kirimDisposisi dipanggil.');
        Log::info('Request input: ' . json_encode($request->all()));
        Log::info('Submission ID (via Route Model Binding): ' . $submission->id);
        // --- Akhir Debugging Awal ---

        // Ambil input 'disposisi_pilihan' dari form
        $disposisiPilihan = $request->input('disposisi_pilihan');

        // Peta alamat email untuk setiap jabatan/penerima
        $emailMap = [
            "IPDS" => "ipds3205@bps.go.id",
            "TU" => "sastiprasasti01@gmail.com",
            "Kepala Kantor" => "nevihendri@bps.go.id",
            "Neraca" => "farhannursidiq2@gmail.com`",
            "Sosial" => "sosial@example.com",
            "Distribusi" => "distribusi@example.com",
            "Produksi" => "produksi@example.com"
        ];

        // --- Validasi Input ---
        if (!isset($disposisiPilihan) || !isset($emailMap[$disposisiPilihan])) {
            Log::warning("Gagal Kirim Email: Pilihan disposisi tidak valid atau tidak ditemukan di emailMap. Pilihan: " . ($disposisiPilihan ?? 'NULL'));
            return back()->with('error_email_' . $submission->id, 'Pilihan disposisi tidak valid.')
                         ->withInput(['submission_id_feedback' => $submission->id]);
        }

        $emailTujuan = $emailMap[$disposisiPilihan];

        // --- Ambil Data yang Dibutuhkan dari Objek Submission ---
        // Pastikan kolom-kolom ini ada di tabel 'submissions' Anda.
        // Asumsi relasi user ada: $submission->user->name
        $nomorSurat = $submission->nomor_surat ?? $submission->id; // Fallback ke ID jika nomor_surat tidak ada
        $namaPengirim = $submission->user->name ?? 'Pengaju Tidak Dikenal'; // Asumsi relasi 'user' ada dan memiliki 'name'
        $perihalSurat = $submission->tujuan ?? $submission->nama; // Asumsi 'tujuan' adalah perihal, fallback ke 'nama' submission

        // --- Debugging Data Mailable (Opsional) ---
        Log::info("Data yang akan dikirim ke Mailable:");
        Log::info("  Penerima (Jabatan): " . strtoupper($disposisiPilihan));
        Log::info("  Nomor Surat: " . $nomorSurat);
        Log::info("  Nama Pengirim (Pengaju): " . $namaPengirim);
        Log::info("  Perihal Surat: " . $perihalSurat);
        Log::info("  Email Tujuan: " . $emailTujuan);
        // --- Akhir Debugging Data Mailable ---

        // --- Proses Pengiriman Email ---
        try {
            Mail::to($emailTujuan)->send(new DisposisiSurat(
                strtoupper($disposisiPilihan), // Contoh: "IPDS"
                $nomorSurat,                   // Contoh: "001/BPS/2024"
                $namaPengirim,                 // Contoh: "Budi Santoso"
                $perihalSurat                  // Contoh: "Permohonan Cuti Tahunan"
            ));

            Log::info("Email disposisi berhasil dikirim ke: {$emailTujuan} untuk Submission ID: {$submission->id}.");
            return back()->with('success_email_' . $submission->id, 'Email disposisi berhasil dikirim ke ' . $disposisiPilihan . '!')
                         ->withInput(['submission_id_feedback' => $submission->id]);
        } catch (\Exception $e) {
            // Tangkap dan log error jika pengiriman email gagal
            Log::error("Gagal mengirim email disposisi untuk Submission ID: {$submission->id}. Error: " . $e->getMessage());
            return back()->with('error_email_' . $submission->id, 'Gagal mengirim email: ' . $e->getMessage())
                         ->withInput(['submission_id_feedback' => $submission->id]);
        }
    }
}