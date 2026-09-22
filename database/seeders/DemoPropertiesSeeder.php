<?php

namespace Database\Seeders;

use App\Support\PropertyVideo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class DemoPropertiesSeeder extends Seeder
{
    private const PROPERTY_COUNT = 100;

    /**
     * Seed a deterministic, non-destructive property demonstration dataset.
     *
     * @throws JsonException
     */
    public function run(): void
    {
        $this->call([
            AntiquitiesSeeder::class,
            CommercializationsSeeder::class,
            TypologiesSeeder::class,
            GaragesSeeder::class,
            OrientationsSeeder::class,
            PropertyUsesSeeder::class,
            PropertyViewsSeeder::class,
            CurrencyTypesSeeder::class,
        ]);

        $properties = $this->properties();

        if (count($properties) !== self::PROPERTY_COUNT) {
            throw new RuntimeException('El dataset debe contener exactamente 100 propiedades.');
        }

        $baseDate = Carbon::create(2025, 1, 1, 12, 0, 0, 'America/Argentina/Buenos_Aires');

        DB::transaction(function () use ($properties, $baseDate): void {
            foreach ($properties as $index => $property) {
                if (DB::table('bienesraices')->where('Codigo', $property['codigo'])->exists()) {
                    continue;
                }

                $video = PropertyVideo::parse($property['video_url']);
                $timestamp = $baseDate->copy()->addDays($index);
                $slug = Str::limit(Str::slug(implode(' ', array_filter([
                    'propiedad',
                    $property['ambientes'] !== null ? $property['ambientes'].' ambientes' : null,
                    $property['barrio'] ?: $property['localidad'],
                    $property['codigo'],
                ]))), 190, '');

                DB::table('bienesraices')->insert([
                    'Codigo' => $property['codigo'],
                    'Descrip' => $property['descripcion'],
                    'IdTipologia' => $property['id_tipologia'],
                    'IdUso' => $property['id_uso'],
                    'Antiguedad' => $property['antiguedad'],
                    'IdOrientacion' => $property['id_orientacion'],
                    'IdCochera' => $property['id_cochera'],
                    'IdVista' => $property['id_vista'],
                    'SupCubiertaPropia' => $property['sup_cubierta_propia'],
                    'SupTerreno' => $property['sup_terreno'],
                    'Frente' => $property['frente'],
                    'Fondo' => $property['fondo'],
                    'MtsFondo' => $property['metros_fondo'],
                    'Luminosidad' => $property['luminosidad'],
                    'Plantas' => $property['plantas'],
                    'Ambientes' => $property['ambientes'],
                    'Sanitarios' => $property['sanitarios'],
                    'Suite' => $property['suite'],
                    'Dormitorios' => $property['dormitorios'],
                    'LineasTel' => $property['lineas_telefonicas'],
                    'IdComercializacion' => $property['id_comercializacion'],
                    'ImporteVta' => $property['importe_venta'],
                    'ImporteAlq' => $property['importe_alquiler'],
                    'idTipoMonedaVta' => $property['id_tipo_moneda_venta'],
                    'idTipoMonedaAlq' => $property['id_tipo_moneda_alquiler'],
                    'Calle' => $property['calle'],
                    'Numero' => $property['numero'],
                    'Piso' => $property['piso'],
                    'Torre' => $property['torre'],
                    'Provincia' => $property['provincia'],
                    'Partido' => $property['partido'],
                    'Localidad' => $property['localidad'],
                    'Barrio' => $property['barrio'],
                    'CodigoPostal' => null,
                    'Latitud' => $property['latitud'],
                    'Longitud' => $property['longitud'],
                    'Destacada' => $property['destacada'],
                    'Slug' => $slug,
                    'TieneFoto' => false,
                    'TieneVideo' => $video !== null,
                    'VideoUrl' => $video['url'] ?? null,
                    'Hab' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws JsonException
     */
    private function properties(): array
    {
        $path = database_path('seeders/data/demo_properties.json');
        $json = file_get_contents($path);

        if ($json === false) {
            throw new RuntimeException("No se pudo leer el dataset {$path}.");
        }

        /** @var array{columns: array<int, string>, rows: array<int, array<int, mixed>>} $dataset */
        $dataset = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return array_map(
            static function (array $row) use ($dataset): array {
                $property = array_combine($dataset['columns'], $row);

                if ($property === false) {
                    throw new RuntimeException('Una propiedad del dataset no coincide con sus columnas.');
                }

                return $property;
            },
            $dataset['rows'],
        );
    }
}
