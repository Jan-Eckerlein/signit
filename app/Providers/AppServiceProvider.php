<?php

namespace App\Providers;

use App\Models\DocumentSigner;
use App\Models\DocumentFieldValue;
use App\Observers\DocumentSignerObserver;
use App\Observers\DocumentFieldValueObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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
        Route::model('document_signer', DocumentSigner::class);

        // Register observers
        DocumentSigner::observe(DocumentSignerObserver::class);

        // Register the paginateOrGetAll macro on Builder
        Builder::macro('paginateOrGetAll', function(Request $request, ?string $resourceClass = null, int $defaultPerPage = 20, int $maxPerPage = 1000) {
            /** @var \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $this */

            
            // Allow bypassing pagination with ?all=true
            if ($request->boolean('all')) {
                return $this->get()->toResourceCollection($resourceClass);
            }
            
            // Use per_page from query parameter, default to provided value
            $perPage = $request->get('per_page', $defaultPerPage);
            
            // Safety limit to prevent abuse
            if ($perPage > $maxPerPage) {
                $perPage = $maxPerPage;
            }
            
            return $this->paginate($perPage)->toResourceCollection($resourceClass);
        });
    }
}
