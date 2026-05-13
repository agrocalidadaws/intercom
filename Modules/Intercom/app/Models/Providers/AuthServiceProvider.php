<?php

namespace Modules\Intercom\Models\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Modules\Intercom\Exceptions\Handler;

class AuthServiceProvider extends ServiceProvider
{

  /**
   * Register any application services.
   */
  public function register(): void
  {
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {

    Passport::enablePasswordGrant();
    Passport::tokensExpireIn(now()->addMinutes(30));
    Passport::refreshTokensExpireIn(now()->addDays(1));

    // Definir qué "scopes" están disponibles
    Passport::tokensCan([
      'admin' => 'Acceso total a la plataforma',
      'user'  => 'Acceso limitado',
    ]);
  }
  
}
