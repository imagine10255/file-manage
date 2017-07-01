<?php

namespace Fatansy\System;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;


class FileManageServiceProvider extends ServiceProvider
{
    /**
     * namespace.
     * @var string
     */
    protected $namespace = 'Imagine10255\FileManage\Http\Controllers';
    protected $packageName = 'file-manage';


    /**
     * Bootstrap any application services.
     * @param Request $request
     * @param Router $router
     */
    public function boot(Request $request, Router $router)
    {
        $this->handleRoutes($router);
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * register routes.
     * @param Router $router
     * @param array $config
     */
    protected function handleRoutes(Router $router, $config = [])
    {
        if (!$this->app->runningInConsole() && $this->app->routesAreCached() === false){
            $router->group(array_merge([
                'namespace' => $this->namespace,
                'middleware' => ['web'],
            ]), function($router){
                require __DIR__.'/Http/routes.php';
            });
        }
    }


    /**
     * handle publishes.
     */
    protected function handlePublishes()
    {
    }

}
