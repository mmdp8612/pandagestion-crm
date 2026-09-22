<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePropertyUseRequest;
use App\Http\Requests\Admin\UpdatePropertyUseRequest;
use App\Http\Requests\Admin\UpdatePropertyUseStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class PropertyUseController extends Controller
{
    public function index(): View
    {
        $propertyUses = DB::table('tip_uso')
            ->orderBy('Descrip')
            ->orderBy('IdUso')
            ->paginate(15);

        return view('admin.catalogs.uses.index', compact('propertyUses'));
    }

    public function create(): View
    {
        return view('admin.catalogs.uses.create');
    }

    public function store(StorePropertyUseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::table('tip_uso')->insert([
            'IdUso' => $data['id'],
            'Descrip' => $data['descripcion'],
            'Hab' => $data['hab'],
        ]);

        return to_route('admin.catalogs.uses.index')
            ->with('success', "El uso {$data['descripcion']} se creó correctamente.");
    }

    public function edit(string $propertyUse): View
    {
        return view('admin.catalogs.uses.edit', [
            'propertyUse' => $this->findOrFail($propertyUse),
        ]);
    }

    public function update(UpdatePropertyUseRequest $request, string $propertyUse): RedirectResponse
    {
        $currentPropertyUse = $this->findOrFail($propertyUse);
        $data = $request->validated();

        DB::table('tip_uso')
            ->where('IdUso', $currentPropertyUse->IdUso)
            ->update([
                'Descrip' => $data['descripcion'],
                'Hab' => $data['hab'],
            ]);

        return to_route('admin.catalogs.uses.index')
            ->with('success', "El uso {$data['descripcion']} se actualizó correctamente.");
    }

    public function updateStatus(UpdatePropertyUseStatusRequest $request, string $propertyUse): RedirectResponse
    {
        $currentPropertyUse = $this->findOrFail($propertyUse);
        $isEnabled = $request->boolean('hab');

        DB::table('tip_uso')
            ->where('IdUso', $currentPropertyUse->IdUso)
            ->update(['Hab' => $isEnabled]);

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.catalogs.uses.index')
            ->with('success', "El uso {$currentPropertyUse->Descrip} se {$status} correctamente.");
    }

    private function findOrFail(string $id): stdClass
    {
        $propertyUse = DB::table('tip_uso')
            ->where('IdUso', $id)
            ->first();

        abort_if($propertyUse === null, 404);

        return $propertyUse;
    }
}
