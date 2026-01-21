<?php

namespace App\Providers;

use App\Services\Grading\GradeCalculator;
use App\Services\Grading\GradeBoundaryResolver;
use App\Services\Grading\GPACalculator;
use App\Services\Grading\CGPACalculator;
use App\Services\Grading\ClassificationResolver;
use App\Services\Grading\AcademicStandingResolver;
use App\Services\Grading\RetakeCapEnforcer;
use App\Services\Grading\GradingService;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for Grading Services
 *
 * Registers all grading-related services into the container.
 */
class GradingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GradeBoundaryResolver::class);
        $this->app->singleton(RetakeCapEnforcer::class);

        $this->app->singleton(GradeCalculator::class, function ($app) {
            return new GradeCalculator(
                $app->make(GradeBoundaryResolver::class),
                $app->make(RetakeCapEnforcer::class),
            );
        });

        $this->app->singleton(GPACalculator::class);
        $this->app->singleton(CGPACalculator::class);
        $this->app->singleton(ClassificationResolver::class);
        $this->app->singleton(AcademicStandingResolver::class);

        $this->app->singleton(GradingService::class, function ($app) {
            return new GradingService(
                $app->make(GradeCalculator::class),
                $app->make(GPACalculator::class),
                $app->make(CGPACalculator::class),
                $app->make(ClassificationResolver::class),
                $app->make(AcademicStandingResolver::class),
                $app->make(GradeBoundaryResolver::class),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
