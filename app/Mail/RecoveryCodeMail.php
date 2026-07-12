<?php

namespace App\Mail;

use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecoveryCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crear una nueva instancia del mensaje.
     */
    public function __construct(
        public string $code,
        public Person $person
    ) {
        $this->code = $code;
        $this->person = $person;
    }

    /**
     * Definir el asunto (subject) del correo.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de recuperación de contraseña',
        );
    }

    /**
     * Definir el contenido del correo.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.recovery-code',
            with: [
                'code' => $this->code,
                'person' => $this->person,
            ]
        );
    }

    /**
     * Archivos adjuntos (si los hubiera).
     */
    public function attachments(): array
    {
        return [];
    }
}
