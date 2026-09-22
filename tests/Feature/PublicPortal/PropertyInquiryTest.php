<?php

namespace Tests\Feature\PublicPortal;

use App\Mail\PropertyInquiryConfirmation;
use App\Mail\PropertyInquiryReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PropertyInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_detail_displays_the_inquiry_form(): void
    {
        $this->insertProperty();

        $this->get(route('public.properties.show', 'casa-en-palermo-prop001'))
            ->assertOk()
            ->assertSee('Consultá por esta propiedad')
            ->assertSee('name="nombre"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="telefono"', false)
            ->assertSee('name="mensaje"', false)
            ->assertSee(route('public.properties.inquiries.store', 'casa-en-palermo-prop001'), false);
    }

    public function test_visitor_can_send_an_inquiry_and_both_parties_are_notified(): void
    {
        Mail::fake();
        $smtpUsername = 'smtp-auth@example.test';
        config()->set('mail.mailers.smtp.username', $smtpUsername);
        $propertyId = $this->insertProperty();
        $this->insertAgency();

        $response = $this->post(route('public.properties.inquiries.store', 'casa-en-palermo-prop001'), [
            'nombre' => '  Laura   Pérez ',
            'email' => ' LAURA@EXAMPLE.COM ',
            'telefono' => ' 11 5555-1234 ',
            'mensaje' => 'Quisiera coordinar una visita.',
            'website' => '',
        ]);

        $response
            ->assertRedirect(route('public.properties.show', 'casa-en-palermo-prop001').'#consulta')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('consultas', [
            'idBienRaiz' => $propertyId,
            'CodigoPropiedad' => 'PROP001',
            'Nombre' => 'Laura Pérez',
            'Email' => 'laura@example.com',
            'Telefono' => '11 5555-1234',
            'Mensaje' => 'Quisiera coordinar una visita.',
            'Estado' => 'nueva',
        ]);

        Mail::assertSent(PropertyInquiryReceived::class, function (PropertyInquiryReceived $mail) use ($smtpUsername): bool {
            return $mail->hasTo('consultas@inmobiliaria.test')
                && $mail->hasReplyTo('laura@example.com')
                && ! $mail->hasTo($smtpUsername)
                && ! $mail->hasCc($smtpUsername)
                && ! $mail->hasBcc($smtpUsername)
                && $mail->property['codigo'] === 'PROP001'
                && $mail->inquiry['email'] === 'laura@example.com';
        });
        Mail::assertSent(PropertyInquiryConfirmation::class, function (PropertyInquiryConfirmation $mail) use ($smtpUsername): bool {
            $rendered = $mail->render();

            return $mail->hasTo('laura@example.com')
                && $mail->hasReplyTo('consultas@inmobiliaria.test')
                && ! $mail->hasTo($smtpUsername)
                && ! $mail->hasCc($smtpUsername)
                && ! $mail->hasBcc($smtpUsername)
                && str_contains($rendered, 'Quisiera coordinar una visita.')
                && str_contains($rendered, route('public.properties.show', 'casa-en-palermo-prop001'))
                && ! str_contains($rendered, route('admin.inquiries.show', 1));
        });
        Mail::assertSent(PropertyInquiryReceived::class, 1);
        Mail::assertSent(PropertyInquiryConfirmation::class, 1);
        Mail::assertSentCount(2);
    }

    public function test_phone_only_inquiry_notifies_only_the_agency(): void
    {
        Mail::fake();
        $this->insertProperty();
        $this->insertAgency();

        $this->post(route('public.properties.inquiries.store', 'casa-en-palermo-prop001'), [
            'nombre' => 'Contacto telefonico',
            'email' => '',
            'telefono' => '11 5555-1234',
            'mensaje' => 'Quisiera recibir mas informacion.',
            'website' => '',
        ])->assertSessionHas('success');

        Mail::assertSent(PropertyInquiryReceived::class, function (PropertyInquiryReceived $mail): bool {
            return $mail->hasTo('consultas@inmobiliaria.test')
                && ! $mail->hasReplyTo('consultas@inmobiliaria.test');
        });
        Mail::assertNotSent(PropertyInquiryConfirmation::class);
        Mail::assertSentCount(1);
    }

    public function test_inquiry_requires_at_least_one_contact_method(): void
    {
        Mail::fake();
        $this->insertProperty();

        $this->post(route('public.properties.inquiries.store', 'casa-en-palermo-prop001'), [
            'nombre' => 'Laura Pérez',
            'email' => '',
            'telefono' => '',
            'mensaje' => 'Quisiera recibir más información.',
            'website' => '',
        ])
            ->assertSessionHasErrors(['email', 'telefono']);

        $this->assertDatabaseCount('consultas', 0);
        Mail::assertNothingSent();
    }

    public function test_honeypot_rejects_automated_submissions(): void
    {
        Mail::fake();
        $this->insertProperty();

        $this->post(route('public.properties.inquiries.store', 'casa-en-palermo-prop001'), [
            'nombre' => 'Bot automático',
            'email' => 'bot@example.com',
            'telefono' => '',
            'mensaje' => 'Mensaje automatizado.',
            'website' => 'https://spam.example',
        ])
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('consultas', 0);
        Mail::assertNothingSent();
    }

    public function test_disabled_and_unknown_properties_do_not_accept_inquiries(): void
    {
        Mail::fake();
        $this->insertProperty(enabled: false);
        $payload = [
            'nombre' => 'Laura Pérez',
            'email' => 'laura@example.com',
            'telefono' => '',
            'mensaje' => 'Quisiera recibir más información.',
            'website' => '',
        ];

        $this->post(route('public.properties.inquiries.store', 'casa-en-palermo-prop001'), $payload)
            ->assertNotFound();
        $this->post(route('public.properties.inquiries.store', 'propiedad-inexistente'), $payload)
            ->assertNotFound();

        $this->assertDatabaseCount('consultas', 0);
        Mail::assertNothingSent();
    }

    private function insertAgency(): void
    {
        DB::table('inmobiliaria')->insert([
            'id' => 1,
            'RazonSocial' => 'Inmobiliaria de prueba',
            'Email' => 'consultas@inmobiliaria.test',
            'Hab' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertProperty(bool $enabled = true): int
    {
        return DB::table('bienesraices')->insertGetId([
            'Codigo' => 'PROP001',
            'Descrip' => 'Casa amplia y luminosa.',
            'Calle' => 'Avenida Santa Fe',
            'Numero' => '3200',
            'Provincia' => 'Ciudad Autónoma de Buenos Aires',
            'Localidad' => 'Buenos Aires',
            'Barrio' => 'Palermo',
            'Destacada' => false,
            'Slug' => 'casa-en-palermo-prop001',
            'TieneFoto' => false,
            'TieneVideo' => false,
            'Hab' => $enabled,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
