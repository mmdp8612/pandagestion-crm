<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicInquiryRequest;
use App\Mail\PropertyInquiryConfirmation;
use App\Mail\PropertyInquiryReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PublicInquiryController extends Controller
{
    public function store(StorePublicInquiryRequest $request, string $slug): RedirectResponse
    {
        $property = DB::table('bienesraices')
            ->select(['id', 'Codigo', 'Slug', 'Calle', 'Numero', 'Barrio', 'Localidad'])
            ->where('Slug', $slug)
            ->where('Hab', true)
            ->first();

        abort_if($property === null, 404);

        $data = $request->validated();
        $now = now();

        $inquiryId = DB::table('consultas')->insertGetId([
            'idBienRaiz' => $property->id,
            'CodigoPropiedad' => $property->Codigo,
            'Nombre' => $data['nombre'],
            'Email' => $data['email'],
            'Telefono' => $data['telefono'],
            'Mensaje' => $data['mensaje'],
            'Estado' => 'nueva',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $agency = DB::table('inmobiliaria')
            ->select(['RazonSocial', 'Email'])
            ->where('id', 1)
            ->where('Hab', true)
            ->first();

        if ($agency?->Email) {
            $inquiry = [
                'id' => $inquiryId,
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'telefono' => $data['telefono'],
                'mensaje' => $data['mensaje'],
            ];
            $propertySummary = [
                'codigo' => $property->Codigo,
                'slug' => $property->Slug,
                'domicilio' => trim(implode(' ', array_filter([$property->Calle, $property->Numero]))),
                'ubicacion' => implode(', ', array_filter([$property->Barrio, $property->Localidad])),
            ];
            $agencySummary = [
                'nombre' => $agency->RazonSocial ?: config('app.name'),
                'email' => $agency->Email,
            ];

            $this->sendSafely(
                $agency->Email,
                new PropertyInquiryReceived($inquiry, $propertySummary),
            );

            if ($data['email']) {
                $this->sendSafely(
                    $data['email'],
                    new PropertyInquiryConfirmation($inquiry, $propertySummary, $agencySummary),
                );
            }
        }

        return redirect(route('public.properties.show', $property->Slug).'#consulta')
            ->with('success', 'Tu consulta fue enviada. Nos pondremos en contacto a la brevedad.');
    }

    private function sendSafely(string $recipient, Mailable $mailable): void
    {
        try {
            Mail::to($recipient)->send($mailable);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
