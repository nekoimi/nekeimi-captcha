<?php
/**
 * ----------------------------------------------------------------------
 *              nekoimi <i@sakuraio.com>
 *                                          ------
 *   Copyright (c) 2017-2019 https://nekoimi.com All rights reserved.
 * ----------------------------------------------------------------------
 */

namespace Nekoimi\Canvas;

use Illuminate\Support\ServiceProvider;

class LumenServiceProvider extends ServiceProvider
{
    /**
     * Register service.
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/config.php';
        $this->mergeConfigFrom($configPath, 'nekocanvas');

        $config = $this->app['config']->get('nekocanvas', []);

        $this->app->singleton(
            'nekoimi.canvas.captcha',
            function($app) use ($config){
                return new Captcha(
                    $config,
                    $app->make('cache'),
                    $app['Illuminate\Filesystem\Filesystem'],
                    $app['Intervention\Image\ImageManager']
                );
            }
        );

        $this->app->singleton(
            'nekoimi.canvas.avatar',
            function($app) use ($config){
                return new Avatar(
                    $config,
                    $app['Illuminate\Filesystem\Filesystem'],
                    $app['Intervention\Image\ImageManager']
                );
            }
        );
    }

    /**
     * Bootstrap the application.
     */
    public function boot()
    {
        $configPath = require_once __DIR__ . '/../config/config.php';
        if (function_exists('config_path')){
            $publishPath = config_path('nekocanvas.php');
        } else{
            $publishPath = base_path('config/nekocanvas.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');
    }

}
