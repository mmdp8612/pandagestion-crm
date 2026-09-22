<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCommercializationRequest;
use App\Http\Requests\Admin\UpdateCommercializationRequest;
use App\Http\Requests\Admin\UpdateCommercializationStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class CommercializationController extends Controller
{
    public function index(): View
    {
        $commercializations = DB::table('tip_comercializacion')
            ->orderBy('Descrip')
            ->orderBy('IdComercializacion')
            ->paginate(15);

        return view('admin.catalogs.commercializations.index', compact('commercializations'));
    }

    public function create(): View
    {
        return view('admin.catalogs.commercializations.create');
    }

    public function store(StoreCommercializationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::table('tip_comercializacion')->insert([
            'IdComercializacion' => $data['id'],
            'Descrip' => $data['descripcion'],
            'Hab' => $data['hab'],
        ]);

        return to_route('admin.catalogs.commercializations.index')
            ->with('success', "La comercialización {$data['descripcion']} se creó correctamente.");
    }

    public function edit(string $commercialization): View
    {
        return view('admin.catalogs.commercializations.edit', [
            'commercialization' => $this->findOrFail($commercialization),
        ]);
    }

    public function update(UpdateCommercializationRequest $request, string $commercialization): RedirectResponse
    {
        $currentCommercialization = $this->findOrFail($commercialization);
        $data = $request->validated();

        DB::table('tip_comercializacion')
            ->where('IdComercializacion', $currentCommercialization->IdComercializacion)
            ->update([
                'Descrip' => $data['descripcion'],
                'Hab' => $data['hab'],
            ]);

        return to_route('admin.catalogs.commercializations.index')
            ->with('success', "La comercialización {$data['descripcion']} se actualizó correctamente.");
    }

    public function updateStatus(UpdateCommercializationStatusRequest $request, string $commercialization): RedirectResponse
    {
        $currentCommercialization = $this->findOrFail($commercialization);
        $isEnabled = $request->boolean('hab');

        DB::table('tip_comercializacion')
            ->where('IdComercializacion', $currentCommercialization->IdComercializacion)
            ->update(['Hab' => $isEnabled]);

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.catalogs.commercializations.index')
            ->with('success', "La comercialización {$currentCommercialization->Descrip} se {$status} correctamente.");
    }

    private function findOrFail(string $id): stdClass
    {
        $commercialization = DB::table('tip_comercializacion')
            ->where('IdComercializacion', $id)
            ->first();

        abort_if($commercialization === null, 404);

        return $commercialization;
    }
}
