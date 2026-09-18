<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    require_once app_path('Services/Webauthn/LocalCredentialAttestationValidator.php');
    require_once app_path('Services/Webauthn/LocalCredentialAssertionValidator.php');

    // Override WebAuthn Validators for Localhost (HTTP) support
    $this->app->bind(
      \LaravelWebauthn\Services\Webauthn\CredentialAttestationValidator::class ,
      function ($app) {
      return new \App\Services\Webauthn\LocalCredentialAttestationValidator(
      $app['request'],
      $app[\Illuminate\Contracts\Cache\Repository::class],
      $app[\Symfony\Component\Serializer\SerializerInterface::class],
      $app[\Webauthn\AuthenticatorAttestationResponseValidator::class]
      );
    }
    );

    $this->app->bind(
      \LaravelWebauthn\Services\Webauthn\CredentialAssertionValidator::class ,
      function ($app) {
      return new \App\Services\Webauthn\LocalCredentialAssertionValidator(
      $app['request'],
      $app[\Illuminate\Contracts\Cache\Repository::class],
      $app[\Symfony\Component\Serializer\SerializerInterface::class],
      $app[\Webauthn\AuthenticatorAssertionResponseValidator::class]
      );
    }
    );
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    if ($this->app->environment('production')) {
      URL::forceScheme('https');
    // Model::shouldBeStrict(true);
    }

    // paginate bootstrap 5
    // if (method_exists(\Illuminate\Pagination\AbstractPaginator::class, 'useBootstrap')) {
    //   \Illuminate\Pagination\AbstractPaginator::useBootstrap();
    // }

    Paginator::useBootstrapFour();

    try {
      $otherSec = \App\Models\SecuritySetting::get('security_other', []);
      $duration = isset($otherSec['session_duration']) ? (int)$otherSec['session_duration'] : null;

      if ($duration !== null) {
        if ($duration > 0) {
          config(['session.lifetime' => $duration]);
        }
      }
    }
    catch (\Exception $e) {
    // Fallback to default config if DB fails or table doesn't exist yet
    }

    // Ensure application name reflects domain / admin settings (domain -> general -> fallback)
    try {
      if (class_exists('Helper')) {
        $siteTitle = \Helper::branding('title', null, true);
      } else {
        $siteTitle = null;
      }

      if (empty($siteTitle)) {
        $siteTitle = config('app.name');
      }

      if (empty($siteTitle)) {
        $siteTitle = 'KiyoVN';
      }

      config(['app.name' => $siteTitle]);
    } catch (\Exception $e) {
      // Silently ignore — leave existing config if something fails
    }
  }
}
