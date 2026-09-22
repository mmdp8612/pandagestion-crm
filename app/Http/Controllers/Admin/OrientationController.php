<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrientationRequest;
use App\Http\Requests\Admin\UpdateOrientationRequest;
use App\Http\Requests\Admin\UpdateOrientationStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class OrientationController extends Controller
{
    public function index(): View
    {
        $orientations = DB::table('tip_orientacion')
            ->orderBy('Descrip')
            ->orderBy('IdOrientacion')
            ->paginate(15);

        return view('admin.catalogs.orientations.index', compact('orientations'));
    }

    public function create(): View
    {
        return view('admin.catalogs.orientations.create');
    }

    public function store(StoreOrientationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::table('tip_orientacion')->insert([
            'IdOrientacion' => $data['id'],
            'Descrip' => $data['descripcion'],
            'Hab' => $data['hab'],
        ]);

        return to_route('admin.catalogs.orientations.index')
            ->with('success', "La orientación {$data['descripcion']} se creó correctamente.");
    }

    public function edit(string $orientation): View
    {
        return view('admin.catalogs.orientations.edit', [
            'orientation' => $this->findOrFail($orientation),
        ]);
    }

    public function update(UpdateOrientationRequest $request, string $orientation): RedirectResponse
    {
        $currentOrientation = $this->findOrFail($orientation);
        $data = $request->validated();

        DB::table('tip_orientacion')
            ->where('IdOrientacion', $currentOrientation->IdOrientacion)
            ->update([
                'Descrip' => $data['descripcion'],
                'Hab' => $data['hab'],
            ]);

        return to_route('admin.catalogs.orientations.index')
            ->with('success', "La orientación {$data['descripcion']} se actualizó correctamente.");
    }

    public function updateStatus(UpdateOrientationStatusRequest $request, string $orientation): RedirectResponse
    {
        $currentOrientation = $this->findOrFail($orientation);
        $isEnabled = $request->boolean('hab');

        DB::table('tip_orientacion')
            ->where('IdOrientacion', $currentOrientation->IdOrientacion)
            ->update(['Hab' => $isEnabled]);

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.catalogs.orientations.index')
            ->with('success', "La orientación {$currentOrientation->Descrip} se {$status} correctamente.");
    }

    private function findOrFail(string $id): stdClass
    {
        $orientation = DB::table('tip_orientacion')
            ->where('IdOrientacion', $id)
            ->first();

        abort_if($orientation === null, 404);

        return $orientation;
    }
}
