<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $canViewProperties = $request->user()->can('bienesraices');
        $canViewInquiries = $request->user()->can('consultas');

        $propertyStats = $canViewProperties ? $this->propertyStats() : null;
        $inquiryStats = $canViewInquiries ? $this->inquiryStats() : null;

        return view('admin.dashboard', [
            'propertyStats' => $propertyStats,
            'latestProperties' => $canViewProperties ? $this->latestProperties() : collect(),
            'inquiryStats' => $inquiryStats,
            'latestInquiries' => $canViewInquiries ? $this->latestInquiries() : collect(),
        ]);
    }

    /**
     * @return array{total: int, enabled: int, disabled: int, featured: int, with_photos: int, without_photos: int}
     */
    private function propertyStats(): array
    {
        $summary = DB::table('bienesraices')
            ->selectRaw(
                'COUNT(*) as total,
                COALESCE(SUM(CASE WHEN Hab = ? THEN 1 ELSE 0 END), 0) as enabled,
                COALESCE(SUM(CASE WHEN Hab = ? THEN 1 ELSE 0 END), 0) as disabled,
                COALESCE(SUM(CASE WHEN Destacada = ? THEN 1 ELSE 0 END), 0) as featured,
                COALESCE(SUM(CASE WHEN TieneFoto = ? THEN 1 ELSE 0 END), 0) as with_photos,
                COALESCE(SUM(CASE WHEN TieneFoto = ? THEN 1 ELSE 0 END), 0) as without_photos',
                [true, false, true, true, false]
            )
            ->first();

        return [
            'total' => (int) $summary->total,
            'enabled' => (int) $summary->enabled,
            'disabled' => (int) $summary->disabled,
            'featured' => (int) $summary->featured,
            'with_photos' => (int) $summary->with_photos,
            'without_photos' => (int) $summary->without_photos,
        ];
    }

    /**
     * @return array{new: int, in_progress: int}
     */
    private function inquiryStats(): array
    {
        $summary = DB::table('consultas')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN Estado = ? THEN 1 ELSE 0 END), 0) as new_count,
                COALESCE(SUM(CASE WHEN Estado = ? THEN 1 ELSE 0 END), 0) as in_progress_count',
                ['nueva', 'en_proceso']
            )
            ->first();

        return [
            'new' => (int) $summary->new_count,
            'in_progress' => (int) $summary->in_progress_count,
        ];
    }

    /**
     * @return Collection<int, object>
     */
    private function latestProperties(): Collection
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
            ->leftJoin('tip_comercializacion', 'bienesraices.IdComercializacion', '=', 'tip_comercializacion.IdComercializacion')
            ->select([
                'bienesraices.id',
                'bienesraices.Codigo',
                'bienesraices.Calle',
                'bienesraices.Numero',
                'bienesraices.Barrio',
                'bienesraices.Localidad',
                'bienesraices.Hab',
                'bienesraices.Destacada',
                'tip_tipologia.Descrip as TipologiaDescripcion',
                'tip_comercializacion.Descrip as ComercializacionDescripcion',
            ])
            ->selectSub($mainImage, 'ImagenPrincipal')
            ->orderByDesc('bienesraices.created_at')
            ->orderByDesc('bienesraices.id')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    private function latestInquiries(): Collection
    {
        return DB::table('consultas')
            ->select([
                'id',
                'CodigoPropiedad',
                'Nombre',
                'Email',
                'Telefono',
                'Mensaje',
                'Estado',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }
}
