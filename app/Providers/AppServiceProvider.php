<?php

namespace App\Providers;

use App\Services\I18nService;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // No services need to be registered.
        // If You return from defult views folder comment the code 
        $path = resource_path('ll_custom_views/views');
        if (is_dir($path)) {
            View::addLocation(resource_path('ll_custom_views/views'));
        }
        
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request, I18nService $i18nService, UrlGenerator $url)
    {
        // Log all queries
        /*
        \DB::listen(function ($query) {
            \Log::info([
                $query->sql,
                $query->bindings,
                $query->time
            ]);
        });
        */

        if (! app()->runningInConsole()) {
            // Force SSL if required
            if (config('default.force_ssl')) {
                $url->forceScheme('https');
            }
        }
    }
}
