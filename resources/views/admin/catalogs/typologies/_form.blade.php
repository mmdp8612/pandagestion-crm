<div class="space-y-6 p-6 sm:p-8">
    <div>
        <label for="id" class="block text-sm font-bold text-slate-700">Código</label>
        <input
            id="id"
            @if (! $typology) name="id" @endif
            type="text"
            value="{{ old('id', $typology->IdTipologia ?? '') }}"
            maxlength="4"
            autocomplete="off"
            @if ($typology) readonly @else required @endif
            @class([
                'mt-2 block w-full rounded-lg border px-3 py-2.5 font-mono uppercase text-slate-950 shadow-sm outline-none transition',
                'border-slate-300 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20' => ! $typology,
                'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-500' => $typology,
            ])
        >
        <p class="mt-2 text-xs leading-5 text-slate-500">
            @if ($typology)
                El código histórico es permanente y no puede modificarse.
            @else
                Hasta 4 letras, números o guiones. Se guardará en mayúsculas y luego no podrá modificarse.
            @endif
        </p>
        @error('id')
            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="descripcion" class="block text-sm font-bold text-slate-700">Descripción</label>
        <input
            id="descripcion"
            name="descripcion"
            type="text"
            value="{{ old('descripcion', $typology->Descrip ?? '') }}"
            required
            maxlength="20"
            autocomplete="off"
            class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
        >
        <p class="mt-2 text-xs leading-5 text-slate-500">Máximo 20 caracteres para conservar la compatibilidad histórica.</p>
        @error('descripcion')
            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-6 sm:grid-cols-[minmax(0,1fr)_12rem]">
        <div>
            <label for="tipo_general" class="block text-sm font-bold text-slate-700">Tipo general</label>
            <input
                id="tipo_general"
                name="tipo_general"
                type="text"
                value="{{ old('tipo_general', $typology->TipoGral ?? '') }}"
                maxlength="15"
                autocomplete="off"
                class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
            >
            <p class="mt-2 text-xs leading-5 text-slate-500">Agrupa tipologías relacionadas, por ejemplo Casas o Departamentos.</p>
            @error('tipo_general')
                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="orden_tipo_general" class="block text-sm font-bold text-slate-700">Orden del grupo</label>
            <input
                id="orden_tipo_general"
                name="orden_tipo_general"
                type="number"
                value="{{ old('orden_tipo_general', $typology->OrdTipoGral ?? 0) }}"
                required
                min="0"
                max="32767"
                step="1"
                inputmode="numeric"
                class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
            >
            <p class="mt-2 text-xs leading-5 text-slate-500">Los valores menores aparecen primero.</p>
            @error('orden_tipo_general')
                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <input type="hidden" name="hab" value="0">
        <label for="hab" class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/40">
            <input
                id="hab"
                name="hab"
                type="checkbox"
                value="1"
                @checked((bool) old('hab', $typology->Hab ?? true))
                class="mt-0.5 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
            >
            <span>
                <span class="block text-sm font-bold text-slate-800">Tipología habilitada</span>
                <span class="mt-1 block text-sm leading-5 text-slate-500">Estará disponible para utilizarla en nuevas propiedades.</span>
            </span>
        </label>
        @error('hab')
            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>
</div>
