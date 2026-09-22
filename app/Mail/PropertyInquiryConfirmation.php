<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertyInquiryConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{id: int, nombre: string, email: string|null, telefono: string|null, mensaje: string}  $inquiry
     * @param  array{codigo: string, slug: string, domicilio: string, ubicacion: string}  $property
     * @param  array{nombre: string, email: string}  $agency
     */
    public function __construct(
        public array $inquiry,
        public array $property,
        public array $agency,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->agency['email'], $this->agency['nombre'])],
            subject: 'Recibimos tu consulta por la propiedad '.$this->property['codigo'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.property-inquiry-confirmation',
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}
