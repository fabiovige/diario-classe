<?php

namespace App\Modules\Assessment\Infrastructure\Providers;

use App\Modules\Assessment\Domain\Strategies\GradeCalculationFactory;
use Illuminate\Support\ServiceProvider;

class AssessmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GradeCalculationFactory::class);
    }

    public function boot(): void {}
}
