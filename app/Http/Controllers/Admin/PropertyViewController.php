<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePropertyViewRequest;
use App\Http\Requests\Admin\UpdatePropertyViewRequest;
use App\Http\Requests\Admin\UpdatePropertyViewStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class PropertyViewController extends Controller
{
    public function index(): View
    {
        $propertyViews = DB::table('tip_vista')
            ->orderBy('Descrip')
            ->orderBy('IdVista')
            ->paginate(15);

        return view('admin.catalogs.views.index', compact('propertyViews'));
    }

    public function create(): View
    {
        return view('admin.catalogs.views.create');
    }

    public function store(StorePropertyViewRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::table('tip_vista')->insert([
            'IdVista' => $data['id'],
            'Descrip' => $data['descripcion'],
            'Hab' => $data['hab'],
        ]);

        return to_route('admin.catalogs.views.index')
            ->with('success', "La vista {$data['descripcion']} se creó correctamente.");
    }

    public function edit(string $propertyView): View
    {
        return view('admin.catalogs.views.edit', [
            'propertyView' => $this->findOrFail($propertyView),
        ]);
    }

    public function update(UpdatePropertyViewRequest $request, string $propertyView): RedirectResponse
    {
        $currentPropertyView = $this->findOrFail($propertyView);
        $data = $request->validated();

        DB::table('tip_vista')
            ->where('IdVista', $currentPropertyView->IdVista)
            ->update([
                'Descrip' => $data['descripcion'],
                'Hab' => $data['hab'],
            ]);

        return to_route('admin.catalogs.views.index')
            ->with('success', "La vista {$data['descripcion']} se actualizó correctamente.");
    }

    public function updateStatus(UpdatePropertyViewStatusRequest $request, string $propertyView): RedirectResponse
    {
        $currentPropertyView = $this->findOrFail($propertyView);
        $isEnabled = $request->boolean('hab');

        DB::table('tip_vista')
            ->where('IdVista', $currentPropertyView->IdVista)
            ->update(['Hab' => $isEnabled]);

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.catalogs.views.index')
            ->with('success', "La vista {$currentPropertyView->Descrip} se {$status} correctamente.");
    }

    private function findOrFail(string $id): stdClass
    {
        $propertyView = DB::table('tip_vista')
            ->where('IdVista', $id)
            ->first();

        abort_if($propertyView === null, 404);

        return $propertyView;
    }
}
