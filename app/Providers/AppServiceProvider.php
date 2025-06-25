<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Policies\ClientPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TimeEntryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Invoice;
use App\Policies\InvoicePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Здесь мы говорим Laravel, какую политику использовать для какой модели.
        // Это как установить правила доступа в нашем цифровом замке.
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(TimeEntry::class, TimeEntryPolicy::class);
		Gate::policy(Invoice::class, InvoicePolicy::class);
    }
}