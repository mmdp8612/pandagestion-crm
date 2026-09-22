<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGarageRequest;
use App\Http\Requests\Admin\UpdateGarageRequest;
use App\Http\Requests\Admin\UpdateGarageStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class GarageController extends Controller
{
    public function index(): View
    {
        $garages = DB::table('tip_cochera')
            ->orderBy('Descrip')
            ->orderBy('IdCochera')
            ->paginate(15);

        return view('admin.catalogs.garages.index', compact('garages'));
    }

    public function create(): View
    {
        return view('admin.catalogs.garages.create');
    }

    public function store(StoreGarageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::table('tip_cochera')->insert([
            'IdCochera' => $data['id'],
            'Descrip' => $data['descripcion'],
            'Hab' => $data['hab'],
        ]);

        return to_route('admin.catalogs.garages.index')
            ->with('success', "La cochera {$data['descripcion']} se creó correctamente.");
    }

    public function edit(string $garage): View
    {
        return view('admin.catalogs.garages.edit', [
            'garage' => $this->findOrFail($garage),
        ]);
    }

    public function update(UpdateGarageRequest $request, string $garage): RedirectResponse
    {
        $currentGarage = $this->findOrFail($garage);
        $data = $request->validated();

        DB::table('tip_cochera')
            ->where('IdCochera', $currentGarage->IdCochera)
            ->update([
                'Descrip' => $data['descripcion'],
                'Hab' => $data['hab'],
            ]);

        return to_route('admin.catalogs.garages.index')
            ->with('success', "La cochera {$data['descripcion']} se actualizó correctamente.");
    }

    public function updateStatus(UpdateGarageStatusRequest $request, string $garage): RedirectResponse
    {
        $currentGarage = $this->findOrFail($garage);
        $isEnabled = $request->boolean('hab');

        DB::table('tip_cochera')
            ->where('IdCochera', $currentGarage->IdCochera)
            ->update(['Hab' => $isEnabled]);

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.catalogs.garages.index')
            ->with('success', "La cochera {$currentGarage->Descrip} se {$status} correctamente.");
    }

    private function findOrFail(string $id): stdClass
    {
        $garage = DB::table('tip_cochera')
            ->where('IdCochera', $id)
            ->first();

        abort_if($garage === null, 404);

        return $garage;
    }
}
