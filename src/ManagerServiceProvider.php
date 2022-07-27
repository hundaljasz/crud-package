<?php

namespace briza\manager;

use Illuminate\Support\ServiceProvider;
use briza\manager\Generators\CrudCommand;

class ManagerServiceProvider extends ServiceProvider
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
