<?php

namespace Hanoivip\VietnamPrepaidCard;

use Illuminate\Support\ServiceProvider;
use Hanoivip\VietnamPrepaidCard\Services\CardToGame;
use Hanoivip\VietnamPrepaidCard\Services\CardToWeb;

class LibServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/hanoivip'),
            __DIR__.'/../config' => config_path(),
        ]);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        //$this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom( __DIR__.'/../lang', 'hanoivip.vpcard');
        $this->mergeConfigFrom( __DIR__.'/../config/vpcard.php', 'vpcard');
        $this->loadViewsFrom(__DIR__ . '/../views', 'hanoivip.vpcard');
    }
    
    public function register()
    {
        $this->commands([
        ]);
        $this->app->bind('CardToWeb', CardToWeb::class);
        $this->app->bind('CardToGame', CardToGame::class);
    }
}
