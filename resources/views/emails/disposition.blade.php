<!DOCTYPE html>
<html>
<head>
    <title>Disposisi Surat Tugas</title>
</head>
<body>
    <p>Yth. {{ $recipientName }},</p>
    <p>Anda menerima disposisi untuk surat tugas dengan ID #{{ $submission->id }} atas nama {{ $submission->nama }}.</p>
    <p>Detail pengajuan:</p>
    <ul>
        <li><strong>Nama Pemohon:</strong> {{ $submission->nama }}</li>
        <li><strong>Tujuan:</strong> {{ $submission->tujuan }}</li>
        <li><strong>Jenis Form:</strong> {{ $submission->jenis_form }}</li>
        <li><strong>Status:</strong> {{ $submission->status }}</li>
        </ul>
    <p>Untuk melihat detail lengkap submission, silakan kunjungi link berikut:</p>
    <p><a href="{{ $submissionDetailUrl }}">{{ $submissionDetailUrl }}</a></p>
    <p>Mohon untuk ditindaklanjuti.</p>
    <p>Terima kasih.</p>
    <p>Hormat kami,</p>
    <p>Sistem Pengajuan Surat Tugas</p>
</body>
</html>