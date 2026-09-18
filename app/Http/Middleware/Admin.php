<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    \Illuminate\Support\Facades\Log::info("Admin MIDI ENTER: " . $request->fullUrl());
    // 1. Basic Auth & Status Check
    if (!auth()->check()) {
      return redirect()->route('login');
    }

    if ($request->user()->status !== 'active') {
      return abort(401);
    }

    $user = $request->user();

    // 2. Check if user has ANY admin permission to even see the admin area
    if (!$user->isAdmin()) {
        return abort(403, 'Bạn không có quyền truy cập trang quản trị.');
    }

    // 3. Granular Permission Mapping
    $routeName = $request->route()->getName();
    $permissionMap = [
        'admin.dashboard' => 'admin_dashboard_view',
        'admin.dashboard.api.revenue' => 'admin_dashboard_view',
        'admin.dashboard.api.kpis' => 'admin_dashboard_view',
        'admin.update' => 'admin_dashboard_view',

        'admin.kiyoai' => 'admin_kiyoai_use',
        'admin.kiyoai.chat' => 'admin_kiyoai_use',

        'admin.statistical' => 'admin_statistical_view',
        'admin.statistical.store' => 'admin_statistical_view',
        'admin.statistical.show' => 'admin_statistical_view',
        'admin.statistical.update' => 'admin_statistical_view',
        'admin.statistical.delete' => 'admin_statistical_view',

        'admin.users' => 'admin_users_view',
        'admin.users.index' => 'admin_users_view',
        'admin.users.api' => 'admin_users_view',
        'admin.users.edit' => 'admin_users_edit',
        'admin.users.show' => 'admin_users_edit',
        'admin.users.update' => 'admin_users_edit',
        'admin.users.delete' => 'admin_users_delete',
        'admin.users.delete.api' => 'admin_users_delete',
        'admin.users.update_balance' => 'admin_users_update_balance',
        
        'admin.role' => 'admin_role_view',
        'admin.role.store' => 'admin_role_add',
        'admin.role.edit' => 'admin_role_edit',
        'admin.role.update' => 'admin_role_edit',
        'admin.role.delete' => 'admin_role_delete',

        'admin.settings.general' => 'admin_settings_general_view',
        'admin.settings.general.update' => 'admin_settings_general_system_edit',
        'admin.settings.apis' => 'admin_settings_apis_view',
        'admin.settings.apis.update' => 'admin_settings_apis_update',
        'admin.settings.notices' => 'admin_settings_notices_view',
        'admin.settings.notices.update' => 'admin_settings_notices_update',

        'admin.security' => 'admin_security_settings_view',
        'admin.security.update' => 'admin_security_settings_update',
        'admin.security.block' => 'admin_security_block_view',
        'admin.security.block.store' => 'admin_security_block_add',
        'admin.security.block.delete' => 'admin_security_block_delete',

        'admin.automations' => 'admin_automations_view',
        'admin.automations.store' => 'admin_automations_view',
        'admin.automations.update' => 'admin_automations_view',
        'admin.automations.delete' => 'admin_automations_delete',
        'admin.automations.status' => 'admin_automations_view',

        'admin.domain.index' => 'admin_domain_view',
        'admin.domain.store' => 'admin_domain_add',
        'admin.domain.edit' => 'admin_domain_edit',
        'admin.domain.update' => 'admin_domain_edit',
        'admin.domain.delete' => 'admin_domain_delete',

        'admin.currency.index' => 'admin_currency_view',
        'admin.currency.store' => 'admin_currency_add',
        'admin.currency.update' => 'admin_currency_edit',
        'admin.currency.delete' => 'admin_currency_delete',
        'admin.currency.sync_all' => 'admin_currency_sync_all',

        'admin.language' => 'admin_language_view',
        'admin.language.store' => 'admin_language_add',
        'admin.language.update' => 'admin_language_edit',
        'admin.language.delete' => 'admin_language_delete',

        'admin.pin-groups' => 'admin_pin_groups_view',
        'admin.pin-groups.store' => 'admin_pin_groups_add',
        'admin.pin-groups.update' => 'admin_pin_groups_edit',
        'admin.pin-groups.delete' => 'admin_pin_groups_delete',

        'admin.transactions' => 'admin_transactions_view',
        'admin.transactions.api' => 'admin_transactions_view',
        'admin.transactions.delete.api' => 'admin_transactions_view',
        'admin.histories' => 'admin_histories_view',
        'admin.histories.index' => 'admin_histories_view',
        'admin.histories.api' => 'admin_histories_view',
        'admin.histories.delete.api' => 'admin_histories_view',
        'admin.logs' => 'admin_logs_view',
        'admin.logs.delete.api' => 'admin_logs_view',

        'admin.deposit.banks' => 'admin_deposit_banks_view',
        'admin.deposit.banks.delete.api' => 'admin_deposit_banks_view',
        'admin.deposit.bank.api' => 'admin_deposit_banks_view',
        'admin.deposit.banks.api' => 'admin_deposit_banks_view',
        'admin.deposit.banks.clear-all' => 'admin_deposit_banks_view',
        'admin.deposit.banks.config' => 'admin_deposit_banks_edit',
        'admin.deposit.banks.config.update' => 'admin_deposit_banks_edit',
        'admin.banks.store' => 'admin_deposit_banks_edit',
        'admin.banks.update' => 'admin_deposit_banks_edit',
        'admin.banks.delete' => 'admin_deposit_banks_edit',
        
        'admin.deposit.cards' => 'admin_deposit_cards_view',
        'admin.deposit.cards.delete' => 'admin_deposit_cards_view',
        'admin.deposit.cards.config' => 'admin_deposit_cards_edit',
        'admin.deposit.cards.config.update' => 'admin_deposit_cards_edit',

        'admin.deposit.usdt' => 'admin_deposit_usdt_view',
        'admin.deposit.usdt.api' => 'admin_deposit_usdt_view',
        'admin.deposit.usdt.delete.api' => 'admin_deposit_usdt_view',
        'admin.deposit.usdt.config' => 'admin_deposit_usdt_edit',
        'admin.usdt.config' => 'admin_deposit_usdt_edit',
        'admin.deposit.usdt.config.update' => 'admin_deposit_usdt_edit',

        'admin.deposit.paypal' => 'admin_deposit_paypal_view',
        'admin.deposit.paypal.api' => 'admin_deposit_paypal_view',
        'admin.deposit.paypal.delete.api' => 'admin_deposit_paypal_view',
        'admin.deposit.paypal.config' => 'admin_deposit_paypal_edit',
        'admin.paypal.config' => 'admin_deposit_paypal_edit',
        'admin.deposit.paypal.config.update' => 'admin_deposit_paypal_edit',

        'admin.deposit.perfect_money' => 'admin_deposit_perfect_money_view',
        'admin.deposit.perfect_money.api' => 'admin_deposit_perfect_money_view',
        'admin.deposit.perfect_money.delete.api' => 'admin_deposit_perfect_money_view',
        'admin.deposit.perfect_money.config' => 'admin_deposit_perfect_money_edit',
        'admin.perfect_money.config' => 'admin_deposit_perfect_money_edit',
        'admin.deposit.perfect_money.config.update' => 'admin_deposit_perfect_money_edit',

        'admin.invoices' => 'admin_invoices_view',
        'admin.invoices.api' => 'admin_invoices_view',
        'admin.invoices.delete' => 'admin_invoices_delete',

        'admin.tickets' => 'admin_tickets_view',
        'admin.tickets.reply' => 'admin_tickets_reply',
        'admin.tickets.status' => 'admin_tickets_close',
        'admin.tickets.delete' => 'admin_tickets_delete',

        'admin.notifications.index' => 'admin_notifications_view',
        'admin.notifications.store' => 'admin_notifications_send',
        'admin.notifications.destroy' => 'admin_notifications_delete',

        'admin.affiliates' => 'admin_affiliates_view',
        'admin.withdraws' => 'admin_withdraws_view',
        'admin.withdraws.update' => 'admin_withdraws_approve',
        'admin.staff.withdraws' => 'admin_staff_withdraws_view',
        'admin.staff.withdraws.update' => 'admin_staff_withdraws_approve',

        'admin.posts' => 'admin_posts_view',
        'admin.posts.create' => 'admin_posts_add',
        'admin.posts.store' => 'admin_posts_add',
        'admin.posts.show' => 'admin_posts_edit',
        'admin.posts.update' => 'admin_posts_edit',
        'admin.posts.delete' => 'admin_posts_delete',

        // New mappings
        'admin.template.index' => 'admin_template_view',
        'admin.template.edit' => 'admin_template_edit',
        'admin.template.update' => 'admin_template_edit',
        
        'admin.inventories' => 'admin_inventories_view',
        'admin.inventories.vars' => 'admin_inventories_vars_view',
        
        'admin.coupons' => 'admin_coupons_view',
        'admin.coupons.show' => 'admin_coupons_view',
        'admin.coupons.store' => 'admin_coupons_add',
        'admin.coupons.delete' => 'admin_coupons_delete',
        
        'admin.promotions' => 'admin_promotions_view',
        'admin.promotions.store' => 'admin_promotions_add',
        'admin.promotions.update' => 'admin_promotions_edit',
        'admin.promotions.delete' => 'admin_promotions_delete',

        // Products
        'admin.categories' => 'admin_categories_view',
        'admin.service.index' => 'admin_service_view',
        'admin.service.store' => 'admin_service_edit',
        'admin.service.edit' => 'admin_service_edit',
        'admin.service.update' => 'admin_service_edit',
        'admin.service.delete' => 'admin_service_delete',
        'admin.service.update-prize' => 'admin_service_edit',
        'admin.service.update-priority' => 'admin_service_edit',
        'admin.boosting.groups' => 'admin_boosting_groups_view',
        'admin.boosting.orders' => 'admin_boosting_orders_view',
        'admin.items.groups' => 'admin_items_groups_view',
        'admin.items.orders' => 'admin_items_orders_view',
        'admin.accounts.groups' => 'admin_accounts_groups_view',
        'admin.accounts.items' => 'admin_accounts_items_view',
        'admin.accountsv2.groups' => 'admin_accountsv2_groups_view',
        'admin.accountsv2.items' => 'admin_accountsv2_items_view',
        'admin.accountsv2.orders' => 'admin_accountsv2_orders_view',
        'admin.accountsv2.orders.delete' => 'admin_accountsv2_orders_view',
        'admin.accountsv2.orders.clear-all' => 'admin_accountsv2_orders_view',
        'admin.accountsv2.api' => 'admin_accountsv2_api_view',
        // API Data Routes
        'admin.accounts.items.api' => 'admin_accounts_items_view',
        'admin.accountsv2.items.api' => 'admin_accountsv2_items_view',
        'admin.items.orders.clear-all' => 'admin_items_orders_view',
        'admin.boosting.orders.clear-all' => 'admin_boosting_orders_view',
        'admin.transactions.clear-all' => 'admin_transactions_view',
        'admin.histories.clear-all' => 'admin_histories_view',
        'admin.invoices.clear-all' => 'admin_invoices_view',
        'admin.accounts' => 'admin_accounts_items_view',
        'admin.accountsv2' => 'admin_accountsv2_items_view',
        'admin.boosting' => 'admin_boosting_orders_view',
        'admin.items' => 'admin_items_orders_view',
        'admin.tools.upload' => 'admin_accounts_items_view',
    ];

    $matchedPermission = null;
    $routeName = trim((string)$routeName);

    if (isset($permissionMap[$routeName])) {
        $matchedPermission = $permissionMap[$routeName];
    } else {
        foreach ($permissionMap as $pattern => $perm) {
            if (str_starts_with($routeName, $pattern . '.')) {
                $matchedPermission = $perm;
                break;
            }
        }
    }

    if ($matchedPermission) {
        // Special Dynamic Permissions for Shared APIs
        if ($routeName === 'admin.transactions.api' || $routeName === 'admin.histories.api') {
            $type = $request->input('type');
            if ($type === 'perfect_money' && $user->hasPermission('admin_deposit_perfect_money_view')) {
                return $next($request);
            }
            if ($type === 'usdt' && $user->hasPermission('admin_deposit_usdt_view')) {
                return $next($request);
            }
            if ($type === 'paypal' && $user->hasPermission('admin_deposit_paypal_view')) {
                return $next($request);
            }
            if (($type === 'deposit-card' || $type === 'cards') && $user->hasPermission('admin_deposit_cards_view')) {
                return $next($request);
            }
            if (($type === 'deposit-bank' || $type === 'banks') && $user->hasPermission('admin_deposit_banks_view')) {
                return $next($request);
            }
        }

        $hasPerm = $user->hasPermission($matchedPermission);
        \Illuminate\Support\Facades\Log::info("Admin MIDI: User {$user->id}, Route [{$routeName}], Perm [{$matchedPermission}], Result: " . ($hasPerm ? 'TRUE' : 'FALSE'));
        if (!$hasPerm) {
            if ($request->wantsJson()) {
                return response()->json(['status' => false, 'message' => 'Bạn không có quyền ' . $matchedPermission], 403);
            }
            return abort(403, 'Bạn không có quyền ' . $matchedPermission);
        }
    } else {
        \Illuminate\Support\Facades\Log::warning("Admin MIDI: UNMAPPED ROUTE - User {$user->id}, Route [{$routeName}], Path [{$request->path()}]");
        
        // Strict Mode: Unmapped routes are DENIED!
        $safeRoutes = ['admin.tools', 'admin.upload', 'admin.kiyoai', 'admin.dashboard', 'admin.profile.update', 'admin.profile.update-password'];
        $isSafe = false;
        foreach ($safeRoutes as $safe) {
            if (str_starts_with((string)$routeName, $safe)) {
                $isSafe = true; break;
            }
        }
        
        if (!$isSafe) {
            // Path-based fallback for tools
            if (str_contains($request->path(), '/tools/upload')) {
                $isSafe = true;
            }
        }

        if (!$isSafe) {
            if ($request->wantsJson()) {
                return response()->json(['status' => false, 'message' => 'Tính năng chưa được phân quyền hoặc bạn không có quyền truy cập.'], 403);
            }
            return abort(403, 'Tính năng chưa được phân quyền hoặc bạn không có quyền truy cập.');
        }
    }

    // disable method POST on Demo
    if (env('APP_DEMO', false) && $request->method() === 'POST') {
      return redirect()->back()->with('error', 'Thao tác này bị vô hiệu hoá trên trang DEMO!');
    }

    // // check license
    // if ($request->method() === 'POST' && !$request->routeIs('admin.tools.upload')) {


    //   // only groups settings
    //   if ($request->routeIs('admin.settings.*')) {


    //     $check = checkLicenseKey(env('CLIENT_SECRET_KEY'));

    //     if ($check['status'] !== true) {
    //       if ($request->wantsJson()) {
    //         return response()->json([
    //           'status'  => 400,
    //           'error'   => [
    //             'message' => '[POST][License Error]: ' . $check['msg'],
    //           ],
    //           'message' => '[POST][License Error]: ' . $check['msg'],
    //         ], 400);
    //       } else {
    //         return redirect()->back()->with('error', '[POST][License Error]: ' . $check['msg']);
    //       }
    //     }

    //   }

    // }

    return $next($request);
  }
}
