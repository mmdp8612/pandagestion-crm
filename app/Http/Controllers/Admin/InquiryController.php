<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InquiryIndexRequest;
use App\Http\Requests\Admin\UpdateInquiryStatusRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class InquiryController extends Controller
{
    private const STATUS_LABELS = [
        'nueva' => 'Nueva',
        'en_proceso' => 'En proceso',
        'respondida' => 'Respondida',
        'descartada' => 'Descartada',
    ];

    public function index(InquiryIndexRequest $request): View
    {
        $validated = $request->validated();
        $search = $validated['buscar'] ?? null;
        $status = $validated['estado'] ?? null;

        $inquiries = DB::table('consultas')
            ->join('bienesraices', 'consultas.idBienRaiz', '=', 'bienesraices.id')
            ->select([
                'consultas.*',
                'bienesraices.Slug as PropiedadSlug',
                'bienesraices.Calle as PropiedadCalle',
                'bienesraices.Numero as PropiedadNumero',
                'bienesraices.Barrio as PropiedadBarrio',
                'bienesraices.Localidad as PropiedadLocalidad',
            ])
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('consultas.Nombre', 'like', "%{$search}%")
                        ->orWhere('consultas.Email', 'like', "%{$search}%")
                        ->orWhere('consultas.Telefono', 'like', "%{$search}%")
                        ->orWhere('consultas.CodigoPropiedad', 'like', "%{$search}%");
                });
            })
            ->when($status, fn (Builder $query, string $status) => $query->where('consultas.Estado', $status))
            ->orderByDesc('consultas.created_at')
            ->orderByDesc('consultas.id')
            ->paginate(15);

        $filters = array_filter([
            'buscar' => $search,
            'estado' => $status,
        ], fn ($value) => $value !== null && $value !== '');

        $inquiries->appends($filters);

        return view('admin.inquiries.index', [
            'inquiries' => $inquiries,
            'statusLabels' => self::STATUS_LABELS,
            'filters' => $filters,
            'hasFilters' => $filters !== [],
        ]);
    }

    public function show(string $inquiry): View
    {
        return view('admin.inquiries.show', [
            'inquiry' => $this->findOrFail($inquiry),
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function updateStatus(UpdateInquiryStatusRequest $request, string $inquiry): RedirectResponse
    {
        $currentInquiry = $this->findOrFail($inquiry);
        $status = $request->validated('estado');

        DB::table('consultas')
            ->where('id', $currentInquiry->id)
            ->update([
                'Estado' => $status,
                'updated_at' => now(),
            ]);

        return to_route('admin.inquiries.show', $currentInquiry->id)
            ->with('success', 'El estado de la consulta se actualizó a '.strtolower(self::STATUS_LABELS[$status]).'.');
    }

    private function findOrFail(string $inquiry): stdClass
    {
        $record = DB::table('consultas')
            ->join('bienesraices', 'consultas.idBienRaiz', '=', 'bienesraices.id')
            ->select([
                'consultas.*',
                'bienesraices.Slug as PropiedadSlug',
                'bienesraices.Calle as PropiedadCalle',
                'bienesraices.Numero as PropiedadNumero',
                'bienesraices.Barrio as PropiedadBarrio',
                'bienesraices.Localidad as PropiedadLocalidad',
                'bienesraices.Hab as PropiedadHabilitada',
            ])
            ->where('consultas.id', $inquiry)
            ->first();

        abort_if($record === null, 404);

        return $record;
    }
}
