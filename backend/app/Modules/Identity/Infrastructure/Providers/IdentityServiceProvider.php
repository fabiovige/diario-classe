<?php

namespace App\Modules\Identity\Infrastructure\Providers;

use App\Modules\Identity\Infrastructure\Middleware\CheckPermission;
use App\Modules\Identity\Infrastructure\Middleware\ApplySchoolScope;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('permission', CheckPermission::class);
        $router->aliasMiddleware('school.scope', ApplySchoolScope::class);
    }
}
