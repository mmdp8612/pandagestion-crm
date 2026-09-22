<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Geocoding\NominatimGeocoder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AddressSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.nominatim.base_url', 'https://nominatim.openstreetmap.org');
        config()->set('services.nominatim.user_agent', 'PandaGestion/1.0 (https://panda.test)');
        config()->set('services.nominatim.cache_ttl', 86400);

        Cache::flush();
        RateLimiter::clear(NominatimGeocoder::RATE_LIMIT_KEY);
        Http::preventStrayRequests();
    }

    public function test_guests_are_redirected_from_the_address_search(): void
    {
        $this->get(route('admin.real-estate-agency.addresses.search', ['query' => 'Corrientes 1234']))
            ->assertRedirect(route('login'));
    }

    public function test_users_without_configuration_permission_cannot_search_addresses(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('admin.real-estate-agency.addresses.search', ['query' => 'Corrientes 1234']))
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_users_with_real_estate_permission_can_search_from_the_property_form_only(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response([$this->providerResult()]),
        ]);
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('bienesraices');

        $this->actingAs($user)
            ->getJson(route('admin.real-estate-agency.addresses.search', ['query' => 'Corrientes 1234']))
            ->assertForbidden();

        $this->actingAs($user)
            ->getJson(route('admin.properties.addresses.search', ['query' => 'Corrientes 1234']))
            ->assertOk()
            ->assertJsonPath('data.0.calle', 'Avenida Corrientes')
            ->assertJsonPath('data.0.numero', '1234');

        Http::assertSentCount(1);
    }

    public function test_the_address_query_is_validated(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->getJson(route('admin.real-estate-agency.addresses.search', ['query' => 'abc']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('query');

        Http::assertNothingSent();
    }

    public function test_administrators_can_search_and_receive_normalized_address_data(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response([$this->providerResult()]),
        ]);
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->getJson(route('admin.real-estate-agency.addresses.search', [
                'query' => '  Avenida   Corrientes 1234, Buenos Aires  ',
            ]))
            ->assertOk()
            ->assertJsonPath('data.0.id', '12345')
            ->assertJsonPath('data.0.label', 'Avenida Corrientes 1234, San Nicolás, Buenos Aires, Argentina')
            ->assertJsonPath('data.0.calle', 'Avenida Corrientes')
            ->assertJsonPath('data.0.numero', '1234')
            ->assertJsonPath('data.0.domicilio', 'Avenida Corrientes 1234')
            ->assertJsonPath('data.0.codigo_postal', 'C1043')
            ->assertJsonPath('data.0.provincia', 'Ciudad Autónoma de Buenos Aires')
            ->assertJsonPath('data.0.partido', 'Comuna 3')
            ->assertJsonPath('data.0.localidad', 'Buenos Aires')
            ->assertJsonPath('data.0.barrio', 'San Nicolás')
            ->assertJsonPath('data.0.latitud', '-34.6037000')
            ->assertJsonPath('data.0.longitud', '-58.3816000');

        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return str_starts_with($request->url(), 'https://nominatim.openstreetmap.org/search?')
                && $request->hasHeader('User-Agent', 'PandaGestion/1.0 (https://panda.test)')
                && $request->hasHeader('Referer', 'http://localhost:8000')
                && $data['q'] === 'Avenida Corrientes 1234, Buenos Aires'
                && $data['format'] === 'jsonv2'
                && $data['addressdetails'] === 1
                && $data['limit'] === 5
                && $data['accept-language'] === 'es'
                && $data['layer'] === 'address';
        });
    }

    public function test_identical_searches_are_served_from_the_cache(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response([$this->providerResult()]),
        ]);
        $administrator = $this->createAdministrator();
        $route = route('admin.real-estate-agency.addresses.search', ['query' => 'Corrientes 1234']);

        $this->actingAs($administrator)->getJson($route)->assertOk();
        $this->actingAs($administrator)->getJson($route)->assertOk();

        Http::assertSentCount(1);
    }

    public function test_uncached_provider_requests_are_limited_globally_to_one_per_second(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response([$this->providerResult()]),
        ]);
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->getJson(route('admin.real-estate-agency.addresses.search', ['query' => 'Corrientes 1234']))
            ->assertOk();

        $this->actingAs($administrator)
            ->getJson(route('admin.real-estate-agency.addresses.search', ['query' => 'Santa Fe 2000']))
            ->assertTooManyRequests()
            ->assertHeader('Retry-After')
            ->assertJsonPath('message', 'Esperá un segundo antes de realizar otra búsqueda.');

        Http::assertSentCount(1);
    }

    public function test_provider_failures_return_a_controlled_service_error(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response(['error' => 'Unavailable'], 503),
        ]);
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->getJson(route('admin.real-estate-agency.addresses.search', ['query' => 'Corrientes 1234']))
            ->assertServiceUnavailable()
            ->assertJsonPath(
                'message',
                'No se pudo consultar el servicio de direcciones. Intentá nuevamente en unos instantes.'
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function providerResult(): array
    {
        return [
            'place_id' => 12345,
            'display_name' => 'Avenida Corrientes 1234, San Nicolás, Buenos Aires, Argentina',
            'lat' => '-34.6037000',
            'lon' => '-58.3816000',
            'address' => [
                'road' => 'Avenida Corrientes',
                'house_number' => '1234',
                'postcode' => 'C1043',
                'state' => 'Ciudad Autónoma de Buenos Aires',
                'municipality' => 'Comuna 3',
                'city' => 'Buenos Aires',
                'suburb' => 'San Nicolás',
            ],
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
