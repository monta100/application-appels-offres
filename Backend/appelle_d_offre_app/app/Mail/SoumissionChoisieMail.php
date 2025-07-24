<?php

namespace App\Mail;

use App\Models\soumission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;


class SoumissionChoisieMail extends Mailable
{
    use Queueable, SerializesModels;

public $soumission;

public function __construct(soumission $soumission)
{
    $this->soumission = $soumission;
}
  public function envelope(): Envelope
{
    return new Envelope(
        subject: '🎉 Votre soumission a été choisie !',
    );
}

public function content(): Content
{
    return new Content(
        view: 'emails.soumission_choisie', // le nom de la vue que tu vas créer
        with: [
            'soumission' => $this->soumission,
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
        return [];
    }
}
