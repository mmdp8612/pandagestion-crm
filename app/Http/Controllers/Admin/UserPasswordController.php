<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUpdateUserPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserPasswordController extends Controller
{
    public function edit(Request $request, User $user): View
    {
        abort_if($request->user()->is($user), 403);

        return view('admin.users.password', compact('user'));
    }

    public function update(AdminUpdateUserPasswordRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $user): void {
            $user->forceFill([
                'password' => $data['password'],
                'remember_token' => Str::random(60),
            ])->save();

            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->delete();
        });

        return to_route('admin.users.index')
            ->with('success', "La contraseña de {$user->name} se restableció correctamente.");
    }
}
