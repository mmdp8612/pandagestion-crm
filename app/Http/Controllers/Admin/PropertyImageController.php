<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePropertyImagesRequest;
use App\Http\Requests\Admin\UpdatePropertyImageOrderRequest;
use App\Http\Requests\Admin\UpdatePropertyImageStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use stdClass;
use Throwable;

class PropertyImageController extends Controller
{
    public function index(string $property): View
    {
        $currentProperty = $this->findPropertyOrFail($property);
        $images = DB::table('bienesraices_imagenes')
            ->where('idBienRaiz', $currentProperty->id)
            ->orderBy('Orden')
            ->orderBy('id')
            ->get();

        return view('admin.properties.images.index', [
            'property' => $currentProperty,
            'images' => $images,
            'enabledImagesCount' => $images->filter(static fn (stdClass $image): bool => (bool) $image->Hab)->count(),
        ]);
    }

    public function store(StorePropertyImagesRequest $request, string $property): RedirectResponse
    {
        $currentProperty = $this->findPropertyOrFail($property);
        $storedPaths = [];

        foreach ($request->file('imagenes', []) as $image) {
            $path = $image->store("bienesraices/{$currentProperty->id}/imagenes", 'public');

            if (! is_string($path)) {
                Storage::disk('public')->delete($storedPaths);

                throw ValidationException::withMessages([
                    'imagenes' => 'No se pudieron guardar las imágenes. Intentá nuevamente.',
                ]);
            }

            $storedPaths[] = $path;
        }

        try {
            DB::transaction(function () use ($currentProperty, $storedPaths): void {
                $propertyExists = DB::table('bienesraices')
                    ->where('id', $currentProperty->id)
                    ->lockForUpdate()
                    ->exists();

                abort_unless($propertyExists, 404);

                $existingImages = DB::table('bienesraices_imagenes')
                    ->where('idBienRaiz', $currentProperty->id)
                    ->lockForUpdate()
                    ->get();
                $nextOrder = ((int) $existingImages->max('Orden')) + 1;
                $now = now();

                foreach ($storedPaths as $path) {
                    DB::table('bienesraices_imagenes')->insert([
                        'idBienRaiz' => $currentProperty->id,
                        'Archivo' => $path,
                        'Orden' => $nextOrder++,
                        'Portada' => false,
                        'Hab' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                }

                $this->synchronizePublicationState($currentProperty->id);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }

        $count = count($storedPaths);

        return to_route('admin.properties.images.index', $currentProperty->id)
            ->with('success', $count === 1
                ? 'La imagen se cargó correctamente.'
                : "Las {$count} imágenes se cargaron correctamente.");
    }

    public function updateCover(string $property, string $image): RedirectResponse
    {
        $currentProperty = $this->findPropertyOrFail($property);

        DB::transaction(function () use ($currentProperty, $image): void {
            $currentImage = DB::table('bienesraices_imagenes')
                ->where('idBienRaiz', $currentProperty->id)
                ->where('id', $image)
                ->lockForUpdate()
                ->first();

            abort_if($currentImage === null, 404);

            if (! $currentImage->Hab) {
                throw ValidationException::withMessages([
                    'imagen' => 'Habilitá la imagen antes de seleccionarla como portada.',
                ]);
            }

            DB::table('bienesraices_imagenes')
                ->where('idBienRaiz', $currentProperty->id)
                ->update([
                    'Portada' => false,
                    'updated_at' => now(),
                ]);

            DB::table('bienesraices_imagenes')
                ->where('id', $currentImage->id)
                ->update([
                    'Portada' => true,
                    'updated_at' => now(),
                ]);

            $this->synchronizePublicationState($currentProperty->id);
        });

        return to_route('admin.properties.images.index', $currentProperty->id)
            ->with('success', 'La imagen de portada se actualizó correctamente.');
    }

    public function updateStatus(UpdatePropertyImageStatusRequest $request, string $property, string $image): RedirectResponse
    {
        $currentProperty = $this->findPropertyOrFail($property);
        $isEnabled = $request->boolean('hab');

        DB::transaction(function () use ($currentProperty, $image, $isEnabled): void {
            $propertyExists = DB::table('bienesraices')
                ->where('id', $currentProperty->id)
                ->lockForUpdate()
                ->exists();

            abort_unless($propertyExists, 404);

            $currentImage = DB::table('bienesraices_imagenes')
                ->where('idBienRaiz', $currentProperty->id)
                ->where('id', $image)
                ->lockForUpdate()
                ->first();

            abort_if($currentImage === null, 404);

            DB::table('bienesraices_imagenes')
                ->where('id', $currentImage->id)
                ->update([
                    'Hab' => $isEnabled,
                    'Portada' => false,
                    'updated_at' => now(),
                ]);

            $this->synchronizePublicationState(
                $currentProperty->id,
                $isEnabled ? (int) $currentImage->id : null,
            );
        });

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.properties.images.index', $currentProperty->id)
            ->with('success', "La imagen se {$status} correctamente.");
    }

    public function updateOrder(UpdatePropertyImageOrderRequest $request, string $property): RedirectResponse
    {
        $currentProperty = $this->findPropertyOrFail($property);
        $orderedImageIds = array_map('intval', $request->validated()['imagenes']);

        DB::transaction(function () use ($currentProperty, $orderedImageIds): void {
            $propertyExists = DB::table('bienesraices')
                ->where('id', $currentProperty->id)
                ->lockForUpdate()
                ->exists();

            abort_unless($propertyExists, 404);

            $existingImageIds = DB::table('bienesraices_imagenes')
                ->where('idBienRaiz', $currentProperty->id)
                ->lockForUpdate()
                ->pluck('id')
                ->map(static fn (int|string $id): int => (int) $id)
                ->all();
            $sortedExistingIds = $existingImageIds;
            $sortedOrderedIds = $orderedImageIds;
            sort($sortedExistingIds);
            sort($sortedOrderedIds);

            if ($sortedExistingIds !== $sortedOrderedIds) {
                throw ValidationException::withMessages([
                    'imagenes' => 'El orden debe incluir exactamente todas las imágenes de esta propiedad.',
                ]);
            }

            $now = now();

            foreach ($orderedImageIds as $index => $imageId) {
                DB::table('bienesraices_imagenes')
                    ->where('idBienRaiz', $currentProperty->id)
                    ->where('id', $imageId)
                    ->update([
                        'Orden' => $index + 1,
                        'updated_at' => $now,
                    ]);
            }

            DB::table('bienesraices')
                ->where('id', $currentProperty->id)
                ->update(['updated_at' => $now]);
        });

        return to_route('admin.properties.images.index', $currentProperty->id)
            ->with('success', 'El orden de las imágenes se guardó correctamente.');
    }

    public function destroy(string $property, string $image): RedirectResponse
    {
        $currentProperty = $this->findPropertyOrFail($property);
        $currentImage = $this->findImageOrFail($currentProperty->id, $image);

        DB::transaction(function () use ($currentProperty, $currentImage): void {
            DB::table('bienesraices_imagenes')
                ->where('idBienRaiz', $currentProperty->id)
                ->lockForUpdate()
                ->get();

            DB::table('bienesraices_imagenes')
                ->where('id', $currentImage->id)
                ->delete();

            $remainingImages = DB::table('bienesraices_imagenes')
                ->where('idBienRaiz', $currentProperty->id)
                ->orderBy('Orden')
                ->orderBy('id')
                ->get();

            foreach ($remainingImages as $index => $remainingImage) {
                DB::table('bienesraices_imagenes')
                    ->where('id', $remainingImage->id)
                    ->update([
                        'Orden' => $index + 1,
                        'updated_at' => now(),
                    ]);
            }

            $this->synchronizePublicationState($currentProperty->id);
        });

        Storage::disk('public')->delete($currentImage->Archivo);

        return to_route('admin.properties.images.index', $currentProperty->id)
            ->with('success', 'La imagen se eliminó correctamente.');
    }

    private function findPropertyOrFail(string $id): stdClass
    {
        $property = DB::table('bienesraices')->where('id', $id)->first();

        abort_if($property === null, 404);

        return $property;
    }

    private function findImageOrFail(int $propertyId, string $id): stdClass
    {
        $image = DB::table('bienesraices_imagenes')
            ->where('idBienRaiz', $propertyId)
            ->where('id', $id)
            ->first();

        abort_if($image === null, 404);

        return $image;
    }

    private function synchronizePublicationState(int $propertyId, ?int $preferredCoverId = null): void
    {
        $images = DB::table('bienesraices_imagenes')
            ->where('idBienRaiz', $propertyId)
            ->orderBy('Orden')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $enabledImages = $images->filter(static fn (stdClass $image): bool => (bool) $image->Hab);
        $coverImage = $enabledImages->first(static fn (stdClass $image): bool => (bool) $image->Portada);

        if ($coverImage === null && $preferredCoverId !== null) {
            $coverImage = $enabledImages->first(
                static fn (stdClass $image): bool => (int) $image->id === $preferredCoverId,
            );
        }

        $coverImage ??= $enabledImages->first();
        $now = now();

        DB::table('bienesraices_imagenes')
            ->where('idBienRaiz', $propertyId)
            ->where('Portada', true)
            ->when($coverImage !== null, function ($query) use ($coverImage): void {
                $query->where('id', '!=', $coverImage->id);
            })
            ->update([
                'Portada' => false,
                'updated_at' => $now,
            ]);

        if ($coverImage !== null && ! $coverImage->Portada) {
            DB::table('bienesraices_imagenes')
                ->where('id', $coverImage->id)
                ->update([
                    'Portada' => true,
                    'updated_at' => $now,
                ]);
        }

        DB::table('bienesraices')
            ->where('id', $propertyId)
            ->update([
                'TieneFoto' => $enabledImages->isNotEmpty(),
                'updated_at' => $now,
            ]);
    }
}
