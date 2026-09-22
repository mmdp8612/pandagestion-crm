<?php

namespace App\Http\Controllers;

use App\Support\PropertyVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class PublicPropertyController extends Controller
{
    public function show(string $slug): View
    {
        $property = $this->findPublishedPropertyOrFail($slug);
        $images = DB::table('bienesraices_imagenes')
            ->select(['id', 'Archivo', 'Orden', 'Portada'])
            ->where('idBienRaiz', $property->id)
            ->where('Hab', true)
            ->orderBy('Orden')
            ->orderBy('id')
            ->get();

        $agency = DB::table('inmobiliaria')
            ->where('id', 1)
            ->where('Hab', true)
            ->first();

        $whatsappDigits = $agency?->Whatsapp
            ? preg_replace('/\D+/', '', $agency->Whatsapp)
            : null;

        $phone = $agency?->Telefonos
            ? trim(preg_split('/[,;]/', $agency->Telefonos)[0])
            : null;

        return view('public.properties.show', [
            'property' => $property,
            'images' => $images,
            'coverImage' => $images->first(fn (stdClass $image): bool => (bool) $image->Portada) ?? $images->first(),
            'mapLinks' => $this->mapLinks($property->Latitud, $property->Longitud),
            'video' => PropertyVideo::parse($property->VideoUrl),
            'agency' => $agency,
            'brandName' => $agency?->RazonSocial ?: config('app.name', 'PandaGestion'),
            'agencyAddress' => $agency
                ? implode(', ', array_filter([
                    $agency->Domicilio,
                    $agency->Barrio,
                    $agency->Localidad,
                    $agency->Provincia,
                ]))
                : null,
            'whatsappUrl' => $whatsappDigits ? 'https://wa.me/'.$whatsappDigits : null,
            'phoneUrl' => $phone ? 'tel:'.preg_replace('/[^0-9+]/', '', $phone) : null,
        ]);
    }

    private function findPublishedPropertyOrFail(string $slug): stdClass
    {
        $property = DB::table('bienesraices')
            ->leftJoin('tip_tipologia', 'bienesraices.IdTipologia', '=', 'tip_tipologia.IdTipologia')
            ->leftJoin('tip_uso', 'bienesraices.IdUso', '=', 'tip_uso.IdUso')
            ->leftJoin('tip_antiguedad', 'bienesraices.Antiguedad', '=', 'tip_antiguedad.IdAntiguedad')
            ->leftJoin('tip_orientacion', 'bienesraices.IdOrientacion', '=', 'tip_orientacion.IdOrientacion')
            ->leftJoin('tip_cochera', 'bienesraices.IdCochera', '=', 'tip_cochera.IdCochera')
            ->leftJoin('tip_vista', 'bienesraices.IdVista', '=', 'tip_vista.IdVista')
            ->leftJoin('tip_comercializacion', 'bienesraices.IdComercializacion', '=', 'tip_comercializacion.IdComercializacion')
            ->leftJoin('tip_tipomoneda as moneda_venta', 'bienesraices.idTipoMonedaVta', '=', 'moneda_venta.idTipoMoneda')
            ->leftJoin('tip_tipomoneda as moneda_alquiler', 'bienesraices.idTipoMonedaAlq', '=', 'moneda_alquiler.idTipoMoneda')
            ->select([
                'bienesraices.*',
                'tip_tipologia.Descrip as TipologiaDescripcion',
                'tip_tipologia.TipoGral as TipologiaGrupo',
                'tip_uso.Descrip as UsoDescripcion',
                'tip_antiguedad.Descrip as AntiguedadDescripcion',
                'tip_orientacion.Descrip as OrientacionDescripcion',
                'tip_cochera.Descrip as CocheraDescripcion',
                'tip_vista.Descrip as VistaDescripcion',
                'tip_comercializacion.Descrip as ComercializacionDescripcion',
                'moneda_venta.Descrip as MonedaVentaDescripcion',
                'moneda_venta.Simbolo as MonedaVentaSimbolo',
                'moneda_alquiler.Descrip as MonedaAlquilerDescripcion',
                'moneda_alquiler.Simbolo as MonedaAlquilerSimbolo',
            ])
            ->where('bienesraices.Slug', $slug)
            ->where('bienesraices.Hab', true)
            ->first();

        abort_if($property === null, 404);

        return $property;
    }

    /**
     * @return array{embed: string, full: string}|null
     */
    private function mapLinks(mixed $latitudeValue, mixed $longitudeValue): ?array
    {
        if ($latitudeValue === null || $longitudeValue === null) {
            return null;
        }

        $latitude = (float) $latitudeValue;
        $longitude = (float) $longitudeValue;

        if (! is_finite($latitude) || ! is_finite($longitude)
            || $latitude < -90 || $latitude > 90
            || $longitude < -180 || $longitude > 180) {
            return null;
        }

        $boundingBox = implode(',', [
            max(-180, $longitude - 0.008),
            max(-90, $latitude - 0.004),
            min(180, $longitude + 0.008),
            min(90, $latitude + 0.004),
        ]);

        return [
            'embed' => 'https://www.openstreetmap.org/export/embed.html?'.http_build_query([
                'bbox' => $boundingBox,
                'layer' => 'mapnik',
                'marker' => "{$latitude},{$longitude}",
            ], '', '&', PHP_QUERY_RFC3986),
            'full' => 'https://www.openstreetmap.org/?'.http_build_query([
                'mlat' => $latitude,
                'mlon' => $longitude,
            ], '', '&', PHP_QUERY_RFC3986)."#map=17/{$latitude}/{$longitude}",
        ];
    }
}
