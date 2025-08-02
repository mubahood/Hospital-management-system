<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\ConsultationRepositoryInterface;
use App\Repositories\Contracts\PatientRepositoryInterface;
use App\Repositories\Contracts\BillingRepositoryInterface;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\ConsultationRepository;
use App\Repositories\PatientRepository;
use App\Repositories\BillingRepository;
use App\Repositories\InventoryRepository;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\BillingItem;

/**
 * RepositoryServiceProvider - Registers all repository dependencies
 * 
 * This provider binds repository interfaces to their concrete implementations
 * for dependency injection throughout the application
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings
     */
    public function register(): void
    {
        // Consultation Repository
        $this->app->bind(ConsultationRepositoryInterface::class, function ($app) {
            return new ConsultationRepository(new Consultation());
        });

        // Patient Repository
        $this->app->bind(PatientRepositoryInterface::class, function ($app) {
            return new PatientRepository(new Patient());
        });

        // Billing Repository
        $this->app->bind(BillingRepositoryInterface::class, function ($app) {
            return new BillingRepository(new BillingItem());
        });

        // Inventory Repository
        $this->app->bind(InventoryRepositoryInterface::class, function ($app) {
            return new InventoryRepository(new \App\Models\DoseItem());
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}
