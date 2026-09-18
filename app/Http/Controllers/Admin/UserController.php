<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaTransaction;
use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function index()
  {
    $total_users = User::count();
    $total_balance = User::sum('balance');
    $total_deposit = User::sum('total_deposit');
    $total_admins = User::where('role', 'like', '%admin%')->orWhere('role', 'like', '%Admin%')->count();
    $total_banned = User::where('status', 'locked')->count();

    return view('admin.users.index', compact('total_users', 'total_balance', 'total_deposit', 'total_admins', 'total_banned'));
  }

  public function show($id)
  {
    $user = User::findOrFail($id);

    // If for some reason the IP is still N/A, try falling back to other sources
    if ($user->ip_address === 'N/A' || empty($user->ip_address)) {
      $latestHistory = \App\Models\History::where('user_id', $user->id)
        ->orderBy('id', 'desc')
        ->first();

      $user->ip_address = $latestHistory ? $latestHistory->ip_address : ($user->last_login_ip ?? 'N/A');
    }

    // Auto-generate or fix 2FA Secret if missing/incompatible
    if (empty($user->google2fa_secret) || strlen($user->google2fa_secret) > 32) {
      $google2fa = new \PragmaRX\Google2FA\Google2FA();
      // Using 16 characters for maximum compatibility (RFC 4226)
      $user->google2fa_secret = $google2fa->generateSecretKey(16);
      $user->save();
    }

    $collaborator_categories = \App\Models\Category::orderBy('priority', 'desc')->get();
    $account_groups = \App\Models\Group::where('status', true)->get();

    // Fetch domains for Partner assignment
    $available_domains = \App\Models\DomainSetting::pluck('domain')->toArray();
    $roles = \App\Models\Role::all();

    return view('admin.users.show', compact('user', 'collaborator_categories', 'account_groups', 'available_domains', 'roles'));
  }


  public function update(Request $request, $id)
  {
    $action = $request->input('action', null);
    $currentUser = auth()->user();
    $targetUser = User::findOrFail($id);

    // Check if current user has higher or equal level than target user
    if ($currentUser->getRoleLevel() < $targetUser->getRoleLevel() && $currentUser->id !== $targetUser->id) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn không có quyền chỉnh sửa người dùng có cấp bậc cao hơn.'
        ], 403);
    }

    if ($action === 'update-info') {
      $payload = $request->validate([
        'role' => 'sometimes|required', // Custom validation logic handled or leave loose for now as we check values manually if needed, or stick to string for simple single select if user reverts. Actually, let's allow string or array.
        // 'role' => 'sometimes|required|in:admin,member,collaborator,partner', -> This fails for array.
        // We will validate manually or assume valid if array keys are correct in UI.
        'email' => 'sometimes|required|email|unique:users,email,' . $id,
        'full_name' => 'sometimes|required|string|max:255',
        'status' => 'sometimes|required|in:active,locked',
        'password' => 'nullable|string|min:6',
        'colla_type' => 'nullable|array',
        'colla_type.*' => 'in:account,boosting,items',
        'colla_percent' => 'nullable|numeric|min:0|max:100',
        'colla_category_ids' => 'nullable|array',
        'staff_group_ids' => 'nullable|array',
        'phone' => 'nullable|string|max:20',
        'gender' => 'nullable|in:male,female,lesbian,bisexual,gay,transgender',
        'domain' => 'nullable|string',
      ]);

      $user = User::findOrFail($id);

      if (isset($payload['password'])) {
        $payload['password'] = bcrypt($payload['password']);
      }
      else {
        unset($payload['password']);
      }

      // Auto Block when status is changed to locked
      if (isset($payload['status']) && $payload['status'] === 'locked' && $user->status !== 'locked') {
        \App\Helpers\SecurityGuard::banUser($user->username, 'ban', 'Hệ thống: Khóa tài khoản từ trang quản lý');
      }

      // Safe check for roles (can be array or string depending on view)
      $roles = $payload['role'] ?? null;
      $isCollaborator = false;
      if ($roles) {
        $isCollaborator = is_array($roles) ? (in_array('collaborator', $roles) || in_array('Cộng tác viên', $roles) || in_array('Cộng Tác Viên', $roles)) : ($roles === 'collaborator' || strtolower($roles) === 'cộng tác viên');
      }

      if ($isCollaborator) {
        // Handle colla_type as array
        if (isset($payload['colla_type'])) {
          $payload['colla_type'] = (array)$payload['colla_type'];
        }

        // Sync Account Groups (reusing staff_group_ids field)
        $group_ids = array_map('intval', $payload['staff_group_ids'] ?? []);
        $getGroups = Group::where('status', true)->whereIn('id', $group_ids)->get();
        $payload['staff_group_ids'] = $getGroups->pluck('id')->toArray();
      }
      else {
      // Not a collaborator or role changed, maybe clear?
      // For now, only clear if not admin/member? Or just ignore.
      // User request focuses on when "Cộng tác viên" is selected.
      }

      // Handle Role as Array or String for storage
      if (isset($payload['role'])) {
          $roleNames = is_array($payload['role']) ? $payload['role'] : explode(',', $payload['role']);
          $roleNames = array_map('trim', $roleNames);
          
          $maxAssignedLevel = \App\Models\Role::whereIn('name', $roleNames)->max('level') ?? 0;
          if ($maxAssignedLevel > $currentUser->getRoleLevel()) {
              return response()->json([
                  'status' => false,
                  'message' => 'Bạn không thể gán vai trò có cấp bậc cao hơn cấp bậc của chính mình.'
              ], 403);
          }

          if (is_array($payload['role'])) {
              $payload['role'] = implode(',', $payload['role']);
          }
      }

      $user->update($payload);

      Helper::addHistory('Cập nhật thông tin của ' . $user->username . ' [' . $action . ']', $payload);

      return response()->json([
        'status' => true,
        'message' => 'Cập nhật thông tin của ' . $user->username . ' thành công'
      ]);
    }
    elseif ($action === 'plus-money') {
      $payload = $request->validate([
        'amount' => 'required|numeric|min:0',
        'reason' => 'nullable|string|max:255',
        'wallet_type' => 'nullable|in:balance,colla_balance,balance_1,balance_2',
      ]);

      $user = User::findOrFail($id);
      $walletType = $payload['wallet_type'] ?? 'balance';
      $before = $user->{ $walletType};

      $user->{ $walletType} += $payload['amount'];

      if ($walletType === 'balance') {
        $user->total_deposit += $payload['amount'];
        $ref = $user->referrer;
        if ($ref !== null) {
          $affiliate = $ref->affiliate;
          if ($affiliate !== null) {
            $affiliate->increment('total_deposit', $payload['amount']);
          }
        }
      }
      $user->save();

      if ($walletType === 'colla_balance') {
        CollaTransaction::create([
          'user_id' => $user->id,
          'username' => $user->username,
          'type' => 'admin_adjust',
          'amount' => $payload['amount'],
          'status' => 'Completed',
          'balance_before' => $before,
          'balance_after' => $user->colla_balance,
          'description' => 'Admin cộng tiền: ' . ($payload['reason'] ?? ''),
          'payment_info' => [],
        ]);
      }
      else {
        Transaction::create([
          'code' => 'SP3-' . Helper::randomString(7, true),
          'amount' => $payload['amount'],
          'balance_after' => $user->{ $walletType},
          'balance_before' => $before,
          'type' => $walletType === 'balance' ? 'deposit-bank' : 'admin-change',
          'extras' => [
            'reason' => ($payload['reason'] ?? ''),
            'change' => 'admin-change',
            'wallet_type' => $walletType,
          ],
          'status' => 'paid',
          'content' => '#' . auth()->id() . ': ' . ($payload['reason'] ?? ''),
          'user_id' => $user->id,
          'username' => $user->username,
        ]);
      }

      Helper::addHistory('Cộng tiền thành công cho ' . $user->username . ' [' . $action . '] - ' . $walletType, $payload);

      return response()->json([
        'status' => true,
        'message' => 'Cộng tiền thành công cho ' . $user->username . ', số dư ' . $walletType . ' cuối : ' . Helper::formatCurrency($user->{ $walletType})
      ]);
    }
    elseif ($action === 'sub-money') {
      $payload = $request->validate([
        'amount' => 'required|numeric|min:0',
        'reason' => 'nullable|string|max:255',
        'wallet_type' => 'nullable|in:balance,colla_balance,balance_1,balance_2',
      ]);

      $user = User::findOrFail($id);
      $walletType = $payload['wallet_type'] ?? 'balance';
      $before = $user->{ $walletType};

      $user->{ $walletType} -= $payload['amount'];
      $user->save();

      if ($walletType === 'colla_balance') {
        CollaTransaction::create([
          'user_id' => $user->id,
          'username' => $user->username,
          'type' => 'admin_adjust',
          'amount' => $payload['amount'],
          'status' => 'Completed',
          'balance_before' => $before,
          'balance_after' => $user->colla_balance,
          'description' => 'Admin trừ tiền: ' . ($payload['reason'] ?? ''),
          'payment_info' => [],
        ]);
      }
      else {
        Transaction::create([
          'code' => 'SP3-' . Helper::randomString(10, true),
          'amount' => $payload['amount'],
          'balance_after' => $user->{ $walletType},
          'balance_before' => $before,
          'type' => 'admin-change',
          'extras' => [
            'reason' => ($payload['reason'] ?? ''),
            'wallet_type' => $walletType,
          ],
          'status' => 'paid',
          'content' => '#' . auth()->id() . ': ' . ($payload['reason'] ?? ''),
          'user_id' => $user->id,
          'username' => $user->username,
        ]);
      }

      Helper::addHistory('Trừ tiền tài khoản ' . $user->username . ' thành công [' . $action . '] - ' . $walletType, $payload);

      return response()->json([
        'status' => true,
        'message' => 'Trừ tiền tài khoản ' . $user->username . ', số dư ' . $walletType . ' cuối : ' . Helper::formatCurrency($user->{ $walletType})
      ]);
    }
    elseif ($action === 'plus-money-colla') {
      $payload = $request->validate([
        'amount' => 'required|numeric|min:0',
        'reason' => 'nullable|string|max:255',
      ]);

      $user = User::findOrFail($id);
      $before = $user->colla_balance;
      $user->colla_balance += $payload['amount'];
      $user->save();

      CollaTransaction::create([
        'user_id' => $user->id,
        'username' => $user->username,
        'type' => 'admin_adjust',
        'amount' => $payload['amount'],
        'status' => 'Completed',
        'balance_before' => $before,
        'balance_after' => $user->colla_balance,
        'description' => 'Admin cộng tiền: ' . ($payload['reason'] ?? ''),
        'payment_info' => [],
      ]);

      Helper::addHistory('Cộng tiền CTV thành công cho ' . $user->username, $payload);

      return response()->json([
        'status' => true,
        'message' => 'Cộng tiền CTV thành công cho ' . $user->username . ', số dư CTV cuối : ' . Helper::formatCurrency($user->colla_balance)
      ]);
    }
    elseif ($action === 'sub-money-colla') {
      $payload = $request->validate([
        'amount' => 'required|numeric|min:0',
        'reason' => 'nullable|string|max:255',
      ]);

      $user = User::findOrFail($id);
      $before = $user->colla_balance;
      $user->colla_balance -= $payload['amount'];
      $user->save();

      CollaTransaction::create([
        'user_id' => $user->id,
        'username' => $user->username,
        'type' => 'admin_adjust',
        'amount' => $payload['amount'],
        'status' => 'Completed',
        'balance_before' => $before,
        'balance_after' => $user->colla_balance,
        'description' => 'Admin trừ tiền: ' . ($payload['reason'] ?? ''),
        'payment_info' => [],
      ]);

      Helper::addHistory('Trừ tiền CTV thành công cho ' . $user->username, $payload);

      return response()->json([
        'status' => true,
        'message' => 'Trừ tiền CTV thành công cho ' . $user->username . ', số dư CTV cuối : ' . Helper::formatCurrency($user->colla_balance)
      ]);
    }
    else if ($action === 'plus-commision') {
      $payload = $request->validate([
        'amount' => 'required|numeric|min:0',
        'reason' => 'nullable|string|max:255',
      ]);

      $user = User::findOrFail($id);

      $user->colla_balance += $payload['amount'];
      $user->save();

      CollaTransaction::create([
        'type' => 'boosting',
        'user_id' => $user->id,
        'username' => $user->username,
        'amount' => $payload['amount'],
        'status' => 'Completed',
        'reference' => null,
        'description' => '#' . auth()->id() . ': ' . ($payload['reason'] ?? ''),
        'balance_before' => $user->colla_balance - $payload['amount'],
        'balance_after' => $user->colla_balance,
      ]);

      Helper::addHistory('Cộng hoa hồng cho ' . $user->username . ' [' . $action . ']', $payload);

      return response()->json([
        'status' => true,
        'message' => 'Cộng hoa hồng cho ' . $user->username . ', số dư cuối : ' . Helper::formatCurrency($user->colla_balance)
      ]);
    }
    else if ($action === 'sub-commision') {
      $payload = $request->validate([
        'amount' => 'required|numeric|min:0',
        'reason' => 'nullable|string|max:255',
      ]);

      $user = User::findOrFail($id);

      $user->colla_balance -= $payload['amount'];
      $user->save();

      CollaTransaction::create([
        'type' => 'boosting',
        'user_id' => $user->id,
        'username' => $user->username,
        'amount' => $payload['amount'],
        'status' => 'Completed',
        'reference' => null,
        'description' => '#' . auth()->id() . ': ' . ($payload['reason'] ?? ''),
        'balance_before' => $user->colla_balance + $payload['amount'],
        'balance_after' => $user->colla_balance,
      ]);

      Helper::addHistory('Trừ hoa hồng cho ' . $user->username . ' [' . $action . ']', $payload);

      return response()->json([
        'status' => true,
        'message' => 'Trừ hoa hồng cho ' . $user->username . ', số dư cuối : ' . Helper::formatCurrency($user->colla_balance)
      ]);
    }
    else {
      return redirect()->back()->with('error', 'Không tìm thấy hành động');
    }
  }
}
