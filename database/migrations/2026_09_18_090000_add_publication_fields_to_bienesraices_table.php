<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->boolean('Destacada')->default(false);
            $table->string('Slug', 190)->nullable();
        });

        $usedSlugs = [];

        $properties = DB::table('bienesraices')
            ->leftJoin('tip_tipologia', 'bienesraices.IdTipologia', '=', 'tip_tipologia.IdTipologia')
            ->select([
                'bienesraices.id',
                'bienesraices.Codigo',
                'bienesraices.Ambientes',
                'bienesraices.Barrio',
                'bienesraices.Localidad',
                'tip_tipologia.Descrip as TipologiaDescripcion',
            ])
            ->orderBy('bienesraices.id')
            ->get();

        foreach ($properties as $property) {
            $baseSlug = Str::slug(implode(' ', array_filter([
                $property->TipologiaDescripcion ?: 'propiedad',
                $property->Ambientes !== null ? $property->Ambientes.' ambientes' : null,
                $property->Barrio ?: $property->Localidad,
                $property->Codigo,
            ], static fn (mixed $value): bool => $value !== null && $value !== '')));

            $slug = Str::limit($baseSlug, 190, '');
            $suffix = 2;

            while (isset($usedSlugs[$slug])) {
                $suffixText = '-'.$suffix;
                $slug = Str::limit($baseSlug, 190 - strlen($suffixText), '').$suffixText;
                $suffix++;
            }

            $usedSlugs[$slug] = true;

            DB::table('bienesraices')
                ->where('id', $property->id)
                ->update(['Slug' => $slug]);
        }

        Schema::table('bienesraices', function (Blueprint $table) {
            $table->string('Slug', 190)->nullable(false)->change();
            $table->index('Destacada');
            $table->unique('Slug');
        });
    }

    public function down(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->dropIndex(['Destacada']);
            $table->dropUnique(['Slug']);
            $table->dropColumn(['Destacada', 'Slug']);
        });
    }
};
