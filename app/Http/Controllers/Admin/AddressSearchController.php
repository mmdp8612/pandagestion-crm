<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SearchAddressRequest;
use App\Services\Geocoding\NominatimGeocoder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class AddressSearchController extends Controller
{
    public function __invoke(SearchAddressRequest $request, NominatimGeocoder $geocoder): JsonResponse
    {
        try {
            $results = $geocoder->search($request->validated('query'));
        } catch (TooManyRequestsHttpException $exception) {
            return response()->json([
                'message' => 'Esperá un segundo antes de realizar otra búsqueda.',
            ], 429, $exception->getHeaders());
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo consultar el servicio de direcciones. Intentá nuevamente en unos instantes.',
            ], 503);
        }

        return response()->json([
            'data' => $results,
        ]);
    }
}
