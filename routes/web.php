<?php

use App\Http\Controllers\Admin\AddressSearchController;
use App\Http\Controllers\Admin\AntiquityController;
use App\Http\Controllers\Admin\CommercializationController;
use App\Http\Controllers\Admin\CurrencyTypeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GarageController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\OrientationController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\PropertyImageController;
use App\Http\Controllers\Admin\PropertyUseController;
use App\Http\Controllers\Admin\PropertyViewController;
use App\Http\Controllers\Admin\RealEstateAgencyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TypologyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicInquiryController;
use App\Http\Controllers\PublicPropertyController;
use App\Http\Controllers\PublicSeoController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('home');
Route::get('/sitemap.xml', [PublicSeoController::class, 'sitemap'])->name('public.sitemap');
Route::get('/robots.txt', [PublicSeoController::class, 'robots'])->name('public.robots');
Route::get('/propiedades', [PublicHomeController::class, 'properties'])
    ->name('public.properties.index');
Route::get('/propiedades/{slug}', [PublicPropertyController::class, 'show'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->name('public.properties.show');
Route::post('/propiedades/{slug}/consultas', [PublicInquiryController::class, 'store'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->middleware('throttle:5,1')
    ->name('public.properties.inquiries.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)
            ->middleware('can:dashboard')
            ->name('dashboard');

        Route::get('/bienesraices', [PropertyController::class, 'index'])
            ->middleware('can:bienesraices')
            ->name('properties.index');

        Route::get('/bienesraices/nuevo', [PropertyController::class, 'create'])
            ->middleware('can:bienesraices')
            ->name('properties.create');

        Route::get('/bienesraices/direcciones/buscar', AddressSearchController::class)
            ->middleware('can:bienesraices')
            ->name('properties.addresses.search');

        Route::post('/bienesraices', [PropertyController::class, 'store'])
            ->middleware('can:bienesraices')
            ->name('properties.store');

        Route::get('/bienesraices/{property}', [PropertyController::class, 'show'])
            ->whereNumber('property')
            ->middleware('can:bienesraices')
            ->name('properties.show');

        Route::get('/bienesraices/{property}/editar', [PropertyController::class, 'edit'])
            ->whereNumber('property')
            ->middleware('can:bienesraices')
            ->name('properties.edit');

        Route::put('/bienesraices/{property}', [PropertyController::class, 'update'])
            ->whereNumber('property')
            ->middleware('can:bienesraices')
            ->name('properties.update');

        Route::patch('/bienesraices/{property}/estado', [PropertyController::class, 'updateStatus'])
            ->whereNumber('property')
            ->middleware('can:bienesraices')
            ->name('properties.status.update');

        Route::get('/bienesraices/{property}/imagenes', [PropertyImageController::class, 'index'])
            ->whereNumber('property')
            ->middleware('can:bienesraices')
            ->name('properties.images.index');

        Route::post('/bienesraices/{property}/imagenes', [PropertyImageController::class, 'store'])
            ->whereNumber('property')
            ->middleware('can:bienesraices')
            ->name('properties.images.store');

        Route::patch('/bienesraices/{property}/imagenes/orden', [PropertyImageController::class, 'updateOrder'])
            ->whereNumber('property')
            ->middleware('can:bienesraices')
            ->name('properties.images.order.update');

        Route::patch('/bienesraices/{property}/imagenes/{image}/portada', [PropertyImageController::class, 'updateCover'])
            ->whereNumber(['property', 'image'])
            ->middleware('can:bienesraices')
            ->name('properties.images.cover.update');

        Route::patch('/bienesraices/{property}/imagenes/{image}/estado', [PropertyImageController::class, 'updateStatus'])
            ->whereNumber(['property', 'image'])
            ->middleware('can:bienesraices')
            ->name('properties.images.status.update');

        Route::delete('/bienesraices/{property}/imagenes/{image}', [PropertyImageController::class, 'destroy'])
            ->whereNumber(['property', 'image'])
            ->middleware('can:bienesraices')
            ->name('properties.images.destroy');

        Route::get('/consultas', [InquiryController::class, 'index'])
            ->middleware('can:consultas')
            ->name('inquiries.index');

        Route::get('/consultas/{inquiry}', [InquiryController::class, 'show'])
            ->whereNumber('inquiry')
            ->middleware('can:consultas')
            ->name('inquiries.show');

        Route::patch('/consultas/{inquiry}/estado', [InquiryController::class, 'updateStatus'])
            ->whereNumber('inquiry')
            ->middleware('can:consultas')
            ->name('inquiries.status.update');

        Route::get('/catalogos/antiguedades', [AntiquityController::class, 'index'])
            ->middleware('can:catalogo')
            ->name('catalogs.antiquities.index');

        Route::get('/catalogos/antiguedades/nueva', [AntiquityController::class, 'create'])
            ->middleware('can:catalogo')
            ->name('catalogs.antiquities.create');

        Route::post('/catalogos/antiguedades', [AntiquityController::class, 'store'])
            ->middleware('can:catalogo')
            ->name('catalogs.antiquities.store');

        Route::get('/catalogos/antiguedades/{antiquity}/editar', [AntiquityController::class, 'edit'])
            ->middleware('can:catalogo')
            ->name('catalogs.antiquities.edit');

        Route::put('/catalogos/antiguedades/{antiquity}', [AntiquityController::class, 'update'])
            ->middleware('can:catalogo')
            ->name('catalogs.antiquities.update');

        Route::patch('/catalogos/antiguedades/{antiquity}/estado', [AntiquityController::class, 'updateStatus'])
            ->middleware('can:catalogo')
            ->name('catalogs.antiquities.status.update');

        Route::get('/catalogos/comercializaciones', [CommercializationController::class, 'index'])
            ->middleware('can:catalogo')
            ->name('catalogs.commercializations.index');

        Route::get('/catalogos/comercializaciones/nueva', [CommercializationController::class, 'create'])
            ->middleware('can:catalogo')
            ->name('catalogs.commercializations.create');

        Route::post('/catalogos/comercializaciones', [CommercializationController::class, 'store'])
            ->middleware('can:catalogo')
            ->name('catalogs.commercializations.store');

        Route::get('/catalogos/comercializaciones/{commercialization}/editar', [CommercializationController::class, 'edit'])
            ->middleware('can:catalogo')
            ->name('catalogs.commercializations.edit');

        Route::put('/catalogos/comercializaciones/{commercialization}', [CommercializationController::class, 'update'])
            ->middleware('can:catalogo')
            ->name('catalogs.commercializations.update');

        Route::patch('/catalogos/comercializaciones/{commercialization}/estado', [CommercializationController::class, 'updateStatus'])
            ->middleware('can:catalogo')
            ->name('catalogs.commercializations.status.update');

        Route::get('/catalogos/tipologias', [TypologyController::class, 'index'])
            ->middleware('can:catalogo')
            ->name('catalogs.typologies.index');

        Route::get('/catalogos/tipologias/nueva', [TypologyController::class, 'create'])
            ->middleware('can:catalogo')
            ->name('catalogs.typologies.create');

        Route::post('/catalogos/tipologias', [TypologyController::class, 'store'])
            ->middleware('can:catalogo')
            ->name('catalogs.typologies.store');

        Route::get('/catalogos/tipologias/{typology}/editar', [TypologyController::class, 'edit'])
            ->middleware('can:catalogo')
            ->name('catalogs.typologies.edit');

        Route::put('/catalogos/tipologias/{typology}', [TypologyController::class, 'update'])
            ->middleware('can:catalogo')
            ->name('catalogs.typologies.update');

        Route::patch('/catalogos/tipologias/{typology}/estado', [TypologyController::class, 'updateStatus'])
            ->middleware('can:catalogo')
            ->name('catalogs.typologies.status.update');

        Route::get('/catalogos/cocheras', [GarageController::class, 'index'])
            ->middleware('can:catalogo')
            ->name('catalogs.garages.index');

        Route::get('/catalogos/cocheras/nueva', [GarageController::class, 'create'])
            ->middleware('can:catalogo')
            ->name('catalogs.garages.create');

        Route::post('/catalogos/cocheras', [GarageController::class, 'store'])
            ->middleware('can:catalogo')
            ->name('catalogs.garages.store');

        Route::get('/catalogos/cocheras/{garage}/editar', [GarageController::class, 'edit'])
            ->middleware('can:catalogo')
            ->name('catalogs.garages.edit');

        Route::put('/catalogos/cocheras/{garage}', [GarageController::class, 'update'])
            ->middleware('can:catalogo')
            ->name('catalogs.garages.update');

        Route::patch('/catalogos/cocheras/{garage}/estado', [GarageController::class, 'updateStatus'])
            ->middleware('can:catalogo')
            ->name('catalogs.garages.status.update');

        Route::get('/catalogos/orientaciones', [OrientationController::class, 'index'])
            ->middleware('can:catalogo')
            ->name('catalogs.orientations.index');

        Route::get('/catalogos/orientaciones/nueva', [OrientationController::class, 'create'])
            ->middleware('can:catalogo')
            ->name('catalogs.orientations.create');

        Route::post('/catalogos/orientaciones', [OrientationController::class, 'store'])
            ->middleware('can:catalogo')
            ->name('catalogs.orientations.store');

        Route::get('/catalogos/orientaciones/{orientation}/editar', [OrientationController::class, 'edit'])
            ->middleware('can:catalogo')
            ->name('catalogs.orientations.edit');

        Route::put('/catalogos/orientaciones/{orientation}', [OrientationController::class, 'update'])
            ->middleware('can:catalogo')
            ->name('catalogs.orientations.update');

        Route::patch('/catalogos/orientaciones/{orientation}/estado', [OrientationController::class, 'updateStatus'])
            ->middleware('can:catalogo')
            ->name('catalogs.orientations.status.update');

        Route::get('/catalogos/usos', [PropertyUseController::class, 'index'])
            ->middleware('can:catalogo')
            ->name('catalogs.uses.index');

        Route::get('/catalogos/usos/nuevo', [PropertyUseController::class, 'create'])
            ->middleware('can:catalogo')
            ->name('catalogs.uses.create');

        Route::post('/catalogos/usos', [PropertyUseController::class, 'store'])
            ->middleware('can:catalogo')
            ->name('catalogs.uses.store');

        Route::get('/catalogos/usos/{propertyUse}/editar', [PropertyUseController::class, 'edit'])
            ->middleware('can:catalogo')
            ->name('catalogs.uses.edit');

        Route::put('/catalogos/usos/{propertyUse}', [PropertyUseController::class, 'update'])
            ->middleware('can:catalogo')
            ->name('catalogs.uses.update');

        Route::patch('/catalogos/usos/{propertyUse}/estado', [PropertyUseController::class, 'updateStatus'])
            ->middleware('can:catalogo')
            ->name('catalogs.uses.status.update');

        Route::get('/catalogos/vistas', [PropertyViewController::class, 'index'])
            ->middleware('can:catalogo')
            ->name('catalogs.views.index');

        Route::get('/catalogos/vistas/nueva', [PropertyViewController::class, 'create'])
            ->middleware('can:catalogo')
            ->name('catalogs.views.create');

        Route::post('/catalogos/vistas', [PropertyViewController::class, 'store'])
            ->middleware('can:catalogo')
            ->name('catalogs.views.store');

        Route::get('/catalogos/vistas/editar/{propertyView}', [PropertyViewController::class, 'edit'])
            ->where('propertyView', '.*')
            ->middleware('can:catalogo')
            ->name('catalogs.views.edit');

        Route::put('/catalogos/vistas/{propertyView}', [PropertyViewController::class, 'update'])
            ->where('propertyView', '.*')
            ->middleware('can:catalogo')
            ->name('catalogs.views.update');

        Route::patch('/catalogos/vistas/estado/{propertyView}', [PropertyViewController::class, 'updateStatus'])
            ->where('propertyView', '.*')
            ->middleware('can:catalogo')
            ->name('catalogs.views.status.update');

        Route::get('/catalogos/tipos-moneda', [CurrencyTypeController::class, 'index'])
            ->middleware('can:catalogo')
            ->name('catalogs.currency-types.index');

        Route::get('/catalogos/tipos-moneda/nuevo', [CurrencyTypeController::class, 'create'])
            ->middleware('can:catalogo')
            ->name('catalogs.currency-types.create');

        Route::post('/catalogos/tipos-moneda', [CurrencyTypeController::class, 'store'])
            ->middleware('can:catalogo')
            ->name('catalogs.currency-types.store');

        Route::get('/catalogos/tipos-moneda/{currencyType}/editar', [CurrencyTypeController::class, 'edit'])
            ->whereNumber('currencyType')
            ->middleware('can:catalogo')
            ->name('catalogs.currency-types.edit');

        Route::put('/catalogos/tipos-moneda/{currencyType}', [CurrencyTypeController::class, 'update'])
            ->whereNumber('currencyType')
            ->middleware('can:catalogo')
            ->name('catalogs.currency-types.update');

        Route::patch('/catalogos/tipos-moneda/{currencyType}/estado', [CurrencyTypeController::class, 'updateStatus'])
            ->whereNumber('currencyType')
            ->middleware('can:catalogo')
            ->name('catalogs.currency-types.status.update');

        Route::get('/configuracion/inmobiliaria', [RealEstateAgencyController::class, 'edit'])
            ->middleware('can:configuracion')
            ->name('real-estate-agency.edit');

        Route::get('/configuracion/inmobiliaria/direcciones/buscar', AddressSearchController::class)
            ->middleware('can:configuracion')
            ->name('real-estate-agency.addresses.search');

        Route::put('/configuracion/inmobiliaria', [RealEstateAgencyController::class, 'update'])
            ->middleware('can:configuracion')
            ->name('real-estate-agency.update');

        Route::delete('/configuracion/inmobiliaria/logo', [RealEstateAgencyController::class, 'destroyLogo'])
            ->middleware('can:configuracion')
            ->name('real-estate-agency.logo.destroy');

        Route::get('/configuracion/usuarios', [UserController::class, 'index'])
            ->middleware('can:usuarios')
            ->name('users.index');

        Route::get('/configuracion/usuarios/nuevo', [UserController::class, 'create'])
            ->middleware('can:usuarios')
            ->name('users.create');

        Route::post('/configuracion/usuarios', [UserController::class, 'store'])
            ->middleware('can:usuarios')
            ->name('users.store');

        Route::get('/configuracion/usuarios/{user}/editar', [UserController::class, 'edit'])
            ->middleware('can:usuarios')
            ->name('users.edit');

        Route::put('/configuracion/usuarios/{user}', [UserController::class, 'update'])
            ->middleware('can:usuarios')
            ->name('users.update');

        Route::delete('/configuracion/usuarios/{user}', [UserController::class, 'destroy'])
            ->middleware('can:usuarios')
            ->name('users.destroy');

        Route::patch('/configuracion/usuarios/{user}/estado', [UserController::class, 'updateStatus'])
            ->middleware('can:usuarios')
            ->name('users.status.update');

        Route::get('/configuracion/usuarios/{user}/contrasena', [UserPasswordController::class, 'edit'])
            ->middleware('can:usuarios')
            ->name('users.password.edit');

        Route::put('/configuracion/usuarios/{user}/contrasena', [UserPasswordController::class, 'update'])
            ->middleware('can:usuarios')
            ->name('users.password.update');

        Route::get('/configuracion/contrasena', [PasswordController::class, 'edit'])
            ->name('password.edit');

        Route::put('/configuracion/contrasena', [PasswordController::class, 'update'])
            ->name('password.update');

        Route::get('/configuracion/roles', [RoleController::class, 'index'])
            ->middleware('can:roles')
            ->name('roles.index');

        Route::get('/configuracion/roles/nuevo', [RoleController::class, 'create'])
            ->middleware('can:roles')
            ->name('roles.create');

        Route::post('/configuracion/roles', [RoleController::class, 'store'])
            ->middleware('can:roles')
            ->name('roles.store');

        Route::get('/configuracion/roles/{role}/editar', [RoleController::class, 'edit'])
            ->middleware('can:roles')
            ->name('roles.edit');

        Route::put('/configuracion/roles/{role}', [RoleController::class, 'update'])
            ->middleware('can:roles')
            ->name('roles.update');

        Route::delete('/configuracion/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('can:roles')
            ->name('roles.destroy');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
