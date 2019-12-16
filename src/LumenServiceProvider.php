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
        $this->app->configure('nekocanvas');

        $this->app->singleton('nekoimi.canvas.avatar', function ($app) {
            return new Avatar(
                $this->configure('avatar'),
                $app['Illuminate\Filesystem\Filesystem'],
                $app['Intervention\Image\ImageManager']
            );
        });

        $this->app->singleton('nekoimi.canvas.captcha', function ($app) {
            return new Captcha(
                $this->configure('captcha'),
                $app->make('cache'),
                $app['Illuminate\Filesystem\Filesystem'],
                $app['Intervention\Image\ImageManager']
            );
        });
    }

    /**
     * @param string|null $name
     * @return mixed
     */
    protected function configure(string $name = null)
    {
        if (is_null($name)) {
            return config('nekocanvas', []);
        }

        return config('nekocanvas.' . $name, []);
    }
}
