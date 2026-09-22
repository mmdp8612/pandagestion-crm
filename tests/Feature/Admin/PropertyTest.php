<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\AntiquitiesSeeder;
use Database\Seeders\CommercializationsSeeder;
use Database\Seeders\CurrencyTypesSeeder;
use Database\Seeders\GaragesSeeder;
use Database\Seeders\OrientationsSeeder;
use Database\Seeders\PropertyUsesSeeder;
use Database\Seeders\PropertyViewsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TypologiesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_property_management(): void
    {
        $this->get(route('admin.properties.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.properties.create'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.properties.show', 1))
            ->assertRedirect(route('login'));

        $this->post(route('admin.properties.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_users_without_real_estate_permission_cannot_manage_properties(): void
    {
        $user = User::factory()->create();
        $propertyId = $this->insertProperty();

        $this->actingAs($user)
            ->get(route('admin.properties.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.properties.show', $propertyId))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.properties.store'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.properties.status.update', $propertyId), ['hab' => false])
            ->assertForbidden();

        $this->actingAs($user)
            ->getJson(route('admin.properties.addresses.search', ['query' => 'Corrientes 1234']))
            ->assertForbidden();

        $this->assertDatabaseCount('bienesraices', 1);
        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'Hab' => true,
        ]);
    }

    public function test_users_with_real_estate_permission_can_access_the_module_and_its_menu(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('bienesraices');

        $this->actingAs($user)
            ->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSee('Bienes Raíces')
            ->assertSee('0 propiedades')
            ->assertSee(route('admin.properties.index'), false)
            ->assertDontSee('Próximamente');

        $this->actingAs($user)
            ->get(route('admin.properties.create'))
            ->assertOk();
    }

    public function test_administrators_can_view_the_initial_property_form_with_address_search_and_map(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.properties.create'))
            ->assertOk()
            ->assertSee('Nueva propiedad')
            ->assertSee('name="codigo"', false)
            ->assertSee('name="id_tipologia"', false)
            ->assertSee('value="DPTO"', false)
            ->assertSee('name="id_uso"', false)
            ->assertSee('value="VIVI"', false)
            ->assertSee('name="antiguedad"', false)
            ->assertSee('value="AES"', false)
            ->assertSee('name="id_orientacion"', false)
            ->assertSee('value="N"', false)
            ->assertSee('name="id_cochera"', false)
            ->assertSee('value="CCU"', false)
            ->assertSee('name="id_vista"', false)
            ->assertSee('value="C/FTE"', false)
            ->assertSee('name="id_comercializacion"', false)
            ->assertSee('value="A-V"', false)
            ->assertSee('name="importe_venta"', false)
            ->assertSee('name="id_tipo_moneda_venta"', false)
            ->assertSee('value="0"', false)
            ->assertSee('name="importe_alquiler"', false)
            ->assertSee('name="id_tipo_moneda_alquiler"', false)
            ->assertSee('name="sup_cubierta_propia"', false)
            ->assertSee('name="sup_terreno"', false)
            ->assertSee('name="frente"', false)
            ->assertSee('name="fondo"', false)
            ->assertSee('name="metros_fondo"', false)
            ->assertSee('name="luminosidad"', false)
            ->assertSee('name="plantas"', false)
            ->assertSee('name="ambientes"', false)
            ->assertSee('name="sanitarios"', false)
            ->assertSee('name="dormitorios"', false)
            ->assertSee('name="suite"', false)
            ->assertSee('name="lineas_telefonicas"', false)
            ->assertSee('name="destacada"', false)
            ->assertDontSee('name="tiene_foto"', false)
            ->assertDontSee('name="tiene_video"', false)
            ->assertSee('name="video_url"', false)
            ->assertSee('YouTube o Vimeo')
            ->assertSee('Podrás cargar las imágenes después de crear la propiedad.')
            ->assertSee('El slug se generará automáticamente')
            ->assertSee('name="calle"', false)
            ->assertSee('name="numero"', false)
            ->assertSee('name="latitud"', false)
            ->assertSee('name="longitud"', false)
            ->assertSee('data-address-search', false)
            ->assertSee(route('admin.properties.addresses.search'), false)
            ->assertSee('OpenStreetMap contributors')
            ->assertSee('data-address-map', false)
            ->assertSee('data-address-map-frame', false)
            ->assertSee('data-address-map-link', false);
    }

    public function test_administrators_can_create_a_property_with_normalized_general_and_location_data(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->post(route('admin.properties.store'), [
                ...$this->validData(),
                'codigo' => ' prop001 ',
                'descripcion' => '  Departamento luminoso  ',
                'id_tipologia' => ' dpto ',
                'id_uso' => ' vivi ',
                'antiguedad' => ' aes ',
                'id_orientacion' => ' n ',
                'id_cochera' => ' ccu ',
                'id_vista' => ' c/fte ',
                'id_comercializacion' => ' a-v ',
                'luminosidad' => '  Muy   Buena  ',
                'calle' => ' Avenida Corrientes ',
                'torre' => ' ',
            ])
            ->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bienesraices', [
            'Codigo' => 'PROP001',
            'Descrip' => 'Departamento luminoso',
            'IdTipologia' => 'DPTO',
            'IdUso' => 'VIVI',
            'Antiguedad' => 'AES',
            'IdOrientacion' => 'N',
            'IdCochera' => 'CCU',
            'IdVista' => 'C/FTE',
            'IdComercializacion' => 'A-V',
            'ImporteVta' => 150000,
            'ImporteAlq' => 800000.5,
            'idTipoMonedaVta' => 0,
            'idTipoMonedaAlq' => 1,
            'SupCubiertaPropia' => 85.5,
            'SupTerreno' => 120.75,
            'Frente' => 12.5,
            'Fondo' => 24.75,
            'MtsFondo' => 30.25,
            'Luminosidad' => 'Muy Buena',
            'Plantas' => 1,
            'Ambientes' => 4,
            'Sanitarios' => 2,
            'Suite' => 1,
            'Dormitorios' => 3,
            'LineasTel' => 2,
            'Destacada' => true,
            'TieneFoto' => false,
            'TieneVideo' => true,
            'VideoUrl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'Slug' => 'departamento-4-ambientes-san-nicolas-prop001',
            'Calle' => 'Avenida Corrientes',
            'Numero' => '1234',
            'Piso' => '5',
            'Torre' => null,
            'Provincia' => 'Ciudad Autónoma de Buenos Aires',
            'Partido' => 'Comuna 3',
            'Localidad' => 'Buenos Aires',
            'Barrio' => 'San Nicolás',
            'CodigoPostal' => 'C1043',
            'Latitud' => -34.6037,
            'Longitud' => -58.3816,
            'Hab' => true,
        ]);
    }

    public function test_property_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.properties.create'))
            ->post(route('admin.properties.store'), [
                'codigo' => 'PROP-001',
                'descripcion' => str_repeat('a', 5001),
                'calle' => ' ',
                'latitud' => '91',
                'tiene_foto' => '1',
                'tiene_video' => '1',
                'video_url' => 'https://youtube.com.ejemplo.test/watch?v=dQw4w9WgXcQ',
                'hab' => 'inválido',
            ])
            ->assertRedirect(route('admin.properties.create'))
            ->assertSessionHasErrors([
                'codigo',
                'descripcion',
                'calle',
                'latitud',
                'longitud',
                'destacada',
                'tiene_foto',
                'tiene_video',
                'video_url',
                'hab',
            ]);

        $this->assertDatabaseCount('bienesraices', 0);
    }

    public function test_physical_characteristics_are_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.properties.create'))
            ->post(route('admin.properties.store'), [
                ...$this->validData(),
                'sup_cubierta_propia' => '-1',
                'sup_terreno' => '120.999',
                'frente' => '-1',
                'fondo' => '120.999',
                'metros_fondo' => 'muchos',
                'luminosidad' => str_repeat('a', 31),
                'plantas' => '-1',
                'ambientes' => '3.5',
                'sanitarios' => '65536',
                'suite' => 'uno',
                'dormitorios' => '-1',
                'lineas_telefonicas' => '2.5',
            ])
            ->assertRedirect(route('admin.properties.create'))
            ->assertSessionHasErrors([
                'sup_cubierta_propia',
                'sup_terreno',
                'frente',
                'fondo',
                'metros_fondo',
                'luminosidad',
                'plantas',
                'ambientes',
                'sanitarios',
                'suite',
                'dormitorios',
                'lineas_telefonicas',
            ]);

        $this->assertDatabaseCount('bienesraices', 0);
    }

    public function test_commercial_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.properties.create'))
            ->post(route('admin.properties.store'), [
                ...$this->validData(),
                'id_comercializacion' => 'XXX',
                'importe_venta' => '-1',
                'id_tipo_moneda_venta' => '',
                'importe_alquiler' => '',
                'id_tipo_moneda_alquiler' => '1',
            ])
            ->assertRedirect(route('admin.properties.create'))
            ->assertSessionHasErrors([
                'id_comercializacion',
                'importe_venta',
                'id_tipo_moneda_venta',
                'importe_alquiler',
            ]);

        $this->assertDatabaseCount('bienesraices', 0);
    }

    public function test_disabled_commercial_values_cannot_be_assigned_to_a_new_property(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_comercializacion')->where('IdComercializacion', 'A-V')->update(['Hab' => false]);
        DB::table('tip_tipomoneda')->whereIn('idTipoMoneda', [0, 1])->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->from(route('admin.properties.create'))
            ->post(route('admin.properties.store'), $this->validData())
            ->assertRedirect(route('admin.properties.create'))
            ->assertSessionHasErrors([
                'id_comercializacion',
                'id_tipo_moneda_venta',
                'id_tipo_moneda_alquiler',
            ]);

        $this->assertDatabaseCount('bienesraices', 0);
    }

    public function test_disabled_catalog_values_cannot_be_assigned_to_a_new_property(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_tipologia')->where('IdTipologia', 'DPTO')->update(['Hab' => false]);
        DB::table('tip_uso')->where('IdUso', 'VIVI')->update(['Hab' => false]);
        DB::table('tip_antiguedad')->where('IdAntiguedad', 'AES')->update(['Hab' => false]);
        DB::table('tip_orientacion')->where('IdOrientacion', 'N')->update(['Hab' => false]);
        DB::table('tip_cochera')->where('IdCochera', 'CCU')->update(['Hab' => false]);
        DB::table('tip_vista')->where('IdVista', 'C/FTE')->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->from(route('admin.properties.create'))
            ->post(route('admin.properties.store'), $this->validData())
            ->assertRedirect(route('admin.properties.create'))
            ->assertSessionHasErrors([
                'id_tipologia',
                'id_uso',
                'antiguedad',
                'id_orientacion',
                'id_cochera',
                'id_vista',
            ]);

        $this->assertDatabaseCount('bienesraices', 0);
    }

    public function test_property_code_must_be_unique_after_normalization(): void
    {
        $administrator = $this->createAdministrator();
        $this->insertProperty();

        $this->actingAs($administrator)
            ->from(route('admin.properties.create'))
            ->post(route('admin.properties.store'), [
                ...$this->validData(),
                'codigo' => ' prop001 ',
            ])
            ->assertRedirect(route('admin.properties.create'))
            ->assertSessionHasErrors('codigo');

        $this->assertDatabaseCount('bienesraices', 1);
    }

    public function test_administrators_can_edit_a_property_and_change_its_unique_code(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();

        $this->actingAs($administrator)
            ->get(route('admin.properties.edit', $propertyId))
            ->assertOk()
            ->assertSee('Editar propiedad')
            ->assertSee('value="PROP001"', false)
            ->assertSee('value="departamento-4-ambientes-san-nicolas-prop001"', false)
            ->assertSee('name="regenerar_slug"', false)
            ->assertSee('data-confirm-when="#regenerar_slug"', false)
            ->assertSee('data-address-map', false);

        $this->actingAs($administrator)
            ->put(route('admin.properties.update', $propertyId), [
                ...$this->validData(),
                'codigo' => ' prop002 ',
                'descripcion' => ' ',
                'id_tipologia' => '',
                'id_uso' => '',
                'antiguedad' => '',
                'id_orientacion' => '',
                'id_cochera' => '',
                'id_vista' => '',
                'id_comercializacion' => '',
                'importe_venta' => '',
                'id_tipo_moneda_venta' => '',
                'importe_alquiler' => '',
                'id_tipo_moneda_alquiler' => '',
                'sup_cubierta_propia' => '',
                'sup_terreno' => '',
                'frente' => '',
                'fondo' => '',
                'metros_fondo' => '',
                'luminosidad' => '',
                'plantas' => '',
                'ambientes' => '',
                'sanitarios' => '',
                'suite' => '',
                'dormitorios' => '',
                'lineas_telefonicas' => '',
                'video_url' => '',
                'calle' => ' Calle Nueva ',
                'numero' => ' ',
                'latitud' => '',
                'longitud' => '',
                'hab' => '0',
            ])
            ->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'Codigo' => 'PROP002',
            'Descrip' => null,
            'IdTipologia' => null,
            'IdUso' => null,
            'Antiguedad' => null,
            'IdOrientacion' => null,
            'IdCochera' => null,
            'IdVista' => null,
            'IdComercializacion' => null,
            'ImporteVta' => null,
            'ImporteAlq' => null,
            'idTipoMonedaVta' => null,
            'idTipoMonedaAlq' => null,
            'SupCubiertaPropia' => null,
            'SupTerreno' => null,
            'Frente' => null,
            'Fondo' => null,
            'MtsFondo' => null,
            'Luminosidad' => null,
            'Plantas' => null,
            'Ambientes' => null,
            'Sanitarios' => null,
            'Suite' => null,
            'Dormitorios' => null,
            'LineasTel' => null,
            'Destacada' => true,
            'TieneFoto' => false,
            'TieneVideo' => false,
            'VideoUrl' => null,
            'Slug' => 'departamento-4-ambientes-san-nicolas-prop001',
            'Calle' => 'Calle Nueva',
            'Numero' => null,
            'Latitud' => null,
            'Longitud' => null,
            'Hab' => false,
        ]);
        $this->assertDatabaseMissing('bienesraices', [
            'id' => $propertyId,
            'Codigo' => 'PROP001',
        ]);
    }

    public function test_property_update_rejects_a_code_used_by_another_property(): void
    {
        $administrator = $this->createAdministrator();
        $firstPropertyId = $this->insertProperty('PROP001');
        $secondPropertyId = $this->insertProperty('PROP002');

        $this->actingAs($administrator)
            ->from(route('admin.properties.edit', $secondPropertyId))
            ->put(route('admin.properties.update', $secondPropertyId), [
                ...$this->validData(),
                'codigo' => 'PROP001',
            ])
            ->assertRedirect(route('admin.properties.edit', $secondPropertyId))
            ->assertSessionHasErrors('codigo');

        $this->assertDatabaseHas('bienesraices', [
            'id' => $firstPropertyId,
            'Codigo' => 'PROP001',
        ]);
        $this->assertDatabaseHas('bienesraices', [
            'id' => $secondPropertyId,
            'Codigo' => 'PROP002',
        ]);
    }

    public function test_slug_is_preserved_by_default_and_regenerated_only_when_requested(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();

        $this->actingAs($administrator)
            ->put(route('admin.properties.update', $propertyId), [
                ...$this->validData(),
                'codigo' => 'PROP002',
                'barrio' => 'Palermo',
                'regenerar_slug' => '0',
            ])
            ->assertRedirect(route('admin.properties.index'));

        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'Codigo' => 'PROP002',
            'Slug' => 'departamento-4-ambientes-san-nicolas-prop001',
        ]);

        $this->actingAs($administrator)
            ->put(route('admin.properties.update', $propertyId), [
                ...$this->validData(),
                'codigo' => 'PROP002',
                'barrio' => 'Palermo',
                'regenerar_slug' => '1',
            ])
            ->assertRedirect(route('admin.properties.index'));

        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'Codigo' => 'PROP002',
            'Slug' => 'departamento-4-ambientes-palermo-prop002',
        ]);
    }

    public function test_generated_slug_receives_a_suffix_when_its_base_is_already_in_use(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty('PROP999');

        DB::table('bienesraices')
            ->where('id', $propertyId)
            ->update(['Slug' => 'departamento-4-ambientes-san-nicolas-prop001']);

        $this->actingAs($administrator)
            ->post(route('admin.properties.store'), $this->validData())
            ->assertRedirect(route('admin.properties.index'));

        $this->assertDatabaseHas('bienesraices', [
            'Codigo' => 'PROP001',
            'Slug' => 'departamento-4-ambientes-san-nicolas-prop001-2',
        ]);
    }

    public function test_a_property_can_keep_catalog_values_disabled_after_they_were_assigned(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();

        DB::table('tip_tipologia')->where('IdTipologia', 'DPTO')->update(['Hab' => false]);
        DB::table('tip_uso')->where('IdUso', 'VIVI')->update(['Hab' => false]);
        DB::table('tip_antiguedad')->where('IdAntiguedad', 'AES')->update(['Hab' => false]);
        DB::table('tip_orientacion')->where('IdOrientacion', 'N')->update(['Hab' => false]);
        DB::table('tip_cochera')->where('IdCochera', 'CCU')->update(['Hab' => false]);
        DB::table('tip_vista')->where('IdVista', 'C/FTE')->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->get(route('admin.properties.edit', $propertyId))
            ->assertOk()
            ->assertSee('Departamento (deshabilitada)')
            ->assertSee('Vivienda (deshabilitado)')
            ->assertSee('A Estrenar (deshabilitada)')
            ->assertSee('Norte (deshabilitada)')
            ->assertSee('Cochera Cubierta (deshabilitada)')
            ->assertSee('Contrafrente (deshabilitada)');

        $this->actingAs($administrator)
            ->put(route('admin.properties.update', $propertyId), $this->validData())
            ->assertRedirect(route('admin.properties.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'IdTipologia' => 'DPTO',
            'IdUso' => 'VIVI',
            'Antiguedad' => 'AES',
            'IdOrientacion' => 'N',
            'IdCochera' => 'CCU',
            'IdVista' => 'C/FTE',
        ]);

        DB::table('tip_tipologia')->where('IdTipologia', 'CASA')->update(['Hab' => false]);
        DB::table('tip_uso')->where('IdUso', 'COME')->update(['Hab' => false]);
        DB::table('tip_antiguedad')->where('IdAntiguedad', 'A10')->update(['Hab' => false]);
        DB::table('tip_orientacion')->where('IdOrientacion', 'E')->update(['Hab' => false]);
        DB::table('tip_cochera')->where('IdCochera', 'COC')->update(['Hab' => false]);
        DB::table('tip_vista')->where('IdVista', 'INTERNO')->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->from(route('admin.properties.edit', $propertyId))
            ->put(route('admin.properties.update', $propertyId), [
                ...$this->validData(),
                'id_tipologia' => 'CASA',
                'id_uso' => 'COME',
                'antiguedad' => 'A10',
                'id_orientacion' => 'E',
                'id_cochera' => 'COC',
                'id_vista' => 'INTERNO',
            ])
            ->assertRedirect(route('admin.properties.edit', $propertyId))
            ->assertSessionHasErrors([
                'id_tipologia',
                'id_uso',
                'antiguedad',
                'id_orientacion',
                'id_cochera',
                'id_vista',
            ]);

        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'IdTipologia' => 'DPTO',
            'IdUso' => 'VIVI',
            'Antiguedad' => 'AES',
            'IdOrientacion' => 'N',
            'IdCochera' => 'CCU',
            'IdVista' => 'C/FTE',
        ]);
    }

    public function test_property_list_displays_a_compact_relevant_summary_without_horizontal_scroll(): void
    {
        $administrator = $this->createAdministrator();
        $this->insertProperty();

        $this->actingAs($administrator)
            ->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSee('Imagen')
            ->assertSee('Propiedad')
            ->assertSee('Características')
            ->assertSee('Comercialización')
            ->assertSee('Sin imagen')
            ->assertSee('Departamento')
            ->assertSee('Vivienda')
            ->assertSee('Ambos V/A')
            ->assertSee('Venta: u$s 150.000,00')
            ->assertSee('Alquiler: $ 800.000,50')
            ->assertSee('85,50 m² cubiertos')
            ->assertSee('120,75 m² terreno')
            ->assertSee('4 ambientes')
            ->assertSee('3 dormitorios')
            ->assertSee('2 baños')
            ->assertSee('Destacada')
            ->assertSee('Video')
            ->assertSee('table-fixed', false)
            ->assertDontSee('overflow-x-auto', false)
            ->assertDontSee('A Estrenar')
            ->assertDontSee('Norte')
            ->assertDontSee('Cochera Cubierta')
            ->assertDontSee('Contrafrente')
            ->assertDontSee('Luminosidad: Excelente')
            ->assertDontSee('departamento-4-ambientes-san-nicolas-prop001');
    }

    public function test_administrators_can_view_the_complete_property_detail_with_gallery_and_map(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();
        $now = now();

        DB::table('bienesraices')->where('id', $propertyId)->update(['TieneFoto' => true]);
        DB::table('bienesraices_imagenes')->insert([
            [
                'idBienRaiz' => $propertyId,
                'Archivo' => "bienesraices/{$propertyId}/imagenes/portada.jpg",
                'Orden' => 1,
                'Portada' => true,
                'Hab' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'idBienRaiz' => $propertyId,
                'Archivo' => "bienesraices/{$propertyId}/imagenes/interior.jpg",
                'Orden' => 2,
                'Portada' => false,
                'Hab' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'idBienRaiz' => $propertyId,
                'Archivo' => "bienesraices/{$propertyId}/imagenes/oculta.jpg",
                'Orden' => 3,
                'Portada' => false,
                'Hab' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.properties.show', $propertyId))
            ->assertOk()
            ->assertSee('Ficha de PROP001')
            ->assertSee('Departamento')
            ->assertSee('Vivienda')
            ->assertSee('A Estrenar')
            ->assertSee('Norte')
            ->assertSee('Cochera Cubierta')
            ->assertSee('Contrafrente')
            ->assertSee('Ambos V/A')
            ->assertSee('u$s 150.000,00')
            ->assertSee('$ 800.000,50')
            ->assertSee('85,50 m²')
            ->assertSee('120,75 m²')
            ->assertSee('12,50 m²')
            ->assertSee('30,25 m')
            ->assertSee('Departamento luminoso')
            ->assertSee('Avenida Corrientes 1234')
            ->assertSee('San Nicolás')
            ->assertSee('departamento-4-ambientes-san-nicolas-prop001')
            ->assertSee('https://www.openstreetmap.org/export/embed.html?', false)
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('https://www.youtube.com/watch?v=dQw4w9WgXcQ', false)
            ->assertSee('OpenStreetMap contributors')
            ->assertSee("bienesraices/{$propertyId}/imagenes/portada.jpg", false)
            ->assertSee("bienesraices/{$propertyId}/imagenes/interior.jpg", false)
            ->assertDontSee("bienesraices/{$propertyId}/imagenes/oculta.jpg", false)
            ->assertSee('Posición 1')
            ->assertSee('Posición 2')
            ->assertSee(route('admin.properties.edit', $propertyId), false)
            ->assertSee(route('admin.properties.images.index', $propertyId), false);

        $this->actingAs($administrator)
            ->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSee('aria-label="Ver la ficha de la propiedad PROP001"', false)
            ->assertSee(route('admin.properties.show', $propertyId), false);
    }

    public function test_a_property_can_keep_disabled_commercial_values_but_cannot_replace_them_with_others(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();

        DB::table('tip_comercializacion')->where('IdComercializacion', 'A-V')->update(['Hab' => false]);
        DB::table('tip_tipomoneda')->whereIn('idTipoMoneda', [0, 1])->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->get(route('admin.properties.edit', $propertyId))
            ->assertOk()
            ->assertSee('Ambos V/A (deshabilitada)')
            ->assertSee('Dolares (u$s) - deshabilitada')
            ->assertSee('Pesos ($) - deshabilitada');

        $this->actingAs($administrator)
            ->put(route('admin.properties.update', $propertyId), $this->validData())
            ->assertRedirect(route('admin.properties.index'))
            ->assertSessionHasNoErrors();

        DB::table('tip_comercializacion')->where('IdComercializacion', 'VTA')->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->from(route('admin.properties.edit', $propertyId))
            ->put(route('admin.properties.update', $propertyId), [
                ...$this->validData(),
                'id_comercializacion' => 'VTA',
                'id_tipo_moneda_venta' => '1',
                'id_tipo_moneda_alquiler' => '0',
            ])
            ->assertRedirect(route('admin.properties.edit', $propertyId))
            ->assertSessionHasErrors([
                'id_comercializacion',
                'id_tipo_moneda_venta',
                'id_tipo_moneda_alquiler',
            ]);

        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'IdComercializacion' => 'A-V',
            'idTipoMonedaVta' => 0,
            'idTipoMonedaAlq' => 1,
        ]);
    }

    public function test_properties_can_be_searched_by_code_address_neighborhood_or_locality(): void
    {
        $administrator = $this->createAdministrator();
        $firstPropertyId = $this->insertProperty('PROP001');
        $secondPropertyId = $this->insertProperty('PROP002', 1);

        DB::table('bienesraices')
            ->where('id', $secondPropertyId)
            ->update([
                'Calle' => 'Calle Falsa',
                'Numero' => '742',
                'Barrio' => 'Palermo',
                'Localidad' => 'Rosario',
            ]);

        foreach (['PROP001', 'Avenida Corrientes 1234'] as $search) {
            $this->actingAs($administrator)
                ->get(route('admin.properties.index', ['buscar' => $search]))
                ->assertOk()
                ->assertSee('aria-label="Editar la propiedad PROP001"', false)
                ->assertDontSee('aria-label="Editar la propiedad PROP002"', false);
        }

        foreach (['Palermo', 'Rosario'] as $search) {
            $this->actingAs($administrator)
                ->get(route('admin.properties.index', ['buscar' => $search]))
                ->assertOk()
                ->assertSee('aria-label="Editar la propiedad PROP002"', false)
                ->assertDontSee('aria-label="Editar la propiedad PROP001"', false);
        }

        $this->assertDatabaseHas('bienesraices', [
            'id' => $firstPropertyId,
            'Codigo' => 'PROP001',
        ]);
    }

    public function test_property_filters_can_be_combined(): void
    {
        $administrator = $this->createAdministrator();
        $targetPropertyId = $this->insertProperty('OBJETIVO');
        $wrongTypologyId = $this->insertProperty('OTRATIPO', 1);
        $wrongCommercializationId = $this->insertProperty('OTRAVENTA', 2);
        $disabledPropertyId = $this->insertProperty('INACTIVA', 3);
        $notFeaturedPropertyId = $this->insertProperty('NODESTACA', 4);

        DB::table('bienesraices')->where('id', $wrongTypologyId)->update(['IdTipologia' => 'CASA']);
        DB::table('bienesraices')->where('id', $wrongCommercializationId)->update(['IdComercializacion' => 'VTA']);
        DB::table('bienesraices')->where('id', $disabledPropertyId)->update(['Hab' => false]);
        DB::table('bienesraices')->where('id', $notFeaturedPropertyId)->update(['Destacada' => false]);

        $response = $this->actingAs($administrator)
            ->get(route('admin.properties.index', [
                'buscar' => 'Corrientes',
                'tipologia' => 'dpto',
                'comercializacion' => 'a-v',
                'estado' => 'habilitadas',
                'destacada' => '1',
            ]));

        $response
            ->assertOk()
            ->assertSee('1 propiedad')
            ->assertSee('aria-label="Editar la propiedad OBJETIVO"', false)
            ->assertDontSee('aria-label="Editar la propiedad OTRATIPO"', false)
            ->assertDontSee('aria-label="Editar la propiedad OTRAVENTA"', false)
            ->assertDontSee('aria-label="Editar la propiedad INACTIVA"', false)
            ->assertDontSee('aria-label="Editar la propiedad NODESTACA"', false)
            ->assertSee('value="DPTO" selected', false)
            ->assertSee('value="A-V" selected', false)
            ->assertSee('value="habilitadas" selected', false)
            ->assertSee('name="destacada"', false)
            ->assertSee('checked', false)
            ->assertSee('Limpiar');

        $this->assertDatabaseHas('bienesraices', [
            'id' => $targetPropertyId,
            'Codigo' => 'OBJETIVO',
        ]);
    }

    public function test_property_filters_are_preserved_during_pagination(): void
    {
        $administrator = $this->createAdministrator();

        for ($index = 1; $index <= 16; $index++) {
            $this->insertProperty(sprintf('FILTRO%02d', $index), $index);
        }

        $this->actingAs($administrator)
            ->get(route('admin.properties.index', [
                'buscar' => 'Corrientes',
                'tipologia' => 'DPTO',
                'comercializacion' => 'A-V',
                'estado' => 'habilitadas',
                'destacada' => '1',
            ]))
            ->assertOk()
            ->assertSee('16 propiedades')
            ->assertSee('buscar=Corrientes', false)
            ->assertSee('tipologia=DPTO', false)
            ->assertSee('comercializacion=A-V', false)
            ->assertSee('estado=habilitadas', false)
            ->assertSee('destacada=1', false)
            ->assertSee('page=2', false);
    }

    public function test_property_filters_are_validated(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.properties.index'))
            ->get(route('admin.properties.index', [
                'buscar' => str_repeat('a', 121),
                'tipologia' => 'NOPE',
                'comercializacion' => 'XXX',
                'estado' => 'otro',
                'destacada' => '0',
            ]))
            ->assertRedirect(route('admin.properties.index'))
            ->assertSessionHasErrors([
                'buscar',
                'tipologia',
                'comercializacion',
                'estado',
                'destacada',
            ]);
    }

    public function test_properties_are_paginated_by_fifteen_records(): void
    {
        $administrator = $this->createAdministrator();

        for ($index = 1; $index <= 16; $index++) {
            $this->insertProperty(sprintf('PROP%03d', $index), $index);
        }

        $this->actingAs($administrator)
            ->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSee('16 propiedades')
            ->assertSee('page=2', false)
            ->assertDontSee('aria-label="Editar la propiedad PROP001"', false);

        $this->actingAs($administrator)
            ->get(route('admin.properties.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('aria-label="Editar la propiedad PROP001"', false);
    }

    public function test_administrators_can_enable_and_disable_properties_with_confirmation_controls(): void
    {
        $administrator = $this->createAdministrator();
        $propertyId = $this->insertProperty();

        $this->actingAs($administrator)
            ->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Deshabilitar PROP001?"', false)
            ->assertSee('data-confirm', false);

        $this->actingAs($administrator)
            ->patch(route('admin.properties.status.update', $propertyId), ['hab' => '0'])
            ->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'Hab' => false,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.properties.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Habilitar PROP001?"', false)
            ->assertSee('data-confirm-variant="success"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.properties.status.update', $propertyId), ['hab' => '1'])
            ->assertRedirect(route('admin.properties.index'));

        $this->assertDatabaseHas('bienesraices', [
            'id' => $propertyId,
            'Hab' => true,
        ]);
    }

    public function test_unknown_properties_return_not_found(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.properties.edit', 999))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->get(route('admin.properties.show', 999))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('admin.properties.update', 999), $this->validData())
            ->assertNotFound();
    }

    /**
     * @return array<string, string>
     */
    private function validData(): array
    {
        return [
            'codigo' => 'PROP001',
            'descripcion' => 'Departamento luminoso',
            'id_tipologia' => 'DPTO',
            'id_uso' => 'VIVI',
            'antiguedad' => 'AES',
            'id_orientacion' => 'N',
            'id_cochera' => 'CCU',
            'id_vista' => 'C/FTE',
            'id_comercializacion' => 'A-V',
            'importe_venta' => '150000.00',
            'id_tipo_moneda_venta' => '0',
            'importe_alquiler' => '800000.50',
            'id_tipo_moneda_alquiler' => '1',
            'sup_cubierta_propia' => '85.50',
            'sup_terreno' => '120.75',
            'frente' => '12.50',
            'fondo' => '24.75',
            'metros_fondo' => '30.25',
            'luminosidad' => 'Excelente',
            'plantas' => '1',
            'ambientes' => '4',
            'sanitarios' => '2',
            'suite' => '1',
            'dormitorios' => '3',
            'lineas_telefonicas' => '2',
            'destacada' => '1',
            'video_url' => ' https://youtu.be/dQw4w9WgXcQ?t=30 ',
            'calle' => 'Avenida Corrientes',
            'numero' => '1234',
            'piso' => '5',
            'torre' => 'A',
            'provincia' => 'Ciudad Autónoma de Buenos Aires',
            'partido' => 'Comuna 3',
            'localidad' => 'Buenos Aires',
            'barrio' => 'San Nicolás',
            'codigo_postal' => 'C1043',
            'latitud' => '-34.6037000',
            'longitud' => '-58.3816000',
            'hab' => '1',
        ];
    }

    private function insertProperty(string $code = 'PROP001', int $minutes = 0): int
    {
        $timestamp = now()->addMinutes($minutes);

        return DB::table('bienesraices')->insertGetId([
            'Codigo' => $code,
            'Descrip' => 'Departamento luminoso',
            'IdTipologia' => 'DPTO',
            'IdUso' => 'VIVI',
            'Antiguedad' => 'AES',
            'IdOrientacion' => 'N',
            'IdCochera' => 'CCU',
            'IdVista' => 'C/FTE',
            'IdComercializacion' => 'A-V',
            'ImporteVta' => 150000,
            'ImporteAlq' => 800000.5,
            'idTipoMonedaVta' => 0,
            'idTipoMonedaAlq' => 1,
            'SupCubiertaPropia' => 85.5,
            'SupTerreno' => 120.75,
            'Frente' => 12.5,
            'Fondo' => 24.75,
            'MtsFondo' => 30.25,
            'Luminosidad' => 'Excelente',
            'Plantas' => 1,
            'Ambientes' => 4,
            'Sanitarios' => 2,
            'Suite' => 1,
            'Dormitorios' => 3,
            'LineasTel' => 2,
            'Destacada' => true,
            'TieneFoto' => false,
            'TieneVideo' => true,
            'VideoUrl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'Slug' => 'departamento-4-ambientes-san-nicolas-'.strtolower($code),
            'Calle' => 'Avenida Corrientes',
            'Numero' => '1234',
            'Piso' => '5',
            'Torre' => 'A',
            'Provincia' => 'Ciudad Autónoma de Buenos Aires',
            'Partido' => 'Comuna 3',
            'Localidad' => 'Buenos Aires',
            'Barrio' => 'San Nicolás',
            'CodigoPostal' => 'C1043',
            'Latitud' => -34.6037,
            'Longitud' => -58.3816,
            'Hab' => true,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(TypologiesSeeder::class);
        $this->seed(PropertyUsesSeeder::class);
        $this->seed(AntiquitiesSeeder::class);
        $this->seed(OrientationsSeeder::class);
        $this->seed(GaragesSeeder::class);
        $this->seed(PropertyViewsSeeder::class);
        $this->seed(CommercializationsSeeder::class);
        $this->seed(CurrencyTypesSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
