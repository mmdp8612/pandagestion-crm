<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_and_users_without_permission_are_forbidden(): void
    {
        $this->get(route('admin.inquiries.index'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.inquiries.index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_list_search_and_filter_inquiries(): void
    {
        $user = $this->authorizedUser();
        $propertyId = $this->insertProperty();
        $targetId = $this->insertInquiry($propertyId, [
            'Nombre' => 'Laura Pérez',
            'Email' => 'laura@example.com',
            'Estado' => 'en_proceso',
        ]);
        $this->insertInquiry($propertyId, [
            'Nombre' => 'Martín Gómez',
            'Email' => 'martin@example.com',
            'Estado' => 'nueva',
        ]);

        $this->actingAs($user)
            ->get(route('admin.inquiries.index', ['buscar' => 'Laura', 'estado' => 'en_proceso']))
            ->assertOk()
            ->assertSee('Laura Pérez')
            ->assertDontSee('Martín Gómez')
            ->assertSee('En proceso')
            ->assertSee(route('admin.inquiries.show', $targetId), false)
            ->assertSee('Consultas');
    }

    public function test_inquiries_are_paginated_and_filters_are_preserved(): void
    {
        $user = $this->authorizedUser();
        $propertyId = $this->insertProperty();

        foreach (range(1, 16) as $number) {
            $this->insertInquiry($propertyId, [
                'Nombre' => "Contacto {$number}",
                'Estado' => 'nueva',
            ]);
        }

        $this->actingAs($user)
            ->get(route('admin.inquiries.index', ['estado' => 'nueva']))
            ->assertOk()
            ->assertViewHas('inquiries', fn ($inquiries) => $inquiries->count() === 15
                && $inquiries->total() === 16
                && str_contains($inquiries->url(2), 'estado=nueva'));
    }

    public function test_authorized_user_can_view_an_inquiry(): void
    {
        $user = $this->authorizedUser();
        $propertyId = $this->insertProperty();
        $inquiryId = $this->insertInquiry($propertyId);

        $this->actingAs($user)
            ->get(route('admin.inquiries.show', $inquiryId))
            ->assertOk()
            ->assertSee('Laura Pérez')
            ->assertSee('laura@example.com')
            ->assertSee('Quisiera coordinar una visita.')
            ->assertSee('PROP001')
            ->assertSee('Ficha administrativa');
    }

    public function test_authorized_user_can_update_the_inquiry_status(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('administrador');
        $propertyId = $this->insertProperty();
        $inquiryId = $this->insertInquiry($propertyId);

        $this->actingAs($user)
            ->patch(route('admin.inquiries.status.update', $inquiryId), ['estado' => 'respondida'])
            ->assertRedirect(route('admin.inquiries.show', $inquiryId))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('consultas', [
            'id' => $inquiryId,
            'Estado' => 'respondida',
        ]);
    }

    public function test_invalid_status_and_unknown_inquiry_are_rejected(): void
    {
        $user = $this->authorizedUser();
        $propertyId = $this->insertProperty();
        $inquiryId = $this->insertInquiry($propertyId);

        $this->actingAs($user)
            ->patch(route('admin.inquiries.status.update', $inquiryId), ['estado' => 'invalido'])
            ->assertSessionHasErrors('estado');

        $this->actingAs($user)
            ->get(route('admin.inquiries.show', 999999))
            ->assertNotFound();

        $this->assertDatabaseHas('consultas', [
            'id' => $inquiryId,
            'Estado' => 'nueva',
        ]);
    }

    private function authorizedUser(): User
    {
        $permission = Permission::findOrCreate('consultas', 'web');
        $permissionProperties = Permission::findOrCreate('bienesraices', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo([$permission, $permissionProperties]);

        return $user;
    }

    private function insertProperty(): int
    {
        return DB::table('bienesraices')->insertGetId([
            'Codigo' => 'PROP001',
            'Calle' => 'Avenida Santa Fe',
            'Numero' => '3200',
            'Localidad' => 'Buenos Aires',
            'Barrio' => 'Palermo',
            'Destacada' => false,
            'Slug' => 'casa-en-palermo-prop001',
            'TieneFoto' => false,
            'TieneVideo' => false,
            'Hab' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertInquiry(int $propertyId, array $overrides = []): int
    {
        return DB::table('consultas')->insertGetId(array_merge([
            'idBienRaiz' => $propertyId,
            'CodigoPropiedad' => 'PROP001',
            'Nombre' => 'Laura Pérez',
            'Email' => 'laura@example.com',
            'Telefono' => '11 5555-1234',
            'Mensaje' => 'Quisiera coordinar una visita.',
            'Estado' => 'nueva',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
