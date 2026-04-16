<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
          Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer', 'JWT')
            );
        })
        ->withOperationTransformers(function (Operation $operation, RouteInfo $routeInfo) {
            $middlewares = $routeInfo->route->middleware();

            if (! in_array('auth:sanctum', $middlewares)) {
                $operation->security = [];
            }
        });

        // Paksa HTTPS jika diakses melalui Ngrok (karena Ngrok menggunakan HTTPS, untuk simulasi)
        if (str_contains(request()->header('host'), 'ngrok-free.dev')) {
            URL::forceScheme('https');
        }
    }
}
