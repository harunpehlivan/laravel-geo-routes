<?php

namespace LaraCrafts\GeoRoutes;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use LaraCrafts\GeoRoutes\Http\Middleware\GeoRoutesMiddleware;

class GeoRoutesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/geo-routes.php', 'geo-routes');
        $this->publishes([__DIR__ . '/../config/geo-routes.php' => config_path('geo-routes.php')], 'config');

        if (version_compare($this->app->version(), '5.5.0', '>=')) {
            $this->registerMacros();
        }

        Router::macro('GeoGroup', function (array $attributes, Closure $callback) {
            return new GeoGroup($attributes, $callback);
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $router = $this->app->make('router');

        if (method_exists($router, 'aliasMiddleware')) {
            $router->aliasMiddleware('geo', GeoRoutesMiddleware::class);
        } else {
            $router->middleware('geo', GeoRoutesMiddleware::class);
        }
    }

    /**
     * Register the route macros.
     *
     * @return void
     */
    protected function registerMacros()
    {
        Route::macro('allowFrom', function (string ...$countries) {
            return new GeoRoute($this, $countries, 'allow');
        });

        Route::macro('denyFrom', function (string ...$countries) {
            return new GeoRoute($this, $countries, 'deny');
        });

        Route::macro('from', function (string ...$countries) {
            return new GeoRoute($this, $countries, 'allow');
        });
    }
}
