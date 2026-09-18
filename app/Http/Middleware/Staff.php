<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Staff
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (!auth()->check()) {
      return redirect()->route('login');
    }

    $user = auth()->user();

    // Check status
    if ($user->status !== 'active') {
      return abort(401, 'Tài khoản của bạn đã bị khóa hoặc không hoạt động');
    }

    // Role-agnostic check based on colla_type array
    // We use array_map trim to be safe with any whitespace issues
    $collaType = array_map('trim', (array) $user->colla_type);
    
    // Check if user has at least one valid collaborator type OR is an admin or staff (bypass total lockout)
    $hasCategory = count(array_intersect($collaType, ['account', 'boosting', 'items'])) > 0;
    
    if (!in_array($user->role, ['admin', 'staff', 'accounting', 'partner']) && !$hasCategory) {
      return abort(401, 'Bạn không có quyền truy cập trang này');
    }

    // Disable method POST on Demo
    if (env('APP_DEMO', false) && $request->method() === 'POST') {
      return redirect()->back()->with('error', 'Thao tác này bị vô hiệu hoá trên trang DEMO!');
    }

    return $next($request);
  }
}
