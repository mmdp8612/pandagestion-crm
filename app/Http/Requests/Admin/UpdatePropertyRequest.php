<?php

namespace App\Http\Requests\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use stdClass;

class UpdatePropertyRequest extends SavePropertyRequest
{
    private ?stdClass $currentProperty = null;

    protected function uniqueCodeRule(): Unique
    {
        return Rule::unique('bienesraices', 'Codigo')
            ->ignore((int) $this->route('property'));
    }

    protected function currentCatalogValue(string $propertyColumn): string|int|null
    {
        $this->currentProperty ??= DB::table('bienesraices')
            ->where('id', (int) $this->route('property'))
            ->first();

        $value = $this->currentProperty?->{$propertyColumn};

        return (is_string($value) && $value !== '') || is_int($value) ? $value : null;
    }
}
