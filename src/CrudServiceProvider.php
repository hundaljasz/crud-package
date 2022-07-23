<?php

namespace devjaskirat\crud;

use Illuminate\Support\ServiceProvider;
use devjaskirat\crud\Generators\CrudCommand;

class CrudServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->registerCommands();
    }

    protected function registerCommands()
    {
        $this->commands(CrudCommand::class);
    }
}
