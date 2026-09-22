<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRealEstateAgencyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RealEstateAgencyController extends Controller
{
    private const RECORD_ID = 1;

    public function edit(): View
    {
        $agency = DB::table('inmobiliaria')
            ->where('id', self::RECORD_ID)
            ->first();

        $logoUrl = $agency?->Logo
            ? Storage::disk('public')->url($agency->Logo)
            : null;

        return view('admin.real-estate-agency.edit', compact('agency', 'logoUrl'));
    }

    public function update(UpdateRealEstateAgencyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $now = now();
        $newLogoPath = null;

        if ($request->hasFile('logo')) {
            $storedLogoPath = $request->file('logo')->store('inmobiliaria/logos', 'public');

            if (! is_string($storedLogoPath)) {
                throw ValidationException::withMessages([
                    'logo' => 'No se pudo guardar el logo. Intentá nuevamente.',
                ]);
            }

            $newLogoPath = $storedLogoPath;
        }

        $oldLogoPath = null;

        try {
            DB::transaction(function () use ($data, $newLogoPath, $now, &$oldLogoPath): void {
                $agency = DB::table('inmobiliaria')
                    ->where('id', self::RECORD_ID)
                    ->lockForUpdate()
                    ->first();

                $oldLogoPath = $agency?->Logo;

                $attributes = [
                    'RazonSocial' => $data['razon_social'],
                    'Telefonos' => $data['telefonos'],
                    'Whatsapp' => $data['whatsapp'],
                    'Email' => $data['email'],
                    'Domicilio' => $data['domicilio'],
                    'CodigoPostal' => $data['codigo_postal'],
                    'Provincia' => $data['provincia'],
                    'Partido' => $data['partido'],
                    'Localidad' => $data['localidad'],
                    'Barrio' => $data['barrio'],
                    'Latitud' => $data['latitud'],
                    'Longitud' => $data['longitud'],
                    'Web' => $data['web'],
                    'Matricula' => $data['matricula'],
                    'Hab' => $data['hab'],
                    'updated_at' => $now,
                ];

                if ($newLogoPath !== null || $agency === null) {
                    $attributes['Logo'] = $newLogoPath;
                }

                if ($agency !== null) {
                    DB::table('inmobiliaria')
                        ->where('id', self::RECORD_ID)
                        ->update($attributes);

                    return;
                }

                DB::table('inmobiliaria')->insert([
                    'id' => self::RECORD_ID,
                    ...$attributes,
                    'created_at' => $now,
                ]);
            });
        } catch (Throwable $exception) {
            if ($newLogoPath !== null) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if ($newLogoPath !== null && $oldLogoPath !== null && $oldLogoPath !== $newLogoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return to_route('admin.real-estate-agency.edit')
            ->with('success', 'Los datos de la inmobiliaria se guardaron correctamente.');
    }

    public function destroyLogo(): RedirectResponse
    {
        $logoPath = DB::transaction(function (): ?string {
            $agency = DB::table('inmobiliaria')
                ->where('id', self::RECORD_ID)
                ->lockForUpdate()
                ->first();

            if ($agency === null || $agency->Logo === null) {
                return null;
            }

            DB::table('inmobiliaria')
                ->where('id', self::RECORD_ID)
                ->update([
                    'Logo' => null,
                    'updated_at' => now(),
                ]);

            return $agency->Logo;
        });

        if ($logoPath === null) {
            return to_route('admin.real-estate-agency.edit')
                ->with('info', 'La inmobiliaria no tiene un logo cargado.');
        }

        Storage::disk('public')->delete($logoPath);

        return to_route('admin.real-estate-agency.edit')
            ->with('success', 'El logo de la inmobiliaria se eliminó correctamente.');
    }
}
