<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StorePropertyRequest extends SavePropertyRequest
{
    protected function uniqueCodeRule(): Unique
    {
        return Rule::unique('bienesraices', 'Codigo');
    }
}
