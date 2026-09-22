<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PropertyIndexRequest;
use App\Http\Requests\Admin\StorePropertyRequest;
use App\Http\Requests\Admin\UpdatePropertyRequest;
use App\Http\Requests\Admin\UpdatePropertyStatusRequest;
use App\Support\PropertyVideo;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use stdClass;

class PropertyController extends Controller
{
    public function index(PropertyIndexRequest $request): View
    {
        $validated = $request->validated();
        $search = $validated['buscar'] ?? null;
        $typology = $validated['tipologia'] ?? null;
        $commercialization = $validated['comercializacion'] ?? null;
        $status = $validated['estado'] ?? null;
        $featured = ($validated['destacada'] ?? null) === '1';

        $mainImage = DB::table('bienesraices_imagenes')
            ->select('Archivo')
            ->whereColumn('idBienRaiz', 'bienesraices.id')
            ->where('Hab', true)
            ->orderByDesc('Portada')
            ->orderBy('Orden')
            ->orderBy('id')
            ->limit(1);

        $properties = DB::table('bienesraices')
            ->leftJoin('tip_tipologia', 'bienesraices.IdTipologia', '=', 'tip_tipologia.IdTipologia')
            ->leftJoin('tip_uso', 'bienesraices.IdUso', '=', 'tip_uso.IdUso')
            ->leftJoin('tip_comercializacion', 'bienesraices.IdComercializacion', '=', 'tip_comercializacion.IdComercializacion')
            ->leftJoin('tip_tipomoneda as moneda_venta', 'bienesraices.idTipoMonedaVta', '=', 'moneda_venta.idTipoMoneda')
            ->leftJoin('tip_tipomoneda as moneda_alquiler', 'bienesraices.idTipoMonedaAlq', '=', 'moneda_alquiler.idTipoMoneda')
            ->select([
                'bienesraices.*',
                'tip_tipologia.Descrip as TipologiaDescripcion',
                'tip_uso.Descrip as UsoDescripcion',
                'tip_comercializacion.Descrip as ComercializacionDescripcion',
                'moneda_venta.Descrip as MonedaVentaDescripcion',
                'moneda_venta.Simbolo as MonedaVentaSimbolo',
                'moneda_alquiler.Descrip as MonedaAlquilerDescripcion',
                'moneda_alquiler.Simbolo as MonedaAlquilerSimbolo',
            ])
            ->selectSub($mainImage, 'ImagenPrincipal')
            ->when($search, function (Builder $query, string $search): void {
                foreach (explode(' ', $search) as $term) {
                    $query->where(function (Builder $query) use ($term): void {
                        $query
                            ->where('bienesraices.Codigo', 'like', "%{$term}%")
                            ->orWhere('bienesraices.Calle', 'like', "%{$term}%")
                            ->orWhere('bienesraices.Numero', 'like', "%{$term}%")
                            ->orWhere('bienesraices.Barrio', 'like', "%{$term}%")
                            ->orWhere('bienesraices.Localidad', 'like', "%{$term}%");
                    });
                }
            })
            ->when($typology, function (Builder $query, string $typology): void {
                $query->where('bienesraices.IdTipologia', $typology);
            })
            ->when($commercialization, function (Builder $query, string $commercialization): void {
                $query->where('bienesraices.IdComercializacion', $commercialization);
            })
            ->when($status, function (Builder $query, string $status): void {
                $query->where('bienesraices.Hab', $status === 'habilitadas');
            })
            ->when($featured, function (Builder $query): void {
                $query->where('bienesraices.Destacada', true);
            })
            ->orderByDesc('bienesraices.created_at')
            ->orderByDesc('bienesraices.id')
            ->paginate(15);

        $filters = array_filter([
            'buscar' => $search,
            'tipologia' => $typology,
            'comercializacion' => $commercialization,
            'estado' => $status,
            'destacada' => $featured ? '1' : null,
        ], fn ($value) => $value !== null && $value !== '');

        $properties->appends($filters);

        return view('admin.properties.index', [
            'properties' => $properties,
            'typologies' => DB::table('tip_tipologia')
                ->select(['IdTipologia', 'Descrip', 'TipoGral', 'Hab'])
                ->orderBy('OrdTipoGral')
                ->orderBy('TipoGral')
                ->orderBy('Descrip')
                ->orderBy('IdTipologia')
                ->get(),
            'commercializations' => DB::table('tip_comercializacion')
                ->select(['IdComercializacion', 'Descrip', 'Hab'])
                ->orderBy('Descrip')
                ->orderBy('IdComercializacion')
                ->get(),
            'filters' => $filters,
            'hasFilters' => $filters !== [],
        ]);
    }

    public function create(): View
    {
        return view('admin.properties.create', $this->formOptions());
    }

    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $now = now();

        DB::table('bienesraices')->insert([
            ...$this->attributes($data),
            'Slug' => $this->generateSlug($data),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return to_route('admin.properties.index')
            ->with('success', "La propiedad {$data['codigo']} se creó correctamente.");
    }

    public function show(string $property): View
    {
        $currentProperty = $this->findDetailedOrFail($property);
        $images = DB::table('bienesraices_imagenes')
            ->select(['id', 'Archivo', 'Orden', 'Portada'])
            ->where('idBienRaiz', $currentProperty->id)
            ->where('Hab', true)
            ->orderBy('Orden')
            ->orderBy('id')
            ->get();

        return view('admin.properties.show', [
            'property' => $currentProperty,
            'images' => $images,
            'coverImage' => $images->first(fn (stdClass $image): bool => (bool) $image->Portada) ?? $images->first(),
            'mapLinks' => $this->mapLinks($currentProperty->Latitud, $currentProperty->Longitud),
            'video' => PropertyVideo::parse($currentProperty->VideoUrl),
        ]);
    }

    public function edit(string $property): View
    {
        $currentProperty = $this->findOrFail($property);

        return view('admin.properties.edit', [
            'property' => $currentProperty,
            ...$this->formOptions($currentProperty),
        ]);
    }

    public function update(UpdatePropertyRequest $request, string $property): RedirectResponse
    {
        $currentProperty = $this->findOrFail($property);
        $data = $request->validated();

        DB::table('bienesraices')
            ->where('id', $currentProperty->id)
            ->update([
                ...$this->attributes($data),
                'Slug' => $request->boolean('regenerar_slug') || ! $currentProperty->Slug
                    ? $this->generateSlug($data, $currentProperty->id)
                    : $currentProperty->Slug,
                'updated_at' => now(),
            ]);

        return to_route('admin.properties.index')
            ->with('success', "La propiedad {$data['codigo']} se actualizó correctamente.");
    }

    public function updateStatus(UpdatePropertyStatusRequest $request, string $property): RedirectResponse
    {
        $currentProperty = $this->findOrFail($property);
        $isEnabled = $request->boolean('hab');

        DB::table('bienesraices')
            ->where('id', $currentProperty->id)
            ->update([
                'Hab' => $isEnabled,
                'updated_at' => now(),
            ]);

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.properties.index')
            ->with('success', "La propiedad {$currentProperty->Codigo} se {$status} correctamente.");
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'Codigo' => $data['codigo'],
            'Descrip' => $data['descripcion'],
            'IdTipologia' => $data['id_tipologia'],
            'IdUso' => $data['id_uso'],
            'Antiguedad' => $data['antiguedad'],
            'IdOrientacion' => $data['id_orientacion'],
            'IdCochera' => $data['id_cochera'],
            'IdVista' => $data['id_vista'],
            'IdComercializacion' => $data['id_comercializacion'],
            'ImporteVta' => $data['importe_venta'],
            'ImporteAlq' => $data['importe_alquiler'],
            'idTipoMonedaVta' => $data['id_tipo_moneda_venta'],
            'idTipoMonedaAlq' => $data['id_tipo_moneda_alquiler'],
            'SupCubiertaPropia' => $data['sup_cubierta_propia'],
            'SupTerreno' => $data['sup_terreno'],
            'Frente' => $data['frente'],
            'Fondo' => $data['fondo'],
            'MtsFondo' => $data['metros_fondo'],
            'Luminosidad' => $data['luminosidad'],
            'Plantas' => $data['plantas'],
            'Ambientes' => $data['ambientes'],
            'Sanitarios' => $data['sanitarios'],
            'Suite' => $data['suite'],
            'Dormitorios' => $data['dormitorios'],
            'LineasTel' => $data['lineas_telefonicas'],
            'Calle' => $data['calle'],
            'Numero' => $data['numero'],
            'Piso' => $data['piso'],
            'Torre' => $data['torre'],
            'Provincia' => $data['provincia'],
            'Partido' => $data['partido'],
            'Localidad' => $data['localidad'],
            'Barrio' => $data['barrio'],
            'CodigoPostal' => $data['codigo_postal'],
            'Latitud' => $data['latitud'],
            'Longitud' => $data['longitud'],
            'Destacada' => (bool) $data['destacada'],
            'TieneVideo' => $data['video_url'] !== null,
            'VideoUrl' => $data['video_url'],
            'Hab' => $data['hab'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function generateSlug(array $data, ?int $ignoredProperty = null): string
    {
        $typology = $data['id_tipologia'] !== null
            ? DB::table('tip_tipologia')->where('IdTipologia', $data['id_tipologia'])->value('Descrip')
            : null;

        $baseSlug = Str::slug(implode(' ', array_filter([
            $typology ?: 'propiedad',
            $data['ambientes'] !== null ? $data['ambientes'].' ambientes' : null,
            $data['barrio'] ?: $data['localidad'],
            $data['codigo'],
        ], static fn (mixed $value): bool => $value !== null && $value !== '')));

        $slug = Str::limit($baseSlug, 190, '');
        $suffix = 2;

        while ($this->slugExists($slug, $ignoredProperty)) {
            $suffixText = '-'.$suffix;
            $slug = Str::limit($baseSlug, 190 - strlen($suffixText), '').$suffixText;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoredProperty): bool
    {
        return DB::table('bienesraices')
            ->where('Slug', $slug)
            ->when($ignoredProperty !== null, function (Builder $query) use ($ignoredProperty): void {
                $query->where('id', '!=', $ignoredProperty);
            })
            ->exists();
    }

    private function findOrFail(string $id): stdClass
    {
        $property = DB::table('bienesraices')
            ->where('id', $id)
            ->first();

        abort_if($property === null, 404);

        return $property;
    }

    private function findDetailedOrFail(string $id): stdClass
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
            ->where('bienesraices.id', $id)
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

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?stdClass $property = null): array
    {
        $typology = $property?->IdTipologia;
        $use = $property?->IdUso;
        $antiquity = $property?->Antiguedad;
        $orientation = $property?->IdOrientacion;
        $garage = $property?->IdCochera;
        $propertyView = $property?->IdVista;
        $commercialization = $property?->IdComercializacion;
        $saleCurrency = $property?->idTipoMonedaVta;
        $rentCurrency = $property?->idTipoMonedaAlq;

        return [
            'typologies' => $this->availableCatalogOptions('tip_tipologia', 'IdTipologia', $typology, ['OrdTipoGral', 'TipoGral', 'Descrip', 'IdTipologia']),
            'propertyUses' => $this->availableCatalogOptions('tip_uso', 'IdUso', $use, ['Descrip', 'IdUso']),
            'antiquities' => $this->availableCatalogOptions('tip_antiguedad', 'IdAntiguedad', $antiquity, ['Orden', 'Descrip', 'IdAntiguedad']),
            'orientations' => $this->availableCatalogOptions('tip_orientacion', 'IdOrientacion', $orientation, ['Descrip', 'IdOrientacion']),
            'garages' => $this->availableCatalogOptions('tip_cochera', 'IdCochera', $garage, ['Descrip', 'IdCochera']),
            'propertyViews' => $this->availableCatalogOptions('tip_vista', 'IdVista', $propertyView, ['Descrip', 'IdVista']),
            'commercializations' => $this->availableCatalogOptions('tip_comercializacion', 'IdComercializacion', $commercialization, ['Descrip', 'IdComercializacion']),
            'saleCurrencyTypes' => $this->availableCatalogOptions('tip_tipomoneda', 'idTipoMoneda', $saleCurrency, ['Descrip', 'idTipoMoneda']),
            'rentCurrencyTypes' => $this->availableCatalogOptions('tip_tipomoneda', 'idTipoMoneda', $rentCurrency, ['Descrip', 'idTipoMoneda']),
        ];
    }

    /**
     * @param  array<int, string>  $orderColumns
     * @return Collection<int, stdClass>
     */
    private function availableCatalogOptions(string $table, string $keyColumn, string|int|null $currentValue, array $orderColumns): Collection
    {
        $query = DB::table($table)
            ->where(function (Builder $query) use ($keyColumn, $currentValue): void {
                $query->where('Hab', true);

                if ($currentValue !== null) {
                    $query->orWhere($keyColumn, $currentValue);
                }
            });

        foreach ($orderColumns as $column) {
            $query->orderBy($column);
        }

        return $query->get();
    }
}
