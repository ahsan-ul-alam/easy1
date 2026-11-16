<?php

namespace App\Providers;

use App\Support\Localization;
use App\Support\SiteSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SiteSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        View::composer(['layouts.*', 'storefront.*', 'admin.*', 'components.*', 'errors.*'], function (ViewInstance $view): void {
            $view->with('site', $this->app->make(SiteSettings::class));
        });

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)
                ->by(Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip()))
                ->response(fn (Request $request, array $headers) => back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => __('auth.throttle', ['seconds' => Localization::digits($headers['Retry-After'] ?? 60)])]));
        });
    }
}
