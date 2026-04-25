<?php

namespace App\Mail;

use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CodigoRecuperacionEmail extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * Crear una nueva instancia del mensaje.
   */
  public function __construct(
    public string $codigo,
    public Person $persona
  ) {
    $this->codigo = $codigo;
    $this->persona = $persona;
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
      markdown: 'emails.codigo-recuperacion',
      with: [
        'codigo' => $this->codigo,
        'persona' => $this->persona,
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
