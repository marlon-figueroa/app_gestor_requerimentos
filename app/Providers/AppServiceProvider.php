<?php

namespace App\Providers;

use App\Models\CasoUso;
use App\Models\Requerimiento;
use App\Models\RequerimientoAdjunto;
use App\Models\User;
use App\Observers\BitacoraObserver;
use App\Services\MenuService;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        Paginator::useBootstrapFive();

        Carbon::setLocale(config('app.locale'));

        // El rol Administrador tiene acceso total, incluso a permisos creados despues.
        Gate::before(fn ($user) => $user->hasRole('Administrador') ? true : null);

        Gate::define('viewPulse', fn (User $user) => $user->can('monitoreo.pulse'));
        Gate::define('viewLogViewer', fn (User $user) => $user->can('monitoreo.logs'));

        View::composer('layouts.partials.sidebar', function ($view) {
            $view->with('menu', app(MenuService::class)->construir(auth()->user()));
        });

        User::observe(BitacoraObserver::class);
        Role::observe(BitacoraObserver::class);
        Permission::observe(BitacoraObserver::class);
        Requerimiento::observe(BitacoraObserver::class);
        CasoUso::observe(BitacoraObserver::class);
        RequerimientoAdjunto::observe(BitacoraObserver::class);
    }
}
