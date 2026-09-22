<?php

namespace App\Services\Geocoding;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class NominatimGeocoder
{
    public const RATE_LIMIT_KEY = 'nominatim-geocoding';

    /**
     * @return array<int, array<string, string|null>>
     */
    public function search(string $query): array
    {
        $normalizedQuery = preg_replace('/\s+/u', ' ', trim($query)) ?? trim($query);
        $baseUrl = rtrim((string) config('services.nominatim.base_url'), '/');
        $cacheKey = 'nominatim-search:'.hash('sha256', $baseUrl.'|'.mb_strtolower($normalizedQuery));
        $cacheTtl = max(60, (int) config('services.nominatim.cache_ttl', 86400));

        return Cache::remember($cacheKey, now()->addSeconds($cacheTtl), function () use ($baseUrl, $normalizedQuery): array {
            if (RateLimiter::hit(self::RATE_LIMIT_KEY, 1) > 1) {
                throw new TooManyRequestsHttpException(
                    max(1, RateLimiter::availableIn(self::RATE_LIMIT_KEY)),
                    'El servicio de direcciones admite una búsqueda por segundo.'
                );
            }

            $response = Http::acceptJson()
                ->withHeaders([
                    'Accept-Language' => 'es',
                    'Referer' => (string) config('app.url'),
                    'User-Agent' => (string) config('services.nominatim.user_agent'),
                ])
                ->connectTimeout(3)
                ->timeout(8)
                ->get($baseUrl.'/search', [
                    'q' => $normalizedQuery,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'limit' => 5,
                    'accept-language' => 'es',
                    'layer' => 'address',
                ])
                ->throw()
                ->json();

            if (! is_array($response)) {
                return [];
            }

            return array_values(array_filter(array_map(
                fn (mixed $result): ?array => $this->mapResult($result),
                $response
            )));
        });
    }

    /**
     * @return array<string, string|null>|null
     */
    private function mapResult(mixed $result): ?array
    {
        if (! is_array($result) || ! is_array($result['address'] ?? null)) {
            return null;
        }

        $label = $this->stringValue($result['display_name'] ?? null);

        if ($label === null) {
            return null;
        }

        $address = $result['address'];
        $street = $this->firstValue($address, ['road', 'pedestrian', 'footway', 'street']);
        $houseNumber = $this->stringValue($address['house_number'] ?? null);
        $domicilio = trim(implode(' ', array_filter([$street, $houseNumber])));

        return [
            'id' => $this->stringValue($result['place_id'] ?? null) ?? hash('sha256', $label),
            'label' => $label,
            'calle' => $street,
            'numero' => $houseNumber,
            'domicilio' => $domicilio !== '' ? $domicilio : null,
            'codigo_postal' => $this->firstValue($address, ['postcode']),
            'provincia' => $this->firstValue($address, ['state', 'region']),
            'partido' => $this->firstValue($address, ['municipality', 'county', 'state_district']),
            'localidad' => $this->firstValue($address, ['city', 'town', 'village', 'municipality']),
            'barrio' => $this->firstValue($address, ['suburb', 'neighbourhood', 'quarter', 'city_district']),
            'latitud' => $this->coordinate($result['lat'] ?? null, -90, 90),
            'longitud' => $this->coordinate($result['lon'] ?? null, -180, 180),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $keys
     */
    private function firstValue(array $values, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->stringValue($values[$key] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function coordinate(mixed $value, float $minimum, float $maximum): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $coordinate = (float) $value;

        if ($coordinate < $minimum || $coordinate > $maximum) {
            return null;
        }

        return trim((string) $value);
    }
}
