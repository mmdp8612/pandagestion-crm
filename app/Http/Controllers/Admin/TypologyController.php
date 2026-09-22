<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTypologyRequest;
use App\Http\Requests\Admin\UpdateTypologyRequest;
use App\Http\Requests\Admin\UpdateTypologyStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class TypologyController extends Controller
{
    public function index(): View
    {
        $typologies = DB::table('tip_tipologia')
            ->orderBy('OrdTipoGral')
            ->orderBy('TipoGral')
            ->orderBy('Descrip')
            ->orderBy('IdTipologia')
            ->paginate(15);

        return view('admin.catalogs.typologies.index', compact('typologies'));
    }

    public function create(): View
    {
        return view('admin.catalogs.typologies.create');
    }

    public function store(StoreTypologyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::table('tip_tipologia')->insert([
            'IdTipologia' => $data['id'],
            'Descrip' => $data['descripcion'],
            'TipoGral' => $data['tipo_general'],
            'OrdTipoGral' => $data['orden_tipo_general'],
            'Hab' => $data['hab'],
        ]);

        return to_route('admin.catalogs.typologies.index')
            ->with('success', "La tipología {$data['descripcion']} se creó correctamente.");
    }

    public function edit(string $typology): View
    {
        return view('admin.catalogs.typologies.edit', [
            'typology' => $this->findOrFail($typology),
        ]);
    }

    public function update(UpdateTypologyRequest $request, string $typology): RedirectResponse
    {
        $currentTypology = $this->findOrFail($typology);
        $data = $request->validated();

        DB::table('tip_tipologia')
            ->where('IdTipologia', $currentTypology->IdTipologia)
            ->update([
                'Descrip' => $data['descripcion'],
                'TipoGral' => $data['tipo_general'],
                'OrdTipoGral' => $data['orden_tipo_general'],
                'Hab' => $data['hab'],
            ]);

        return to_route('admin.catalogs.typologies.index')
            ->with('success', "La tipología {$data['descripcion']} se actualizó correctamente.");
    }

    public function updateStatus(UpdateTypologyStatusRequest $request, string $typology): RedirectResponse
    {
        $currentTypology = $this->findOrFail($typology);
        $isEnabled = $request->boolean('hab');

        DB::table('tip_tipologia')
            ->where('IdTipologia', $currentTypology->IdTipologia)
            ->update(['Hab' => $isEnabled]);

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.catalogs.typologies.index')
            ->with('success', "La tipología {$currentTypology->Descrip} se {$status} correctamente.");
    }

    private function findOrFail(string $id): stdClass
    {
        $typology = DB::table('tip_tipologia')
            ->where('IdTipologia', $id)
            ->first();

        abort_if($typology === null, 404);

        return $typology;
    }
}
