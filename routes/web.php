<?php

use App\Helpers\Update;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Include debug routes
if (file_exists(__DIR__ . '/debug.php')) {
  require __DIR__ . '/debug.php';
}



/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- */

// Test route to verify Laravel is working
Route::get('/debug-db', function() {
    return [
        'database' => DB::getDatabaseName(),
        'env_database' => env('DB_DATABASE'),
        'config_database' => config('database.connections.mysql.database'),
    ];
});

Route::get('/test-route', function () {
  return 'Laravel is working! Time: ' . date('Y-m-d H:i:s');
});

Route::get('/set-locale/{locale}', App\Http\Controllers\SetLocaleController::class)->name('set-locale');
Route::get('/set-currency/{code}', App\Http\Controllers\SetCurrencyController::class)->name('set-currency');

Route::match (['post', 'get'], '/hooks/sepay-payment', [App\Http\Controllers\Cron\DepositCronController::class , 'sepayCallback'])->name('hooks.sepay-payment');

// CRON & Artisan (Outside localization for stability)
Route::prefix('/cron')->group(function () {
  // Callbacks
  Route::match (['post', 'get'], '/deposit/card-callback', [App\Http\Controllers\Cron\DepositCronController::class , 'cardCallback'])->name('cron.deposit.card-callback');
  Route::get('/deposit/fpayment-callback', [App\Http\Controllers\Cron\DepositCronController::class , 'fpaymentCallback'])->name('cron.deposit.fpayment-callback');
  Route::get('/deposit/pm-callback', [App\Http\Controllers\Cron\DepositCronController::class , 'pmCallback'])->name('cron.deposit.pm-callback');

  // Automation Task
  Route::get('/task', [App\Http\Controllers\Cron\TaskController::class , 'handle'])->name('cron.task');

  // Secure Cron Jobs
  Route::middleware('cron.key')->group(function () {
      Route::get('/accountsv2', [App\Http\Controllers\Cron\AccountV2Controller::class , 'handle'])->name('cron.accountsv2');
      Route::get('/run-backup', [App\Http\Controllers\Cron\BackupController::class , 'run'])->name('cron.run-backup');
      Route::get('/check-payment-staff', [App\Http\Controllers\Staff\DashboardController::class , 'cronCheck'])->name('cron.check-payment-staff');
      Route::get('/deposit', [App\Http\Controllers\Cron\DepositCronController::class , 'check'])->name('cron.deposit.check');

      Route::get('/artisan/init-setup', function () {
          Artisan::call('cache:clear');
          if (!auth()->check() || !auth()->user()->hasRole('partner')) {
            abort(403, 'Unauthorized action.');
          }
          Artisan::call('config:clear');
          Artisan::call('view:clear');
          Artisan::call('route:clear');
          Artisan::call('optimize:clear');
          Artisan::call('key:generate');
          Artisan::call('migrate', ['--force' => true]);
          return Artisan::output();
        }
        );

        Route::get('/artisan/fix-update', function () {
          $update = Update::runUpdate();
          return $update ? 'Update thành công' : 'Update thất bại';
        }
        );
      }
      );
    });

// The shared route definition
$registerRoutes = function () {
  Route::get('/', [App\Http\Controllers\HomeController::class , 'index']);
  Route::get('/join/{ref}', [App\Http\Controllers\HomeController::class , 'ref'])->name('ref');
  Route::get('/categories', [App\Http\Controllers\HomeController::class , 'categories'])->name('categories.index');
  Route::get('/categories/{slug}', [App\Http\Controllers\HomeController::class , 'category'])->name('categories.show');

  Auth::routes();

  Route::get('/login/{provider}', [App\Http\Controllers\Auth\SocialController::class , 'redirectToProvider'])->name('auth.social');
  Route::get('/login/{provider}/callback', [App\Http\Controllers\Auth\SocialController::class , 'handleProviderCallback'])->name('auth.social.callback');

  // Login Verification (Moved to secure-login to free up /verify)
  Route::get('/account/secure-login', [App\Http\Controllers\Auth\VerifyLoginController::class , 'show'])->name('account.login_verify');
  Route::post('/account/secure-login', [App\Http\Controllers\Auth\VerifyLoginController::class , 'verify'])->name('account.login_verify.post');

  // Account Action Verification (Restored to /account/verify as requested)
  Route::get('/account/verify', [App\Http\Controllers\Account\VerifyActionController::class , 'show'])->name('account.verify');
  Route::post('/account/verify', [App\Http\Controllers\Account\VerifyActionController::class , 'verify'])->name('account.verify.post');
  Route::post('/account/verify/resend', [App\Http\Controllers\Account\VerifyActionController::class , 'resend'])->name('account.verify.resend');
  Route::get('/account/verify/cancel', [App\Http\Controllers\Account\VerifyActionController::class , 'cancel'])->name('account.verify.cancel');


  Route::get('/home', [App\Http\Controllers\HomeController::class , 'index'])->name('home');

  // Upload Route
  Route::middleware(['auth'])->post('/upload/image', [App\Http\Controllers\Admin\UploadController::class , 'uploadImage'])->name('upload.image');

  // Pages Route
  Route::prefix('/pages')->group(function () {
      Route::middleware(['auth', 'check.last.login'])->group(function () {
          Route::get('/affiliates', [App\Http\Controllers\PageController::class , 'affiliates'])->name('pages.affiliates');
          Route::get('/affiliates/history', function () {
              return redirect()->route('pages.affiliates', ['tab' => 'history']);
            }
            )->name('pages.affiliates.history');
            Route::get('/affiliates/withdraw', function () {
              return redirect()->route('pages.affiliates', ['tab' => 'withdraw']);
            }
            )->name('pages.affiliates.withdraw');
          }
          );
          Route::get('/privacy-policy', [App\Http\Controllers\PageController::class , 'privacyPolicy'])->name('pages.privacy-policy');
          Route::get('/terms-of-service', [App\Http\Controllers\PageController::class , 'termsOfService'])->name('pages.terms-of-service');
        }
        );

        // Store Routes
        Route::prefix('/tai-khoan')->group(function () {
      Route::get('/', [App\Http\Controllers\Store\AccountController::class , 'list'])->name('store.account.list');
      Route::get('/{slug}', [App\Http\Controllers\Store\AccountController::class , 'index'])->name('store.account');
      Route::get('/thong-tin/{code}', [App\Http\Controllers\Store\AccountController::class , 'show'])->name('store.account.show');
    }
    );
    Route::prefix('/tai-khoan-v2')->group(function () {
      Route::get('/', [App\Http\Controllers\Store\AccountV2Controller::class , 'list'])->name('store.accountv2.list');
      Route::get('/{slug}', [App\Http\Controllers\Store\AccountV2Controller::class , 'index'])->name('store.accountv2');
      Route::get('/thong-tin/{code}', [App\Http\Controllers\Store\AccountV2Controller::class , 'show'])->name('store.accountv2.show');
    }
    );
    Route::prefix('/vat-pham')->group(function () {
      Route::get('/', [App\Http\Controllers\Store\ItemController::class , 'list'])->name('store.item.list');
      Route::get('/{slug}', [App\Http\Controllers\Store\ItemController::class , 'index'])->name('store.item');
      Route::get('/thong-tin/{code}', [App\Http\Controllers\Store\ItemController::class , 'show'])->name('store.item.show');
    }
    );
    Route::prefix('/cay-thue')->group(function () {
      Route::get('/', [App\Http\Controllers\Store\BoostingController::class , 'list'])->name('store.boosting.list');
      Route::get('/{slug}', [App\Http\Controllers\Store\BoostingController::class , 'index'])->name('store.boosting');
      Route::get('/thong-tin/{code}', [App\Http\Controllers\Store\BoostingController::class , 'show'])->name('store.boosting.show');
    }
    );

    // Games Routes
    Route::prefix('/games')->group(function () {
      Route::get('/spin-quest/{id?}', [App\Http\Controllers\Game\SpinQuestController::class , 'index'])->name('games.spin-quest');
    }
    );

    // User Panel
    Route::middleware(['auth', 'check.last.login'])->prefix('/account')->group(function () {
      Route::post('/heartbeat', [App\Http\Controllers\HomeController::class , 'heartbeat'])->name('account.heartbeat');
      Route::post('/offline', [App\Http\Controllers\HomeController::class , 'offline'])->name('account.offline');
      Route::prefix('/profile')->group(function () {
          Route::get('/', [App\Http\Controllers\Account\ProfileController::class , 'index'])->name('account.profile.index');
          Route::post('/update', [App\Http\Controllers\Account\ProfileController::class , 'update'])->name('account.profile.update');
          Route::post('/update-password', [App\Http\Controllers\Account\ProfileController::class , 'updatePassword'])->name('accounts.profile.update-password');

          Route::prefix('/banks')->group(function () {
              Route::post('/add', [App\Http\Controllers\Account\ProfileController::class , 'addBank'])->name('account.profile.banks.add');
              Route::post('/delete/{id}', [App\Http\Controllers\Account\ProfileController::class , 'deleteBank'])->name('account.profile.banks.delete');
              Route::post('/toggle/{id}', [App\Http\Controllers\Account\ProfileController::class , 'toggleBankStatus'])->name('account.profile.banks.toggle');
            }
            );

            Route::prefix('/security')->group(function () {
              Route::get('/2fa/setup', [App\Http\Controllers\Account\TwoFactorController::class , 'setup'])->name('account.security.2fa.setup');
              Route::post('/2fa/enable', [App\Http\Controllers\Account\TwoFactorController::class , 'enable'])->name('account.security.2fa.enable');
              Route::post('/2fa/disable', [App\Http\Controllers\Account\TwoFactorController::class , 'disable'])->name('account.security.2fa.disable');
              Route::post('/otp/send', [App\Http\Controllers\Account\TwoFactorController::class , 'sendOtp'])->name('account.security.otp.send');
            }
            );

            Route::get('/email/send-verification', [App\Http\Controllers\Account\EmailVerificationController::class , 'sendVerification'])->name('account.email.send-verification');
            Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Account\EmailVerificationController::class , 'verifyLink'])
              ->middleware(['signed', 'throttle:6,1'])
              ->name('verification.verify');
            Route::post('/email/verify', [App\Http\Controllers\Account\EmailVerificationController::class , 'verify'])->name('account.email.verify');

            // WebRTC IP Update
            Route::post('/update-webrtc', [App\Http\Controllers\Account\ProfileController::class , 'updateWebRtcIp'])->name('account.profile.update-webrtc');


            // Passkey (WebAuthn) Management
            Route::prefix('/passkey')->group(function () {
              Route::get('/list', [App\Http\Controllers\Account\PasskeyController::class , 'list'])->name('account.passkey.list');
              Route::post('/options', [App\Http\Controllers\Account\PasskeyController::class , 'options'])->name('account.passkey.options');
              Route::post('/register', [App\Http\Controllers\Account\PasskeyController::class , 'register'])->name('account.passkey.register');
              Route::post('/delete/{id}', [App\Http\Controllers\Account\PasskeyController::class , 'destroy'])->name('account.passkey.delete');
            }
            );

            // Linked Social Accounts
            Route::prefix('/linked-accounts')->group(function () {
              Route::get('/link/{provider}', [App\Http\Controllers\Account\LinkedAccountController::class, 'link'])->name('account.linked-accounts.link');
              Route::post('/unlink/{provider}', [App\Http\Controllers\Account\LinkedAccountController::class, 'unlink'])->name('account.linked-accounts.unlink');
            });
          }
          );
          Route::prefix('/deposits')->group(function () {
          Route::get('/', [App\Http\Controllers\Account\DepositController::class , 'index'])->name('account.deposits.index');
          Route::get('/card', [App\Http\Controllers\Account\DepositController::class , 'cards'])->name('account.deposits.cards');
          Route::get('/banking', [App\Http\Controllers\Account\DepositController::class , 'banking'])->name('account.deposits.banking');
          Route::get('/crypto', [App\Http\Controllers\Account\DepositController::class , 'crypto'])->name('account.deposits.crypto');
          Route::get('/paypal', [App\Http\Controllers\Account\DepositController::class , 'paypal'])->name('account.deposits.paypal');
          Route::get('/perfect-money', [App\Http\Controllers\Account\DepositController::class , 'perfectMoney'])->name('account.deposits.perfect-money');
        }
        );
        Route::prefix('/orders')->group(function () {
          Route::get('/items/{code?}', [App\Http\Controllers\Account\OrderController::class , 'items'])->name('account.orders.items');
          Route::post('/items/update-note', [App\Http\Controllers\Account\OrderController::class , 'updateNote'])->name('account.orders.items.update-note');
          Route::get('/accounts/{code?}', [App\Http\Controllers\Account\OrderController::class , 'accounts'])->name('account.orders.accounts');
          Route::post('/accounts/update-note', [App\Http\Controllers\Account\OrderController::class , 'updateAccountNote'])->name('account.orders.accounts.update-note');
          Route::post('/refund', [App\Http\Controllers\Account\OrderController::class , 'refund'])->name('account.orders.refund');
          Route::get('/boosting/{code?}', [App\Http\Controllers\Account\OrderController::class , 'boosting'])->name('account.orders.boosting');
          Route::post('/boosting/update-note', [App\Http\Controllers\Account\OrderController::class , 'updateBoostingNote'])->name('account.orders.boosting.update-note');
        }
        );
        Route::prefix('/withdraws-v2')->group(function () {
          Route::get('/', [App\Http\Controllers\Account\WithdrawV2Controller::class , 'index'])->name('account.withdraws-v2.index');
          Route::get('/forms', [App\Http\Controllers\Account\WithdrawV2Controller::class , 'forms'])->name('account.withdraws-v2.forms');
        }
        );
        Route::prefix('/tickets')->group(function () {
          Route::get('/', [App\Http\Controllers\Account\TicketController::class , 'index'])->name('account.tickets.index');
          Route::post('/store', [App\Http\Controllers\Account\TicketController::class , 'store'])->name('account.tickets.store');
          Route::get('/{code}', [App\Http\Controllers\Account\TicketController::class , 'show'])->name('account.tickets.show');
        }
        );
        Route::post('/upload/image', [App\Http\Controllers\Admin\UploadController::class , 'uploadImage'])->name('account.upload.image');

        // Transactions
        Route::get('/transactions', [App\Http\Controllers\Account\ProfileController::class , 'transactions'])->name('account.transactions.index');
      }
      );

      // Transactions
      // Transactions - MOVED to inside auth group
    
      Route::prefix('/news')->group(function () {
      Route::get('/', [App\Http\Controllers\ArticleController::class , 'index'])->name('articles.index');
      Route::get('/{slug}', [App\Http\Controllers\ArticleController::class , 'show'])->name('articles.show');
    }
    );

    // Admin
    Route::middleware(['auth', 'check.last.login', 'admin'])->prefix('/admin')->group(function () {
      Route::get('/', [App\Http\Controllers\Admin\DashboardController::class , 'index'])->name('admin.dashboard');

      // Dashboard APIs
      Route::get('/api/dashboard/revenue-charts', [App\Http\Controllers\Admin\DashboardController::class , 'apiRevenueCharts'])->name('admin.dashboard.api.revenue');
      Route::get('/api/dashboard/service-kpis', [App\Http\Controllers\Admin\DashboardController::class , 'apiServiceKpis'])->name('admin.dashboard.api.kpis');
      Route::get('/api/dashboard/service-kpis', [App\Http\Controllers\Admin\DashboardController::class , 'apiServiceKpis'])->name('admin.dashboard.api.kpis');
      Route::get('/update', [App\Http\Controllers\Admin\UpdateController::class , 'index'])->name('admin.update');

      // KiyoAI
      Route::prefix('/kiyoai')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\KiyoAIController::class , 'index'])->name('admin.kiyoai');
          Route::post('/chat', [App\Http\Controllers\Admin\KiyoAIController::class , 'chat'])->name('admin.kiyoai.chat');
        }
        );

        // Statistical Dashboard (Campaigns)
        Route::prefix('/statistical')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\StatisticalController::class , 'index'])->name('admin.statistical');
          Route::post('/store', [App\Http\Controllers\Admin\StatisticalController::class , 'store'])->name('admin.statistical.store');
          Route::get('/{id}', [App\Http\Controllers\Admin\StatisticalController::class , 'show'])->name('admin.statistical.show');
          Route::post('/update/{id}', [App\Http\Controllers\Admin\StatisticalController::class , 'update'])->name('admin.statistical.update');
          Route::post('/delete/{id}', [App\Http\Controllers\Admin\StatisticalController::class , 'destroy'])->name('admin.statistical.delete');
        }
        );

        Route::prefix('/affiliates')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\AffiliateController::class , 'index'])->name('admin.affiliates');
          Route::post('/update', [App\Http\Controllers\Admin\AffiliateController::class , 'update'])->name('admin.affiliates.update');
        }
        );
        Route::prefix('/withdraws')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\WithdrawController::class , 'index'])->name('admin.withdraws');
          Route::post('/update/{id}', [App\Http\Controllers\Admin\WithdrawController::class , 'update'])->name('admin.withdraws.update');
        }
        );
        Route::prefix('/pin-groups')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\PinController::class , 'index'])->name('admin.pin-groups');
          Route::post('/store', [App\Http\Controllers\Admin\PinController::class , 'store'])->name('admin.pin-groups.store');
          Route::post('/update', [App\Http\Controllers\Admin\PinController::class , 'update'])->name('admin.pin-groups.update');
          Route::post('/delete', [App\Http\Controllers\Admin\PinController::class , 'delete'])->name('admin.pin-groups.delete');
        }
        );
        Route::prefix('/settings')->group(function () {
          Route::prefix('/general')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Settings\GeneralController::class , 'index'])->name('admin.settings.general');
              Route::post('/', [App\Http\Controllers\Admin\Settings\GeneralController::class , 'update'])->name('admin.settings.general.update');
            }
            );
            Route::prefix('/apis')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Settings\ApiController::class , 'index'])->name('admin.settings.apis');
              Route::post('/', [App\Http\Controllers\Admin\Settings\ApiController::class , 'update'])->name('admin.settings.apis.update');
            }
            );
            Route::prefix('/notices')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Settings\NoticeController::class , 'index'])->name('admin.settings.notices');
              Route::post('/', [App\Http\Controllers\Admin\Settings\NoticeController::class , 'update'])->name('admin.settings.notices.update');
            }
            );
          }
          );

          Route::prefix('/language')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\LanguageController::class , 'index'])->name('admin.language');
          Route::post('/store', [App\Http\Controllers\Admin\LanguageController::class , 'store'])->name('admin.language.store');
          Route::post('/update/{id}', [App\Http\Controllers\Admin\LanguageController::class , 'update'])->name('admin.language.update');
          Route::post('/delete', [App\Http\Controllers\Admin\LanguageController::class , 'delete'])->name('admin.language.delete');
          Route::get('/translation/{id}', [App\Http\Controllers\Admin\LanguageController::class , 'translation'])->name('admin.language.translation');
          Route::post('/translation/update/{id}', [App\Http\Controllers\Admin\LanguageController::class , 'updateTranslation'])->name('admin.language.translation.update');
          Route::post('/translation/delete/{id}', [App\Http\Controllers\Admin\LanguageController::class , 'deleteTranslation'])->name('admin.language.translation.delete');
          Route::post('/translation/auto-translate/{id}', [App\Http\Controllers\Admin\LanguageController::class , 'autoTranslate'])->name('admin.language.translation.auto-translate');
          Route::post('/translation/bulk-auto-translate/{id}', [App\Http\Controllers\Admin\LanguageController::class , 'bulkAutoTranslate'])->name('admin.language.translation.bulk-auto-translate');
          Route::post('/translation/bulk-delete/{id}', [App\Http\Controllers\Admin\LanguageController::class , 'bulkDeleteTranslation'])->name('admin.language.translation.bulk-delete');
          Route::post('/translation/regenerate/{id}', [App\Http\Controllers\Admin\LanguageController::class , 'regenerateTranslations'])->name('admin.language.translation.regenerate');

          Route::post('/delete-theme-config', [App\Http\Controllers\Admin\LanguageController::class , 'deleteTheme'])->name('admin.language.theme.delete');
          Route::get('/{id}/{domain_id?}', [App\Http\Controllers\Admin\LanguageController::class , 'theme'])->name('admin.language.theme')->where('id', '[0-9]+');
          Route::post('/{id}/{domain_id?}', [App\Http\Controllers\Admin\LanguageController::class , 'updateTheme'])->name('admin.language.theme.update')->where('id', '[0-9]+');
        }
        );

        Route::prefix('/currency')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\CurrencyController::class , 'index'])->name('admin.currency.index');
          Route::post('/store', [App\Http\Controllers\Admin\CurrencyController::class , 'store'])->name('admin.currency.store');
          Route::post('/update', [App\Http\Controllers\Admin\CurrencyController::class , 'update'])->name('admin.currency.update');
          Route::post('/delete/{id}', [App\Http\Controllers\Admin\CurrencyController::class , 'destroy'])->name('admin.currency.delete');
          Route::get('/sync-all', [App\Http\Controllers\Admin\CurrencyController::class , 'syncAll'])->name('admin.currency.sync_all');
        }
        );

        Route::prefix('/domain')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\DomainController::class , 'index'])->name('admin.domain.index');
          Route::post('/store', [App\Http\Controllers\Admin\DomainController::class , 'store'])->name('admin.domain.store');
          Route::get('/edit/{id}', [App\Http\Controllers\Admin\DomainController::class , 'edit'])->name('admin.domain.edit');
          Route::post('/update/{id}', [App\Http\Controllers\Admin\DomainController::class , 'update'])->name('admin.domain.update');
          Route::post('/delete-config', [App\Http\Controllers\Admin\DomainController::class , 'deleteConfig'])->name('admin.domain.delete');

          Route::prefix('/redirects')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\DomainRedirectController::class , 'index'])->name('admin.domain.redirects.index');
              Route::get('/create', [App\Http\Controllers\Admin\DomainRedirectController::class , 'create'])->name('admin.domain.redirects.create');
              Route::post('/store', [App\Http\Controllers\Admin\DomainRedirectController::class , 'store'])->name('admin.domain.redirects.store');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\DomainRedirectController::class , 'edit'])->name('admin.domain.redirects.edit');
              Route::post('/update/{id}', [App\Http\Controllers\Admin\DomainRedirectController::class , 'update'])->name('admin.domain.redirects.update');
              Route::post('/delete/{id}', [App\Http\Controllers\Admin\DomainRedirectController::class , 'destroy'])->name('admin.domain.redirects.delete');
            }
            );

            Route::get('/{id}', [App\Http\Controllers\Admin\DomainController::class , 'show'])->name('admin.domain.show');
          }
          );

          Route::prefix('/users')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\UserController::class , 'index'])->name('admin.users');
          Route::get('/edit/{id}', [App\Http\Controllers\Admin\UserController::class , 'show'])->name('admin.users.edit');
          Route::post('/update/{id}', [App\Http\Controllers\Admin\UserController::class , 'update'])->name('admin.users.update');
        }
        );
        Route::prefix('/role')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\RoleController::class , 'index'])->name('admin.role');
          Route::post('/store', [App\Http\Controllers\Admin\RoleController::class , 'store'])->name('admin.role.store');
          Route::get('/{id}', [App\Http\Controllers\Admin\RoleController::class , 'edit'])->name('admin.role.edit');
          Route::post('/update/{id}', [App\Http\Controllers\Admin\RoleController::class , 'update'])->name('admin.role.update');
          Route::post('/delete', [App\Http\Controllers\Admin\RoleController::class , 'destroy'])->name('admin.role.delete');
        }
        );
        Route::prefix('/deposit')->group(function () {
          Route::prefix('/banks')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\BankController::class , 'deposit'])->name('admin.deposit.banks');
            Route::get('/api', [App\Http\Controllers\Api\Admin\Deposit\BankController::class , 'index'])->name('admin.deposit.bank.api');
            Route::get('/config', [App\Http\Controllers\Admin\BankController::class , 'config'])->name('admin.deposit.banks.config');
            Route::post('/config', [App\Http\Controllers\Admin\BankController::class , 'updateConfig'])->name('admin.deposit.banks.config.update');
            Route::post('/store', [App\Http\Controllers\Admin\BankController::class , 'store'])->name('admin.banks.store');
            Route::post('/update', [App\Http\Controllers\Admin\BankController::class , 'update'])->name('admin.banks.update');
            Route::post('/delete', [App\Http\Controllers\Admin\BankController::class , 'delete'])->name('admin.banks.delete');
            Route::post('/clear-all', [App\Http\Controllers\Api\Admin\Deposit\BankController::class, 'clearAll'])->name('admin.deposit.banks.clear-all');
          });

          Route::prefix('/cards')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CardController::class , 'index'])->name('admin.deposit.cards');
            Route::post('/delete', [App\Http\Controllers\Admin\CardController::class , 'delete'])->name('admin.deposit.cards.delete');
            Route::get('/config', [App\Http\Controllers\Admin\CardController::class , 'config'])->name('admin.deposit.cards.config');
            Route::post('/config', [App\Http\Controllers\Admin\CardController::class , 'updateConfig'])->name('admin.deposit.cards.config.update');
            Route::post('/clear-all', [App\Http\Controllers\Admin\CardController::class, 'clearAll'])->name('admin.deposit.cards.clear-all');
          });
        });

        Route::prefix('/deposit/usdt')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\UsdtController::class , 'index'])->name('admin.deposit.usdt');
          Route::get('/api', [App\Http\Controllers\Api\Admin\Deposit\UsdtController::class , 'index'])->name('admin.deposit.usdt.api');
          Route::get('/config', [App\Http\Controllers\Admin\UsdtController::class , 'config'])->name('admin.deposit.usdt.config');
          Route::get('/config-alias', [App\Http\Controllers\Admin\UsdtController::class , 'config'])->name('admin.usdt.config');
          Route::post('/config', [App\Http\Controllers\Admin\UsdtController::class , 'updateConfig'])->name('admin.deposit.usdt.config.update');
          Route::post('/clear-all', [App\Http\Controllers\Api\Admin\Deposit\UsdtController::class, 'clearAll'])->name('admin.deposit.usdt.clear-all');
        }
        );

        Route::prefix('/deposit/paypal')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\PaypalController::class , 'index'])->name('admin.deposit.paypal');
          Route::get('/api', [App\Http\Controllers\Api\Admin\Deposit\PaypalController::class , 'index'])->name('admin.deposit.paypal.api');
          Route::get('/config', [App\Http\Controllers\Admin\PaypalController::class , 'config'])->name('admin.deposit.paypal.config');
          Route::get('/config-alias', [App\Http\Controllers\Admin\PaypalController::class , 'config'])->name('admin.paypal.config');
          Route::post('/config', [App\Http\Controllers\Admin\PaypalController::class , 'updateConfig'])->name('admin.deposit.paypal.config.update');
          Route::post('/clear-all', [App\Http\Controllers\Api\Admin\Deposit\PaypalController::class, 'clearAll'])->name('admin.deposit.paypal.clear-all');
        }
        );

        Route::prefix('/deposit/perfect_money')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\PerfectMoneyController::class , 'index'])->name('admin.deposit.perfect_money');
          Route::get('/api', [App\Http\Controllers\Api\Admin\Deposit\PerfectMoneyController::class , 'index'])->name('admin.deposit.perfect_money.api');
          Route::get('/config', [App\Http\Controllers\Admin\PerfectMoneyController::class , 'config'])->name('admin.deposit.perfect_money.config');
          Route::get('/config-alias', [App\Http\Controllers\Admin\PerfectMoneyController::class , 'config'])->name('admin.perfect_money.config');
          Route::post('/config', [App\Http\Controllers\Admin\PerfectMoneyController::class , 'updateConfig'])->name('admin.deposit.perfect_money.config.update');
          Route::post('/clear-all', [App\Http\Controllers\Api\Admin\Deposit\PerfectMoneyController::class, 'clearAll'])->name('admin.deposit.perfect_money.clear-all');
        }
        );

        Route::prefix('/invoices')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\InvoiceController::class , 'index'])->name('admin.invoices');
          Route::get('/api', [App\Http\Controllers\Api\Admin\InvoiceController::class , 'index'])->name('admin.invoices.api');
          Route::post('/update', [App\Http\Controllers\Admin\InvoiceController::class , 'update'])->name('admin.invoices.update');
          Route::post('/delete', [App\Http\Controllers\Admin\InvoiceController::class , 'delete'])->name('admin.invoices.delete');
          Route::post('/clear-all', [App\Http\Controllers\Api\Admin\InvoiceController::class, 'clearAll'])->name('admin.invoices.clear-all');
        }
        );
        Route::prefix('/transactions')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\TransactionController::class , 'index'])->name('admin.transactions');
          Route::get('/api', [App\Http\Controllers\Api\Admin\TransactionController::class , 'index'])->name('admin.transactions.api');
          Route::post('/clear-all', [App\Http\Controllers\Api\Admin\TransactionController::class, 'clearAll'])->name('admin.transactions.clear-all');
        }
        );
        Route::prefix('/tickets')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\TicketController::class , 'index'])->name('admin.tickets');
          Route::post('/store', [App\Http\Controllers\Admin\TicketController::class , 'store'])->name('admin.tickets.store');
          Route::get('/list', [App\Http\Controllers\Admin\TicketController::class , 'getList'])->name('admin.tickets.list');
          Route::get('/content/{id}', [App\Http\Controllers\Admin\TicketController::class , 'getTicketContent'])->name('admin.tickets.content');
          Route::get('/{id}', [App\Http\Controllers\Admin\TicketController::class , 'show'])->name('admin.tickets.show');
          Route::get('/{id}/messages', [App\Http\Controllers\Admin\TicketController::class , 'getMessages'])->name('admin.tickets.messages');
          Route::post('/{id}/reply', [App\Http\Controllers\Admin\TicketController::class , 'reply'])->name('admin.tickets.reply');
          Route::post('/{id}/note', [App\Http\Controllers\Admin\TicketController::class , 'updateNote'])->name('admin.tickets.update-note');
          Route::post('/{id}/status', [App\Http\Controllers\Admin\TicketController::class , 'status'])->name('admin.tickets.status');
          Route::post('/delete', [App\Http\Controllers\Admin\TicketController::class , 'delete'])->name('admin.tickets.delete');
        }
        );
        Route::prefix('/quick-replies')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\QuickReplyController::class , 'index'])->name('admin.quick-replies.index');
          Route::post('/store', [App\Http\Controllers\Admin\QuickReplyController::class , 'store'])->name('admin.quick-replies.store');
          Route::post('/update/{id}', [App\Http\Controllers\Admin\QuickReplyController::class , 'update'])->name('admin.quick-replies.update');
          Route::post('/delete', [App\Http\Controllers\Admin\QuickReplyController::class , 'delete'])->name('admin.quick-replies.delete');
        }
        );
        Route::post('/upload/image', [App\Http\Controllers\Admin\UploadController::class , 'uploadImage'])->name('admin.upload.image');
        Route::prefix('/posts')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\PostController::class , 'index'])->name('admin.posts');
          Route::get('/create', [App\Http\Controllers\Admin\PostController::class , 'create'])->name('admin.posts.create');
          Route::post('/store', [App\Http\Controllers\Admin\PostController::class , 'store'])->name('admin.posts.store');
          Route::get('/edit/{id}', [App\Http\Controllers\Admin\PostController::class , 'show'])->name('admin.posts.show');
          Route::post('/update/{id}', [App\Http\Controllers\Admin\PostController::class , 'update'])->name('admin.posts.update');
          Route::post('/delete', [App\Http\Controllers\Admin\PostController::class , 'delete'])->name('admin.posts.delete');
          Route::post('/update-priority', [App\Http\Controllers\Admin\PostController::class , 'updatePriority'])->name('admin.posts.update-priority');
        }
        );
        Route::prefix('/transactions')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\TransactionController::class , 'index'])->name('admin.transactions');
        }
        );
        Route::prefix('/template')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\EmailTemplateController::class , 'index'])->name('admin.template.index');
          Route::post('/config', [App\Http\Controllers\Admin\EmailTemplateController::class , 'updateConfig'])->name('admin.template.config');
          Route::get('/{id}/edit', [App\Http\Controllers\Admin\EmailTemplateController::class , 'edit'])->name('admin.template.edit');
          Route::post('/{id}/update', [App\Http\Controllers\Admin\EmailTemplateController::class , 'update'])->name('admin.template.update');
        }
        );
        Route::get('/histories', [App\Http\Controllers\Admin\HistoryController::class , 'index'])->name('admin.histories');
        Route::post('/histories/clear-all', [App\Http\Controllers\Api\Admin\HistoryController::class, 'clearAll'])->name('admin.histories.clear-all');
        Route::prefix('/categories')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\CategoryController::class , 'index'])->name('admin.categories');
          Route::post('/store', [App\Http\Controllers\Admin\CategoryController::class , 'store'])->name('admin.categories.store');
          Route::post('/update', [App\Http\Controllers\Admin\CategoryController::class , 'update'])->name('admin.categories.update');
          Route::post('/delete', [App\Http\Controllers\Admin\CategoryController::class , 'delete'])->name('admin.categories.delete');
          Route::post('/update-priority', [App\Http\Controllers\Admin\CategoryController::class , 'updatePriority'])->name('admin.categories.update-priority');
        }
        );
        Route::prefix('/accounts')->group(function () {
          Route::get('/', function() { return redirect()->route('admin.accounts.items'); })->name('admin.accounts');
          Route::prefix('/groups')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Account\GroupController::class , 'index'])->name('admin.accounts.groups');
              Route::get('/create', [App\Http\Controllers\Admin\Account\GroupController::class , 'create'])->name('admin.accounts.groups.create');
              Route::post('/store', [App\Http\Controllers\Admin\Account\GroupController::class , 'store'])->name('admin.accounts.groups.store');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\Account\GroupController::class , 'edit'])->name('admin.accounts.groups.edit');
              Route::post('/update', [App\Http\Controllers\Admin\Account\GroupController::class , 'update'])->name('admin.accounts.groups.update');
              Route::post('/delete', [App\Http\Controllers\Admin\Account\GroupController::class , 'delete'])->name('admin.accounts.groups.delete');
              Route::post('/update-priority', [App\Http\Controllers\Admin\Account\GroupController::class , 'updatePriority'])->name('admin.accounts.groups.update-priority');
            }
            );
            Route::prefix('/items')->group(function () {
              Route::get('/{id?}', [App\Http\Controllers\Admin\Account\ItemController::class , 'index'])->name('admin.accounts.items');
              Route::post('/store', [App\Http\Controllers\Admin\Account\ItemController::class , 'store'])->name('admin.accounts.items.store');
              Route::post('/copy-list', [App\Http\Controllers\Admin\Account\ItemController::class , 'copyList'])->name('admin.accounts.items.copy-list');
              Route::post('/update-list', [App\Http\Controllers\Admin\Account\ItemController::class , 'updateList'])->name('admin.accounts.items.update-list');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\Account\ItemController::class , 'show'])->name('admin.accounts.items.show');
              Route::post('/update', [App\Http\Controllers\Admin\Account\ItemController::class , 'update'])->name('admin.accounts.items.update');
              Route::post('/delete', [App\Http\Controllers\Admin\Account\ItemController::class , 'delete'])->name('admin.accounts.items.delete');
              Route::post('/delete-list', [App\Http\Controllers\Admin\Account\ItemController::class , 'deleteList'])->name('admin.accounts.items.delete-list');
              Route::post('/update-priority', [App\Http\Controllers\Admin\Account\ItemController::class , 'updatePriority'])->name('admin.accounts.items.update-priority');
              Route::post('/refund', [App\Http\Controllers\Admin\Account\ItemController::class , 'refund'])->name('admin.accounts.items.refund');
              Route::post('/clear-all', [App\Http\Controllers\Admin\Account\ItemController::class , 'clearAll'])->name('admin.accounts.items.clear-all');
            }
            );
          }
          );
          Route::prefix('/service')->group(function () {
          Route::get('/', [\App\Http\Controllers\Admin\Service\ServiceController::class , 'index'])->name('admin.service.index');
          Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Service\ServiceController::class , 'edit'])->name('admin.service.edit');
          Route::post('/store', [\App\Http\Controllers\Admin\Service\ServiceController::class , 'store'])->name('admin.service.store');
          Route::post('/update', [\App\Http\Controllers\Admin\Service\ServiceController::class , 'update'])->name('admin.service.update');
          Route::post('/update-prize', [\App\Http\Controllers\Admin\Service\ServiceController::class , 'updatePrize'])->name('admin.service.update-prize');
          Route::post('/update-priority', [\App\Http\Controllers\Admin\Service\ServiceController::class , 'updatePriority'])->name('admin.service.update-priority');
          Route::post('/delete', [\App\Http\Controllers\Admin\Service\ServiceController::class , 'delete'])->name('admin.service.delete');
        }
        );
        Route::prefix('/accountsv2')->group(function () {
          Route::get('/', function() { return redirect()->route('admin.accountsv2.items'); })->name('admin.accountsv2');
          Route::prefix('/groups')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\AccountV2\GroupController::class , 'index'])->name('admin.accountsv2.groups');
              Route::get('/create', [App\Http\Controllers\Admin\AccountV2\GroupController::class , 'create'])->name('admin.accountsv2.groups.create');
              Route::post('/store', [App\Http\Controllers\Admin\AccountV2\GroupController::class , 'store'])->name('admin.accountsv2.groups.store');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\AccountV2\GroupController::class , 'edit'])->name('admin.accountsv2.groups.edit');
              Route::post('/update', [App\Http\Controllers\Admin\AccountV2\GroupController::class , 'update'])->name('admin.accountsv2.groups.update');
              Route::post('/delete', [App\Http\Controllers\Admin\AccountV2\GroupController::class , 'delete'])->name('admin.accountsv2.groups.delete');
              Route::post('/update-priority', [App\Http\Controllers\Admin\AccountV2\GroupController::class , 'updatePriority'])->name('admin.accountsv2.groups.update-priority');
            }
            );
            Route::prefix('/items')->group(function () {
              Route::get('/{id?}', [App\Http\Controllers\Admin\AccountV2\ItemController::class , 'index'])->name('admin.accountsv2.items');
              Route::post('/store', [App\Http\Controllers\Admin\AccountV2\ItemController::class , 'store'])->name('admin.accountsv2.items.store');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\AccountV2\ItemController::class , 'show'])->name('admin.accountsv2.items.show');
              Route::post('/update', [App\Http\Controllers\Admin\AccountV2\ItemController::class , 'update'])->name('admin.accountsv2.items.update');
              Route::post('/delete', [App\Http\Controllers\Admin\AccountV2\ItemController::class , 'delete'])->name('admin.accountsv2.items.delete');
              Route::post('/delete-list', [App\Http\Controllers\Admin\AccountV2\ItemController::class , 'deleteList'])->name('admin.accountsv2.items.delete-list');
              Route::post('/update-list', [App\Http\Controllers\Admin\AccountV2\ItemController::class , 'updateList'])->name('admin.accountsv2.items.update-list');
              Route::post('/update-priority', [App\Http\Controllers\Admin\AccountV2\ItemController::class , 'updatePriority'])->name('admin.accountsv2.items.update-priority');
              Route::post('/get-api-products', [App\Http\Controllers\Admin\AccountV2\ItemController::class , 'getApiProducts'])->name('admin.accountsv2.items.get-api-products');
            }
            );
            Route::prefix('/orders')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\AccountV2\OrderController::class , 'index'])->name('admin.accountsv2.orders');
              Route::post('/refund', [App\Http\Controllers\Admin\AccountV2\OrderController::class , 'refund'])->name('admin.accountsv2.orders.refund');
              Route::post('/update', [App\Http\Controllers\Admin\AccountV2\OrderController::class , 'update'])->name('admin.accountsv2.orders.update');
              Route::post('/delete', [App\Http\Controllers\Admin\AccountV2\OrderController::class , 'delete'])->name('admin.accountsv2.orders.delete');
              Route::post('/clear-all', [App\Http\Controllers\Admin\AccountV2\OrderController::class, 'clearAll'])->name('admin.accountsv2.orders.clear-all');
            }
            );
            Route::prefix('/resources')->group(function () {
              Route::get('/{id?}', [App\Http\Controllers\Admin\AccountV2\ResourceController::class , 'index'])->name('admin.accountsv2.resources');
              Route::post('/store', [App\Http\Controllers\Admin\AccountV2\ResourceController::class , 'store'])->name('admin.accountsv2.resources.store');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\AccountV2\ResourceController::class , 'show'])->name('admin.accountsv2.resources.show');
              Route::post('/update', [App\Http\Controllers\Admin\AccountV2\ResourceController::class , 'update'])->name('admin.accountsv2.resources.update');
              Route::post('/export', [App\Http\Controllers\Admin\AccountV2\ResourceController::class , 'export'])->name('admin.accountsv2.resources.export');
              Route::post('/delete', [App\Http\Controllers\Admin\AccountV2\ResourceController::class , 'delete'])->name('admin.accountsv2.resources.delete');
            }
            );
            Route::prefix('/api')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\AccountV2\ApiController::class , 'index'])->name('admin.accountsv2.api');
              Route::post('/store', [App\Http\Controllers\Admin\AccountV2\ApiController::class , 'store'])->name('admin.accountsv2.api.store');
              Route::post('/update', [App\Http\Controllers\Admin\AccountV2\ApiController::class , 'update'])->name('admin.accountsv2.api.update');
              Route::post('/delete', [App\Http\Controllers\Admin\AccountV2\ApiController::class , 'delete'])->name('admin.accountsv2.api.delete');
            }
            );
          }
          );
          Route::prefix('/items')->group(function () {
          Route::get('/', function() { return redirect()->route('admin.items.orders'); })->name('admin.items');
          Route::prefix('/ingame')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Item\InGameController::class , 'index'])->name('admin.items.ingame');
              Route::post('/store', [App\Http\Controllers\Admin\Item\InGameController::class , 'store'])->name('admin.items.ingame.store');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\Item\InGameController::class , 'show'])->name('admin.items.ingame.show');
              Route::post('/update', [App\Http\Controllers\Admin\Item\InGameController::class , 'update'])->name('admin.items.ingame.update');
              Route::post('/delete', [App\Http\Controllers\Admin\Item\InGameController::class , 'delete'])->name('admin.items.ingame.delete');
            }
            );
            Route::prefix('/groups')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Item\GroupController::class , 'index'])->name('admin.items.groups');
              Route::post('/store', [App\Http\Controllers\Admin\Item\GroupController::class , 'store'])->name('admin.items.groups.store');
              Route::post('/update', [App\Http\Controllers\Admin\Item\GroupController::class , 'update'])->name('admin.items.groups.update');
              Route::post('/delete', [App\Http\Controllers\Admin\Item\GroupController::class , 'delete'])->name('admin.items.groups.delete');
              Route::post('/update-priority', [App\Http\Controllers\Admin\Item\GroupController::class , 'updatePriority'])->name('admin.items.groups.update-priority');
            }
            );
            Route::prefix('/packages')->group(function () {
              Route::get('/{id}', [App\Http\Controllers\Admin\Item\PackageController::class , 'index'])->name('admin.items.packages');
              Route::post('/store', [App\Http\Controllers\Admin\Item\PackageController::class , 'store'])->name('admin.items.packages.store');
              Route::post('/update', [App\Http\Controllers\Admin\Item\PackageController::class , 'update'])->name('admin.items.packages.update');
              Route::post('/delete', [App\Http\Controllers\Admin\Item\PackageController::class , 'delete'])->name('admin.items.packages.delete');
              Route::post('/update-priority', [App\Http\Controllers\Admin\Item\PackageController::class , 'updatePriority'])->name('admin.items.packages.update-priority');
            }
            );
            Route::prefix('/data')->group(function () {
              Route::get('/{id?}', [App\Http\Controllers\Admin\Item\DataController::class , 'index'])->name('admin.items.data');
              Route::post('/store', [App\Http\Controllers\Admin\Item\DataController::class , 'store'])->name('admin.items.data.store');
              Route::post('/save-default', [App\Http\Controllers\Admin\Item\DataController::class , 'saveDefault'])->name('admin.items.data.save-default');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\Item\DataController::class , 'show'])->name('admin.items.data.show');
              Route::post('/update', [App\Http\Controllers\Admin\Item\DataController::class , 'update'])->name('admin.items.data.update');
              Route::post('/update-list', [App\Http\Controllers\Admin\Item\DataController::class , 'updateList'])->name('admin.items.data.update-list');
              Route::post('/delete', [App\Http\Controllers\Admin\Item\DataController::class , 'delete'])->name('admin.items.data.delete');
              Route::post('/update-priority', [App\Http\Controllers\Admin\Item\DataController::class , 'updatePriority'])->name('admin.items.data.update-priority');
            }
            );
            Route::prefix('/orders')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Item\OrderController::class , 'index'])->name('admin.items.orders');
              Route::post('/update', [App\Http\Controllers\Admin\Item\OrderController::class , 'update'])->name('admin.items.orders.update');
              Route::post('/refund', [App\Http\Controllers\Admin\Item\OrderController::class , 'refund'])->name('admin.items.orders.refund');
              Route::post('/delete', [App\Http\Controllers\Admin\Item\OrderController::class , 'delete'])->name('admin.items.orders.delete');
              Route::post('/clear-all', [App\Http\Controllers\Admin\Item\OrderController::class , 'clearAll'])->name('admin.items.orders.clear-all');
            }
            );
          }
          );
          Route::prefix('/boosting')->group(function () {
          Route::get('/', function() { return redirect()->route('admin.boosting.orders'); })->name('admin.boosting');
          Route::prefix('/groups')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Boosting\GroupController::class , 'index'])->name('admin.boosting.groups');
              Route::post('/store', [App\Http\Controllers\Admin\Boosting\GroupController::class , 'store'])->name('admin.boosting.groups.store');
              Route::post('/update', [App\Http\Controllers\Admin\Boosting\GroupController::class , 'update'])->name('admin.boosting.groups.update');
              Route::post('/delete', [App\Http\Controllers\Admin\Boosting\GroupController::class , 'delete'])->name('admin.boosting.groups.delete');
              Route::post('/update-priority', [App\Http\Controllers\Admin\Boosting\GroupController::class , 'updatePriority'])->name('admin.boosting.groups.update-priority');
            }
            );
            Route::prefix('/packages')->group(function () {
              Route::get('/{id?}', [App\Http\Controllers\Admin\Boosting\PackageController::class , 'index'])->name('admin.boosting.packages');
              Route::post('/store', [App\Http\Controllers\Admin\Boosting\PackageController::class , 'store'])->name('admin.boosting.packages.store');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\Boosting\PackageController::class , 'show'])->name('admin.boosting.packages.show');
              Route::post('/update', [App\Http\Controllers\Admin\Boosting\PackageController::class , 'update'])->name('admin.boosting.packages.update');
              Route::post('/delete', [App\Http\Controllers\Admin\Boosting\PackageController::class , 'delete'])->name('admin.boosting.packages.delete');
              Route::post('/update-priority', [App\Http\Controllers\Admin\Boosting\PackageController::class , 'updatePriority'])->name('admin.boosting.packages.update-priority');
            }
            );
            Route::prefix('/products')->group(function () {
              Route::get('/{id?}', [App\Http\Controllers\Admin\Boosting\ProductController::class , 'index'])->name('admin.boosting.products');
              Route::post('/save-default', [App\Http\Controllers\Admin\Boosting\ProductController::class , 'saveDefault'])->name('admin.boosting.products.save-default');
              Route::post('/store', [App\Http\Controllers\Admin\Boosting\ProductController::class , 'store'])->name('admin.boosting.products.store');
              Route::get('/edit/{id}', [App\Http\Controllers\Admin\Boosting\ProductController::class , 'show'])->name('admin.boosting.products.show');
              Route::post('/update', [App\Http\Controllers\Admin\Boosting\ProductController::class , 'update'])->name('admin.boosting.products.update');
              Route::post('/delete', [App\Http\Controllers\Admin\Boosting\ProductController::class , 'delete'])->name('admin.boosting.products.delete');
              Route::post('/update-priority', [App\Http\Controllers\Admin\Boosting\ProductController::class , 'updatePriority'])->name('admin.boosting.products.update-priority');
            }
            );
            Route::prefix('/orders')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Boosting\OrderController::class , 'index'])->name('admin.boosting.orders');
              Route::post('/update', [App\Http\Controllers\Admin\Boosting\OrderController::class , 'update'])->name('admin.boosting.orders.update');
              Route::post('/refund', [App\Http\Controllers\Admin\Boosting\OrderController::class , 'refund'])->name('admin.boosting.orders.refund');
              Route::post('/delete', [App\Http\Controllers\Admin\Boosting\OrderController::class , 'delete'])->name('admin.boosting.orders.delete');
              Route::post('/clear-all', [App\Http\Controllers\Admin\Boosting\OrderController::class , 'clearAll'])->name('admin.boosting.orders.clear-all');
            }
            );
          }
          );
          Route::prefix('/inventories')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\Inventory\InventoryController::class , 'index'])->name('admin.inventories');
          Route::prefix('/vars')->group(function () {
              Route::get('/', [App\Http\Controllers\Admin\Inventory\VarController::class , 'index'])->name('admin.inventories.vars');
              Route::post('/store', [App\Http\Controllers\Admin\Inventory\VarController::class , 'store'])->name('admin.inventories.vars.store');
              Route::post('/update', [App\Http\Controllers\Admin\Inventory\VarController::class , 'update'])->name('admin.inventories.vars.update');
              Route::post('/delete', [App\Http\Controllers\Admin\Inventory\VarController::class , 'delete'])->name('admin.inventories.vars.delete');
            }
            );
          }
          );
          Route::prefix('/staff/withdraws')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\Staff\WithdrawController::class , 'index'])->name('admin.staff.withdraws');
          Route::post('/update', [App\Http\Controllers\Admin\Staff\WithdrawController::class , 'update'])->name('admin.staff.withdraws.update');
        }
        );

        // Security
        Route::prefix('/security')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\SecurityController::class , 'index'])->name('admin.security');
          Route::post('/', [App\Http\Controllers\Admin\SecurityController::class , 'store'])->name('admin.security.update');

          Route::get('/block', [App\Http\Controllers\Admin\SecurityController::class , 'indexBan'])->name('admin.security.block');
          Route::post('/block/add', [App\Http\Controllers\Admin\SecurityController::class , 'storeBan'])->name('admin.security.block.store');
          Route::post('/block/delete', [App\Http\Controllers\Admin\SecurityController::class , 'deleteBan'])->name('admin.security.block.delete');
        }
        );

        // Coupons
        Route::prefix('/coupons')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\CouponController::class , 'index'])->name('admin.coupons');
          Route::get('/{id}', [App\Http\Controllers\Admin\CouponController::class , 'show'])->name('admin.coupons.show');
          Route::post('/', [App\Http\Controllers\Admin\CouponController::class , 'store'])->name('admin.coupons.store');
          Route::post('/delete', [App\Http\Controllers\Admin\CouponController::class , 'destroy'])->name('admin.coupons.delete');
        }
        );

        // Automations
        Route::prefix('/automations')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\AutomationController::class , 'index'])->name('admin.automations');
          Route::post('/store', [App\Http\Controllers\Admin\AutomationController::class , 'store'])->name('admin.automations.store');
          Route::post('/update', [App\Http\Controllers\Admin\AutomationController::class , 'update'])->name('admin.automations.update');
          Route::post('/delete', [App\Http\Controllers\Admin\AutomationController::class , 'destroy'])->name('admin.automations.delete');
          Route::post('/status', [App\Http\Controllers\Admin\AutomationController::class , 'status'])->name('admin.automations.status');
        }
        );

        // Promotions
        Route::prefix('/promotions')->group(function () {
          Route::get('/', [App\Http\Controllers\Admin\PromotionController::class , 'index'])->name('admin.promotions');
          Route::post('/store', [App\Http\Controllers\Admin\PromotionController::class , 'store'])->name('admin.promotions.store');
          Route::post('/update', [App\Http\Controllers\Admin\PromotionController::class , 'update'])->name('admin.promotions.update');
          Route::post('/delete', [App\Http\Controllers\Admin\PromotionController::class , 'destroy'])->name('admin.promotions.delete');
        }
        );
      }
      );

      // Staff
      Route::middleware(['auth', 'check.last.login', 'staff'])->prefix('/staff')->group(function () {
      Route::get('/', [App\Http\Controllers\Staff\DashboardController::class , 'index'])->name('staff.dashboard');
      Route::prefix('/products')->group(function () {
          Route::prefix('/accounts')->group(function () {
              Route::get('/groups', [App\Http\Controllers\Staff\Product\AccountController::class , 'index'])->name('staff.products.accounts.groups');
              Route::get('/items/{id}', [App\Http\Controllers\Staff\Product\AccountController::class , 'items'])->name('staff.products.accounts.items');
              Route::post('/items/store', [App\Http\Controllers\Staff\Product\AccountController::class , 'store'])->name('staff.products.accounts.items.store');
              Route::get('/items/edit/{id}', [App\Http\Controllers\Staff\Product\AccountController::class , 'show'])->name('staff.products.accounts.items.show');
              Route::post('/items/update', [App\Http\Controllers\Staff\Product\AccountController::class , 'update'])->name('staff.products.accounts.items.update');
              Route::post('/items/delete', [App\Http\Controllers\Staff\Product\AccountController::class , 'delete'])->name('staff.products.accounts.items.delete');
              Route::post('/items/delete-list', [App\Http\Controllers\Staff\Product\AccountController::class , 'deleteList'])->name('staff.products.accounts.items.delete-list');
            }
            );
          }
          );
          Route::prefix('/orders')->group(function () {
          Route::prefix('/items')->group(function () {
              Route::get('/', [App\Http\Controllers\Staff\Order\ItemController::class , 'index'])->name('staff.orders.items.index');
              Route::post('/claim', [App\Http\Controllers\Staff\Order\ItemController::class , 'claim'])->name('staff.orders.items.claim');
              Route::post('/update', [App\Http\Controllers\Staff\Order\ItemController::class , 'update'])->name('staff.orders.items.update');
            }
            );
            Route::prefix('/boostings')->group(function () {
              Route::get('/', [App\Http\Controllers\Staff\Order\BoostingController::class , 'index'])->name('staff.orders.boostings.index');
              Route::post('/claim', [App\Http\Controllers\Staff\Order\BoostingController::class , 'claim'])->name('staff.orders.boostings.claim');
              Route::post('/update', [App\Http\Controllers\Staff\Order\BoostingController::class , 'update'])->name('staff.orders.boostings.update');
            }
            );
            Route::prefix('/accounts')->group(function () {
              Route::get('/', [App\Http\Controllers\Staff\Order\AccountController::class , 'index'])->name('staff.orders.accounts.index');
              Route::post('/update', [App\Http\Controllers\Staff\Order\AccountController::class , 'update'])->name('staff.orders.accounts.update');
            }
            );
          }
          );
          Route::prefix('/withdraws')->group(function () {
          Route::post('/store', [App\Http\Controllers\Staff\WithdrawController::class , 'store'])->name('staff.withdraws.store');
        }
        );
      }
      );

      // User Notifications
      Route::prefix('/account/notifications')->middleware(['auth'])->group(function () {
      Route::get('/', [App\Http\Controllers\Account\NotificationController::class , 'index'])->name('account.notifications.index');
      Route::get('/{code}', [App\Http\Controllers\Account\NotificationController::class , 'show'])->name('account.notifications.show');
      Route::post('/read-all', [App\Http\Controllers\Account\NotificationController::class , 'readAll'])->name('account.notifications.read_all');
    }
    );

    // Admin Notifications
    Route::prefix('/admin/notifications')->middleware(['auth', 'admin'])->group(function () {
      Route::get('/', [App\Http\Controllers\Admin\NotificationController::class , 'index'])->name('admin.notifications.index');
      Route::post('/send', [App\Http\Controllers\Admin\NotificationController::class , 'store'])->name('admin.notifications.store');
      Route::get('/edit/{id}', [App\Http\Controllers\Admin\NotificationController::class , 'edit'])->name('admin.notifications.edit');
      Route::post('/update', [App\Http\Controllers\Admin\NotificationController::class , 'update'])->name('admin.notifications.update');
      Route::post('/delete', [App\Http\Controllers\Admin\NotificationController::class , 'destroy'])->name('admin.notifications.destroy');
    }
    );

    // System Logs
    Route::middleware(['auth', 'admin'])->get('/admin/logs', [App\Http\Controllers\Admin\SystemLogController::class , 'index'])->name('admin.logs');
    Route::middleware(['auth', 'admin'])->post('/admin/logs/delete', [App\Http\Controllers\Admin\SystemLogController::class , 'delete'])->name('admin.logs.delete');
    Route::middleware(['auth', 'admin'])->post('/admin/logs/clear-all', [App\Http\Controllers\Admin\SystemLogController::class , 'clearAll'])->name('admin.logs.clear-all');
    // PARTNER ROUTES
    Route::prefix('partner')->group(function () {
      Route::middleware(['auth', 'partner'])->group(function () {
          Route::get('/', [App\Http\Controllers\Partner\DashboardController::class , 'index'])->name('partner.dashboard');

          // Settings
          Route::get('/settings', [App\Http\Controllers\Partner\SettingsController::class , 'index'])->name('partner.settings.index');
          Route::post('/settings/update', [App\Http\Controllers\Partner\SettingsController::class , 'update'])->name('partner.settings.update');

          // Notices
          Route::get('/notices', [App\Http\Controllers\Partner\SettingsController::class , 'notices'])->name('partner.settings.notices');
          Route::post('/notices/update', [App\Http\Controllers\Partner\SettingsController::class , 'updateNotices'])->name('partner.settings.notices.update');

          // API for Charts/Stats
          Route::get('/api/charts', [App\Http\Controllers\Partner\DashboardController::class , 'apiRevenueCharts'])->name('partner.api.charts');
          Route::get('/api/kpis', [App\Http\Controllers\Partner\DashboardController::class , 'apiServiceKpis'])->name('partner.api.kpis');
        }
        );
      }
      );
    };

// Apply Localization to all routes using the dual-group method (Explicitly defined with and without prefix)
Route::middleware('localization')->group(function () use ($registerRoutes) {
  // 1. Routes with MANDATORY locale prefix
  Route::group(['prefix' => '{locale}', 'where' => ['locale' => '[a-zA-Z]{2}']], $registerRoutes);

  // 2. Routes WITHOUT locale prefix (Default)
  Route::group([], $registerRoutes);

  // 3. Catch-all for Short Campaign/Referral Links (Must be LAST)
  // E.g. /123123 -> Redirects with ?utm_source=123123
  Route::get('/{ref}', [App\Http\Controllers\HomeController::class , 'ref'])
    ->where('ref', '[a-zA-Z0-9_-]+')
    ->name('short.ref');
});

Route::any('{any}', function () {
    $shouldRedirect = false;
    try {
        if (class_exists('Helper')) {
            $shouldRedirect = (\Helper::getConfig('redirect_404_to_home') == 1);
        }
        if (!$shouldRedirect && function_exists('setting')) {
            $shouldRedirect = (setting('redirect_404_to_home') == 1);
        }
    } catch (\Exception $e) {}

    if ($shouldRedirect) {
        \Illuminate\Support\Facades\Log::info("404 Route Catch-all Redirecting to home: " . request()->fullUrl());
        return redirect()->to(url('/'));
    }
    abort(404);
})->where('any', '.*');
