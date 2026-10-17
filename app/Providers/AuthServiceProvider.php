<?php

namespace App\Providers;

use App\Models\DocumentGenere;
use App\Models\ModeleDocument;
use App\Policies\DocumentGenerePolicy;
use App\Policies\ModeleDocumentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DocumentGenere::class => DocumentGenerePolicy::class,
        ModeleDocument::class => ModeleDocumentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
