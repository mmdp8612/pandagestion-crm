<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertyInquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{id: int, nombre: string, email: string|null, telefono: string|null, mensaje: string}  $inquiry
     * @param  array{codigo: string, slug: string, domicilio: string, ubicacion: string}  $property
     */
    public function __construct(
        public array $inquiry,
        public array $property,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->inquiry['email']
                ? [new Address($this->inquiry['email'], $this->inquiry['nombre'])]
                : [],
            subject: 'Nueva consulta por la propiedad '.$this->property['codigo'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.property-inquiry-received',
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
