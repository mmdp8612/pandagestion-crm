<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAntiquityRequest;
use App\Http\Requests\Admin\UpdateAntiquityRequest;
use App\Http\Requests\Admin\UpdateAntiquityStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class AntiquityController extends Controller
{
    public function index(): View
    {
        $antiquities = DB::table('tip_antiguedad')
            ->orderByRaw('CASE WHEN Orden IS NULL THEN 1 ELSE 0 END')
            ->orderBy('Orden')
            ->orderBy('Descrip')
            ->paginate(15);

        return view('admin.catalogs.antiquities.index', compact('antiquities'));
    }

    public function create(): View
    {
        return view('admin.catalogs.antiquities.create');
    }

    public function store(StoreAntiquityRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::table('tip_antiguedad')->insert([
            'IdAntiguedad' => $data['id'],
            'Descrip' => $data['descripcion'],
            'Orden' => $data['orden'],
            'Hab' => $data['hab'],
        ]);

        return to_route('admin.catalogs.antiquities.index')
            ->with('success', "La antigüedad {$data['descripcion']} se creó correctamente.");
    }

    public function edit(string $antiquity): View
    {
        return view('admin.catalogs.antiquities.edit', [
            'antiquity' => $this->findOrFail($antiquity),
        ]);
    }

    public function update(UpdateAntiquityRequest $request, string $antiquity): RedirectResponse
    {
        $currentAntiquity = $this->findOrFail($antiquity);
        $data = $request->validated();

        DB::table('tip_antiguedad')
            ->where('IdAntiguedad', $currentAntiquity->IdAntiguedad)
            ->update([
                'Descrip' => $data['descripcion'],
                'Orden' => $data['orden'],
                'Hab' => $data['hab'],
            ]);

        return to_route('admin.catalogs.antiquities.index')
            ->with('success', "La antigüedad {$data['descripcion']} se actualizó correctamente.");
    }

    public function updateStatus(UpdateAntiquityStatusRequest $request, string $antiquity): RedirectResponse
    {
        $currentAntiquity = $this->findOrFail($antiquity);
        $isEnabled = $request->boolean('hab');

        DB::table('tip_antiguedad')
            ->where('IdAntiguedad', $currentAntiquity->IdAntiguedad)
            ->update(['Hab' => $isEnabled]);

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.catalogs.antiquities.index')
            ->with('success', "La antigüedad {$currentAntiquity->Descrip} se {$status} correctamente.");
    }

    private function findOrFail(string $id): stdClass
    {
        $antiquity = DB::table('tip_antiguedad')
            ->where('IdAntiguedad', $id)
            ->first();

        abort_if($antiquity === null, 404);

        return $antiquity;
    }
}
