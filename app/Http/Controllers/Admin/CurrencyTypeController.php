<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCurrencyTypeRequest;
use App\Http\Requests\Admin\UpdateCurrencyTypeRequest;
use App\Http\Requests\Admin\UpdateCurrencyTypeStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class CurrencyTypeController extends Controller
{
    public function index(): View
    {
        $currencyTypes = DB::table('tip_tipomoneda')
            ->orderBy('Descrip')
            ->orderBy('idTipoMoneda')
            ->paginate(15);

        return view('admin.catalogs.currency-types.index', compact('currencyTypes'));
    }

    public function create(): View
    {
        return view('admin.catalogs.currency-types.create');
    }

    public function store(StoreCurrencyTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::table('tip_tipomoneda')->insert([
            'idTipoMoneda' => $data['id'],
            'Descrip' => $data['descripcion'],
            'Simbolo' => $data['simbolo'],
            'Hab' => $data['hab'],
        ]);

        return to_route('admin.catalogs.currency-types.index')
            ->with('success', "El tipo de moneda {$data['descripcion']} se creó correctamente.");
    }

    public function edit(string $currencyType): View
    {
        return view('admin.catalogs.currency-types.edit', [
            'currencyType' => $this->findOrFail($currencyType),
        ]);
    }

    public function update(UpdateCurrencyTypeRequest $request, string $currencyType): RedirectResponse
    {
        $currentCurrencyType = $this->findOrFail($currencyType);
        $data = $request->validated();

        DB::table('tip_tipomoneda')
            ->where('idTipoMoneda', $currentCurrencyType->idTipoMoneda)
            ->update([
                'Descrip' => $data['descripcion'],
                'Simbolo' => $data['simbolo'],
                'Hab' => $data['hab'],
            ]);

        return to_route('admin.catalogs.currency-types.index')
            ->with('success', "El tipo de moneda {$data['descripcion']} se actualizó correctamente.");
    }

    public function updateStatus(UpdateCurrencyTypeStatusRequest $request, string $currencyType): RedirectResponse
    {
        $currentCurrencyType = $this->findOrFail($currencyType);
        $isEnabled = $request->boolean('hab');

        DB::table('tip_tipomoneda')
            ->where('idTipoMoneda', $currentCurrencyType->idTipoMoneda)
            ->update(['Hab' => $isEnabled]);

        $status = $isEnabled ? 'habilitó' : 'deshabilitó';

        return to_route('admin.catalogs.currency-types.index')
            ->with('success', "El tipo de moneda {$currentCurrencyType->Descrip} se {$status} correctamente.");
    }

    private function findOrFail(string $id): stdClass
    {
        $currencyType = DB::table('tip_tipomoneda')
            ->where('idTipoMoneda', $id)
            ->first();

        abort_if($currencyType === null, 404);

        return $currencyType;
    }
}
