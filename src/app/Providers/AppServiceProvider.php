<?php

namespace App\Providers;

use App\Domains\Collect\Helpers\RequestHelper;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Request::macro(
            'setCleanPathInfo',
            function () {
                /** @var Request $this */
                $uri = RequestHelper::removeHostInUri($this->pathInfo);
                $this->pathInfo  = $uri;
            }
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
