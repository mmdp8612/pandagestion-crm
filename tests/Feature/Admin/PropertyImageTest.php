<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_users_without_permission_cannot_manage_property_images(): void
    {
        $propertyId = $this->insertProperty();

        $this->get(route('admin.properties.images.index', $propertyId))
            ->assertRedirect(route('login'));

        $this->patch(route('admin.properties.images.order.update', $propertyId), ['imagenes' => [1]])
            ->assertRedirect(route('login'));

        $this->patch(route('admin.properties.images.status.update', [$propertyId, 1]), ['hab' => '0'])
            ->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.properties.images.index', $propertyId))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.properties.images.store', $propertyId), [
                'imagenes' => [UploadedFile::fake()->image('frente.jpg')],
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.properties.images.order.update', $propertyId), ['imagenes' => [1]])
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.properties.images.status.update', [$propertyId, 1]), ['hab' => '0'])
            ->assertForbidden();

        $this->assertDatabaseCount('bienesraices_imagenes', 0);
    }

    public function test_administrators_can_upload_multiple_safe_images_and_the_first_becomes_cover(): void
    {
        Storage::fake('public');
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();

        $this->actingAs($administrator)
            ->post(route('admin.properties.images.store', $propertyId), [
                'imagenes' => [
                    UploadedFile::fake()->image('frente.jpg', 1200, 900)->size(800),
                    UploadedFile::fake()->image('living.png', 1200, 900)->size(900),
                ],
            ])
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHas('success');

        $images = DB::table('bienesraices_imagenes')
            ->where('idBienRaiz', $propertyId)
            ->orderBy('Orden')
            ->get();

        $this->assertCount(2, $images);
        $this->assertTrue((bool) $images[0]->Portada);
        $this->assertFalse((bool) $images[1]->Portada);
        $this->assertSame(1, $images[0]->Orden);
        $this->assertSame(2, $images[1]->Orden);
        $this->assertStringStartsWith("bienesraices/{$propertyId}/imagenes/", $images[0]->Archivo);
        Storage::disk('public')->assertExists($images[0]->Archivo);
        Storage::disk('public')->assertExists($images[1]->Archivo);
        $mainImageUrl = Storage::disk('public')->url($images[0]->Archivo);
        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'TieneFoto' => true,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.properties.images.index', $propertyId))
            ->assertOk()
            ->assertSee('Imágenes de PROP001')
            ->assertSee('2 imágenes')
            ->assertSee('Portada')
            ->assertSee('data-image-sorter', false)
            ->assertSee('data-image-order-submit', false)
            ->assertSee(route('admin.properties.images.order.update', $propertyId), false)
            ->assertSee('data-confirm-title="¿Eliminar esta imagen?"', false);

        $this->actingAs($administrator)
            ->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSee($mainImageUrl, false)
            ->assertSee('alt="Portada de la propiedad PROP001"', false)
            ->assertDontSee('Sin imagen')
            ->assertSee(route('admin.properties.images.index', $propertyId), false);
    }

    public function test_property_images_are_validated_before_upload(): void
    {
        Storage::fake('public');
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();

        $this->actingAs($administrator)
            ->from(route('admin.properties.images.index', $propertyId))
            ->post(route('admin.properties.images.store', $propertyId), [
                'imagenes' => [UploadedFile::fake()->create('archivo.svg', 100, 'image/svg+xml')],
            ])
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHasErrors('imagenes.0');

        $this->actingAs($administrator)
            ->from(route('admin.properties.images.index', $propertyId))
            ->post(route('admin.properties.images.store', $propertyId), [
                'imagenes' => [UploadedFile::fake()->image('grande.jpg')->size(5121)],
            ])
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHasErrors('imagenes.0');

        $this->assertDatabaseCount('bienesraices_imagenes', 0);
        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'TieneFoto' => false,
        ]);
    }

    public function test_administrators_can_change_the_cover_image(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();
        $firstImage = $this->insertImage($propertyId, 'primera.jpg', 1, true);
        $secondImage = $this->insertImage($propertyId, 'segunda.jpg', 2, false);

        $this->actingAs($administrator)
            ->patch(route('admin.properties.images.cover.update', [$propertyId, $secondImage]))
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $firstImage,
            'Portada' => false,
        ]);
        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $secondImage,
            'Portada' => true,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url("bienesraices/{$propertyId}/imagenes/segunda.jpg"), false)
            ->assertDontSee(Storage::disk('public')->url("bienesraices/{$propertyId}/imagenes/primera.jpg"), false);
    }

    public function test_images_can_be_disabled_and_enabled_while_cover_and_photo_indicator_stay_synchronized(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();
        $firstImage = $this->insertImage($propertyId, 'primera.jpg', 1, true);
        $secondImage = $this->insertImage($propertyId, 'segunda.jpg', 2, false);
        DB::table('bienesraices')->where('id', $propertyId)->update(['TieneFoto' => true]);

        $this->actingAs($administrator)
            ->get(route('admin.properties.images.index', $propertyId))
            ->assertOk()
            ->assertSee('2 imágenes · 2 habilitadas')
            ->assertSee('data-confirm-title="¿Deshabilitar esta imagen?"', false)
            ->assertSee(route('admin.properties.images.status.update', [$propertyId, $firstImage]), false);

        $this->actingAs($administrator)
            ->patch(route('admin.properties.images.status.update', [$propertyId, $firstImage]), ['hab' => '0'])
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $firstImage,
            'Hab' => false,
            'Portada' => false,
        ]);
        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $secondImage,
            'Hab' => true,
            'Portada' => true,
        ]);
        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'TieneFoto' => true,
        ]);
        $this->assertDatabaseCount('bienesraices_imagenes', 2);

        $this->actingAs($administrator)
            ->get(route('admin.properties.show', $propertyId))
            ->assertOk()
            ->assertDontSee("bienesraices/{$propertyId}/imagenes/primera.jpg", false)
            ->assertSee("bienesraices/{$propertyId}/imagenes/segunda.jpg", false);

        $this->actingAs($administrator)
            ->get(route('admin.properties.images.index', $propertyId))
            ->assertOk()
            ->assertSee('2 imágenes · 1 habilitada')
            ->assertSee('data-confirm-title="¿Habilitar esta imagen?"', false)
            ->assertDontSee(route('admin.properties.images.cover.update', [$propertyId, $firstImage]), false);

        $this->actingAs($administrator)
            ->patch(route('admin.properties.images.status.update', [$propertyId, $secondImage]), ['hab' => '0'])
            ->assertRedirect(route('admin.properties.images.index', $propertyId));

        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $secondImage,
            'Hab' => false,
            'Portada' => false,
        ]);
        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'TieneFoto' => false,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.properties.show', $propertyId))
            ->assertOk()
            ->assertSee('Todavía no hay imágenes habilitadas.')
            ->assertDontSee("bienesraices/{$propertyId}/imagenes/primera.jpg", false)
            ->assertDontSee("bienesraices/{$propertyId}/imagenes/segunda.jpg", false);

        $this->actingAs($administrator)
            ->patch(route('admin.properties.images.status.update', [$propertyId, $firstImage]), ['hab' => '1'])
            ->assertRedirect(route('admin.properties.images.index', $propertyId));

        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $firstImage,
            'Hab' => true,
            'Portada' => true,
        ]);
        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'TieneFoto' => true,
        ]);
        $this->assertDatabaseCount('bienesraices_imagenes', 2);
    }

    public function test_disabled_images_cannot_be_selected_as_cover_and_status_is_validated(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();
        $firstImage = $this->insertImage($propertyId, 'primera.jpg', 1, true);
        $disabledImage = $this->insertImage($propertyId, 'segunda.jpg', 2, false, false);
        DB::table('bienesraices')->where('id', $propertyId)->update(['TieneFoto' => true]);

        $this->actingAs($administrator)
            ->from(route('admin.properties.images.index', $propertyId))
            ->patch(route('admin.properties.images.cover.update', [$propertyId, $disabledImage]))
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHasErrors('imagen');

        $this->actingAs($administrator)
            ->from(route('admin.properties.images.index', $propertyId))
            ->patch(route('admin.properties.images.status.update', [$propertyId, $disabledImage]), ['hab' => 'inválido'])
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHasErrors('hab');

        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $firstImage,
            'Hab' => true,
            'Portada' => true,
        ]);
        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $disabledImage,
            'Hab' => false,
            'Portada' => false,
        ]);
    }

    public function test_administrators_can_reorder_all_property_images_without_changing_the_cover(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();
        $firstImage = $this->insertImage($propertyId, 'primera.jpg', 10, true);
        $secondImage = $this->insertImage($propertyId, 'segunda.jpg', 20, false);
        $thirdImage = $this->insertImage($propertyId, 'tercera.jpg', 30, false);

        $this->actingAs($administrator)
            ->patch(route('admin.properties.images.order.update', $propertyId), [
                'imagenes' => [$thirdImage, $firstImage, $secondImage],
            ])
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $thirdImage,
            'Orden' => 1,
            'Portada' => false,
        ]);
        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $firstImage,
            'Orden' => 2,
            'Portada' => true,
        ]);
        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $secondImage,
            'Orden' => 3,
            'Portada' => false,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.properties.images.index', $propertyId))
            ->assertOk()
            ->assertSeeInOrder(['tercera.jpg', 'primera.jpg', 'segunda.jpg']);
    }

    public function test_reordering_rejects_duplicates_missing_images_and_images_from_another_property(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();
        $firstImage = $this->insertImage($propertyId, 'primera.jpg', 1, true);
        $secondImage = $this->insertImage($propertyId, 'segunda.jpg', 2, false);
        $otherPropertyId = $this->insertProperty('PROP002');
        $otherImage = $this->insertImage($otherPropertyId, 'otra.jpg', 1, true);

        $this->actingAs($administrator)
            ->from(route('admin.properties.images.index', $propertyId))
            ->patch(route('admin.properties.images.order.update', $propertyId), [
                'imagenes' => [$firstImage, $firstImage],
            ])
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHasErrors('imagenes.1');

        $this->actingAs($administrator)
            ->from(route('admin.properties.images.index', $propertyId))
            ->patch(route('admin.properties.images.order.update', $propertyId), [
                'imagenes' => [$firstImage, $otherImage],
            ])
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHasErrors('imagenes');

        $this->actingAs($administrator)
            ->from(route('admin.properties.images.index', $propertyId))
            ->patch(route('admin.properties.images.order.update', $propertyId), [
                'imagenes' => [$firstImage],
            ])
            ->assertRedirect(route('admin.properties.images.index', $propertyId))
            ->assertSessionHasErrors('imagenes');

        $this->assertDatabaseHas('bienesraices_imagenes', ['id' => $firstImage, 'Orden' => 1]);
        $this->assertDatabaseHas('bienesraices_imagenes', ['id' => $secondImage, 'Orden' => 2]);
        $this->assertDatabaseHas('bienesraices_imagenes', ['id' => $otherImage, 'Orden' => 1]);
    }

    public function test_deleting_images_reassigns_the_cover_and_synchronizes_the_photo_indicator(): void
    {
        Storage::fake('public');
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();
        $firstImage = $this->insertImage($propertyId, 'primera.jpg', 1, true);
        $secondImage = $this->insertImage($propertyId, 'segunda.jpg', 2, false);
        DB::table('bienesraices')->where('id', $propertyId)->update(['TieneFoto' => true]);
        Storage::disk('public')->put("bienesraices/{$propertyId}/imagenes/primera.jpg", 'imagen');
        Storage::disk('public')->put("bienesraices/{$propertyId}/imagenes/segunda.jpg", 'imagen');

        $this->actingAs($administrator)
            ->delete(route('admin.properties.images.destroy', [$propertyId, $firstImage]))
            ->assertRedirect(route('admin.properties.images.index', $propertyId));

        Storage::disk('public')->assertMissing("bienesraices/{$propertyId}/imagenes/primera.jpg");
        $this->assertDatabaseHas('bienesraices_imagenes', [
            'id' => $secondImage,
            'Orden' => 1,
            'Portada' => true,
        ]);
        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'TieneFoto' => true,
        ]);

        $this->actingAs($administrator)
            ->delete(route('admin.properties.images.destroy', [$propertyId, $secondImage]))
            ->assertRedirect(route('admin.properties.images.index', $propertyId));

        Storage::disk('public')->assertMissing("bienesraices/{$propertyId}/imagenes/segunda.jpg");
        $this->assertDatabaseCount('bienesraices_imagenes', 0);
        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'TieneFoto' => false,
        ]);
    }

    public function test_unknown_or_unrelated_images_return_not_found(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();
        $otherPropertyId = $this->insertProperty('PROP002');
        $otherImageId = $this->insertImage($otherPropertyId, 'otra.jpg', 1, true);

        $this->actingAs($administrator)
            ->patch(route('admin.properties.images.cover.update', [$propertyId, $otherImageId]))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->patch(route('admin.properties.images.status.update', [$propertyId, $otherImageId]), ['hab' => '0'])
            ->assertNotFound();

        $this->actingAs($administrator)
            ->patch(route('admin.properties.images.status.update', [$propertyId, 999]), ['hab' => '0'])
            ->assertNotFound();

        $this->actingAs($administrator)
            ->delete(route('admin.properties.images.destroy', [$propertyId, 999]))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->get(route('admin.properties.images.index', 999))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->patch(route('admin.properties.images.order.update', 999), ['imagenes' => [$otherImageId]])
            ->assertNotFound();
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }

    private function insertProperty(string $code = 'PROP001'): int
    {
        return DB::table('bienesraices')->insertGetId([
            'Codigo' => $code,
            'Descrip' => 'Propiedad de prueba',
            'Calle' => 'Avenida Corrientes',
            'Numero' => '1234',
            'Destacada' => false,
            'Slug' => strtolower($code),
            'TieneFoto' => false,
            'TieneVideo' => false,
            'Hab' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertImage(int $propertyId, string $filename, int $order, bool $cover, bool $enabled = true): int
    {
        return DB::table('bienesraices_imagenes')->insertGetId([
            'idBienRaiz' => $propertyId,
            'Archivo' => "bienesraices/{$propertyId}/imagenes/{$filename}",
            'Orden' => $order,
            'Portada' => $cover,
            'Hab' => $enabled,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
