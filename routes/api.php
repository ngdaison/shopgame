<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider and all of them will | be assigned to the "api" middleware group. Make something great! | */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

// Deposit Routes
Route::middleware('auth:sanctum')->prefix('/deposit')->group(function () {
  Route::post('/paypal-confirm', [App\Http\Controllers\Api\Deposit\PaypalController::class , 'index']);
});



// Admin Routes (Supports both Session and Sanctum Token)
Route::middleware(['web', 'auth', 'admin'])->prefix('/admin')->group(function () {
  // User Routes
  Route::prefix('/users')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\Admin\UserController::class , 'index'])->name('admin.users.api');
      Route::post('/delete/{id}', [App\Http\Controllers\Api\Admin\UserController::class , 'delete'])->name('admin.users.delete.api');
    }
    );

    // Transaction Routes
    Route::prefix('/transactions')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\Admin\TransactionController::class , 'index'])->name('admin.transactions.api');
      Route::post('/delete', [App\Http\Controllers\Api\Admin\TransactionController::class , 'delete'])->name('admin.transactions.delete.api');
    }
    );

    // Invoice Routes
    Route::prefix('/invoices')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\Admin\InvoiceController::class , 'index'])->name('admin.invoices.api');
    }
    );
    Route::prefix('/histories')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\Admin\HistoryController::class , 'index'])->name('admin.histories.api');
      Route::post('/delete', [App\Http\Controllers\Api\Admin\HistoryController::class , 'delete'])->name('admin.histories.delete.api');
    }
    );
    Route::prefix('/deposit')->group(function () {
      Route::prefix('/banks')->group(function () {
        Route::post('/delete', [App\Http\Controllers\Api\Admin\Deposit\BankController::class , 'delete'])->name('admin.deposit.banks.delete.api');
      });
    });
    // Tools Routes
    Route::middleware('throttle:200,1')->prefix('/tools')->group(function () {
      Route::post('/upload', [App\Http\Controllers\Api\Tools\UploadController::class , 'index'])->name('admin.tools.upload');
    }
    );

    // Data Routes
    Route::prefix('/data')->group(function () {
      Route::get('/accounts-v1', [App\Http\Controllers\Api\Admin\DataController::class , 'accountsV1'])->name('admin.accounts.items.api');
      Route::get('/accounts-v2', [App\Http\Controllers\Api\Admin\DataController::class , 'accountsV2'])->name('admin.accountsv2.items.api');
    }
    );  });

Route::prefix('/staff')->middleware(['auth:sanctum', 'staff'])->group(function () {
  // Tools Routes
  Route::prefix('/tools')->group(function () {
      Route::post('/upload', [App\Http\Controllers\Api\Tools\UploadController::class , 'index'])->name('staff.tools.upload');
    }
    );  });

// Coupons
Route::post('/check-coupon', [App\Http\Controllers\Api\CouponController::class , 'check']);

// Games Routes
Route::middleware('auth:sanctum')->prefix('/games')->group(function () {
  Route::post('/spin-quest/turn', [App\Http\Controllers\Api\Game\SpinQuestController::class , 'turn']);
  Route::post('/spin-quest/turn-test', [App\Http\Controllers\Api\Game\SpinQuestController::class , 'turnTest']);


});

// Accounts Routes
Route::middleware(['auth:sanctum'])->prefix('/accounts')->group(function () {
  // Profiles Routes
  Route::get('/histories', [App\Http\Controllers\Api\Account\HistoryController::class , 'index']);
  Route::get('/transactions', [App\Http\Controllers\Api\Account\TransactionController::class , 'index']);
  // Invoices Routes
  Route::get('/invoices', [App\Http\Controllers\Api\Account\InvoiceController::class , 'index']);
  Route::get('/invoices/{id}', [App\Http\Controllers\Api\Account\InvoiceController::class , 'show']);
  Route::post('/invoices', [App\Http\Controllers\Api\Account\InvoiceController::class , 'store']);
  // Deposits Routes
  Route::get('/card-list', [App\Http\Controllers\Api\Account\DepositController::class , 'cardList']);
  Route::post('/send-card', [App\Http\Controllers\Api\Account\DepositController::class , 'sendCard']);

});
// Users Routes
Route::middleware(['auth:sanctum'])->name('api.users.')->prefix('/users')->group(function () {
  // Affiliate Routes
  Route::name('affiliates.')->prefix('/affiliates')->group(function () {
      Route::post('/withdraw', [App\Http\Controllers\Api\User\AffiliateController::class , 'withdraw'])->name('withdraw');
      Route::post('/update-code', [App\Http\Controllers\Api\User\AffiliateController::class , 'updateCode'])->name('update-code');
      Route::get('/history', [App\Http\Controllers\Api\User\AffiliateController::class , 'history'])->name('history');
      Route::get('/withdraw-history', [App\Http\Controllers\Api\User\AffiliateController::class , 'withdrawHistory'])->name('withdraw-history');
    }
    );
    // Withdraws Routes
    Route::prefix('/withdraws')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\User\WithdrawV2Controller::class , 'histories']);
      Route::post('/store', [App\Http\Controllers\Api\User\WithdrawV2Controller::class , 'store']);
    }
    );

    Route::prefix('/gift-rewards')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\User\GiftRewardController::class , 'index']);
      Route::post('/claim', [App\Http\Controllers\Api\User\GiftRewardController::class , 'claim']);
    }
    );
    // Invoices Routes
    Route::get('/invoices', [App\Http\Controllers\Api\User\InvoiceController::class , 'index']);
    Route::get('/invoices/{id}', [App\Http\Controllers\Api\User\InvoiceController::class , 'show']);

    // Bank Accounts Routes
    Route::get('/banks', [App\Http\Controllers\Api\User\BankingController::class , 'index']);
    // Tickets Routes
    Route::get('/tickets', [App\Http\Controllers\Api\User\TicketController::class , 'index']);
    Route::post('/tickets', [App\Http\Controllers\Api\User\TicketController::class , 'store']);
    Route::get('/tickets/{id}', [App\Http\Controllers\Api\User\TicketController::class , 'show']);
    Route::post('/tickets/{id}/reply', [App\Http\Controllers\Api\User\TicketController::class , 'reply']);  });

// Static Routes
Route::prefix('/static')->group(function () {
  Route::get('/skins/{id}', [App\Http\Controllers\Api\Store\AccountController::class , 'skins']);
  Route::get('/champions/{id}', [App\Http\Controllers\Api\Store\AccountController::class , 'champions']);
});

// Categories
Route::prefix('/categories')->group(function () {
  Route::get('/', [App\Http\Controllers\Api\CategoryController::class , 'index']);
});
Route::prefix('/groups')->group(function () {
  Route::get('/', [App\Http\Controllers\Api\GroupController::class , 'index']);
});

// Store Routes
Route::prefix('/stores')->group(function () {
  Route::prefix('/accounts')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\Store\AccountController::class , 'index']);
      Route::get('/{code}', [App\Http\Controllers\Api\Store\AccountController::class , 'show']);
      Route::post('/{code}/buy', [App\Http\Controllers\Api\Store\AccountController::class , 'buy'])->middleware('auth:sanctum');
    }
    );
    Route::prefix('/accounts-v2')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\Store\AccountV2Controller::class , 'index']);
      Route::get('/{code}', [App\Http\Controllers\Api\Store\AccountV2Controller::class , 'show']);
      Route::post('/{code}/buy', [App\Http\Controllers\Api\Store\AccountV2Controller::class , 'buy'])->middleware('auth:sanctum');
    }
    );
    Route::prefix('/items')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\Store\ItemController::class , 'index']);
      Route::get('/{code}', [App\Http\Controllers\Api\Store\ItemController::class , 'show']);
      Route::post('/{slug}/buy', [App\Http\Controllers\Api\Store\ItemController::class , 'buy'])->middleware('auth:sanctum');
    }
    );
    Route::prefix('/list-ingame')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\Store\ListIngameController::class , 'index']);
    }
    );
    Route::prefix('/boosting-game')->group(function () {
      Route::get('/', [App\Http\Controllers\Api\Store\BoostingGameController::class , 'index']);
      Route::get('/products', [App\Http\Controllers\Api\Store\BoostingGameController::class , 'products']);
      Route::post('/buy-multiple', [App\Http\Controllers\Api\Store\BoostingGameController::class , 'buyMultiple'])->middleware('auth:sanctum');
      Route::get('/{slug}', [App\Http\Controllers\Api\Store\BoostingGameController::class , 'show']);
      Route::post('/{slug}/buy', [App\Http\Controllers\Api\Store\BoostingGameController::class , 'buy'])->middleware('auth:sanctum');
    }
    );  });

Route::get('/orders', [App\Http\Controllers\Api\OrderController::class , 'index']);

Route::prefix('/tools')->group(function () {
  Route::post('/get-current-otp', function (Request $request) {
      $payload = $request->validate([
        'secret' => 'required|string',
      ]);

      if (!is_valid_2fa_secret($payload['secret'])) {
        return response()->json([
        'status' => 400,
        'message' => 'Mã secret two factor không hợp lệ',
        ], 400);
      }

      return response()->json([
      'data' => generate_code_2fa($payload['secret']),
      'status' => 200,
      'message' => 'Lấy mã OTP thành công',
      ]);
    }
    );  });
