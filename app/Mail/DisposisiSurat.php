<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DisposisiSurat extends Mailable
{
    use Queueable, SerializesModels;

    public $penerima;   // Jabatan penerima (contoh: IPDS, TU)
    public $nomorSurat;
    public $pengirim;   // Nama pengaju/user
    public $perihal;    // Perihal surat/tujuan pengajuan

    /**
     * Buat instance pesan Mailable baru.
     *
     * @param string $penerima Nama jabatan penerima (contoh: "IPDS")
     * @param string $nomorSurat Nomor surat dari submission
     * @param string $pengirim Nama pengaju/pengirim surat
     * @param string $perihal Perihal atau tujuan dari surat/pengajuan
     * @return void
     */
    public function __construct($penerima, $nomorSurat, $pengirim, $perihal)
    {
        $this->penerima = $penerima;
        $this->nomorSurat = $nomorSurat;
        $this->pengirim = $pengirim;
        $this->perihal = $perihal;
    }

    /**
     * Bangun pesan.
     *
     * @return $this
     */
    public function build()
    {
        // Subjek email
        // Anda bisa membuatnya dinamis, misal: "Disposisi Surat untuk " . $this->penerima
        return $this->subject('Disposisi Surat')
                    ->view('emails.kirim_email'); // Pastikan ini mengarah ke file blade kamu untuk isi email
    }
}