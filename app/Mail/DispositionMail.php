<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DispositionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $submission;
    public $recipientName;
    public $submissionDetailUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Submission $submission, string $recipientName)
    {
        $this->submission = $submission;
        $this->recipientName = $recipientName;
        // Generate URL untuk detail submission
        $this->submissionDetailUrl = route('submissions.show', $submission);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Disposisi Surat Tugas - ' . $this->submission->nama . ' (ID: #' . $this->submission->id . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.disposition', // Nama view untuk template email
            with: [
                'submission' => $this->submission,
                'recipientName' => $this->recipientName,
                'submissionDetailUrl' => $this->submissionDetailUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return []; // Anda bisa menambahkan attachment di sini jika ada dokumen yang ingin dilampirkan
    }
}