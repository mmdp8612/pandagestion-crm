@php
    $flashMessages = [
        'success' => [
            'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            'label' => 'Éxito',
        ],
        'error' => [
            'classes' => 'border-red-200 bg-red-50 text-red-900',
            'label' => 'Error',
        ],
        'warning' => [
            'classes' => 'border-amber-200 bg-amber-50 text-amber-900',
            'label' => 'Atención',
        ],
        'info' => [
            'classes' => 'border-sky-200 bg-sky-50 text-sky-900',
            'label' => 'Información',
        ],
    ];

    $hasFlashMessages = collect(array_keys($flashMessages))
        ->contains(fn (string $type) => session()->has($type));
@endphp

@if ($hasFlashMessages || $errors->any())
    <div class="mb-6 space-y-3" aria-live="polite">
        @foreach ($flashMessages as $type => $presentation)
            @if (session()->has($type))
                <div
                    class="flex items-start gap-3 rounded-xl border px-4 py-3 text-sm {{ $presentation['classes'] }}"
                    role="{{ $type === 'error' ? 'alert' : 'status' }}"
                    data-dismissible-alert
                >
                    <div class="min-w-0 flex-1">
                        <p class="font-bold">{{ $presentation['label'] }}</p>
                        <p class="mt-0.5">{{ session($type) }}</p>
                    </div>
                    <button type="button" class="rounded-md p-1 opacity-60 transition hover:bg-black/5 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current" data-alert-dismiss aria-label="Cerrar mensaje">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                <p class="font-bold">Revisá los datos ingresados</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
