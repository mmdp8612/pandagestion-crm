<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicPropertySearchRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(): View
    {
        $properties = $this->propertyCardsQuery()
            ->orderByDesc('bienesraices.created_at')
            ->orderByDesc('bienesraices.id')
            ->limit(6)
            ->get();

        return view('public.home', array_merge(
            $this->portalContext(),
            $this->catalogContext(),
            [
                'properties' => $properties,
                'filters' => [],
                'hasFilters' => false,
            ]
        ));
    }

    public function properties(PublicPropertySearchRequest $request): View
    {
        $filters = $this->validatedFilters($request->validated());
        $properties = $this->filteredProperties($filters);
        $properties->appends($filters);
        $filterGroups = $this->filterGroups();

        return view('public.properties.index', array_merge(
            $this->portalContext(),
            [
                'properties' => $properties,
                'filters' => $filters,
                'hasFilters' => $filters !== [],
                'filterGroups' => $filterGroups,
                'activeFilters' => $this->activeFilters($filters, $filterGroups),
            ]
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function portalContext(): array
    {
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

        return [
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogContext(): array
    {
        return [
            'typologies' => DB::table('tip_tipologia')
                ->select(['IdTipologia', 'Descrip'])
                ->where('Hab', true)
                ->orderBy('OrdTipoGral')
                ->orderBy('TipoGral')
                ->orderBy('Descrip')
                ->orderBy('IdTipologia')
                ->get(),
            'commercializations' => DB::table('tip_comercializacion')
                ->select(['IdComercializacion', 'Descrip'])
                ->where('Hab', true)
                ->orderBy('Descrip')
                ->orderBy('IdComercializacion')
                ->get(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function validatedFilters(array $validated): array
    {
        $filters = [];

        if (isset($validated['ubicacion'])) {
            $filters['ubicacion'] = $validated['ubicacion'];
        }

        foreach ([
            'operacion',
            'tipologia',
            'ambientes',
            'provincia',
            'partido',
            'localidad',
            'cochera',
            'antiguedad',
            'orientacion',
            'vista',
            'moneda_venta',
            'moneda_alquiler',
        ] as $key) {
            if (($validated[$key] ?? []) !== []) {
                $filters[$key] = array_values($validated[$key]);
            }
        }

        if (isset($validated['orden']) && $validated['orden'] !== 'recientes') {
            $filters['orden'] = $validated['orden'];
        }

        return $filters;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filteredProperties(array $filters): LengthAwarePaginator
    {
        $query = $this->propertyCardsQuery()
            ->when($filters['ubicacion'] ?? null, function (Builder $query, string $location): void {
                foreach (explode(' ', $location) as $term) {
                    $query->where(function (Builder $query) use ($term): void {
                        $query
                            ->where('bienesraices.Calle', 'like', "%{$term}%")
                            ->orWhere('bienesraices.Numero', 'like', "%{$term}%")
                            ->orWhere('bienesraices.Barrio', 'like', "%{$term}%")
                            ->orWhere('bienesraices.Localidad', 'like', "%{$term}%")
                            ->orWhere('bienesraices.Partido', 'like', "%{$term}%")
                            ->orWhere('bienesraices.Provincia', 'like', "%{$term}%");
                    });
                }
            })
            ->when($filters['operacion'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.IdComercializacion', $values))
            ->when($filters['tipologia'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.IdTipologia', $values))
            ->when($filters['ambientes'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.Ambientes', array_map('intval', $values)))
            ->when($filters['provincia'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.Provincia', $values))
            ->when($filters['partido'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.Partido', $values))
            ->when($filters['localidad'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.Localidad', $values))
            ->when($filters['cochera'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.IdCochera', $values))
            ->when($filters['antiguedad'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.Antiguedad', $values))
            ->when($filters['orientacion'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.IdOrientacion', $values))
            ->when($filters['vista'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.IdVista', $values))
            ->when($filters['moneda_venta'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.idTipoMonedaVta', array_map('intval', $values)))
            ->when($filters['moneda_alquiler'] ?? [], fn (Builder $query, array $values) => $query
                ->whereIn('bienesraices.idTipoMonedaAlq', array_map('intval', $values)));

        match ($filters['orden'] ?? 'recientes') {
            'antiguas' => $query
                ->orderBy('bienesraices.created_at')
                ->orderBy('bienesraices.id'),
            'superficie_desc' => $query
                ->orderByRaw('CASE WHEN bienesraices.SupCubiertaPropia IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('bienesraices.SupCubiertaPropia')
                ->orderByDesc('bienesraices.created_at')
                ->orderByDesc('bienesraices.id'),
            'superficie_asc' => $query
                ->orderByRaw('CASE WHEN bienesraices.SupCubiertaPropia IS NULL THEN 1 ELSE 0 END')
                ->orderBy('bienesraices.SupCubiertaPropia')
                ->orderByDesc('bienesraices.created_at')
                ->orderByDesc('bienesraices.id'),
            default => $query
                ->orderByDesc('bienesraices.created_at')
                ->orderByDesc('bienesraices.id'),
        };

        return $query->paginate(9);
    }

    private function propertyCardsQuery(): Builder
    {
        $mainImage = DB::table('bienesraices_imagenes')
            ->select('Archivo')
            ->whereColumn('idBienRaiz', 'bienesraices.id')
            ->where('Hab', true)
            ->orderByDesc('Portada')
            ->orderBy('Orden')
            ->orderBy('id')
            ->limit(1);

        return DB::table('bienesraices')
            ->leftJoin('tip_tipologia', 'bienesraices.IdTipologia', '=', 'tip_tipologia.IdTipologia')
            ->leftJoin('tip_uso', 'bienesraices.IdUso', '=', 'tip_uso.IdUso')
            ->leftJoin('tip_comercializacion', 'bienesraices.IdComercializacion', '=', 'tip_comercializacion.IdComercializacion')
            ->leftJoin('tip_tipomoneda as moneda_venta', 'bienesraices.idTipoMonedaVta', '=', 'moneda_venta.idTipoMoneda')
            ->leftJoin('tip_tipomoneda as moneda_alquiler', 'bienesraices.idTipoMonedaAlq', '=', 'moneda_alquiler.idTipoMoneda')
            ->select([
                'bienesraices.id',
                'bienesraices.Codigo',
                'bienesraices.Slug',
                'bienesraices.Descrip',
                'bienesraices.Destacada',
                'bienesraices.Calle',
                'bienesraices.Numero',
                'bienesraices.Barrio',
                'bienesraices.Localidad',
                'bienesraices.Provincia',
                'bienesraices.Ambientes',
                'bienesraices.Dormitorios',
                'bienesraices.Sanitarios',
                'bienesraices.SupCubiertaPropia',
                'bienesraices.SupTerreno',
                'bienesraices.ImporteVta',
                'bienesraices.ImporteAlq',
                'tip_tipologia.Descrip as TipologiaDescripcion',
                'tip_uso.Descrip as UsoDescripcion',
                'tip_comercializacion.Descrip as ComercializacionDescripcion',
                'moneda_venta.Descrip as MonedaVentaDescripcion',
                'moneda_venta.Simbolo as MonedaVentaSimbolo',
                'moneda_alquiler.Descrip as MonedaAlquilerDescripcion',
                'moneda_alquiler.Simbolo as MonedaAlquilerSimbolo',
            ])
            ->selectSub($mainImage, 'ImagenPrincipal')
            ->where('bienesraices.Hab', true);
    }

    /**
     * @return array<int, array{key: string, label: string, options: array<int, array{value: string, label: string, count: int}>}>
     */
    private function filterGroups(): array
    {
        $groups = [
            [
                'key' => 'operacion',
                'label' => 'Operación',
                'options' => $this->catalogFacetOptions(
                    'tip_comercializacion',
                    'IdComercializacion',
                    'Descrip',
                    'IdComercializacion'
                ),
            ],
            [
                'key' => 'tipologia',
                'label' => 'Tipo de propiedad',
                'options' => $this->catalogFacetOptions(
                    'tip_tipologia',
                    'IdTipologia',
                    'Descrip',
                    'IdTipologia'
                ),
            ],
            [
                'key' => 'ambientes',
                'label' => 'Ambientes',
                'options' => $this->publishedValueFacetOptions('Ambientes'),
            ],
            [
                'key' => 'provincia',
                'label' => 'Provincia',
                'options' => $this->publishedValueFacetOptions('Provincia'),
            ],
            [
                'key' => 'partido',
                'label' => 'Partido',
                'options' => $this->publishedValueFacetOptions('Partido'),
            ],
            [
                'key' => 'localidad',
                'label' => 'Localidad',
                'options' => $this->publishedValueFacetOptions('Localidad'),
            ],
            [
                'key' => 'cochera',
                'label' => 'Cochera',
                'options' => $this->catalogFacetOptions('tip_cochera', 'IdCochera', 'Descrip', 'IdCochera'),
            ],
            [
                'key' => 'antiguedad',
                'label' => 'Antigüedad',
                'options' => $this->catalogFacetOptions('tip_antiguedad', 'IdAntiguedad', 'Descrip', 'Antiguedad'),
            ],
            [
                'key' => 'orientacion',
                'label' => 'Orientación',
                'options' => $this->catalogFacetOptions('tip_orientacion', 'IdOrientacion', 'Descrip', 'IdOrientacion'),
            ],
            [
                'key' => 'vista',
                'label' => 'Vista',
                'options' => $this->catalogFacetOptions('tip_vista', 'IdVista', 'Descrip', 'IdVista'),
            ],
            [
                'key' => 'moneda_venta',
                'label' => 'Moneda de venta',
                'options' => $this->catalogFacetOptions(
                    'tip_tipomoneda',
                    'idTipoMoneda',
                    'Descrip',
                    'idTipoMonedaVta'
                ),
            ],
            [
                'key' => 'moneda_alquiler',
                'label' => 'Moneda de alquiler',
                'options' => $this->catalogFacetOptions(
                    'tip_tipomoneda',
                    'idTipoMoneda',
                    'Descrip',
                    'idTipoMonedaAlq'
                ),
            ],
        ];

        return array_values(array_filter(
            $groups,
            fn (array $group): bool => $group['options'] !== []
        ));
    }

    /**
     * @return array<int, array{value: string, label: string, count: int}>
     */
    private function catalogFacetOptions(
        string $catalogTable,
        string $catalogKey,
        string $catalogLabel,
        string $propertyColumn
    ): array {
        return DB::table($catalogTable.' as catalog')
            ->join('bienesraices as property', 'property.'.$propertyColumn, '=', 'catalog.'.$catalogKey)
            ->where('catalog.Hab', true)
            ->where('property.Hab', true)
            ->select([
                'catalog.'.$catalogKey.' as FilterValue',
                'catalog.'.$catalogLabel.' as FilterLabel',
            ])
            ->selectRaw('COUNT(*) as FilterCount')
            ->groupBy('catalog.'.$catalogKey, 'catalog.'.$catalogLabel)
            ->orderBy('catalog.'.$catalogLabel)
            ->orderBy('catalog.'.$catalogKey)
            ->get()
            ->map(fn (object $option): array => [
                'value' => (string) $option->FilterValue,
                'label' => (string) $option->FilterLabel,
                'count' => (int) $option->FilterCount,
            ])
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string, count: int}>
     */
    private function publishedValueFacetOptions(string $column): array
    {
        return DB::table('bienesraices')
            ->where('Hab', true)
            ->whereNotNull($column)
            ->when(
                $column === 'Ambientes',
                fn (Builder $query) => $query->where($column, '>', 0),
                fn (Builder $query) => $query->where($column, '<>', '')
            )
            ->select($column.' as FilterValue')
            ->selectRaw('COUNT(*) as FilterCount')
            ->groupBy($column)
            ->orderBy($column)
            ->get()
            ->map(fn (object $option): array => [
                'value' => (string) $option->FilterValue,
                'label' => (string) $option->FilterValue,
                'count' => (int) $option->FilterCount,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int, array{key: string, label: string, options: array<int, array{value: string, label: string, count: int}>}>  $filterGroups
     * @return array<int, array{label: string, url: string}>
     */
    private function activeFilters(array $filters, array $filterGroups): array
    {
        $active = [];

        if (isset($filters['ubicacion'])) {
            $active[] = [
                'label' => 'Ubicación: '.$filters['ubicacion'],
                'url' => route('public.properties.index', $this->withoutFilter($filters, 'ubicacion')),
            ];
        }

        foreach ($filterGroups as $group) {
            $options = collect($group['options'])->keyBy('value');

            foreach ($filters[$group['key']] ?? [] as $value) {
                $value = (string) $value;
                $option = $options->get($value);

                if (! $option) {
                    continue;
                }

                $active[] = [
                    'label' => $group['label'].': '.$option['label'],
                    'url' => route(
                        'public.properties.index',
                        $this->withoutFilterValue($filters, $group['key'], $value)
                    ),
                ];
            }
        }

        if (isset($filters['orden'])) {
            $active[] = [
                'label' => 'Orden: '.match ($filters['orden']) {
                    'antiguas' => 'Más antiguas',
                    'superficie_desc' => 'Mayor superficie',
                    'superficie_asc' => 'Menor superficie',
                    default => 'Más recientes',
                },
                'url' => route('public.properties.index', $this->withoutFilter($filters, 'orden')),
            ];
        }

        return $active;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function withoutFilter(array $filters, string $key): array
    {
        unset($filters[$key]);

        return $filters;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function withoutFilterValue(array $filters, string $key, string $value): array
    {
        $remaining = array_values(array_filter(
            $filters[$key] ?? [],
            fn ($selected): bool => (string) $selected !== $value
        ));

        if ($remaining === []) {
            unset($filters[$key]);
        } else {
            $filters[$key] = $remaining;
        }

        return $filters;
    }
}
