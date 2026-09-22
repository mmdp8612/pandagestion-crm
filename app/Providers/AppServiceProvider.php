<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function ($view): void {
            $user = Auth::user();
            $newInquiryCount = 0;

            if ($user?->can('consultas')) {
                $newInquiryCount = DB::table('consultas')
                    ->where('Estado', 'nueva')
                    ->count();
            }

            $view->with('newInquiryCount', $newInquiryCount);
        });
    }
}
